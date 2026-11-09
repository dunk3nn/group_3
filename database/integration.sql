USE border_entry_system;

CREATE TABLE IF NOT EXISTS travelers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20) NOT NULL
);

CREATE TABLE IF NOT EXISTS vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    traveler_id INT NOT NULL,
    vehicle_name VARCHAR(255) NOT NULL,
    plate_number VARCHAR(20) NOT NULL,
    capacity INT NOT NULL,
    FOREIGN KEY (traveler_id) REFERENCES travelers(id)
);

CREATE TABLE IF NOT EXISTS entry_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    traveler_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    checkpoint_id INT NULL,
    entry_date DATE NOT NULL,
    entry_time TIME NOT NULL,
    purpose VARCHAR(255) NOT NULL,
    FOREIGN KEY (traveler_id) REFERENCES travelers(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    FOREIGN KEY (checkpoint_id) REFERENCES checkpoints(id)
);

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_roles FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE IF NOT EXISTS Lanes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checkpoint_id INT NOT NULL,
    lane_number INT NOT NULL,
    FOREIGN KEY (checkpoint_id) REFERENCES checkpoints(id)
);

CREATE TABLE IF NOT EXISTS Vehicle_Types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL,
    base_fee DECIMAL(10,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS Entry_Types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS Fee_Rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_type_id INT NOT NULL,
    vehicle_type_id INT NOT NULL,
    base_fee DECIMAL(10,2) NOT NULL CHECK (base_fee >= 0),
    free_hours INT NOT NULL DEFAULT 0 CHECK (free_hours >= 0),
    overstay_rate_per_hour DECIMAL(10,2) NOT NULL DEFAULT 0.00 CHECK (overstay_rate_per_hour >= 0),
    effective_date DATE NOT NULL,
    UNIQUE (entry_type_id, vehicle_type_id, effective_date),
    FOREIGN KEY (entry_type_id) REFERENCES Entry_Types(id),
    FOREIGN KEY (vehicle_type_id) REFERENCES Vehicle_Types(id)
);

CREATE TABLE IF NOT EXISTS Penalties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_record_id INT NOT NULL,
    fee_rule_id INT NOT NULL,
    overstay_hours INT NOT NULL CHECK (overstay_hours > 0),
    penalty_amount DECIMAL(10,2) NOT NULL CHECK (penalty_amount >= 0),
    date_issued DATE NOT NULL,
    FOREIGN KEY (entry_record_id) REFERENCES entry_records(id),
    FOREIGN KEY (fee_rule_id) REFERENCES Fee_Rules(id)
);

CREATE TABLE IF NOT EXISTS Payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_record_id INT NOT NULL,
    fee_rule_id INT NOT NULL,
    penalty_id INT NULL,
    amount_paid DECIMAL(10,2) NOT NULL CHECK (amount_paid > 0),
    payment_method VARCHAR(30),
    payment_date DATETIME NOT NULL,
    FOREIGN KEY (entry_record_id) REFERENCES entry_records(id),
    FOREIGN KEY (fee_rule_id) REFERENCES Fee_Rules(id),
    FOREIGN KEY (penalty_id) REFERENCES Penalties(id)
);

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