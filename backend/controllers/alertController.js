const alertModel = require('../models/alertModel');

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
        const result = await alertModel.addAlert(req.body);
        res.status(201).json({ success: true, message: 'Alert added successfully!', id: result.insertId });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};

exports.updateAlert = async (req, res) => {
    try {
        const result = await alertModel.updateAlert(req.params.id, req.body);
        if (result.affectedRows === 0) {
            return res.status(404).json({ success: false, message: 'Alert not found.' });
        }
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
        res.status(200).json({ success: true, message: 'Alert deleted successfully!' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
};
