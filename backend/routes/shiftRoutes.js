// Shift routes
const express = require('express');
const router = express.Router();
const shiftController = require('../controllers/shiftController');

// Shift endpoints
router.get('/', shiftController.getAllShifts);
router.get('/:id', shiftController.getShiftById);
router.post('/', shiftController.addShift);
router.put('/:id', shiftController.updateShift);
router.delete('/:id', shiftController.deleteShift);

module.exports = router;