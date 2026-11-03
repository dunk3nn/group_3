// Assignment routes
const express = require('express');
const router = express.Router();
const assignmentController = require('../controllers/assignmentController');

// Assignment endpoints
router.get('/', assignmentController.getAllAssignments);
router.get('/:id', assignmentController.getAssignmentById);
router.post('/', assignmentController.addAssignment);
router.put('/:id', assignmentController.updateAssignment);
router.delete('/:id', assignmentController.deleteAssignment);

module.exports = router;