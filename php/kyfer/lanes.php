<?php
require_once '../../db.php';
require_once 'helpers.php';

$message = '';
$error   = '';
$form    = ['id' => '', 'checkpoint_id' => '', 'lane_number' => ''];

// ---------- Handle Add / Edit / Delete ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id            = (int)($_POST['id'] ?? 0);
        $checkpoint_id = (int)($_POST['checkpoint_id'] ?? 0);
        $lane_raw      = trim($_POST['lane_number'] ?? '');
        $form = ['id' => $id ?: '', 'checkpoint_id' => $checkpoint_id ?: '', 'lane_number' => $lane_raw];

        // Validation
        if ($checkpoint_id <= 0) {
            $error = 'Please choose a checkpoint.';
        } elseif ($lane_raw === '' || !ctype_digit($lane_raw) || (int)$lane_raw < 1) {
            $error = 'Lane number is required and must be a whole number 1 or higher.';
        } else {
            $lane_number = (int)$lane_raw;
            try {
                if ($action === 'add') {
                    $stmt = $conn->prepare("INSERT INTO lanes (checkpoint_id, lane_number) VALUES (?, ?)");
                    $stmt->bind_param("ii", $checkpoint_id, $lane_number);
                    $stmt->execute();
                    $message = 'Lane added.';
                } else {
                    $stmt = $conn->prepare("UPDATE lanes SET checkpoint_id = ?, lane_number = ? WHERE id = ?");
                    $stmt->bind_param("iii", $checkpoint_id, $lane_number, $id);
                    $stmt->execute();
                    $message = 'Lane updated.';
                }
                $stmt->close();
                $form = ['id' => '', 'checkpoint_id' => '', 'lane_number' => ''];
            } catch (mysqli_sql_exception $e) {
                // 1452 = chosen checkpoint doesn't exist (foreign key check failed)
                if ($e->getCode() == 1452) {
                    $error = 'That checkpoint does not exist.';
                } else {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $conn->prepare("DELETE FROM lanes WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $message = 'Lane deleted.';
        } catch (mysqli_sql_exception $e) {
            $error = 'Cannot delete this lane: ' . $e->getMessage();
        }
    }
}

// ---------- ?edit=ID loads a lane into the form ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT id, checkpoint_id, lane_number FROM lanes WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $form = $row;
    } else {
        $error = 'Lane not found.';
    }
}

// Checkpoints for the dropdown
$cp_list = $conn->query("SELECT id, checkpoint_name FROM checkpoints ORDER BY checkpoint_name");

// Lanes with their checkpoint name (JOIN)
$lanes = $conn->query(
    "SELECT l.id, l.lane_number, c.checkpoint_name
     FROM lanes l
     INNER JOIN checkpoints c ON l.checkpoint_id = c.id
     ORDER BY c.checkpoint_name, l.lane_number"
);

page_header('Lanes', 'lanes.php');
?>

<h1>Lanes</h1>

<?php if ($message): ?><div class="success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>

<div class="box">
    <h2><?= $form['id'] ? 'Edit Lane' : 'Add Lane' ?></h2>
    <form method="post">
        <input type="hidden" name="action" value="<?= $form['id'] ? 'edit' : 'add' ?>">
        <input type="hidden" name="id" value="<?= h($form['id']) ?>">

        <label>Checkpoint *</label>
        <select name="checkpoint_id">
            <option value="">-- Choose a checkpoint --</option>
            <?php while ($c = $cp_list->fetch_assoc()): ?>
                <option value="<?= h($c['id']) ?>" <?= ((string)$form['checkpoint_id'] === (string)$c['id']) ? 'selected' : '' ?>>
                    <?= h($c['checkpoint_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Lane Number *</label>
        <input type="number" name="lane_number" min="1" value="<?= h($form['lane_number']) ?>">

        <button type="submit"><?= $form['id'] ? 'Save Changes' : 'Add Lane' ?></button>
        <?php if ($form['id']): ?><a class="btn btn-gray" href="lanes.php">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="box">
    <h2>All Lanes</h2>
    <table>
        <tr><th>ID</th><th>Checkpoint</th><th>Lane Number</th><th>Actions</th></tr>
        <?php while ($r = $lanes->fetch_assoc()): ?>
        <tr>
            <td><?= h($r['id']) ?></td>
            <td><?= h($r['checkpoint_name']) ?></td>
            <td><?= h($r['lane_number']) ?></td>
            <td>
                <a class="btn" href="lanes.php?edit=<?= h($r['id']) ?>">Edit</a>
                <form class="inline" method="post" onsubmit="return confirm('Delete this lane?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= h($r['id']) ?>">
                    <button class="btn-red" type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php page_footer(); ?>