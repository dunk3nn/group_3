<?php
// violations.php - Add, view, edit and delete violations.
require_once '../../db.php';   // shared database connection ($conn)
require_once 'helpers.php';    // h(), log_action(), page_header(), page_footer()

$conn->set_charset("utf8mb4");

$allowed_severity = ['low', 'medium', 'high'];
$allowed_status   = ['open', 'resolved', 'dismissed'];

// Messages shown after a successful action (we redirect with ?msg=...)
$messages = [
    'added'   => 'Violation added successfully.',
    'updated' => 'Violation updated successfully.',
    'deleted' => 'Violation deleted successfully.',
];
$success = $messages[$_GET['msg'] ?? ''] ?? '';
$error   = '';

// Values shown in the form (empty for "add", filled for "edit")
$form = [
    'id' => 0, 'entry_records_id' => '', 'violation_type' => '',
    'description' => '', 'severity' => 'low', 'status' => 'open',
];

// ---------- HANDLE FORM SUBMITS (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ----- SAVE (add or edit) -----
    if ($action === 'save') {
        $form['id']               = (int)($_POST['id'] ?? 0);
        $form['entry_records_id'] = (int)($_POST['entry_records_id'] ?? 0);
        $form['violation_type']   = trim($_POST['violation_type'] ?? '');
        $form['description']      = trim($_POST['description'] ?? '');
        $form['severity']         = $_POST['severity'] ?? '';
        $form['status']           = $_POST['status'] ?? '';

        // Validation: check everything BEFORE touching the database
        if ($form['entry_records_id'] <= 0) {
            $error = 'Please choose an entry record.';
        } elseif ($form['violation_type'] === '') {
            $error = 'Violation type is required.';
        } elseif (strlen($form['violation_type']) > 100) {
            $error = 'Violation type must be 100 characters or less.';
        } elseif (!in_array($form['severity'], $allowed_severity, true)) {
            $error = 'Please choose a valid severity.';
        } elseif (!in_array($form['status'], $allowed_status, true)) {
            $error = 'Please choose a valid status.';
        } else {
            // Make sure the chosen entry record really exists
            $stmt = $conn->prepare("SELECT id FROM entry_records WHERE id = ?");
            $stmt->bind_param("i", $form['entry_records_id']);
            $stmt->execute();
            $stmt->store_result();
            $entry_exists = $stmt->num_rows > 0;
            $stmt->close();

            if (!$entry_exists) {
                $error = 'That entry record does not exist.';
            } elseif ($form['id'] > 0) {
                // EDIT: update an existing row
                $stmt = $conn->prepare(
                    "UPDATE violations
                     SET entry_records_id = ?, violation_type = ?, description = ?, severity = ?, status = ?
                     WHERE id = ?"
                );
                $stmt->bind_param("issssi", $form['entry_records_id'], $form['violation_type'],
                    $form['description'], $form['severity'], $form['status'], $form['id']);
                $stmt->execute();
                $stmt->close();
                log_action($conn, 'UPDATE', 'violations', $form['id'],
                    'Updated violation: ' . $form['violation_type'] . ' (status: ' . $form['status'] . ')');
                header('Location: violations.php?msg=updated');
                exit;
            } else {
                // ADD: insert a new row
                $stmt = $conn->prepare(
                    "INSERT INTO violations (entry_records_id, violation_type, description, severity, status)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->bind_param("issss", $form['entry_records_id'], $form['violation_type'],
                    $form['description'], $form['severity'], $form['status']);
                $stmt->execute();
                $new_id = $stmt->insert_id;
                $stmt->close();
                log_action($conn, 'CREATE', 'violations', $new_id,
                    'Added violation: ' . $form['violation_type']);
                header('Location: violations.php?msg=added');
                exit;
            }
        }
    }

    // ----- DELETE -----
    if ($action === 'delete') {
        $delete_id = (int)($_POST['id'] ?? 0);

        // Rule: a violation that has alerts attached cannot be deleted
        // (alerts.violations_id points to it with a foreign key).
        $stmt = $conn->prepare("SELECT COUNT(*) FROM alerts WHERE violations_id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->bind_result($alert_count);
        $stmt->fetch();
        $stmt->close();

        if ($delete_id <= 0) {
            $error = 'Invalid violation.';
        } elseif ($alert_count > 0) {
            $error = "Cannot delete violation #$delete_id because it has $alert_count alert(s). Delete those alerts first.";
        } else {
            $stmt = $conn->prepare("DELETE FROM violations WHERE id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();
            log_action($conn, 'DELETE', 'violations', $delete_id, 'Deleted violation #' . $delete_id);
            header('Location: violations.php?msg=deleted');
            exit;
        }
    }
}

// ---------- EDIT MODE: load a row into the form when ?edit=ID ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare(
        "SELECT id, entry_records_id, violation_type, description, severity, status
         FROM violations WHERE id = ?"
    );
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $form = $row;
    } else {
        $error = 'Violation not found.';
    }
}

