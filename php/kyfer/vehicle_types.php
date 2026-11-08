<?php
require_once '../../db.php';
require_once 'helpers.php';

$message = '';
$error   = '';
$form    = ['id' => '', 'type_name' => '', 'base_fee' => ''];

// ---------- Handle Add / Edit / Delete ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id       = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['type_name'] ?? '');
        $fee_raw  = trim($_POST['base_fee'] ?? '');
        $form = ['id' => $id ?: '', 'type_name' => $name, 'base_fee' => $fee_raw];

        // Validation
        if ($name === '') {
            $error = 'Vehicle type name is required.';
        } elseif (strlen($name) > 50) {
            $error = 'Vehicle type name must be 50 characters or less.';
        } elseif ($fee_raw === '') {
            $error = 'Base fee is required.';
        } elseif (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $fee_raw)) {
            $error = 'Base fee must be a number 0 or higher, with up to 2 decimal places.';
        } else {
            $fee = (float)$fee_raw;
            try {
                if ($action === 'add') {
                    $stmt = $conn->prepare("INSERT INTO vehicle_types (type_name, base_fee) VALUES (?, ?)");
                    $stmt->bind_param("sd", $name, $fee);
                    $stmt->execute();
                    $message = 'Vehicle type added.';
                } else {
                    $stmt = $conn->prepare("UPDATE vehicle_types SET type_name = ?, base_fee = ? WHERE id = ?");
                    $stmt->bind_param("sdi", $name, $fee, $id);
                    $stmt->execute();
                    $message = 'Vehicle type updated.';
                }
                $stmt->close();
                $form = ['id' => '', 'type_name' => '', 'base_fee' => ''];
            } catch (mysqli_sql_exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $conn->prepare("DELETE FROM vehicle_types WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $message = 'Vehicle type deleted.';
        } catch (mysqli_sql_exception $e) {
            // 1451 = another table (like fee_rules later) still uses this vehicle type
            if ($e->getCode() == 1451) {
                $error = 'Cannot delete: this vehicle type is still used by another table.';
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// ---------- ?edit=ID loads a row into the form ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT id, type_name, base_fee FROM vehicle_types WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $form = $row;
    } else {
        $error = 'Vehicle type not found.';
    }
}

$types = $conn->query("SELECT id, type_name, base_fee FROM vehicle_types ORDER BY id");

page_header('Vehicle Types', 'vehicle_types.php');
?>

<h1>Vehicle Types</h1>

<?php if ($message): ?><div class="success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>

<div class="box">
    <h2><?= $form['id'] ? 'Edit Vehicle Type' : 'Add Vehicle Type' ?></h2>
    <form method="post">
        <input type="hidden" name="action" value="<?= $form['id'] ? 'edit' : 'add' ?>">
        <input type="hidden" name="id" value="<?= h($form['id']) ?>">

        <label>Type Name *</label>
        <input type="text" name="type_name" maxlength="50" value="<?= h($form['type_name']) ?>">

        <label>Base Fee *</label>
        <input type="number" name="base_fee" min="0" step="0.01" value="<?= h($form['base_fee']) ?>">

        <button type="submit"><?= $form['id'] ? 'Save Changes' : 'Add Vehicle Type' ?></button>
        <?php if ($form['id']): ?><a class="btn btn-gray" href="vehicle_types.php">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="box">
    <h2>All Vehicle Types</h2>
    <table>
        <tr><th>ID</th><th>Type Name</th><th>Base Fee</th><th>Actions</th></tr>
        <?php while ($r = $types->fetch_assoc()): ?>
        <tr>
            <td><?= h($r['id']) ?></td>
            <td><?= h($r['type_name']) ?></td>
            <td><?= h(number_format((float)$r['base_fee'], 2)) ?></td>
            <td>
                <a class="btn" href="vehicle_types.php?edit=<?= h($r['id']) ?>">Edit</a>
                <form class="inline" method="post" onsubmit="return confirm('Delete this vehicle type?');">
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