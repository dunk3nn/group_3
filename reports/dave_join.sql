-- GROUP 3
-- Dave's DBMS JOIN Report
-- Personnel, Shifts, Checkpoint Assignments, and Checkpoints

SELECT
    p.id AS personnel_id,
    p.name AS personnel_name,
    p.position,
    d.department_name,
    s.shift_name,
    s.start_time,
    s.end_time,
    c.checkpoint_name,
    c.location AS checkpoint_location,
    ca.assignment_date

FROM checkpoint_assignments ca

INNER JOIN personnel p
    ON ca.personnel_id = p.id

INNER JOIN shifts s
    ON ca.shift_id = s.id

INNER JOIN checkpoints c
    ON ca.checkpoint_id = c.id

LEFT JOIN departments d
    ON p.department_id = d.id

ORDER BY
    ca.assignment_date DESC,
    p.name ASC;