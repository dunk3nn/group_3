-- michael_join.sql
-- Report: Violations with their related entry record (LEFT JOIN)
--
-- LEFT JOIN keeps EVERY violation, even if no matching entry record is found
-- (the entry columns would show NULL). Here violations.entry_records_id is NOT NULL
-- with a foreign key, so every violation does have an entry, but LEFT JOIN is still safe.

SELECT
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
ORDER BY v.id DESC;
