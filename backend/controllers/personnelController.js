const personnelModel = require('../models/personnelModel');

// Get all personnel
exports.getAllPersonnel = async (req, res) => {
    try {
        const personnel = await personnelModel.getAllPersonnel();

        res.status(200).json({
            success: true,
            data: personnel
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Get personnel by ID
exports.getPersonnelById = async (req, res) => {
    try {
        const personnel = await personnelModel.getPersonnelById(req.params.id);

        if (!personnel) {
            return res.status(404).json({
                success: false,
                message: 'Personnel not found.'
            });
        }

        res.status(200).json({
            success: true,
            data: personnel
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Add personnel
exports.addPersonnel = async (req, res) => {
    try {
        const result = await personnelModel.addPersonnel(req.body);

        res.status(201).json({
            success: true,
            message: 'Personnel added successfully!',
            id: result.insertId
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Update personnel
exports.updatePersonnel = async (req, res) => {
    try {
        const result = await personnelModel.updatePersonnel(req.params.id, req.body);

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Personnel not found.'
            });
        }

        res.status(200).json({
            success: true,
            message: 'Personnel updated successfully!'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Delete personnel
exports.deletePersonnel = async (req, res) => {
    try {
        const result = await personnelModel.deletePersonnel(req.params.id);

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Personnel not found.'
            });
        }

        res.status(200).json({
            success: true,
            message: 'Personnel deleted successfully!'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};