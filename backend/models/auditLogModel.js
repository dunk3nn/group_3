const db = require('../config/db');

// Audit log is read-only from the pages
exports.getAllAuditLogs = async () => {
    const [rows] = await db.query('SELECT * FROM audit_logs ORDER BY id DESC');
    return rows;
};

// Adds one row to the audit log (never updates or deletes)
exports.addAuditLog = async (action, tableName, recordId, details) => {
    try {
        const [users] = await db.query('SELECT id FROM users ORDER BY id LIMIT 1');
        const userId = users.length ? users[0].id : null;
        await db.query(
            `INSERT INTO audit_logs (users_id, action, table_name, record_id, details)
             VALUES (?, ?, ?, ?, ?)`,
            [userId, action, tableName, recordId, details]
        );
    } catch (error) {
        console.error('Audit log failed:', error.message);
    }
};