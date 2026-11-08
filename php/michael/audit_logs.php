<?php
// audit_logs.php - READ-ONLY history of actions. No add, edit or delete here.
require_once '../../db.php';   // shared database connection ($conn)
require_once 'helpers.php';    // h(), page_header(), page_footer()

$conn->set_charset("utf8mb4");

// Access restriction: this page only allows GET (viewing).
// Any attempt to send data (POST, etc.) is refused, so history cannot be changed from here.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    die('Audit logs are read-only.');
}

// Optional filter: show only one table's history
$filter = trim($_GET['table'] ?? '');

// Table names for the filter dropdown
$tables = $conn->query(
    "SELECT DISTINCT table_name FROM audit_logs WHERE table_name IS NOT NULL ORDER BY table_name"
);

// Get the logs. LEFT JOIN users so we can show the username (or "-" if there is none).
$sql = "SELECT l.id, l.action, l.table_name, l.record_id, l.details, l.created_at, u.username
        FROM audit_logs l
        LEFT JOIN users u ON l.users_id = u.id";

if ($filter !== '') {
    $stmt = $conn->prepare($sql . " WHERE l.table_name = ? ORDER BY l.id DESC LIMIT 200");
    $stmt->bind_param("s", $filter);
} else {
    $stmt = $conn->prepare($sql . " ORDER BY l.id DESC LIMIT 200");
}
$stmt->execute();
$logs = $stmt->get_result();

page_header('Audit Logs', 'audit_logs.php');
?>

<h1>Audit Logs</h1>

<div class="note">
    This is a read-only history. Records are added automatically when violations or alerts change,
    and they cannot be edited or deleted here. Showing the latest 200 records.
</div>

<div class="box">
    <form method="get" action="audit_logs.php">
        <label>Filter by table</label>
        <select name="table">
            <option value="">-- All tables --</option>
            <?php while ($t = $tables->fetch_assoc()): ?>
                <option value="<?= h($t['table_name']) ?>" <?= $filter === $t['table_name'] ? 'selected' : '' ?>>
                    <?= h($t['table_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Filter</button>
        <a class="btn btn-gray" href="audit_logs.php">Clear</a>
    </form>
</div>

<div class="box">
    <table>
        <tr>
            <th>ID</th><th>Date and Time</th><th>User</th><th>Action</th>
            <th>Table</th><th>Record ID</th><th>Details</th>
        </tr>
        <?php if ($logs->num_rows === 0): ?>
            <tr><td colspan="7">No audit records found.</td></tr>
        <?php endif; ?>
        <?php while ($l = $logs->fetch_assoc()): ?>
        <tr>
            <td><?= (int)$l['id'] ?></td>
            <td><?= h($l['created_at']) ?></td>
            <td><?= $l['username'] ? h($l['username']) : '-' ?></td>
            <td><?= h($l['action']) ?></td>
            <td><?= $l['table_name'] ? h($l['table_name']) : '-' ?></td>
            <td><?= $l['record_id'] !== null ? (int)$l['record_id'] : '-' ?></td>
            <td><?= h($l['details']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php
$stmt->close();
page_footer();
?>
