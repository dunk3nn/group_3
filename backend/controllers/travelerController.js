const travelerModel = require('../models/travelerModel');

// Get all travelers
exports.getAllTravelers = async (req, res) => {
    try {
        const travelers = await travelerModel.getAllTravelers();

        res.status(200).json({
            success: true,
            data: travelers
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Add a traveler
exports.addTraveler = async (req, res) => {
    try {
        const result = await travelerModel.addTraveler(req.body);

        res.status(201).json({
            success: true,
            message: 'Traveler added successfully!',
            id: result.insertId
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Update a traveler
exports.updateTraveler = async (req, res) => {
    try {
        const result = await travelerModel.updateTraveler(req.params.id, req.body);

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Traveler not found.'
            });
        }

        res.status(200).json({
            success: true,
            message: 'Traveler updated successfully!'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Delete a traveler
exports.deleteTraveler = async (req, res) => {
    try {
        const result = await travelerModel.deleteTraveler(req.params.id);

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Traveler not found.'
            });
        }

        res.status(200).json({
            success: true,
            message: 'Traveler deleted successfully!'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};