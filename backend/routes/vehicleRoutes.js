const express = require('express');
const router = express.Router();

const vehicleController = require('../controllers/vehicleController');

// Get all vehicles
router.get('/', vehicleController.getAllVehicles);

// Add a vehicle
router.post('/', vehicleController.addVehicle);

// Update a vehicle
router.put('/:id', vehicleController.updateVehicle);

// Delete a vehicle
router.delete('/:id', vehicleController.deleteVehicle);

module.exports = router;