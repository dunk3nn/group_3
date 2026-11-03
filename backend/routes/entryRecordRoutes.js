const express = require('express');
const router = express.Router();

const entryRecordController = require('../controllers/entryRecordController');

// Get all entry records
router.get('/', entryRecordController.getAllEntryRecords);

// Add an entry record
router.post('/', entryRecordController.addEntryRecord);

// Delete an entry record
router.delete('/:id', entryRecordController.deleteEntryRecord);

module.exports = router;