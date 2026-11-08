<?php
// violation_report.php - JOIN report: violations with their entry record.
require_once '../../db.php';   // shared database connection ($conn)
require_once 'helpers.php';    // h(), page_header(), page_footer()

$conn->set_charset("utf8mb4");

// Same query as reports/michael_join.sql
$sql = "SELECT
            v.id            AS violation_id,
            v.violation_type,
            v.description,
            v.severity,
            v.status,
            v.recorded_at   AS violation_date,
            e.id            AS entry_id,
            e.entry_date,
            e.entry_time,
            e.purpose
        FROM violations v
        LEFT JOIN entry_records e
            ON v.entry_records_id = e.id
        ORDER BY v.id DESC";

$result = $conn->query($sql);

page_header('Violation Report', 'violation_report.php');
?>

<h1>Violation Report</h1>

<div class="note">
    This report joins the <strong>violations</strong> table with the <strong>entry_records</strong> table
    (LEFT JOIN). A violation is always shown, even if its entry record is missing.
</div>

<div class="box">
    <table>
        <tr>
            <th>Violation ID</th><th>Type</th><th>Description</th><th>Severity</th>
            <th>Status</th><th>Violation Date</th>
            <th>Entry ID</th><th>Entry Date</th><th>Entry Time</th><th>Purpose</th>
        </tr>
        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="10">No violations found.</td></tr>
        <?php endif; ?>
        <?php while ($r = $result->fetch_assoc()): ?>
        <tr>
            <td><?= (int)$r['violation_id'] ?></td>
            <td><?= h($r['violation_type']) ?></td>
            <td><?= h($r['description']) ?></td>
            <td><?= h(ucfirst($r['severity'])) ?></td>
            <td><?= h(ucfirst($r['status'])) ?></td>
            <td><?= h($r['violation_date']) ?></td>
            <?php if ($r['entry_id']): ?>
                <td><?= (int)$r['entry_id'] ?></td>
                <td><?= h($r['entry_date']) ?></td>
                <td><?= h($r['entry_time']) ?></td>
                <td><?= h($r['purpose']) ?></td>
            <?php else: ?>
                <td colspan="4">No entry record</td>
            <?php endif; ?>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php page_footer(); ?>
