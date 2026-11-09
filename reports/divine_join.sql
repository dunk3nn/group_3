-- GROUP 3
-- Divine's DBMS JOIN Report
-- Travelers, Vehicles, Entry Records, and Checkpoints

SELECT
    er.id AS entry_record_id,
    t.id AS traveler_id,
    t.first_name,
    t.last_name,
    t.contact_number,
    v.id AS vehicle_id,
    v.vehicle_name,
    v.plate_number,
    v.capacity,
    c.checkpoint_name,
    c.location AS checkpoint_location,
    er.entry_date,
    er.entry_time,
    er.purpose

FROM entry_records er

INNER JOIN travelers t
    ON er.traveler_id = t.id

INNER JOIN vehicles v
    ON er.vehicle_id = v.id

LEFT JOIN checkpoints c
    ON er.checkpoint_id = c.id

ORDER BY
    er.entry_date DESC,
    t.last_name ASC;
