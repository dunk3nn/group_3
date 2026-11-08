<?php
// helpers.php - small shared tools used by all of Michael's pages.
// Each page loads db.php first (require_once '../../db.php'), then this file.

// h() makes text safe to print in HTML (stops people injecting <script> tags).
function h($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

// Finds a user id to record as "who did it".
// There is no login in this module yet, so we use the first user in the users table.
// If your group adds a login later, return the logged-in user's id here instead.
function current_user_id($conn) {
    $result = $conn->query("SELECT id FROM users ORDER BY id LIMIT 1");
    $row = $result ? $result->fetch_assoc() : null;
    return $row ? (int)$row['id'] : null;
}

// Adds ONE new row to audit_logs. It only ever INSERTs - never updates or deletes.
function log_action($conn, $action, $table, $record_id, $details) {
    $user_id = current_user_id($conn);
    $stmt = $conn->prepare(
        "INSERT INTO audit_logs (users_id, action, table_name, record_id, details)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issis", $user_id, $action, $table, $record_id, $details);
    $stmt->execute();
    $stmt->close();
}

// Prints the top of the page: CSS + navigation bar (same on every page).
function page_header($title, $active) {
    $links = [
        'violations.php'       => 'Violations',
        'alerts.php'           => 'Alerts',
        'audit_logs.php'       => 'Audit Logs',
        'violation_report.php' => 'Violation Report',
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= h($title) ?> - Border Entry System</title>
<style>
    body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; color: #222; }
    nav { background: #1f3a5f; padding: 12px 24px; }
    nav a { color: #cfd8e6; text-decoration: none; margin-right: 20px; font-weight: bold; }
    nav a.active { color: #fff; border-bottom: 3px solid #fff; padding-bottom: 4px; }
    .container { max-width: 1100px; margin: 24px auto; padding: 0 16px; }
    h1 { color: #1f3a5f; }
    .box { background: #fff; padding: 18px; border-radius: 8px; margin-bottom: 24px;
           box-shadow: 0 1px 4px rgba(0,0,0,0.12); }
    label { display: block; margin-top: 10px; font-weight: bold; }
    input, select, textarea { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box;
           border: 1px solid #bbb; border-radius: 4px; font-size: 14px; }
    button, .btn { background: #1f3a5f; color: #fff; border: none; padding: 8px 14px; margin-top: 12px;
           border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px; display: inline-block; }
    .btn-red { background: #b91c1c; }
    .btn-gray { background: #6b7280; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top; font-size: 14px; }
    th { background: #e8edf4; }
    .success { background: #dcfce7; color: #166534; padding: 10px; border-radius: 4px; margin-bottom: 14px; }
    .error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 4px; margin-bottom: 14px; }
    .note { background: #fef9c3; color: #854d0e; padding: 10px; border-radius: 4px; margin-bottom: 14px; }
    form.inline { display: inline; }
    form.inline button { margin-top: 0; padding: 5px 10px; }
</style>
</head>
<body>
<nav>
<?php foreach ($links as $file => $label): ?>
    <a href="<?= $file ?>" class="<?= $file === $active ? 'active' : '' ?>"><?= $label ?></a>
<?php endforeach; ?>
</nav>
<div class="container">
<?php
}

// Prints the bottom of the page.
function page_footer() {
    echo "</div>\n</body>\n</html>";
}
