const vehicleModel = require('../models/vehicleModel');

// Get all vehicles
exports.getAllVehicles = async (req, res) => {
    try {
        const vehicles = await vehicleModel.getAllVehicles();

        res.status(200).json({
            success: true,
            data: vehicles
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Add a vehicle
exports.addVehicle = async (req, res) => {
    try {
        const result = await vehicleModel.addVehicle(req.body);

        res.status(201).json({
            success: true,
            message: 'Vehicle added successfully!',
            id: result.insertId
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Update a vehicle
exports.updateVehicle = async (req, res) => {
    try {
        const result = await vehicleModel.updateVehicle(
            req.params.id,
            req.body
        );

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Vehicle not found.'
            });
        }

        res.status(200).json({
            success: true,
            message: 'Vehicle updated successfully!'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};

// Delete a vehicle
exports.deleteVehicle = async (req, res) => {
    try {
        const result = await vehicleModel.deleteVehicle(req.params.id);

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Vehicle not found.'
            });
        }

        res.status(200).json({
            success: true,
            message: 'Vehicle deleted successfully!'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
};