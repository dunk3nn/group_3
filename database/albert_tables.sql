-- database/albert_tables.sql

CREATE TABLE Roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES Roles(id)
);

INSERT INTO Roles (name) VALUES ('Admin'), ('Staff');

INSERT INTO Users (role_id, username, password) VALUES
    (1, 'admin', '<hashed_password_here>'),
    (2, 'staff1', '<hashed_password_here>');

SELECT u.id, u.username, u.password, r.name AS role
FROM Users u
INNER JOIN Roles r ON r.id = u.role_id
WHERE u.username = ? AND u.is_active = 1;

SELECT u.id, u.username, r.name AS role, u.is_active, u.created_at
FROM Users u
INNER JOIN Roles r ON r.id = u.role_id
ORDER BY u.username;

INSERT INTO Users (role_id, username, password) VALUES (?, ?, ?);
UPDATE Users SET role_id = ?, username = ? WHERE id = ?;
UPDATE Users SET is_active = 0 WHERE id = ?;