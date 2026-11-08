<?php
// alerts.php - Create, view, edit, mark read/unread, and delete alerts.
require_once '../../db.php';   // shared database connection ($conn)
require_once 'helpers.php';    // h(), log_action(), page_header(), page_footer()

$conn->set_charset("utf8mb4");

$messages = [
    'added'   => 'Alert created successfully.',
    'updated' => 'Alert updated successfully.',
    'status'  => 'Alert status updated.',
    'deleted' => 'Alert deleted successfully.',
];
$success = $messages[$_GET['msg'] ?? ''] ?? '';
$error   = '';

// Values shown in the form (empty for "add", filled for "edit")
$form = [
    'id' => 0, 'violations_id' => null, 'checkpoints_id' => null,
    'alert_type' => '', 'message' => '', 'is_read' => 0,
];

// Checks that a row with this id exists in a table (table name is written by us, not the user)
function row_exists($conn, $table, $id) {
    $stmt = $conn->prepare("SELECT id FROM $table WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $found = $stmt->num_rows > 0;
    $stmt->close();
    return $found;
}

// ---------- HANDLE FORM SUBMITS (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ----- SAVE (create or edit) -----
    if ($action === 'save') {
        $vraw = $_POST['violations_id'] ?? '';
        $craw = $_POST['checkpoints_id'] ?? '';

        $form['id']             = (int)($_POST['id'] ?? 0);
        $form['violations_id']  = ($vraw === '') ? null : (int)$vraw;   // optional
        $form['checkpoints_id'] = ($craw === '') ? null : (int)$craw;   // optional
        $form['alert_type']     = trim($_POST['alert_type'] ?? '');
        $form['message']        = trim($_POST['message'] ?? '');
        $form['is_read']        = (($_POST['is_read'] ?? '0') === '1') ? 1 : 0;

        // Validation
        if ($form['alert_type'] === '') {
            $error = 'Alert type is required.';
        } elseif (strlen($form['alert_type']) > 100) {
            $error = 'Alert type must be 100 characters or less.';
        } elseif ($form['message'] === '') {
            $error = 'Message is required.';
        } elseif ($form['violations_id'] !== null && !row_exists($conn, 'violations', $form['violations_id'])) {
            $error = 'That violation does not exist.';
        } elseif ($form['checkpoints_id'] !== null && !row_exists($conn, 'checkpoints', $form['checkpoints_id'])) {
            $error = 'That checkpoint does not exist.';
        } elseif ($form['id'] > 0) {
            // EDIT
            $stmt = $conn->prepare(
                "UPDATE alerts
                 SET violations_id = ?, checkpoints_id = ?, alert_type = ?, message = ?, is_read = ?
                 WHERE id = ?"
            );
            $stmt->bind_param("iissii", $form['violations_id'], $form['checkpoints_id'],
                $form['alert_type'], $form['message'], $form['is_read'], $form['id']);
            $stmt->execute();
            $stmt->close();
            log_action($conn, 'UPDATE', 'alerts', $form['id'], 'Updated alert: ' . $form['alert_type']);
            header('Location: alerts.php?msg=updated');
            exit;
        } else {
            // CREATE
            $stmt = $conn->prepare(
                "INSERT INTO alerts (violations_id, checkpoints_id, alert_type, message, is_read)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("iissi", $form['violations_id'], $form['checkpoints_id'],
                $form['alert_type'], $form['message'], $form['is_read']);
            $stmt->execute();
            $new_id = $stmt->insert_id;
            $stmt->close();
            log_action($conn, 'CREATE', 'alerts', $new_id, 'Created alert: ' . $form['alert_type']);
            header('Location: alerts.php?msg=added');
            exit;
        }
    }

    // ----- MARK READ / UNREAD -----
    if ($action === 'toggle') {
        $id     = (int)($_POST['id'] ?? 0);
        $new    = (($_POST['new_status'] ?? '0') === '1') ? 1 : 0;
        if ($id > 0 && row_exists($conn, 'alerts', $id)) {
            $stmt = $conn->prepare("UPDATE alerts SET is_read = ? WHERE id = ?");
            $stmt->bind_param("ii", $new, $id);
            $stmt->execute();
            $stmt->close();
            log_action($conn, 'UPDATE', 'alerts', $id, 'Marked alert as ' . ($new ? 'read' : 'unread'));
            header('Location: alerts.php?msg=status');
            exit;
        }
        $error = 'Alert not found.';
    }

    // ----- DELETE -----
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0 && row_exists($conn, 'alerts', $id)) {
            $stmt = $conn->prepare("DELETE FROM alerts WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            log_action($conn, 'DELETE', 'alerts', $id, 'Deleted alert #' . $id);
            header('Location: alerts.php?msg=deleted');
            exit;
        }
        $error = 'Alert not found.';
    }
}

