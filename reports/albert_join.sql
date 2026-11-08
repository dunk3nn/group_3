-- reports/albert_join.sql
-- Report: Users with their assigned role (INNER JOIN)
-- Tables: users (role_id) -> roles (id)
-- Password hashes are intentionally NOT selected.

USE border_entry_system;

SELECT
    u.id         AS user_id,
    u.username,
    u.email,
    r.role_name,
    CASE WHEN u.is_active = 1 THEN 'Active' ELSE 'Inactive' END AS account_status,
    u.created_at
FROM users AS u
INNER JOIN roles AS r
    ON u.role_id = r.id
ORDER BY r.role_name, u.username;
