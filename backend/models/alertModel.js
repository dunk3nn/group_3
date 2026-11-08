const db = require('../config/db');

// Get all alerts
exports.getAllAlerts = async () => {
    const [rows] = await db.query('SELECT * FROM alerts ORDER BY id DESC');
    return rows;
};

// Add an alert
exports.addAlert = async (a) => {
    const { violations_id, checkpoints_id, alert_type, message, is_read } = a;
    const [result] = await db.query(
        `INSERT INTO alerts (violations_id, checkpoints_id, alert_type, message, is_read)
        VALUES (?, ?, ?, ?, ?)`,
        [violations_id || null, checkpoints_id || null, alert_type, message, is_read ?? 0]
    );
    return result;
};

// Update an alert
exports.updateAlert = async (id, a) => {
    const { violations_id, checkpoints_id, alert_type, message, is_read } = a;
    const [result] = await db.query(
        `UPDATE alerts
        SET violations_id = ?, checkpoints_id = ?, alert_type = ?, message = ?, is_read = ?
        WHERE id = ?`,
        [violations_id || null, checkpoints_id || null, alert_type, message, is_read ?? 0, id]
    );
    return result;
};

// Delete an alert
exports.deleteAlert = async (id) => {
    const [result] = await db.query('DELETE FROM alerts WHERE id = ?', [id]);
    return result;
};
