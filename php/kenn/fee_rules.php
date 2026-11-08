<?php
require_once __DIR__ . '/../../db.php';

$success_msg = "";
$error_msg = "";

// ADD / UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entry_type_id   = $_POST['entry_type_id'] ?? '';
    $vehicle_type_id = $_POST['vehicle_type_id'] ?? '';
    $base_fee        = $_POST['base_fee'] ?? '';
    $free_hours      = $_POST['free_hours'] ?? '';
    $overstay_rate   = $_POST['overstay_rate_per_hour'] ?? '';
    $effective_date  = $_POST['effective_date'] ?? '';
    $id              = $_POST['id'] ?? '';

    if ($entry_type_id === '' || $vehicle_type_id === '' || $base_fee === '' ||
        $free_hours === '' || $overstay_rate === '' || $effective_date === '') {
        $error_msg = "All fields are required!";
    } else {
        if (!empty($id)) {
            $stmt = $conn->prepare("UPDATE Fee_Rules SET entry_type_id = ?, vehicle_type_id = ?, base_fee = ?, free_hours = ?, overstay_rate_per_hour = ?, effective_date = ? WHERE id = ?");
            $stmt->bind_param("iididsi", $entry_type_id, $vehicle_type_id, $base_fee, $free_hours, $overstay_rate, $effective_date, $id);
            $ok = "Fee rule updated successfully!";
        } else {
            $stmt = $conn->prepare("INSERT INTO Fee_Rules (entry_type_id, vehicle_type_id, base_fee, free_hours, overstay_rate_per_hour, effective_date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iidids", $entry_type_id, $vehicle_type_id, $base_fee, $free_hours, $overstay_rate, $effective_date);
            $ok = "Fee rule added successfully!";
        }

        if ($stmt->execute()) {
            $success_msg = $ok;
        } else {
            $error_msg = "Error saving record: " . $stmt->error;
        }
        $stmt->close();
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $del_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Fee_Rules WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    if ($stmt->execute()) {
        $success_msg = "Fee rule deleted successfully!";
    } else {
        $error_msg = "Error deleting record: " . $stmt->error;
    }
    $stmt->close();
}

// LOAD ROW FOR EDIT
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM Fee_Rules WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $edit_data = $res->fetch_assoc();
    $stmt->close();
}

// FETCH DROPDOWN OPTIONS FROM DATABASE
$entry_types_result = $conn->query("SELECT id, type_name FROM entry_types");
$vehicle_types_result = $conn->query("SELECT id, type_name FROM vehicle_types");

