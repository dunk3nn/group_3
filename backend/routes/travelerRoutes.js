const express = require('express');
const router = express.Router();

const travelerController = require('../controllers/travelerController');

console.log('GET TRAVELERS ROUTE LOADED');
router.get('/', travelerController.getAllTravelers);
// Add a traveler
router.post('/', travelerController.addTraveler);

// Update a traveler
router.put('/:id', travelerController.updateTraveler);

// Delete a traveler
router.delete('/:id', travelerController.deleteTraveler);

module.exports = router;