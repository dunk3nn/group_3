```php
<?php
require_once __DIR__ . '/../db.php';

$message = '';
$messageType = '';

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
            'INSERT INTO Personnel (name, position, department_id, contact_number)
             VALUES (?, ?, ?, ?)'
        );

        if ($stmt) {
            $stmt->bind_param('ssis', $name, $position, $department_id, $contact_number);

            if ($stmt->execute()) {
                $message = 'Personnel added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Could not add personnel. Please check that the Department ID exists.';
                $messageType = 'error';
            }

            $stmt->close();
        } else {
            $message = 'Unable to prepare the database query.';
            $messageType = 'error';
        }
    }
}

$result = $conn->query('SELECT * FROM Personnel ORDER BY id ASC');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnel Management</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 20px;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f3f6fa;
            color: #333333;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .page-header {
            background-color: #17365d;
            color: white;
            padding: 28px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .page-header h1 {
            margin: 0 0 10px;
            font-size: 30px;
        }

        .page-header p {
            margin: 6px 0;
            line-height: 1.6;
        }

        .card {
            background-color: white;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid #e1e6ef;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
        }

        .card h2 {
            margin: 0 0 22px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e8eef6;
            color: #17365d;
            font-size: 21px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
            color: #34445a;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 15px;
            background-color: #ffffff;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .button {
            display: inline-block;
            margin-top: 22px;
            padding: 12px 22px;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .button:hover {
            background-color: #1d4ed8;
        }

        .message {
            margin-bottom: 20px;
            padding: 14px 16px;
            border-radius: 6px;
            font-size: 14px;
        }

        .success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th,
        td {
            padding: 14px 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #eaf0f8;
            color: #17365d;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        td {
            font-size: 14px;
        }

        tbody tr:hover {
            background-color: #f7faff;
        }

        .empty-message {
            text-align: center;
            color: #777777;
            padding: 22px;
        }

        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            padding: 5px 0 15px;
        }

        @media (max-width: 600px) {
            body {
                padding: 18px 12px;
            }

            .page-header {
                padding: 22px 18px;
            }

            .page-header h1 {
                font-size: 24px;
            }

            .card {
                padding: 18px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <header class="page-header">
        <h1>Personnel Management</h1>
        <p><strong>Border Entry Payment and Personnel Tracking System</strong></p>
        <p>Manage personnel information and view registered records.</p>
    </header>

    <?php if ($message !== ''): ?>
        <div class="message <?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <section class="card">
        <h2>Add New Personnel</h2>

        <form method="POST" action="">
            <div class="form-grid">

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        maxlength="100"
                        placeholder="Enter full name"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="position">Position</label>
                    <input
                        type="text"
                        id="position"
                        name="position"
                        maxlength="50"
                        placeholder="Enter position"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="department_id">Department ID</label>
                    <input
                        type="number"
                        id="department_id"
                        name="department_id"
                        min="1"
                        placeholder="Example: 1"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input
                        type="text"
                        id="contact_number"
                        name="contact_number"
                        maxlength="15"
                        placeholder="Enter contact number"
                        required
                    >
                </div>

            </div>

            <button type="submit" name="add_personnel" class="button">
                + Add Personnel
            </button>
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
                        <th>Department ID</th>
                        <th>Contact Number</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)$row['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['position'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($row['department_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['contact_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-message">
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
```
