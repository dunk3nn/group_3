const db = require('../config/db');

// Audit log is read-only
exports.getAllAuditLogs = async () => {
    const [rows] = await db.query('SELECT * FROM audit_logs ORDER BY id DESC');
    return rows;
};
