
const bcrypt = require('bcrypt');
const userModel = require('../models/userModel');

// GET all active users
const getAllUsers = async (req, res) => {
    try {
        const users = await userModel.getAllUsers();
        res.json(users);
    } catch (error) {
        console.error(error);
        res.status(500).json({ message: 'Failed to retrieve users' });
    }
};

// POST add user
const createUser = async (req, res) => {
    try {
        const { username, email, password, role_id } = req.body;

        if (!username || !email || !password || !role_id) {
            return res.status(400).json({
                message: 'All fields are required'
            });
        }

        if (
            typeof username !== 'string' ||
            typeof email !== 'string' ||
            typeof password !== 'string' ||
            !Number.isInteger(Number(role_id)) ||
            Number(role_id) < 1
        ) {
            return res.status(400).json({
                message: 'Invalid input'
            });
        }

        if (password.length < 8) {
            return res.status(400).json({
                message: 'Password must be at least 8 characters'
            });
        }

        const existingUser = await userModel.checkExistingUser(
            username.trim(),
            email.trim()
        );

        if (existingUser) {
            return res.status(409).json({
                message: 'Username or email already exists'
            });
        }

        const hashedPassword = await bcrypt.hash(password, 10);

        const result = await userModel.createUser(
            username.trim(),
            email.trim(),
            hashedPassword,
            Number(role_id)
        );

        res.status(201).json({
            message: 'User created successfully',
            user_id: result.insertId
        });

    } catch (error) {
        console.error(error);
        res.status(500).json({
            message: 'Failed to create user'
        });
    }
};

// PUT edit user
const updateUser = async (req, res) => {
    try {
        const id = Number(req.params.id);
        const { username, email, role_id, password } = req.body;

        if (!Number.isInteger(id) || id < 1) {
            return res.status(400).json({
                message: 'Invalid user ID'
            });
        }

        if (!username || !email || !role_id) {
            return res.status(400).json({
                message: 'Username, email, and role are required'
            });
        }

        if (
            typeof username !== 'string' ||
            typeof email !== 'string' ||
            (password !== undefined && typeof password !== 'string') ||
            !Number.isInteger(Number(role_id)) ||
            Number(role_id) < 1
        ) {
            return res.status(400).json({
                message: 'Invalid input'
            });
        }

        if (
            password !== undefined &&
            password.length > 0 &&
            password.length < 8
        ) {
            return res.status(400).json({
                message: 'Password must be at least 8 characters'
            });
        }

        const user = await userModel.getUserById(id);

        if (!user) {
            return res.status(404).json({
                message: 'User not found'
            });
        }

        const existingUser = await userModel.checkExistingUser(
            username.trim(),
            email.trim(),
            id
        );

        if (existingUser) {
            return res.status(409).json({
                message: 'Username or email already exists'
            });
        }

        const hashedPassword = password
            ? await bcrypt.hash(password, 10)
            : null;

        await userModel.updateUser(
            id,
            username.trim(),
            email.trim(),
            Number(role_id),
            hashedPassword
        );

        res.json({
            message: 'User updated successfully'
        });

    } catch (error) {
        console.error(error);
        res.status(500).json({
            message: 'Failed to update user'
        });
    }
};

// DELETE (deactivate) user
const deactivateUser = async (req, res) => {
    try {
        const id = Number(req.params.id);

        if (!Number.isInteger(id) || id < 1) {
            return res.status(400).json({
                message: 'Invalid user ID'
            });
        }

        const result = await userModel.deactivateUser(id);

        if (result.affectedRows === 0) {
            return res.status(404).json({
                message: 'User not found or already inactive'
            });
        }

        res.json({
            message: 'User deactivated successfully'
        });

    } catch (error) {
        console.error(error);
        res.status(500).json({
            message: 'Failed to deactivate user'
        });
    }
};

// GET users with roles
const getUsersWithRoles = async (req, res) => {
    try {
        const users = await userModel.getUsersWithRoles();
        res.json(users);
    } catch (error) {
        console.error(error);
        res.status(500).json({
            message: 'Failed to retrieve user report'
        });
    }
};

// GET available roles
const getAllRoles = async (req, res) => {
    try {
        const roles = await userModel.getAllRoles();
        res.json(roles);
    } catch (error) {
        console.error(error);
        res.status(500).json({
            message: 'Failed to retrieve roles'
        });
    }
};

module.exports = {
    getAllUsers,
    createUser,
    updateUser,
    deactivateUser,
    getUsersWithRoles,
    getAllRoles
};
