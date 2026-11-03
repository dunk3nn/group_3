const auditLogModel = require('../models/auditLogModel');

exports.getAllAuditLogs = async (req, res) => {
    try {
        const records = await auditLogModel.getAllAuditLogs();
        res.status(200).json({ success: true, data: records });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};
