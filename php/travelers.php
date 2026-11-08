
<?php
require_once '../db.php';

$conn->set_charset("utf8mb4");

$message = "";

// ADD OR UPDATE TRAVELER
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    $first_name = trim($_POST["first_name"] ?? "");
    $last_name = trim($_POST["last_name"] ?? "");
    $contact_number = trim($_POST["contact_number"] ?? "");

    if ($action === "save") {
        if ($first_name === "" || $last_name === "" || $contact_number === "") {
            $message = "Please complete all fields.";
        } else {
            $id = (int)($_POST["id"] ?? 0);

            if ($id > 0) {
                $stmt = $conn->prepare(
                    "UPDATE travelers
                     SET first_name = ?, last_name = ?, contact_number = ?
                     WHERE id = ?"
                );
                $stmt->bind_param(
                    "sssi",
                    $first_name,
                    $last_name,
                    $contact_number,
                    $id
                );
                $stmt->execute();
                $message = "Traveler updated successfully!";
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO travelers
                     (first_name, last_name, contact_number)
                     VALUES (?, ?, ?)"
                );
                $stmt->bind_param(
                    "sss",
                    $first_name,
                    $last_name,
                    $contact_number
                );
                $stmt->execute();
                $message = "Traveler added successfully!";
            }

            $stmt->close();
        }
    }

    // DELETE TRAVELER
    if ($action === "delete") {
        $id = (int)($_POST["id"] ?? 0);

        try {
            $stmt = $conn->prepare(
                "DELETE FROM travelers WHERE id = ?"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $message = "Traveler deleted successfully!";
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $message = "Cannot delete this traveler because they may have related vehicle or entry records.";
        }
    }
}

// GET TRAVELER FOR EDITING
$edit_traveler = null;

if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT * FROM travelers WHERE id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $edit_traveler = $result->fetch_assoc();

    $stmt->close();
}

// DISPLAY ALL TRAVELERS
$result = $conn->query(
    "SELECT * FROM travelers ORDER BY id DESC"
);

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Travelers Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 30px;
        }

        .container {
            max-width: 950px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        h1, h2 {
            color: #26364a;
        }

        input {
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
            margin-bottom: 15px;
        }

        form.inline {
            display: inline;
        }
    </style>
</head>
<body>

<div class="container">

    <h1>Travelers Management</h1>

    <?php if ($message !== ""): ?>
        <p class="message"><?= clean($message) ?></p>
    <?php endif; ?>

    <h2>
        <?= $edit_traveler ? "Update Traveler" : "Add New Traveler" ?>
    </h2>

    <form method="POST">
        <input type="hidden" name="action" value="save">

        <input
            type="hidden"
            name="id"
            value="<?= clean($edit_traveler["id"] ?? "") ?>"
        >

        <label>First Name</label>
        <input
            type="text"
            name="first_name"
            value="<?= clean($edit_traveler["first_name"] ?? "") ?>"
            required
        >

        <label>Last Name</label>
        <input
            type="text"
            name="last_name"
            value="<?= clean($edit_traveler["last_name"] ?? "") ?>"
            required
        >

        <label>Contact Number</label>
        <input
            type="text"
            name="contact_number"
            value="<?= clean($edit_traveler["contact_number"] ?? "") ?>"
            required
        >

        <button type="submit">
            <?= $edit_traveler ? "Update Traveler" : "Add Traveler" ?>
        </button>

        <?php if ($edit_traveler): ?>
            <a href="travelers.php">Cancel</a>
        <?php endif; ?>
    </form>

    <hr>

    <h2>Traveler Records</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Contact Number</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= clean($row["id"]) ?></td>
                <td><?= clean($row["first_name"]) ?></td>
                <td><?= clean($row["last_name"]) ?></td>
                <td><?= clean($row["contact_number"]) ?></td>
                <td>
                    <a class="edit"
                       href="travelers.php?edit=<?= clean($row["id"]) ?>">
                        Edit
                    </a>

                    <form method="POST" class="inline"
                          onsubmit="return confirm('Delete this traveler?');">
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
