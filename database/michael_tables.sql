CREATE TABLE violations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_records_id INT NOT NULL,
    violation_type VARCHAR(100) NOT NULL,
    description TEXT,
    severity ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'low',
    status ENUM('open', 'resolved', 'dismissed') NOT NULL DEFAULT 'open',
    recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entry_records_id) REFERENCES entry_records(id)
);

CREATE TABLE alerts (
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

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    users_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    details TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (users_id) REFERENCES users(id)
);