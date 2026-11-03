const db = require('../config/db');

// Get all personnel
exports.getAllPersonnel = async () => {
    const [rows] = await db.query(
        `SELECT 
            p.id,
            p.name,
            p.position,
            p.department_id,
            p.contact_number,
            d.department_name
         FROM Personnel p
         LEFT JOIN Departments d ON p.department_id = d.id`
    );

    return rows;
};

// Get personnel by ID
exports.getPersonnelById = async (id) => {
    const [rows] = await db.query(
        `SELECT 
            p.id,
            p.name,
            p.position,
            p.department_id,
            p.contact_number,
            d.department_name
         FROM Personnel p
         LEFT JOIN Departments d ON p.department_id = d.id
         WHERE p.id = ?`,
        [id]
    );

    return rows[0];
};

// Add personnel
exports.addPersonnel = async (personnel) => {
    const { name, position, department_id, contact_number } = personnel;

    const [result] = await db.query(
        `INSERT INTO Personnel
        (name, position, department_id, contact_number)
        VALUES (?, ?, ?, ?)`,
        [name, position, department_id || null, contact_number]
    );

    return result;
};

// Update personnel
exports.updatePersonnel = async (id, personnel) => {
    const { name, position, department_id, contact_number } = personnel;

    const [result] = await db.query(
        `UPDATE Personnel
         SET name = ?, position = ?, department_id = ?, contact_number = ?
         WHERE id = ?`,
        [name, position, department_id || null, contact_number, id]
    );

    return result;
};

// Delete personnel
exports.deletePersonnel = async (id) => {
    const [result] = await db.query(
        'DELETE FROM Personnel WHERE id = ?',
        [id]
    );

    return result;
};