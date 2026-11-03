const db = require('../config/db');

// Get all violations
exports.getAllViolations = async () => {
    const [rows] = await db.query('SELECT * FROM violations ORDER BY id DESC');
    return rows;
};

// Add a violation
exports.addViolation = async (v) => {
    const { entry_records_id, violation_type, description, severity, status } = v;
    const [result] = await db.query(
        `INSERT INTO violations
        (entry_records_id, violation_type, description, severity, status)
        VALUES (?, ?, ?, ?, ?)`,
        [entry_records_id, violation_type, description || null, severity, status]
    );
    return result;
};

// Update a violation
exports.updateViolation = async (id, v) => {
    const { entry_records_id, violation_type, description, severity, status } = v;
    const [result] = await db.query(
        `UPDATE violations
        SET entry_records_id = ?, violation_type = ?, description = ?,
            severity = ?, status = ?
        WHERE id = ?`,
        [entry_records_id, violation_type, description || null, severity, status, id]
    );
    return result;
};

// Delete a violation
exports.deleteViolation = async (id) => {
    const [result] = await db.query('DELETE FROM violations WHERE id = ?', [id]);
    return result;
};

// LEFT JOIN report: every violation, with its entry record if one exists
exports.getViolationsWithEntries = async () => {
    const [rows] = await db.query(`
        SELECT
            violations.id AS violation_id,
            violations.violation_type,
            violations.severity,
            violations.status,
            entry_records.id AS entry_id,
            entry_records.entry_date,
            entry_records.entry_time,
            CONCAT(travelers.first_name, ' ', travelers.last_name) AS traveler_name,
            vehicles.plate_number
        FROM violations
        LEFT JOIN entry_records ON violations.entry_records_id = entry_records.id
        LEFT JOIN travelers ON entry_records.traveler_id = travelers.id
        LEFT JOIN vehicles ON entry_records.vehicle_id = vehicles.id
        ORDER BY violations.id DESC
    `);
    return rows;
};
