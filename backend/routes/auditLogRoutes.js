const express = require('express');
const router = express.Router();

const auditLogController = require('../controllers/auditLogController');

// Audit log is read-only
router.get('/', auditLogController.getAllAuditLogs);

module.exports = router;
