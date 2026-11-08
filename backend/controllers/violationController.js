const violationModel = require('../models/violationModel');

// Get all violations
exports.getAllViolations = async (req, res) => {
    try {
        const records = await violationModel.getAllViolations();
        res.status(200).json({ success: true, data: records });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};

// Add a violation
exports.addViolation = async (req, res) => {
    try {
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

// Update a violation
exports.updateViolation = async (req, res) => {
    try {
        const result = await violationModel.updateViolation(req.params.id, req.body);
        if (result.affectedRows === 0) {
            return res.status(404).json({ success: false, message: 'Violation not found.' });
        }
        res.status(200).json({ success: true, message: 'Violation updated successfully!' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};

// Delete a violation
exports.deleteViolation = async (req, res) => {
    try {
        const result = await violationModel.deleteViolation(req.params.id);
        if (result.affectedRows === 0) {
            return res.status(404).json({ success: false, message: 'Violation not found.' });
        }
        res.status(200).json({ success: true, message: 'Violation deleted successfully!' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};

// LEFT JOIN report: violations with their entry records
exports.getViolationsWithEntries = async (req, res) => {
    try {
        const records = await violationModel.getViolationsWithEntries();
        res.status(200).json({ success: true, data: records });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};
