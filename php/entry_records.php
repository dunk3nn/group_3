
<?php
require_once '../db.php';

$conn->set_charset("utf8mb4");
$message = "";

// ADD OR UPDATE ENTRY RECORD
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "save") {
        $id = (int)($_POST["id"] ?? 0);
        $traveler_id = (int)($_POST["traveler_id"] ?? 0);
        $vehicle_id = (int)($_POST["vehicle_id"] ?? 0);

        $checkpoint_value = $_POST["checkpoint_id"] ?? "";
        $checkpoint_id = ($checkpoint_value === "")
            ? null : (int)$checkpoint_value;

        $entry_date = $_POST["entry_date"] ?? "";
        $entry_time = $_POST["entry_time"] ?? "";
        $purpose = trim($_POST["purpose"] ?? "");

        if ($traveler_id <= 0 || $vehicle_id <= 0 ||
            $entry_date === "" || $entry_time === "" ||
            $purpose === "") {
            $message = "Please complete all required fields.";
        } else {
            if ($id > 0) {
                $stmt = $conn->prepare(
                    "UPDATE entry_records
                     SET traveler_id=?, vehicle_id=?, checkpoint_id=?,
                         entry_date=?, entry_time=?, purpose=?
                     WHERE id=?"
                );

                $stmt->bind_param(
                    "iiisssi",
                    $traveler_id,
                    $vehicle_id,
                    $checkpoint_id,
                    $entry_date,
                    $entry_time,
                    $purpose,
                    $id
                );
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO entry_records
                     (traveler_id, vehicle_id, checkpoint_id,
                      entry_date, entry_time, purpose)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "iiisss",
                    $traveler_id,
                    $vehicle_id,
                    $checkpoint_id,
                    $entry_date,
                    $entry_time,
                    $purpose
                );
            }

            $stmt->execute();
            $stmt->close();

            $message = ($id > 0)
                ? "Entry record updated successfully!"
                : "Entry record added successfully!";
        }
    }

    // DELETE ENTRY RECORD
    if ($action === "delete") {
        $id = (int)($_POST["id"] ?? 0);

        $stmt = $conn->prepare(
            "DELETE FROM entry_records WHERE id=?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $message = "Entry record deleted successfully!";
    }
}

// GET RECORD FOR EDITING
$edit_record = null;

if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT * FROM entry_records WHERE id=?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $edit_record = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// GET TRAVELERS
$travelers_result = $conn->query(
    "SELECT id, first_name, last_name
     FROM travelers ORDER BY first_name"
);
$travelers = $travelers_result->fetch_all(MYSQLI_ASSOC);

// GET VEHICLES
$vehicles_result = $conn->query(
    "SELECT id, traveler_id, vehicle_name, plate_number
     FROM vehicles ORDER BY vehicle_name"
);
$vehicles = $vehicles_result->fetch_all(MYSQLI_ASSOC);

// GET CHECKPOINTS
$checkpoints_result = $conn->query(
    "SELECT id, checkpoint_name FROM checkpoints
     ORDER BY checkpoint_name"
);
$checkpoints = $checkpoints_result->fetch_all(MYSQLI_ASSOC);

