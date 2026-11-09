const violationModel = require('../models/violationModel');
const db = require('../config/db');
const auditLogModel = require('../models/auditLogModel');

const SEVERITIES = ['low', 'medium', 'high'];
const STATUSES = ['open', 'resolved', 'dismissed'];

async function validateViolation(body) {
    const entryId = Number(body.entry_records_id);
    if (!Number.isInteger(entryId) || entryId < 1) {
        return 'Entry record ID must be a whole number of 1 or more.';
    }
    if (!body.violation_type || String(body.violation_type).trim() === '') {
        return 'Violation type is required.';
    }
    if (String(body.violation_type).length > 100) {
        return 'Violation type must be 100 characters or less.';
    }
    if (!SEVERITIES.includes(body.severity)) {
        return 'Severity must be low, medium or high.';
    }
    if (!STATUSES.includes(body.status)) {
        return 'Status must be open, resolved or dismissed.';
    }
    const [found] = await db.query('SELECT id FROM entry_records WHERE id = ?', [entryId]);
    if (found.length === 0) {
        return 'That entry record does not exist.';
    }
    return '';
}

exports.getAllViolations = async (req, res) => {
    try {
        const records = await violationModel.getAllViolations();
        res.status(200).json({ success: true, data: records });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};

exports.addViolation = async (req, res) => {
    try {
        req.body.status = req.body.status || 'open';
        const problem = await validateViolation(req.body);
        if (problem) {
            return res.status(400).json({ success: false, message: problem });
        }
        const result = await violationModel.addViolation(req.body);
        res.status(201).json({
            success: true,
            message: 'Violation added successfully!',
            id: result.insertId
        });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};

exports.updateViolation = async (req, res) => {
    try {
        const problem = await validateViolation(req.body);
        if (problem) {
            return res.status(400).json({ success: false, message: problem });
        }
        const result = await violationModel.updateViolation(req.params.id, req.body);
        if (result.affectedRows === 0) {
            return res.status(404).json({ success: false, message: 'Violation not found.' });
        }
        res.status(200).json({ success: true, message: 'Violation updated successfully!' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};

exports.deleteViolation = async (req, res) => {
    try {
        const [rows] = await db.query(
            'SELECT COUNT(*) AS total FROM alerts WHERE violations_id = ?',
            [req.params.id]
        );
        if (rows[0].total > 0) {
            return res.status(409).json({
                success: false,
                message: 'Cannot delete violation #' + req.params.id + ' because it has ' +
                         rows[0].total + ' alert(s). Delete those alerts first.'
            });
        }
        const result = await violationModel.deleteViolation(req.params.id);
        if (result.affectedRows === 0) {
            return res.status(404).json({ success: false, message: 'Violation not found.' });
        }
        res.status(200).json({ success: true, message: 'Violation deleted successfully!' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};

exports.getViolationsWithEntries = async (req, res) => {
    try {
        const records = await violationModel.getViolationsWithEntries();
        res.status(200).json({ success: true, data: records });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};