// ---------- EDIT MODE: load a row into the form when ?edit=ID ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare(
        "SELECT id, violations_id, checkpoints_id, alert_type, message, is_read
         FROM alerts WHERE id = ?"
    );
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) { $form = $row; } else { $error = 'Alert not found.'; }
}

// ---------- DATA FOR THE PAGE ----------
$violation_list  = $conn->query("SELECT id, violation_type FROM violations ORDER BY id DESC");
$checkpoint_list = $conn->query("SELECT id, checkpoint_name FROM checkpoints ORDER BY id");

$alerts = $conn->query(
    "SELECT a.id, a.alert_type, a.message, a.is_read, a.created_at,
            v.id AS violation_id, v.violation_type,
            c.checkpoint_name
     FROM alerts a
     LEFT JOIN violations v ON a.violations_id = v.id
     LEFT JOIN checkpoints c ON a.checkpoints_id = c.id
     ORDER BY a.id DESC"
);

page_header('Alerts', 'alerts.php');
?>

<h1>Alerts</h1>

<?php if ($success): ?><div class="success"><?= h($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>

<div class="box">
    <h2><?= $form['id'] > 0 ? 'Edit Alert #' . (int)$form['id'] : 'Create Alert' ?></h2>
    <form method="post" action="alerts.php">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int)$form['id'] ?>">

        <label>Alert Type *</label>
        <input type="text" name="alert_type" maxlength="100" value="<?= h($form['alert_type']) ?>" placeholder="e.g. High severity">

        <label>Message *</label>
        <textarea name="message" rows="3"><?= h($form['message']) ?></textarea>

        <label>Related Violation (optional)</label>
        <select name="violations_id">
            <option value="">-- None --</option>
            <?php while ($v = $violation_list->fetch_assoc()): ?>
                <option value="<?= (int)$v['id'] ?>" <?= (int)$form['violations_id'] === (int)$v['id'] ? 'selected' : '' ?>>
                    Violation #<?= (int)$v['id'] ?> - <?= h($v['violation_type']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Checkpoint (optional)</label>
        <select name="checkpoints_id">
            <option value="">-- None --</option>
            <?php while ($c = $checkpoint_list->fetch_assoc()): ?>
                <option value="<?= (int)$c['id'] ?>" <?= (int)$form['checkpoints_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                    <?= h($c['checkpoint_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Status</label>
        <select name="is_read">
            <option value="0" <?= (int)$form['is_read'] === 0 ? 'selected' : '' ?>>Unread</option>
            <option value="1" <?= (int)$form['is_read'] === 1 ? 'selected' : '' ?>>Read</option>
        </select>

        <button type="submit"><?= $form['id'] > 0 ? 'Update Alert' : 'Create Alert' ?></button>
        <?php if ($form['id'] > 0): ?>
            <a class="btn btn-gray" href="alerts.php">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="box">
    <h2>All Alerts</h2>
    <table>
        <tr>
            <th>ID</th><th>Type</th><th>Message</th><th>Violation</th><th>Checkpoint</th>
            <th>Status</th><th>Created</th><th>Actions</th>
        </tr>
        <?php if ($alerts->num_rows === 0): ?>
            <tr><td colspan="8">No alerts yet.</td></tr>
        <?php endif; ?>
        <?php while ($a = $alerts->fetch_assoc()): ?>
        <tr>
            <td><?= (int)$a['id'] ?></td>
            <td><?= h($a['alert_type']) ?></td>
            <td><?= h($a['message']) ?></td>
            <td><?= $a['violation_id'] ? '#' . (int)$a['violation_id'] . ' - ' . h($a['violation_type']) : '-' ?></td>
            <td><?= $a['checkpoint_name'] ? h($a['checkpoint_name']) : '-' ?></td>
            <td><?= $a['is_read'] ? 'Read' : '<strong>Unread</strong>' ?></td>
            <td><?= h($a['created_at']) ?></td>
            <td>
                <a class="btn" href="alerts.php?edit=<?= (int)$a['id'] ?>">Edit</a>

                <form class="inline" method="post" action="alerts.php">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                    <input type="hidden" name="new_status" value="<?= $a['is_read'] ? 0 : 1 ?>">
                    <button type="submit" class="btn-gray"><?= $a['is_read'] ? 'Mark Unread' : 'Mark Read' ?></button>
                </form>

                <form class="inline" method="post" action="alerts.php"
                      onsubmit="return confirm('Delete alert #<?= (int)$a['id'] ?>?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                    <button type="submit" class="btn-red">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php page_footer(); ?>
