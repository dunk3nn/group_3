// Assignment controller
const assignmentModel = require('../models/assignmentModel');

// Get all assignments
exports.getAllAssignments = async (req, res) => {
    try {
        const assignments = await assignmentModel.getAllAssignments();

        res.status(200).json({
            success: true,
            data: assignments
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Get assignment by ID
exports.getAssignmentById = async (req, res) => {
    try {
        const assignment = await assignmentModel.getAssignmentById(req.params.id);

        if (!assignment) {
            return res.status(404).json({
                success: false,
                message: 'Assignment not found.'
            });
        }

        res.status(200).json({
            success: true,
            data: assignment
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Add assignment
exports.addAssignment = async (req, res) => {
    try {
        const result = await assignmentModel.addAssignment(req.body);

        res.status(201).json({
            success: true,
            message: 'Assignment added successfully.',
            id: result.insertId
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Update assignment
exports.updateAssignment = async (req, res) => {
    try {
        const result = await assignmentModel.updateAssignment(req.params.id, req.body);

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Assignment not found.'
            });
        }

        res.status(200).json({
            success: true,
            message: 'Assignment updated successfully.'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Delete assignment
exports.deleteAssignment = async (req, res) => {
    try {
        const result = await assignmentModel.deleteAssignment(req.params.id);

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Assignment not found.'
            });
        }

        res.status(200).json({
            success: true,
            message: 'Assignment deleted successfully.'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};