// GET ENTRY RECORDS WITH RELATED INFORMATION
$records = $conn->query(
    "SELECT
        e.id,
        e.traveler_id,
        e.vehicle_id,
        e.checkpoint_id,
        e.entry_date,
        e.entry_time,
        e.purpose,
        t.first_name,
        t.last_name,
        v.vehicle_name,
        v.plate_number,
        c.checkpoint_name
     FROM entry_records e
     INNER JOIN travelers t ON e.traveler_id = t.id
     INNER JOIN vehicles v ON e.vehicle_id = v.id
     LEFT JOIN checkpoints c ON e.checkpoint_id = c.id
     ORDER BY e.id DESC"
);

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Entry Records Management</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        h1, h2 {
            color: #26364a;
        }

        input, select {
            padding: 10px;
            margin: 5px 0 12px;
            width: 100%;
            box-sizing: border-box;
        }

        button, .edit {
            padding: 9px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        button {
            background: #1769aa;
            color: white;
        }

        .edit {
            background: #e6a23c;
            color: white;
        }

        .delete {
            background: #d9534f;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #26364a;
            color: white;
        }

        .message {
            background: #e7f5e9;
            padding: 10px;
        }

        form.inline {
            display: inline;
        }
    </style>
</head>

<body>
<div class="container">

    <h1>Entry Records Management</h1>

    <?php if ($message !== ""): ?>
        <p class="message"><?= clean($message) ?></p>
    <?php endif; ?>

    <h2>
        <?= $edit_record ? "Update Entry Record" : "Add New Entry Record" ?>
    </h2>

    <form method="POST">
        <input type="hidden" name="action" value="save">

        <input type="hidden" name="id"
            value="<?= clean($edit_record["id"] ?? "") ?>">

        <label>Traveler</label>
        <select name="traveler_id" required>
            <option value="">Select Traveler</option>

            <?php foreach ($travelers as $traveler): ?>
                <option value="<?= clean($traveler["id"]) ?>"
                    <?= (isset($edit_record["traveler_id"]) &&
                        $edit_record["traveler_id"] == $traveler["id"])
                        ? "selected" : "" ?>>
                    <?= clean($traveler["first_name"] . " " .
                        $traveler["last_name"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Vehicle</label>
        <select name="vehicle_id" required>
            <option value="">Select Vehicle</option>

            <?php foreach ($vehicles as $vehicle): ?>
                <option value="<?= clean($vehicle["id"]) ?>"
                    <?= (isset($edit_record["vehicle_id"]) &&
                        $edit_record["vehicle_id"] == $vehicle["id"])
                        ? "selected" : "" ?>>
                    <?= clean($vehicle["vehicle_name"] . " - " .
                        $vehicle["plate_number"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Checkpoint (Optional)</label>
        <select name="checkpoint_id">
            <option value="">No checkpoint selected</option>

            <?php foreach ($checkpoints as $checkpoint): ?>
                <option value="<?= clean($checkpoint["id"]) ?>"
                    <?= (isset($edit_record["checkpoint_id"]) &&
                        $edit_record["checkpoint_id"] == $checkpoint["id"])
                        ? "selected" : "" ?>>
                    <?= clean($checkpoint["checkpoint_name"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Entry Date</label>
        <input type="date" name="entry_date"
            value="<?= clean($edit_record["entry_date"] ?? "") ?>"
            required>

        <label>Entry Time</label>
        <input type="time" name="entry_time"
            value="<?= clean($edit_record["entry_time"] ?? "") ?>"
            required>

        <label>Purpose</label>
        <input type="text" name="purpose"
            value="<?= clean($edit_record["purpose"] ?? "") ?>"
            placeholder="Enter purpose of visit" required>

        <button type="submit">
            <?= $edit_record ? "Update Record" : "Add Entry Record" ?>
        </button>

        <?php if ($edit_record): ?>
            <a href="entry_records.php">Cancel</a>
        <?php endif; ?>
    </form>

    <hr>

    <h2>Entry Records</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Traveler</th>
            <th>Vehicle</th>
            <th>Checkpoint</th>
            <th>Date</th>
            <th>Time</th>
            <th>Purpose</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = $records->fetch_assoc()): ?>
            <tr>
                <td><?= clean($row["id"]) ?></td>

                <td>
                    <?= clean($row["first_name"] . " " .
                        $row["last_name"]) ?>
                </td>

                <td>
                    <?= clean($row["vehicle_name"] . " (" .
                        $row["plate_number"] . ")") ?>
                </td>

                <td>
                    <?= clean($row["checkpoint_name"] ?? "Not assigned") ?>
                </td>

                <td><?= clean($row["entry_date"]) ?></td>
                <td><?= clean($row["entry_time"]) ?></td>
                <td><?= clean($row["purpose"]) ?></td>

                <td>
                    <a class="edit"
                       href="entry_records.php?edit=<?= clean($row["id"]) ?>">
                        Edit
                    </a>

                    <form method="POST" class="inline"
                        onsubmit="return confirm('Delete this entry record?');">

                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id"
                            value="<?= clean($row["id"]) ?>">

                        <button type="submit" class="delete">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

</div>
</body>
</html>
