<?php
require_once __DIR__ . '/../db.php';

$message = '';
$messageType = '';

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// ADD PERSONNEL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_personnel'])) {
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $department_id = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
    $contact_number = trim($_POST['contact_number'] ?? '');

    if ($name === '' || $position === '' || !$department_id || $contact_number === '') {
        $message = 'Please fill in all fields correctly.';
        $messageType = 'error';
    } else {
        $stmt = $conn->prepare(
            'INSERT INTO personnel (name, position, department_id, contact_number)
             VALUES (?, ?, ?, ?)'
        );

        $stmt->bind_param('ssis', $name, $position, $department_id, $contact_number);

        if ($stmt->execute()) {
            $message = 'Personnel added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Could not add personnel. Check that the department exists.';
            $messageType = 'error';
        }

        $stmt->close();
    }
}

// UPDATE PERSONNEL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_personnel'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $department_id = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
    $contact_number = trim($_POST['contact_number'] ?? '');

    if (!$id || $name === '' || $position === '' || !$department_id || $contact_number === '') {
        $message = 'Please provide valid information.';
        $messageType = 'error';
    } else {
        $stmt = $conn->prepare(
            'UPDATE personnel
             SET name = ?, position = ?, department_id = ?, contact_number = ?
             WHERE id = ?'
        );

        $stmt->bind_param('ssisi', $name, $position, $department_id, $contact_number, $id);

        if ($stmt->execute()) {
            $message = 'Personnel updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Unable to update personnel.';
            $messageType = 'error';
        }

        $stmt->close();
    }
}

// DELETE PERSONNEL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_personnel'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($id) {
        $stmt = $conn->prepare('DELETE FROM personnel WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $message = 'Personnel deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Unable to delete personnel. Check whether this person has assignments.';
            $messageType = 'error';
        }

        $stmt->close();
    }
}

// GET PERSONNEL TO EDIT
$editPersonnel = null;

if (isset($_GET['edit'])) {
    $editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);

    if ($editId) {
        $stmt = $conn->prepare('SELECT * FROM personnel WHERE id = ?');
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $editPersonnel = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

// GET DEPARTMENTS
$departments = $conn->query('SELECT * FROM departments ORDER BY department_name ASC');

// GET PERSONNEL RECORDS
$result = $conn->query(
    'SELECT p.*, d.department_name
     FROM personnel p
     LEFT JOIN departments d ON p.department_id = d.id
     ORDER BY p.id ASC'
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Personnel Management</title>

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    padding: 30px 20px;
    font-family: Arial, Helvetica, sans-serif;
    background: #f3f6fa;
    color: #333;
}

.container {
    max-width: 1100px;
    margin: auto;
}

.page-header {
    background: #17365d;
    color: white;
    padding: 28px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.page-header h1 { margin: 0 0 10px; }

.card {
    background: white;
    padding: 25px;
    margin-bottom: 25px;
    border: 1px solid #e1e6ef;
    border-radius: 10px;
}

.card h2 {
    color: #17365d;
    margin-top: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}

.button {
    display: inline-block;
    margin-top: 18px;
    padding: 11px 18px;
    border: none;
    border-radius: 6px;
    color: white;
    cursor: pointer;
    text-decoration: none;
    font-weight: bold;
}

.add { background: #2563eb; }
.edit { background: #d97706; }
.delete { background: #dc2626; }
.cancel { background: #64748b; }

.button:hover { opacity: 0.85; }

.message {
    padding: 14px;
    margin-bottom: 20px;
    border-radius: 6px;
}

.success {
    background: #dcfce7;
    color: #166534;
}

.error {
    background: #fee2e2;
    color: #991b1b;
}

.table-wrapper { overflow-x: auto; }

table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

th, td {
    padding: 13px;
    border-bottom: 1px solid #e5e7eb;
}

th {
    background: #eaf0f8;
    color: #17365d;
}

.footer {
    text-align: center;
    color: #6b7280;
    font-size: 12px;
    padding: 15px;
}

@media(max-width: 600px) {
    .form-grid { grid-template-columns: 1fr; }
}
</style>
</head>

<body>
<div class="container">

<header class="page-header">
    <h1>Personnel Management</h1>
    <p><strong>Border Entry Payment and Personnel Tracking System</strong></p>
    <p>Manage personnel information and registered records.</p>
</header>

<?php if ($message !== ''): ?>
    <div class="message <?= clean($messageType) ?>">
        <?= clean($message) ?>
    </div>
<?php endif; ?>

<section class="card">
    <h2><?= $editPersonnel ? 'Edit Personnel' : 'Add New Personnel' ?></h2>

    <form method="POST">
        <?php if ($editPersonnel): ?>
            <input type="hidden" name="id" value="<?= clean($editPersonnel['id']) ?>">
        <?php endif; ?>

        <div class="form-grid">

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" maxlength="100"
                    value="<?= clean($editPersonnel['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Position</label>
                <input type="text" name="position" maxlength="50"
                    value="<?= clean($editPersonnel['position'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Department</label>
                <select name="department_id" required>
                    <option value="">Select Department</option>

                    <?php if ($departments): ?>
                        <?php while ($department = $departments->fetch_assoc()): ?>
                            <option value="<?= clean($department['id']) ?>"
                                <?= isset($editPersonnel['department_id']) &&
                                $editPersonnel['department_id'] == $department['id']
                                ? 'selected' : '' ?>>
                                <?= clean($department['department_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="contact_number" maxlength="15"
                    value="<?= clean($editPersonnel['contact_number'] ?? '') ?>" required>
            </div>

        </div>

        <?php if ($editPersonnel): ?>
            <button class="button edit" type="submit" name="update_personnel">
                Update Personnel
            </button>
            <a href="index.php" class="button cancel">Cancel</a>
        <?php else: ?>
            <button class="button add" type="submit" name="add_personnel">
                + Add Personnel
            </button>
        <?php endif; ?>
    </form>
</section>

<section class="card">
    <h2>Personnel Records</h2>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Contact Number</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= clean($row['id']) ?></td>
                        <td><?= clean($row['name']) ?></td>
                        <td><?= clean($row['position']) ?></td>
                        <td><?= clean($row['department_name'] ?? 'Unassigned') ?></td>
                        <td><?= clean($row['contact_number']) ?></td>
                        <td>
                            <a class="button edit"
                               href="index.php?edit=<?= clean($row['id']) ?>">
                                Edit
                            </a>

                            <form method="POST" style="display:inline;"
                                  onsubmit="return confirm('Are you sure you want to delete this personnel record?');">
                                <input type="hidden" name="id"
                                       value="<?= clean($row['id']) ?>">
                                <button class="button delete"
                                        type="submit" name="delete_personnel">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;">
                        No personnel records found.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<footer class="footer">
    Border Entry Payment and Personnel Tracking System
</footer>

</div>
</body>
</html>