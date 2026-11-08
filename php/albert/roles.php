<?php
// php/albert/roles.php  -  CRUD for the `roles` table

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Shared connection file in the repository root.
// NOTE: this code assumes db.php creates a mysqli connection named $conn.
require_once '../../db.php';

$errors  = [];
$message = '';
$edit    = null;

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// ---------- Handle form submissions ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        // ===== DELETE (only if no user has this role) =====
        if ($action === 'delete') {
            $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
            if (!$id) {
                $errors[] = 'Invalid role ID.';
            } else {
                // Safety check: is the role still assigned to users?
                $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role_id = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $count = (int)$stmt->get_result()->fetch_assoc()['total'];
                $stmt->close();

                if ($count > 0) {
                    $errors[] = "Cannot delete this role: it is still assigned to $count user(s). "
                              . "Reassign those users first.";
                } else {
                    $stmt = $conn->prepare("DELETE FROM roles WHERE id = ?");
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                    if ($stmt->affected_rows > 0) {
                        $message = 'Role deleted.';
                    } else {
                        $errors[] = 'Role not found.';
                    }
                    $stmt->close();
                }
            }
        }

        // ===== ADD or UPDATE =====
        if ($action === 'add' || $action === 'update') {
            $id        = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
            $role_name = trim($_POST['role_name'] ?? '');

            if ($role_name === '' || strlen($role_name) > 50) {
                $errors[] = 'Role name is required (up to 50 characters).';
            }
            if ($action === 'update' && !$id) {
                $errors[] = 'Invalid role ID.';
            }

            if (!$errors) {
                if ($action === 'add') {
                    $stmt = $conn->prepare("INSERT INTO roles (role_name) VALUES (?)");
                    $stmt->bind_param('s', $role_name);
                    $stmt->execute();
                    $stmt->close();
                    $message = 'Role added.';
                } else {
                    $stmt = $conn->prepare("UPDATE roles SET role_name = ? WHERE id = ?");
                    $stmt->bind_param('si', $role_name, $id);
                    $stmt->execute();
                    $stmt->close();
                    $message = 'Role updated.';
                }
            } elseif ($action === 'update' && $id) {
                $edit = ['id' => $id, 'role_name' => $role_name];
            }
        }
    } catch (mysqli_sql_exception $ex) {
        if ($ex->getCode() === 1062) {
            $errors[] = 'That role name already exists.';
        } elseif ($ex->getCode() === 1451) {
            $errors[] = 'Cannot delete this role because other records depend on it.';
        } else {
            $errors[] = 'Database error. Please try again.';
        }
    }
}

// ---------- Load a role into the form when ?edit=ID ----------
if ($edit === null && isset($_GET['edit'])) {
    $eid = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($eid) {
        $stmt = $conn->prepare("SELECT id, role_name FROM roles WHERE id = ?");
        $stmt->bind_param('i', $eid);
        $stmt->execute();
        $edit = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$edit) {
            $errors[] = 'Role not found.';
        }
    }
}

// ---------- Load the list with how many users use each role ----------
$roles = $conn->query(
    "SELECT r.id, r.role_name, COUNT(u.id) AS user_count
     FROM roles r
     LEFT JOIN users u ON u.role_id = r.id
     GROUP BY r.id, r.role_name
     ORDER BY r.id"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Roles - Border Entry System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f6f8; color: #222; }
        h1, h2 { margin-bottom: 8px; }
        nav a { margin-right: 12px; }
        .box { background: #fff; padding: 16px; margin-bottom: 20px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,.15); }
        label { display: block; margin-top: 8px; font-weight: bold; }
        input[type=text] { width: 100%; max-width: 320px; padding: 6px; }
        button, .btn { padding: 6px 12px; margin-top: 12px; cursor: pointer; }
        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #2c3e50; color: #fff; }
        .ok  { background: #d4edda; padding: 8px; margin-bottom: 12px; border-radius: 4px; }
        .err { background: #f8d7da; padding: 8px; margin-bottom: 12px; border-radius: 4px; }
        form.inline { display: inline; }
    </style>
</head>
<body>
    <h1>Role Management</h1>
    <nav><a href="users.php">Users</a> <a href="roles.php">Roles</a></nav>

    <?php if ($message): ?><div class="ok"><?= e($message) ?></div><?php endif; ?>
    <?php if ($errors): ?>
        <div class="err"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <div class="box">
        <h2><?= $edit ? 'Edit Role' : 'Add Role' ?></h2>
        <form method="post" action="roles.php">
            <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= e($edit['id']) ?>"><?php endif; ?>

            <label for="role_name">Role Name</label>
            <input type="text" id="role_name" name="role_name" maxlength="50" required
                   value="<?= e($edit['role_name'] ?? '') ?>">

            <button type="submit"><?= $edit ? 'Update Role' : 'Add Role' ?></button>
            <?php if ($edit): ?><a class="btn" href="roles.php">Cancel</a><?php endif; ?>
        </form>
    </div>

    <h2>All Roles</h2>
    <table>
        <tr><th>ID</th><th>Role Name</th><th>Users with this role</th><th>Actions</th></tr>
        <?php if ($roles->num_rows === 0): ?>
            <tr><td colspan="4">No roles yet.</td></tr>
        <?php endif; ?>
        <?php while ($r = $roles->fetch_assoc()): ?>
            <tr>
                <td><?= e($r['id']) ?></td>
                <td><?= e($r['role_name']) ?></td>
                <td><?= e($r['user_count']) ?></td>
                <td>
                    <a href="roles.php?edit=<?= e($r['id']) ?>">Edit</a>
                    <form class="inline" method="post" action="roles.php"
                          onsubmit="return confirm('Delete this role?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
