const express = require('express');
const router = express.Router();

const personnelController = require('../controllers/personnelController');

// Get all personnel
router.get('/', personnelController.getAllPersonnel);

// Get personnel by ID
router.get('/:id', personnelController.getPersonnelById);

// Add personnel
router.post('/', personnelController.addPersonnel);

// Update personnel
router.put('/:id', personnelController.updatePersonnel);

// Delete personnel
router.delete('/:id', personnelController.deletePersonnel);

module.exports = router;
