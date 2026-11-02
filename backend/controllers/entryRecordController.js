const entryRecordModel = require('../models/entryRecordModel');

// Get all entry records
exports.getAllEntryRecords = async (req, res) => {
    try {
        const records = await entryRecordModel.getAllEntryRecords();

        res.status(200).json({
            success: true,
            data: records
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Add an entry record
exports.addEntryRecord = async (req, res) => {
    try {
        const result = await entryRecordModel.addEntryRecord(req.body);

        res.status(201).json({
            success: true,
            message: 'Entry record added successfully!',
            id: result.insertId
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Delete an entry record
exports.deleteEntryRecord = async (req, res) => {
    try {
        const result = await entryRecordModel.deleteEntryRecord(req.params.id);

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Entry record not found.'
            });
        }

        res.status(200).json({
            success: true,
            message: 'Entry record deleted successfully!'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};