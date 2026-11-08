-- michael_dbms.sql
-- Michael's module: violations, alerts, audit_logs
--
-- Run AFTER these tables exist (other members' files):
--   roles, users, checkpoints, travelers, vehicles, entry_records

CREATE TABLE IF NOT EXISTS violations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_records_id INT NOT NULL,
    violation_type VARCHAR(100) NOT NULL,
    description TEXT,
    severity ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'low',
    status ENUM('open', 'resolved', 'dismissed') NOT NULL DEFAULT 'open',
    recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entry_records_id) REFERENCES entry_records(id)
);

CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    violations_id INT,
    checkpoints_id INT,
    alert_type VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (violations_id) REFERENCES violations(id),
    FOREIGN KEY (checkpoints_id) REFERENCES checkpoints(id)
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    users_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    details TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (users_id) REFERENCES users(id)
);

-- Sample data (needs entry_records id 1, checkpoints id 1 and users id 1 to exist)
INSERT INTO violations (entry_records_id, violation_type, description, severity)
VALUES (1, 'Overstay', 'Test violation', 'high');

INSERT INTO alerts (violations_id, checkpoints_id, alert_type, message)
VALUES (1, 1, 'High severity', 'Test alert');

INSERT INTO audit_logs (users_id, action, table_name, record_id, details)
VALUES (1, 'CREATE', 'violations', 1, 'Test audit log');
