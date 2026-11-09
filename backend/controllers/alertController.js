const alertModel = require('../models/alertModel');
const auditLogModel = require('../models/auditLogModel');

exports.getAllAlerts = async (req, res) => {
    try {
        const records = await alertModel.getAllAlerts();
        res.status(200).json({ success: true, data: records });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};

exports.addAlert = async (req, res) => {
    try {
        if (!req.body.alert_type || String(req.body.alert_type).trim() === '') {
            return res.status(400).json({ success: false, message: 'Alert type is required.' });
        }
        if (!req.body.message || String(req.body.message).trim() === '') {
            return res.status(400).json({ success: false, message: 'Message is required.' });
        }
        const result = await alertModel.addAlert(req.body);
        await auditLogModel.addAuditLog('CREATE', 'alerts', result.insertId, 'Created alert: ' + req.body.alert_type);
        res.status(201).json({ success: true, message: 'Alert added successfully!', id: result.insertId });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};

exports.updateAlert = async (req, res) => {
    try {
        if (!req.body.alert_type || String(req.body.alert_type).trim() === '') {
            return res.status(400).json({ success: false, message: 'Alert type is required.' });
        }
        if (!req.body.message || String(req.body.message).trim() === '') {
            return res.status(400).json({ success: false, message: 'Message is required.' });
        }
        const result = await alertModel.updateAlert(req.params.id, req.body);
        if (result.affectedRows === 0) {
            return res.status(404).json({ success: false, message: 'Alert not found.' });
        }
        await auditLogModel.addAuditLog('UPDATE', 'alerts', req.params.id, 'Updated alert: ' + req.body.alert_type);
        res.status(200).json({ success: true, message: 'Alert updated successfully!' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};

exports.deleteAlert = async (req, res) => {
    try {
        const result = await alertModel.deleteAlert(req.params.id);
        if (result.affectedRows === 0) {
            return res.status(404).json({ success: false, message: 'Alert not found.' });
        }
        await auditLogModel.addAuditLog('DELETE', 'alerts', req.params.id, 'Deleted alert #' + req.params.id);
        res.status(200).json({ success: true, message: 'Alert deleted successfully!' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};