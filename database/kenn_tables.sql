CREATE TABLE Entry_Types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE Fee_Rules (
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

CREATE TABLE Penalties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_record_id INT NOT NULL,
    fee_rule_id INT NOT NULL,
    overstay_hours INT NOT NULL CHECK (overstay_hours > 0),
    penalty_amount DECIMAL(10,2) NOT NULL CHECK (penalty_amount >= 0),
    date_issued DATE NOT NULL,
    FOREIGN KEY (entry_record_id) REFERENCES entry_records(id),
    FOREIGN KEY (fee_rule_id) REFERENCES Fee_Rules(id)
);

CREATE TABLE Payments (
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
      FOREIGN KEY (penalty_id) REFERENCES Penalties(id)
);