// FETCH EXISTING RULES WITH NAMES FOR BETTER DISPLAY
$result = $conn->query("
    SELECT fr.*, et.type_name AS entry_name, vt.type_name AS vehicle_name 
    FROM Fee_Rules fr
    LEFT JOIN entry_types et ON fr.entry_type_id = et.id
    LEFT JOIN vehicle_types vt ON fr.vehicle_type_id = vt.id
    ORDER BY fr.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fee Rules Management - Border System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1000px; margin: auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2, h3 { color: #2c3e50; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        form { background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-text { font-size: 0.85em; color: #6c757d; margin-top: 3px; }
        input, select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; background-color: #ffffff !important; color: #000000 !important; }
        button { background: #007bff; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #0056b3; }
        .btn-cancel { background: #6c757d; text-decoration: none; padding: 10px 15px; color: white; border-radius: 4px; display: inline-block; margin-left: 5px; }
        .btn-cancel:hover { background: #5a6268; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #dee2e6; padding: 10px; text-align: left; }
        th { background-color: #f1f3f5; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .action-links a { margin-right: 10px; text-decoration: none; font-weight: bold; }
        .action-edit { color: #ffc107; }
        .action-delete { color: #dc3545; }
    </style>
</head>
<body>

<div class="container">
    <h2>Fee Rules Management</h2>
    <p>Manage base fees, free hours and overstay rates for different entry and vehicle categories.</p>

    <?php if (!empty($success_msg)): ?>
        <div class="alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <h3><?php echo $edit_data ? "Edit Fee Rule" : "Add New Fee Rule"; ?></h3>
    <form method="POST" action="fee_rules.php">
        <input type="hidden" name="id" value="<?php echo $edit_data['id'] ?? ''; ?>">

        <div class="form-group">
            <label>Entry Type:</label>
            <select name="entry_type_id" required>
                <option value="">-- Select Entry Type --</option>
                <?php while ($et = $entry_types_result->fetch_assoc()): ?>
                    <option value="<?php echo $et['id']; ?>" <?php echo (($edit_data['entry_type_id'] ?? '') == $et['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($et['type_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <div class="form-text">Choose whether this rule applies to Entry or Exit points.</div>
        </div>

        <div class="form-group">
            <label>Vehicle Type:</label>
            <select name="vehicle_type_id" required>
                <option value="">-- Select Vehicle Type --</option>
                <?php while ($vt = $vehicle_types_result->fetch_assoc()): ?>
                    <option value="<?php echo $vt['id']; ?>" <?php echo (($edit_data['vehicle_type_id'] ?? '') == $vt['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($vt['type_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <div class="form-text">Choose the vehicle category for this fee rule.</div>
        </div>

        <div class="form-group">
            <label>Base Fee:</label>
            <input type="number" step="0.01" min="0" name="base_fee" placeholder="e.g. 50.00" value="<?php echo htmlspecialchars($edit_data['base_fee'] ?? ''); ?>" required>
            <div class="form-text">The initial flat rate charged.</div>
        </div>

        <div class="form-group">
            <label>Free Hours:</label>
            <input type="number" min="0" name="free_hours" placeholder="e.g. 2" value="<?php echo htmlspecialchars($edit_data['free_hours'] ?? ''); ?>" required>
            <div class="form-text">Number of hours allowed before overstay penalties apply.</div>
        </div>

        <div class="form-group">
            <label>Overstay Rate Per Hour:</label>
            <input type="number" step="0.01" min="0" name="overstay_rate_per_hour" placeholder="e.g. 10.00" value="<?php echo htmlspecialchars($edit_data['overstay_rate_per_hour'] ?? ''); ?>" required>
            <div class="form-text">Hourly penalty fee charged after free hours expire.</div>
        </div>

        <div class="form-group">
            <label>Effective Date:</label>
            <input type="date" name="effective_date" value="<?php echo htmlspecialchars($edit_data['effective_date'] ?? ''); ?>" required>
            <div class="form-text">The date this rule becomes active.</div>
        </div>

        <button type="submit"><?php echo $edit_data ? "Update Fee Rule" : "Save Fee Rule"; ?></button>
        <?php if ($edit_data): ?>
            <a href="fee_rules.php" class="btn-cancel">Cancel</a>
        <?php endif; ?>
    </form>

    <h3>Existing Fee Rules</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Entry Type</th>
                <th>Vehicle Type</th>
                <th>Base Fee</th>
                <th>Free Hours</th>
                <th>Overstay Rate/Hour</th>
                <th>Effective Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['entry_name'] ?? 'Unknown'); ?></td>
                        <td><?php echo htmlspecialchars($row['vehicle_name'] ?? 'Unknown'); ?></td>
                        <td><?php echo number_format($row['base_fee'], 2); ?></td>
                        <td><?php echo $row['free_hours']; ?></td>
                        <td><?php echo number_format($row['overstay_rate_per_hour'], 2); ?></td>
                        <td><?php echo $row['effective_date']; ?></td>
                        <td class="action-links">
                            <a href="fee_rules.php?edit=<?php echo $row['id']; ?>" class="action-edit">Edit</a>
                            <a href="fee_rules.php?delete=<?php echo $row['id']; ?>" class="action-delete" onclick="return confirm('Are you sure you want to delete this rule?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align: center;">No fee rules found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>