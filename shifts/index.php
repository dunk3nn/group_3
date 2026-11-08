```php
<?php
require_once '../db.php';

$conn->set_charset("utf8mb4");

$message = "";
$message_type = "";

// ADD, UPDATE, OR DELETE SHIFT
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    if ($action === "save") {

        $id = (int)($_POST["id"] ?? 0);
        $shift_name = trim($_POST["shift_name"] ?? "");
        $start_time = $_POST["start_time"] ?? "";
        $end_time = $_POST["end_time"] ?? "";

        if ($shift_name === "" || $start_time === "" || $end_time === "") {
            $message = "Please complete all fields.";
            $message_type = "error";

        } elseif (strtotime($end_time) === false || strtotime($start_time) === false) {
            $message = "Please enter valid shift times.";
            $message_type = "error";

        } elseif ($id > 0) {

            $stmt = $conn->prepare(
                "UPDATE shifts
                 SET shift_name = ?, start_time = ?, end_time = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "sssi",
                $shift_name,
                $start_time,
                $end_time,
                $id
            );

            if ($stmt->execute()) {
                $message = "Shift updated successfully!";
                $message_type = "success";
            } else {
                $message = "Error updating shift.";
                $message_type = "error";
            }

            $stmt->close();

        } else {

            $stmt = $conn->prepare(
                "INSERT INTO shifts (shift_name, start_time, end_time)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $shift_name,
                $start_time,
                $end_time
            );

            if ($stmt->execute()) {
                $message = "Shift added successfully!";
                $message_type = "success";
            } else {
                $message = "Error adding shift.";
                $message_type = "error";
            }

            $stmt->close();
        }

    } elseif ($action === "delete") {

        $id = (int)($_POST["id"] ?? 0);

        $stmt = $conn->prepare("DELETE FROM shifts WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $message = "Shift deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting shift. It may be assigned to personnel.";
            $message_type = "error";
        }

        $stmt->close();
    }
}

// GET SHIFT FOR EDITING
$edit_shift = null;

if (isset($_GET["edit"])) {

    $id = (int)$_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT * FROM shifts WHERE id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $edit_shift = $result->fetch_assoc();

    $stmt->close();
}

// GET ALL SHIFTS
$shifts = $conn->query(
    "SELECT * FROM shifts ORDER BY id DESC"
);

function escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shifts Management</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 30px;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        h1 {
            color: #17365d;
        }

        .card {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 11px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button, .button {
            display: inline-block;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            margin-top: 15px;
        }

        .save {
            background: #17365d;
            color: white;
        }

        .edit {
            background: #e6a700;
            color: white;
        }

        .delete {
            background: #c0392b;
            color: white;
        }

        .cancel {
            background: #777;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #17365d;
            color: white;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        @media(max-width: 650px) {
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

    <h1>Shifts Management</h1>
    <p>Manage personnel work schedules and shift assignments.</p>

    <div class="card">

        <h2><?= $edit_shift ? "Edit Shift" : "Add New Shift" ?></h2>

        <?php if ($message !== ""): ?>
            <div class="<?= escape($message_type) ?>">
                <?= escape($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input type="hidden" name="action" value="save">

            <input
                type="hidden"
                name="id"
                value="<?= escape($edit_shift["id"] ?? 0) ?>"
            >

            <label>Shift Name</label>
            <input
                type="text"
                name="shift_name"
                maxlength="50"
                placeholder="Example: Morning Shift"
                value="<?= escape($edit_shift["shift_name"] ?? "") ?>"
                required
            >

            <label>Start Time</label>
            <input
                type="time"
                name="start_time"
                value="<?= escape($edit_shift["start_time"] ?? "") ?>"
                required
            >

            <label>End Time</label>
            <input
                type="time"
                name="end_time"
                value="<?= escape($edit_shift["end_time"] ?? "") ?>"
                required
            >

            <button type="submit" class="save">
                <?= $edit_shift ? "Update Shift" : "Add Shift" ?>
            </button>

            <?php if ($edit_shift): ?>
                <a href="index.php" class="button cancel">Cancel</a>
            <?php endif; ?>

        </form>

    </div>

    <div class="card">

        <h2>Shift Records</h2>

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Shift Name</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($shifts && $shifts->num_rows > 0): ?>

                <?php while ($row = $shifts->fetch_assoc()): ?>

                    <tr>
                        <td><?= escape($row["id"]) ?></td>

                        <td><?= escape($row["shift_name"]) ?></td>

                        <td><?= escape(date("h:i A", strtotime($row["start_time"]))) ?></td>

                        <td><?= escape(date("h:i A", strtotime($row["end_time"]))) ?></td>

                        <td>
                            <a
                                href="index.php?edit=<?= escape($row["id"]) ?>"
                                class="button edit"
                            >
                                Edit
                            </a>

                            <form method="POST" style="display:inline;"
                                  onsubmit="return confirm('Are you sure you want to delete this shift?');">

                                <input type="hidden" name="action" value="delete">

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= escape($row["id"]) ?>"
                                >

                                <button type="submit" class="delete">
                                    Delete
                                </button>

                            </form>
                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="5">No shift records found.</td>
                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>

<?php
$conn->close();
?>
```