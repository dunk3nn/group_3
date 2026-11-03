const express = require('express');
const router = express.Router();

const violationController = require('../controllers/violationController');

// Get all violations
router.get('/', violationController.getAllViolations);

// LEFT JOIN report: violations with their entry records
router.get('/report/with-entries', violationController.getViolationsWithEntries);

// Add a violation
router.post('/', violationController.addViolation);

// Update a violation
router.put('/:id', violationController.updateViolation);

// Delete a violation
router.delete('/:id', violationController.deleteViolation);

module.exports = router;
