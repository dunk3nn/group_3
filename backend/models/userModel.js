const db = require('../config/db');

// GET all active users
const getAllUsers = async () => {
    const [rows] = await db.query(
        `SELECT id, username, email, role_id, created_at, is_active
         FROM users
         WHERE is_active = 1
         ORDER BY id DESC`
    );

    return rows;
};

// GET user by ID
const getUserById = async (id) => {
    const [rows] = await db.query(
        'SELECT id FROM users WHERE id = ? AND is_active = 1',
        [id]
    );

    return rows[0];
};

// Check if username or email already exists
const checkExistingUser = async (username, email, excludeId = 0) => {
    const [rows] = await db.query(
        `SELECT id FROM users
         WHERE (username = ? OR email = ?)
         AND id != ?
         LIMIT 1`,
        [username, email, excludeId]
    );

    return rows[0];
};
// POST add user
const createUser = async (username, email, password, roleId) => {
    const [result] = await db.query(
        `INSERT INTO users (username, email, password, role_id)
         VALUES (?, ?, ?, ?)`,
        [username, email, password, roleId]
    );

    return result;
};

// PUT edit user
const updateUser = async (id, username, email, roleId, password) => {
    let result;

    if (password) {
        [result] = await db.query(
            `UPDATE users
             SET username = ?, email = ?, role_id = ?, password = ?
             WHERE id = ? AND is_active = 1`,
            [username, email, roleId, password, id]
        );
    } else {
        [result] = await db.query(
            `UPDATE users
             SET username = ?, email = ?, role_id = ?
             WHERE id = ? AND is_active = 1`,
            [username, email, roleId, id]
        );
    }

    return result;
};

// DELETE (deactivate) user
const deactivateUser = async (id) => {
    const [result] = await db.query(
        'UPDATE users SET is_active = 0 WHERE id = ? AND is_active = 1',
        [id]
    );

    return result;
};

// GET users with their role names
const getUsersWithRoles = async () => {
    const [rows] = await db.query(
        `SELECT
            users.id,
            users.username,
            users.email,
            users.role_id,
            roles.role_name,
            users.created_at,
            users.is_active
         FROM users
         INNER JOIN roles
         ON users.role_id = roles.id
         WHERE users.is_active = 1
         ORDER BY users.id DESC`
    );

    return rows;
};

// GET available roles
const getAllRoles = async () => {
    const [rows] = await db.query(
        'SELECT id, role_name FROM roles ORDER BY id'
    );

    return rows;
};

module.exports = {
    getAllUsers,
    getUserById,
    checkExistingUser,
    createUser,
    updateUser,
    deactivateUser,
    getUsersWithRoles,
    getAllRoles
};