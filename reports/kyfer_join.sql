-- kyfer_join.sql
-- Lane Report: every lane with the checkpoint it belongs to.
-- INNER JOIN is correct because lanes.checkpoint_id is NOT NULL,
-- so every lane always has a matching checkpoint.

SELECT
    l.id              AS lane_id,
    l.lane_number     AS lane_number,
    c.id              AS checkpoint_id,
    c.checkpoint_name AS checkpoint_name,
    c.location        AS checkpoint_location
FROM lanes l
INNER JOIN checkpoints c ON l.checkpoint_id = c.id
ORDER BY c.checkpoint_name, l.lane_number;