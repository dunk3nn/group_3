-- Michael seed data (run after all table files)

INSERT IGNORE INTO roles (role_name, description)
VALUES ('Admin', 'Administrator');
SET @role_id = (SELECT id FROM roles WHERE role_name = 'Admin' LIMIT 1);

INSERT INTO users (role_id, username, email, password)
VALUES (@role_id, 'test_admin', 'test_admin@example.com', '$2y$10$GllpWKDLOJguN2uXLbrcPeLM9dXOx9BKyMNacLcPjS2hkjC.is0JC');
SET @user_id = LAST_INSERT_ID();

INSERT INTO checkpoints (checkpoint_name, location)
VALUES ('Test Checkpoint', 'Test Location');
SET @checkpoint_id = LAST_INSERT_ID();

INSERT INTO travelers (first_name, last_name, contact_number)
VALUES ('Test', 'Traveler', '09123456789');
SET @traveler_id = LAST_INSERT_ID();

INSERT INTO vehicles (traveler_id, vehicle_name, plate_number, capacity)
VALUES (@traveler_id, 'Test Car', 'ABC1234', 4);
SET @vehicle_id = LAST_INSERT_ID();

INSERT INTO entry_records (traveler_id, vehicle_id, entry_date, entry_time, purpose)
VALUES (@traveler_id, @vehicle_id, CURDATE(), CURTIME(), 'Tourism');
SET @entry_id = LAST_INSERT_ID();

INSERT INTO violations (entry_records_id, violation_type, description, severity)
VALUES (@entry_id, 'Overstay', 'Test violation', 'high');
SET @violation_id = LAST_INSERT_ID();

INSERT INTO alerts (violations_id, checkpoints_id, alert_type, message)
VALUES (@violation_id, @checkpoint_id, 'High severity', 'Test alert');

INSERT INTO audit_logs (users_id, action, table_name, record_id, details)
VALUES (@user_id, 'CREATE', 'violations', @violation_id, 'Test audit log');