<?php
require_once '../../db.php';
require_once 'helpers.php';

// Same query as reports/kyfer_join.sql
$result = $conn->query(
    "SELECT l.id AS lane_id, l.lane_number,
            c.id AS checkpoint_id, c.checkpoint_name, c.location
     FROM lanes l
     INNER JOIN checkpoints c ON l.checkpoint_id = c.id
     ORDER BY c.checkpoint_name, l.lane_number"
);

page_header('Lane Report', 'lane_report.php');
?>

<h1>Lane Report</h1>

<div class="box">
    <p>Each lane with the checkpoint it belongs to (INNER JOIN of lanes and checkpoints).</p>
    <table>
        <tr>
            <th>Lane ID</th><th>Lane Number</th>
            <th>Checkpoint ID</th><th>Checkpoint Name</th><th>Location</th>
        </tr>
        <?php while ($r = $result->fetch_assoc()): ?>
        <tr>
            <td><?= h($r['lane_id']) ?></td>
            <td><?= h($r['lane_number']) ?></td>
            <td><?= h($r['checkpoint_id']) ?></td>
            <td><?= h($r['checkpoint_name']) ?></td>
            <td><?= h($r['location']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <p><strong>Total lanes:</strong> <?= (int)$result->num_rows ?></p>
</div>

<?php page_footer(); ?>