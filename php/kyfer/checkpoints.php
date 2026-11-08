<?php
require_once '../../db.php';   // shared database connection ($conn)
require_once 'helpers.php';    // h(), page_header(), page_footer()

$message = '';
$error   = '';
$form    = ['id' => '', 'checkpoint_name' => '', 'location' => ''];

// ---------- Handle form submissions (Add / Edit / Delete) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id       = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['checkpoint_name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $form     = ['id' => $id ?: '', 'checkpoint_name' => $name, 'location' => $location];

        // Validation
        if ($name === '') {
            $error = 'Checkpoint name is required.';
        } elseif (strlen($name) > 100 || strlen($location) > 100) {
            $error = 'Name and location must be 100 characters or less.';
        } else {
            $location = ($location === '') ? null : $location;  // location may be empty (NULL)
            try {
                if ($action === 'add') {
                    $stmt = $conn->prepare("INSERT INTO checkpoints (checkpoint_name, location) VALUES (?, ?)");
                    $stmt->bind_param("ss", $name, $location);
                    $stmt->execute();
                    $message = 'Checkpoint added.';
                } else {
                    $stmt = $conn->prepare("UPDATE checkpoints SET checkpoint_name = ?, location = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $name, $location, $id);
                    $stmt->execute();
                    $message = 'Checkpoint updated.';
                }
                $stmt->close();
                $form = ['id' => '', 'checkpoint_name' => '', 'location' => ''];  // clear the form
            } catch (mysqli_sql_exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $conn->prepare("DELETE FROM checkpoints WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $message = 'Checkpoint deleted.';
        } catch (mysqli_sql_exception $e) {
            // 1451 = a foreign key (lanes.checkpoint_id) still points to this checkpoint
            if ($e->getCode() == 1451) {
                $error = 'Cannot delete: this checkpoint still has lanes. Delete its lanes first.';
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// ---------- If ?edit=ID is in the URL, load that row into the form ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT id, checkpoint_name, location FROM checkpoints WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $form = $row;
    } else {
        $error = 'Checkpoint not found.';
    }
}

// ---------- Get all checkpoints to display ----------
$checkpoints = $conn->query("SELECT id, checkpoint_name, location FROM checkpoints ORDER BY id");

page_header('Checkpoints', 'checkpoints.php');
?>

<h1>Checkpoints</h1>

<?php if ($message): ?><div class="success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>

<div class="box">
    <h2><?= $form['id'] ? 'Edit Checkpoint' : 'Add Checkpoint' ?></h2>
    <form method="post">
        <input type="hidden" name="action" value="<?= $form['id'] ? 'edit' : 'add' ?>">
        <input type="hidden" name="id" value="<?= h($form['id']) ?>">

        <label>Checkpoint Name *</label>
        <input type="text" name="checkpoint_name" maxlength="100" value="<?= h($form['checkpoint_name']) ?>">

        <label>Location</label>
        <input type="text" name="location" maxlength="100" value="<?= h($form['location']) ?>">

        <button type="submit"><?= $form['id'] ? 'Save Changes' : 'Add Checkpoint' ?></button>
        <?php if ($form['id']): ?><a class="btn btn-gray" href="checkpoints.php">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="box">
    <h2>All Checkpoints</h2>
    <table>
        <tr><th>ID</th><th>Name</th><th>Location</th><th>Actions</th></tr>
        <?php while ($r = $checkpoints->fetch_assoc()): ?>
        <tr>
            <td><?= h($r['id']) ?></td>
            <td><?= h($r['checkpoint_name']) ?></td>
            <td><?= h($r['location']) ?></td>
            <td>
                <a class="btn" href="checkpoints.php?edit=<?= h($r['id']) ?>">Edit</a>
                <form class="inline" method="post" onsubmit="return confirm('Delete this checkpoint?');">
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