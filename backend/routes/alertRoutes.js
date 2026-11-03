const express = require('express');
const router = express.Router();

const alertController = require('../controllers/alertController');

router.get('/', alertController.getAllAlerts);
router.post('/', alertController.addAlert);
router.put('/:id', alertController.updateAlert);
router.delete('/:id', alertController.deleteAlert);

module.exports = router;
