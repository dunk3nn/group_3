
<?php
require_once '../db.php';

$conn->set_charset("utf8mb4");
$message = "";

// ADD OR UPDATE VEHICLE
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "save") {
        $id = (int)($_POST["id"] ?? 0);
        $traveler_id = (int)($_POST["traveler_id"] ?? 0);
        $vehicle_name = trim($_POST["vehicle_name"] ?? "");
        $plate_number = trim($_POST["plate_number"] ?? "");
        $capacity = (int)($_POST["capacity"] ?? 0);

        if ($traveler_id <= 0 || $vehicle_name === "" ||
            $plate_number === "" || $capacity <= 0) {
            $message = "Please complete all fields correctly.";
        } else {
            if ($id > 0) {
                $stmt = $conn->prepare(
                    "UPDATE vehicles
                     SET traveler_id=?, vehicle_name=?,
                         plate_number=?, capacity=?
                     WHERE id=?"
                );
                $stmt->bind_param(
                    "issii",
                    $traveler_id,
                    $vehicle_name,
                    $plate_number,
                    $capacity,
                    $id
                );
                $stmt->execute();
                $message = "Vehicle updated successfully!";
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO vehicles
                     (traveler_id, vehicle_name, plate_number, capacity)
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param(
                    "issi",
                    $traveler_id,
                    $vehicle_name,
                    $plate_number,
                    $capacity
                );
                $stmt->execute();
                $message = "Vehicle added successfully!";
            }
            $stmt->close();
        }
    }

    // DELETE VEHICLE
    if ($action === "delete") {
        $id = (int)($_POST["id"] ?? 0);

        try {
            $stmt = $conn->prepare(
                "DELETE FROM vehicles WHERE id=?"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $message = "Vehicle deleted successfully!";
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $message = "Cannot delete this vehicle because it has related entry records.";
        }
    }
}

// GET VEHICLE FOR EDITING
$edit_vehicle = null;

if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT * FROM vehicles WHERE id=?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_vehicle = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// GET TRAVELERS FOR DROPDOWN
$travelers = $conn->query(
    "SELECT id, first_name, last_name FROM travelers ORDER BY first_name"
);

// DISPLAY VEHICLES WITH TRAVELER NAMES
$vehicles = $conn->query(
    "SELECT v.*, t.first_name, t.last_name
     FROM vehicles v
     INNER JOIN travelers t ON v.traveler_id = t.id
     ORDER BY v.id DESC"
);

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vehicles Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 30px;
        }

        .container {
            max-width: 1050px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        h1, h2 { color: #26364a; }

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

        .delete { background: #d9534f; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
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

        form.inline { display: inline; }
    </style>
</head>
<body>

<div class="container">

    <h1>Vehicles Management</h1>

    <?php if ($message !== ""): ?>
        <p class="message"><?= clean($message) ?></p>
    <?php endif; ?>

    <h2><?= $edit_vehicle ? "Update Vehicle" : "Add New Vehicle" ?></h2>

    <form method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id"
               value="<?= clean($edit_vehicle["id"] ?? "") ?>">

        <label>Traveler</label>
        <select name="traveler_id" required>
            <option value="">Select Traveler</option>

            <?php while ($traveler = $travelers->fetch_assoc()): ?>
                <option value="<?= clean($traveler["id"]) ?>"
                    <?= (isset($edit_vehicle["traveler_id"]) &&
                        $edit_vehicle["traveler_id"] == $traveler["id"])
                        ? "selected" : "" ?>>
                    <?= clean($traveler["first_name"] . " " .
                        $traveler["last_name"]) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Vehicle Name</label>
        <input type="text" name="vehicle_name"
               value="<?= clean($edit_vehicle["vehicle_name"] ?? "") ?>"
               required>

        <label>Plate Number</label>
        <input type="text" name="plate_number"
               value="<?= clean($edit_vehicle["plate_number"] ?? "") ?>"
               required>

        <label>Capacity</label>
        <input type="number" name="capacity" min="1"
               value="<?= clean($edit_vehicle["capacity"] ?? "") ?>"
               required>

        <button type="submit">
            <?= $edit_vehicle ? "Update Vehicle" : "Add Vehicle" ?>
        </button>

        <?php if ($edit_vehicle): ?>
            <a href="vehicles.php">Cancel</a>
        <?php endif; ?>
    </form>

    <hr>

    <h2>Vehicle Records</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Traveler</th>
            <th>Vehicle Name</th>
            <th>Plate Number</th>
            <th>Capacity</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = $vehicles->fetch_assoc()): ?>
            <tr>
                <td><?= clean($row["id"]) ?></td>
                <td><?= clean($row["first_name"] . " " .
                    $row["last_name"]) ?></td>
                <td><?= clean($row["vehicle_name"]) ?></td>
                <td><?= clean($row["plate_number"]) ?></td>
                <td><?= clean($row["capacity"]) ?></td>
                <td>
                    <a class="edit"
                       href="vehicles.php?edit=<?= clean($row["id"]) ?>">
                        Edit
                    </a>

                    <form method="POST" class="inline"
                          onsubmit="return confirm('Delete this vehicle?');">
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
