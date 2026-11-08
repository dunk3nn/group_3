const express = require('express');
const router = express.Router();

const userController = require('../controllers/userController');

// GET registered users
router.get('/', userController.getAllUsers);

// GET users with roles (INNER JOIN)
router.get('/report', userController.getUsersWithRoles);

// GET available roles
router.get('/roles', userController.getAllRoles);

// POST add user
router.post('/', userController.createUser);

// PUT edit user
router.put('/:id', userController.updateUser);

// DELETE (deactivate) user
router.delete('/:id', userController.deactivateUser);

module.exports = router;