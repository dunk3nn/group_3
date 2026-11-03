// Shift controller
const shiftModel = require('../models/shiftModel');

// Get all shifts
exports.getAllShifts = async (req, res) => {
    try {
        const shifts = await shiftModel.getAllShifts();

        res.status(200).json({
            success: true,
            data: shifts
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Get shift by ID
exports.getShiftById = async (req, res) => {
    try {
        const shift = await shiftModel.getShiftById(req.params.id);

        if (!shift) {
            return res.status(404).json({
                success: false,
                message: 'Shift not found.'
            });
        }

        res.status(200).json({
            success: true,
            data: shift
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Add shift
exports.addShift = async (req, res) => {
    try {
        const result = await shiftModel.addShift(req.body);

        res.status(201).json({
            success: true,
            message: 'Shift added successfully.',
            id: result.insertId
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Update shift
exports.updateShift = async (req, res) => {
    try {
        const result = await shiftModel.updateShift(req.params.id, req.body);

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Shift not found.'
            });
        }

        res.status(200).json({
            success: true,
            message: 'Shift updated successfully.'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Delete shift
exports.deleteShift = async (req, res) => {
    try {
        const result = await shiftModel.deleteShift(req.params.id);

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Shift not found.'
            });
        }

        res.status(200).json({
            success: true,
            message: 'Shift deleted successfully.'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};