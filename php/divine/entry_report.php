
<?php
require_once '../db.php';

$conn->set_charset("utf8mb4");

// ENTRY REPORT USING SQL JOIN
$sql = "
    SELECT
        e.id,
        t.first_name,
        t.last_name,
        v.vehicle_name,
        v.plate_number,
        c.checkpoint_name,
        e.entry_date,
        e.entry_time,
        e.purpose
    FROM entry_records e
    INNER JOIN travelers t
        ON e.traveler_id = t.id
    INNER JOIN vehicles v
        ON e.vehicle_id = v.id
    LEFT JOIN checkpoints c
        ON e.checkpoint_id = c.id
    ORDER BY e.entry_date DESC, e.entry_time DESC
";

$result = $conn->query($sql);

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Entry Report</title>

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

        h1 {
            color: #26364a;
        }

        p {
            color: #555;
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

        tr:nth-child(even) {
            background: #f4f6f9;
        }

        .print-btn {
            background: #1769aa;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        @media print {
            .print-btn {
                display: none;
            }

            body {
                background: white;
                padding: 0;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Entry Report</h1>

    <p>
        This report displays entry records with traveler,
        vehicle, and checkpoint information using SQL JOIN.
    </p>

    <button class="print-btn" onclick="window.print()">
        Print Report
    </button>

    <table>
        <tr>
            <th>Entry ID</th>
            <th>Traveler Name</th>
            <th>Vehicle</th>
            <th>Plate Number</th>
            <th>Checkpoint</th>
            <th>Entry Date</th>
            <th>Entry Time</th>
            <th>Purpose</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>

            <?php while ($row = $result->fetch_assoc()): ?>

                <tr>
                    <td><?= clean($row["id"]) ?></td>

                    <td>
                        <?= clean($row["first_name"] . " " .
                            $row["last_name"]) ?>
                    </td>

                    <td><?= clean($row["vehicle_name"]) ?></td>

                    <td><?= clean($row["plate_number"]) ?></td>

                    <td>
                        <?= clean($row["checkpoint_name"] ?? "Not assigned") ?>
                    </td>

                    <td><?= clean($row["entry_date"]) ?></td>

                    <td><?= clean($row["entry_time"]) ?></td>

                    <td><?= clean($row["purpose"]) ?></td>
                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="8">No entry records found.</td>
            </tr>

        <?php endif; ?>

    </table>

</div>

</body>
</html>