// ---------- DATA FOR THE PAGE ----------
// Entry records for the dropdown
$entries = $conn->query("SELECT id, entry_date, purpose FROM entry_records ORDER BY id DESC");

// All violations + their entry (LEFT JOIN keeps a violation even if no entry is found)
$violations = $conn->query(
    "SELECT v.id, v.violation_type, v.description, v.severity, v.status, v.recorded_at,
            e.id AS entry_id, e.entry_date, e.purpose
     FROM violations v
     LEFT JOIN entry_records e ON v.entry_records_id = e.id
     ORDER BY v.id DESC"
);

page_header('Violations', 'violations.php');
?>

<h1>Violations</h1>

<?php if ($success): ?><div class="success"><?= h($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>

<div class="box">
    <h2><?= $form['id'] > 0 ? 'Edit Violation #' . (int)$form['id'] : 'Add Violation' ?></h2>
    <form method="post" action="violations.php">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int)$form['id'] ?>">

        <label>Entry Record *</label>
        <select name="entry_records_id">
            <option value="">-- Choose an entry record --</option>
            <?php while ($e = $entries->fetch_assoc()): ?>
                <option value="<?= (int)$e['id'] ?>" <?= (int)$form['entry_records_id'] === (int)$e['id'] ? 'selected' : '' ?>>
                    Entry #<?= (int)$e['id'] ?> - <?= h($e['entry_date']) ?> - <?= h($e['purpose']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Violation Type *</label>
        <input type="text" name="violation_type" maxlength="100" value="<?= h($form['violation_type']) ?>" placeholder="e.g. Overstay">

        <label>Description</label>
        <textarea name="description" rows="3"><?= h($form['description']) ?></textarea>

        <label>Severity *</label>
        <select name="severity">
            <?php foreach ($allowed_severity as $s): ?>
                <option value="<?= $s ?>" <?= $form['severity'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Status *</label>
        <select name="status">
            <?php foreach ($allowed_status as $s): ?>
                <option value="<?= $s ?>" <?= $form['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit"><?= $form['id'] > 0 ? 'Update Violation' : 'Add Violation' ?></button>
        <?php if ($form['id'] > 0): ?>
            <a class="btn btn-gray" href="violations.php">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="box">
    <h2>All Violations</h2>
    <table>
        <tr>
            <th>ID</th><th>Type</th><th>Description</th><th>Severity</th><th>Status</th>
            <th>Entry Record</th><th>Recorded At</th><th>Actions</th>
        </tr>
        <?php if ($violations->num_rows === 0): ?>
            <tr><td colspan="8">No violations yet.</td></tr>
        <?php endif; ?>
        <?php while ($v = $violations->fetch_assoc()): ?>
        <tr>
            <td><?= (int)$v['id'] ?></td>
            <td><?= h($v['violation_type']) ?></td>
            <td><?= h($v['description']) ?></td>
            <td><?= h(ucfirst($v['severity'])) ?></td>
            <td><?= h(ucfirst($v['status'])) ?></td>
            <td>
                <?php if ($v['entry_id']): ?>
                    #<?= (int)$v['entry_id'] ?> - <?= h($v['entry_date']) ?> (<?= h($v['purpose']) ?>)
                <?php else: ?>
                    No entry record
                <?php endif; ?>
            </td>
            <td><?= h($v['recorded_at']) ?></td>
            <td>
                <a class="btn" href="violations.php?edit=<?= (int)$v['id'] ?>">Edit</a>
                <form class="inline" method="post" action="violations.php"
                      onsubmit="return confirm('Delete violation #<?= (int)$v['id'] ?>?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
                    <button type="submit" class="btn-red">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php page_footer(); ?>
