
-- database/albert_queries.sql

SELECT u.id, u.username, u.password, r.name AS role
FROM Users u
INNER JOIN Roles r ON r.id = u.role_id
WHERE u.username = ? AND u.is_active = 1;

SELECT u.id, u.username, r.name AS role,
       u.is_active, u.created_at
FROM Users u
INNER JOIN Roles r ON r.id = u.role_id
ORDER BY u.username;

INSERT INTO Users (role_id, username, password)
VALUES (?, ?, ?);

UPDATE Users
SET role_id = ?, username = ?
WHERE id = ?;

-- Deactivate user
UPDATE Users
SET is_active = 0
WHERE id = ?;
