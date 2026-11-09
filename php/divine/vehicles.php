```php
<?php
require_once __DIR__ . '/../../db.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset("utf8mb4");

$message = "";
$message_type = "success";

// ADD OR UPDATE VEHICLE
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    try {
        if ($action === "save") {
            $id = (int)($_POST["id"] ?? 0);
            $traveler_id = (int)($_POST["traveler_id"] ?? 0);
            $vehicle_name = trim($_POST["vehicle_name"] ?? "");
            $plate_number = trim($_POST["plate_number"] ?? "");
            $capacity = (int)($_POST["capacity"] ?? 0);

            if (
                $traveler_id <= 0 ||
                $vehicle_name === "" ||
                $plate_number === "" ||
                $capacity <= 0
            ) {
                $message = "Please complete all fields correctly.";
                $message_type = "error";
            } else {
                if ($id > 0) {
                    // UPDATE VEHICLE
                    $stmt = $conn->prepare(
                        "UPDATE vehicles
                         SET traveler_id = ?,
                             vehicle_name = ?,
                             plate_number = ?,
                             capacity = ?
                         WHERE id = ?"
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
                    $stmt->close();

                    $message = "Vehicle updated successfully!";
                } else {
                    // INSERT VEHICLE
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
                    $stmt->close();

                    $message = "Vehicle added successfully!";
                }
            }
        }

        // DELETE VEHICLE
        elseif ($action === "delete") {
            $id = (int)($_POST["id"] ?? 0);

            if ($id > 0) {
                $stmt = $conn->prepare(
                    "DELETE FROM vehicles WHERE id = ?"
                );

                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();

                $message = "Vehicle deleted successfully!";
            } else {
                $message = "Invalid vehicle ID.";
                $message_type = "error";
            }
        }

    } catch (mysqli_sql_exception $e) {
        error_log("Vehicles Error: " . $e->getMessage());

        if ($e->getCode() === 1451) {
            $message = "Cannot delete this vehicle because it has related entry records.";
        } else {
            $message = "The operation could not be completed. Please check your database and try again.";
        }

        $message_type = "error";
    }
}

// GET VEHICLE FOR EDITING
$edit_vehicle = null;

try {
    if (isset($_GET["edit"])) {
        $id = (int)$_GET["edit"];

        if ($id > 0) {
            $stmt = $conn->prepare(
                "SELECT * FROM vehicles WHERE id = ?"
            );

            $stmt->bind_param("i", $id);
            $stmt->execute();

            $edit_vehicle = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
    }

    // GET TRAVELERS FOR DROPDOWN
    $travelers_result = $conn->query(
        "SELECT id, first_name, last_name
         FROM travelers
         ORDER BY first_name"
    );

    $travelers = $travelers_result->fetch_all(MYSQLI_ASSOC);

    // GET VEHICLES WITH TRAVELER INFORMATION
    $vehicles_result = $conn->query(
        "SELECT
            v.id,
            v.traveler_id,
            v.vehicle_name,
            v.plate_number,
            v.capacity,
            t.first_name,
            t.last_name
         FROM vehicles v
         INNER JOIN travelers t
             ON v.traveler_id = t.id
         ORDER BY v.id DESC"
    );

    $vehicles = $vehicles_result->fetch_all(MYSQLI_ASSOC);

} catch (mysqli_sql_exception $e) {
    error_log("Vehicles Loading Error: " . $e->getMessage());

    $message = "Unable to load vehicle records. Please check your database tables and columns.";
    $message_type = "error";

    $travelers = $travelers ?? [];
    $vehicles = $vehicles ?? [];
}

// CLEAN OUTPUT
function clean($value) {
    return htmlspecialchars(
        (string)($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Vehicles Management</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 30px;
            margin: 0;
            color: #333;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        }

        h1, h2 {
            color: #26364a;
        }

        h1 {
            margin-top: 0;
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 5px;
        }

        input, select {
            padding: 10px;
            margin: 5px 0 12px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #1769aa;
        }

        button, .edit, .cancel {
            display: inline-block;
            padding: 9px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        button {
            background: #1769aa;
            color: white;
        }

        button:hover {
            background: #12558b;
        }

        .edit {
            background: #e6a23c;
            color: white;
        }

        .edit:hover {
            background: #c98b2c;
        }

        .delete {
            background: #d9534f;
            color: white;
        }

        .delete:hover {
            background: #bd3e3a;
        }

        .cancel {
            background: #777;
            color: white;
            margin-left: 5px;
        }

        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success {
            background: #e7f5e9;
            color: #216b32;
        }

        .error {
            background: #fdeaea;
            color: #a12626;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            min-width: 750px;
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

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        tr:hover {
            background: #eef3f8;
        }

        form.inline {
            display: inline;
        }

        .empty-message {
            text-align: center;
            padding: 20px;
            color: #777;
        }

        hr {
            border: 0;
            border-top: 1px solid #ddd;
            margin: 30px 0;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Vehicles Management</h1>

    <?php if ($message !== ""): ?>
        <div class="message <?= clean($message_type) ?>">
            <?= clean($message) ?>
        </div>
    <?php endif; ?>

    <h2>
        <?= $edit_vehicle ? "Update Vehicle" : "Add New Vehicle" ?>
    </h2>

    <form method="POST" action="vehicles.php">

        <input type="hidden" name="action" value="save">

        <input
            type="hidden"
            name="id"
            value="<?= clean($edit_vehicle["id"] ?? "") ?>"
        >

        <label for="traveler_id">Traveler</label>

        <select name="traveler_id" id="traveler_id" required>
            <option value="">Select Traveler</option>

            <?php foreach ($travelers as $traveler): ?>
                <option
                    value="<?= clean($traveler["id"]) ?>"
                    <?= (
                        isset($edit_vehicle["traveler_id"]) &&
                        $edit_vehicle["traveler_id"] == $traveler["id"]
                    ) ? "selected" : "" ?>
                >
                    <?= clean(
                        $traveler["first_name"] . " " .
                        $traveler["last_name"]
                    ) ?>
                </option>
            <?php endforeach; ?>

        </select>

        <label for="vehicle_name">Vehicle Name</label>

        <input
            type="text"
            name="vehicle_name"
            id="vehicle_name"
            value="<?= clean($edit_vehicle["vehicle_name"] ?? "") ?>"
            placeholder="Enter vehicle name"
            required
        >

        <label for="plate_number">Plate Number</label>

        <input
            type="text"
            name="plate_number"
            id="plate_number"
            value="<?= clean($edit_vehicle["plate_number"] ?? "") ?>"
            placeholder="Enter plate number"
            required
        >

        <label for="capacity">Capacity</label>

        <input
            type="number"
            name="capacity"
            id="capacity"
            min="1"
            value="<?= clean($edit_vehicle["capacity"] ?? "") ?>"
            placeholder="Enter vehicle capacity"
            required
        >

        <button type="submit">
            <?= $edit_vehicle ? "Update Vehicle" : "Add Vehicle" ?>
        </button>

        <?php if ($edit_vehicle): ?>
            <a href="vehicles.php" class="cancel">Cancel</a>
        <?php endif; ?>

    </form>

    <hr>

    <h2>Vehicle Records</h2>

    <div class="table-wrapper">

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Traveler</th>
                    <th>Vehicle Name</th>
                    <th>Plate Number</th>
                    <th>Capacity</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($vehicles)): ?>

                    <?php foreach ($vehicles as $row): ?>

                        <tr>
                            <td><?= clean($row["id"]) ?></td>

                            <td>
                                <?= clean(
                                    $row["first_name"] . " " .
                                    $row["last_name"]
                                ) ?>
                            </td>

                            <td><?= clean($row["vehicle_name"]) ?></td>

                            <td><?= clean($row["plate_number"]) ?></td>

                            <td><?= clean($row["capacity"]) ?></td>

                            <td>
                                <a
                                    class="edit"
                                    href="vehicles.php?edit=<?= clean($row["id"]) ?>"
                                >
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    action="vehicles.php"
                                    class="inline"
                                    onsubmit="return confirm('Are you sure you want to delete this vehicle?');"
                                >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= clean($row["id"]) ?>"
                                    >

                                    <button type="submit" class="delete">
                                        Delete
                                    </button>

                                </form>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="empty-message">
                            No vehicle records found.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>
        </table>

    </div>

</div>

</body>
</html>
```