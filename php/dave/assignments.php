<?php
require_once '../../db.php';

$conn->set_charset("utf8mb4");

$message = "";
$message_type = "success";

// ADD OR UPDATE ASSIGNMENT
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $personnel_id = (int)($_POST["personnel_id"] ?? 0);
    $shift_id = (int)($_POST["shift_id"] ?? 0);
    $checkpoint_id = (int)($_POST["checkpoint_id"] ?? 0);
    $assignment_date = $_POST["assignment_date"] ?? "";

    if ($action === "save") {
        $id = (int)($_POST["id"] ?? 0);

        if ($personnel_id <= 0 || $shift_id <= 0 ||
            $checkpoint_id <= 0 || empty($assignment_date)) {
            $message = "Please complete all required fields.";
            $message_type = "error";
        } else {
            if ($id > 0) {
                $stmt = $conn->prepare(
                    "UPDATE checkpoint_assignments
                     SET personnel_id = ?, shift_id = ?,
                         checkpoint_id = ?, assignment_date = ?
                     WHERE id = ?"
                );
                $stmt->bind_param(
                    "iiisi",
                    $personnel_id,
                    $shift_id,
                    $checkpoint_id,
                    $assignment_date,
                    $id
                );
                $success_message = "Assignment updated successfully.";
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO checkpoint_assignments
                     (personnel_id, shift_id, checkpoint_id, assignment_date)
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param(
                    "iiis",
                    $personnel_id,
                    $shift_id,
                    $checkpoint_id,
                    $assignment_date
                );
                $success_message = "Assignment added successfully.";
            }

            if ($stmt->execute()) {
                $message = $success_message;
            } else {
                $message = "Error saving assignment: " . $stmt->error;
                $message_type = "error";
            }

            $stmt->close();
        }
    }

    // DELETE ASSIGNMENT
    if ($action === "delete") {
        $id = (int)($_POST["id"] ?? 0);

        $stmt = $conn->prepare(
            "DELETE FROM checkpoint_assignments WHERE id = ?"
        );
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $message = "Assignment deleted successfully.";
        } else {
            $message = "Error deleting assignment: " . $stmt->error;
            $message_type = "error";
        }

        $stmt->close();
    }
}

// GET ASSIGNMENT FOR EDITING
$edit_assignment = null;

if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT * FROM checkpoint_assignments WHERE id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $edit_assignment = $result->fetch_assoc();

    $stmt->close();
}

// GET PERSONNEL
$personnel_result = $conn->query(
    "SELECT id, name FROM personnel ORDER BY name"
);

// GET SHIFTS
$shifts_result = $conn->query(
    "SELECT id, shift_name FROM shifts ORDER BY shift_name"
);

// GET CHECKPOINTS
$checkpoints_result = $conn->query(
    "SELECT id, checkpoint_name FROM checkpoints ORDER BY checkpoint_name"
);

// GET ASSIGNMENTS WITH JOIN
$assignments_result = $conn->query(
    "SELECT
        ca.id,
        ca.assignment_date,
        p.name AS personnel_name,
        s.shift_name,
        c.checkpoint_name
     FROM checkpoint_assignments ca
     INNER JOIN personnel p ON ca.personnel_id = p.id
     INNER JOIN shifts s ON ca.shift_id = s.id
     INNER JOIN checkpoints c ON ca.checkpoint_id = c.id
     ORDER BY ca.assignment_date DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkpoint Assignment Management</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        h1, h2 {
            color: #263445;
        }

        .card {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            box-sizing: border-box;
        }

        button, .button {
            display: inline-block;
            padding: 9px 15px;
            margin-top: 15px;
            border: none;
            border-radius: 4px;
            color: white;
            background: #2463a6;
            cursor: pointer;
            text-decoration: none;
        }

        .delete {
            background: #c0392b;
        }

        .cancel {
            background: #777;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #263f5b;
            color: white;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            background: #d4edda;
            color: #155724;
            border-radius: 5px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .actions form {
            margin: 0;
        }

        @media (max-width: 700px) {
            body {
                padding: 12px;
            }

            .card {
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <h1>Checkpoint Assignment Management</h1>
    <p>Manage personnel, shifts, checkpoints, and assignment dates.</p>

    <?php if ($message !== ""): ?>
        <div class="message <?= $message_type === 'error' ? 'error' : '' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2><?= $edit_assignment ? "Edit Assignment" : "Add New Assignment" ?></h2>

        <form method="POST">
            <input type="hidden" name="action" value="save">

            <input
                type="hidden"
                name="id"
                value="<?= htmlspecialchars($edit_assignment['id'] ?? '') ?>"
            >

            <label>Personnel</label>
            <select name="personnel_id" required>
                <option value="">Select Personnel</option>

                <?php while ($person = $personnel_result->fetch_assoc()): ?>
                    <option
                        value="<?= $person['id'] ?>"
                        <?= (isset($edit_assignment['personnel_id']) &&
                            $edit_assignment['personnel_id'] == $person['id'])
                            ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($person['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Shift</label>
            <select name="shift_id" required>
                <option value="">Select Shift</option>

                <?php while ($shift = $shifts_result->fetch_assoc()): ?>
                    <option
                        value="<?= $shift['id'] ?>"
                        <?= (isset($edit_assignment['shift_id']) &&
                            $edit_assignment['shift_id'] == $shift['id'])
                            ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($shift['shift_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Checkpoint</label>
            <select name="checkpoint_id" required>
                <option value="">Select Checkpoint</option>

                <?php while ($checkpoint = $checkpoints_result->fetch_assoc()): ?>
                    <option
                        value="<?= $checkpoint['id'] ?>"
                        <?= (isset($edit_assignment['checkpoint_id']) &&
                            $edit_assignment['checkpoint_id'] == $checkpoint['id'])
                            ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($checkpoint['checkpoint_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Assignment Date</label>
            <input
                type="date"
                name="assignment_date"
                value="<?= htmlspecialchars($edit_assignment['assignment_date'] ?? '') ?>"
                required
            >

            <button type="submit">
                <?= $edit_assignment ? "Update Assignment" : "Add Assignment" ?>
            </button>

            <?php if ($edit_assignment): ?>
                <a href="assignments.php" class="button cancel">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2>Assignment Records</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Personnel</th>
                    <th>Shift</th>
                    <th>Checkpoint</th>
                    <th>Assignment Date</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($assignments_result && $assignments_result->num_rows > 0): ?>
                    <?php while ($row = $assignments_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['personnel_name']) ?></td>
                            <td><?= htmlspecialchars($row['shift_name']) ?></td>
                            <td><?= htmlspecialchars($row['checkpoint_name']) ?></td>
                            <td><?= htmlspecialchars($row['assignment_date']) ?></td>

                            <td>
                                <div class="actions">
                                    <a
                                        class="button"
                                        href="assignments.php?edit=<?= $row['id'] ?>"
                                    >
                                        Edit
                                    </a>

                                    <form method="POST"
                                          onsubmit="return confirm('Delete this assignment?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No assignment records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>