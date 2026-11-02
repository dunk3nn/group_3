-- Michael seed data (run after all table files)

INSERT INTO travelers (first_name, last_name, contact_number)
VALUES ('Test', 'Traveler', '09123456789');

INSERT INTO vehicles (traveler_id, vehicle_name, plate_number, capacity)
VALUES (1, 'Test Car', 'ABC1234', 4);

INSERT INTO entry_records (traveler_id, vehicle_id, entry_date, entry_time, purpose)
VALUES (1, 1, CURDATE(), CURTIME(), 'Tourism');

INSERT INTO violations (entry_records_id, violation_type, description, severity)
VALUES (1, 'Overstay', 'Test violation', 'high');

INSERT INTO alerts (violations_id, checkpoints_id, alert_type, message)
VALUES (1, 1, 'High severity', 'Test alert');

INSERT INTO users (role_id, username, password)
VALUES (1, 'test_admin', 'test123');

INSERT INTO audit_logs (users_id, action, table_name, record_id, details)
VALUES (1, 'CREATE', 'violations', 1, 'Test audit log');