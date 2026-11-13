const bcrypt = require('bcrypt');
const userModel = require('../models/userModel');

// Get all users
const getAllUsers = async (req, res) => {
    try {
        const users = await userModel.getAllUsers();
        res.json(users);
    } catch (error) {
        console.error(error);
        res.status(500).json({ message: 'Failed to retrieve users' });
    }
};

// Create user
const createUser = async (req, res) => {
    try {
        const { username, email, password, role_id, is_active = 1 } = req.body;

        if (!username || !email || !password || !role_id) {
            return res.status(400).json({ message: 'All fields are required' });
        }

        if (
            typeof username !== 'string' ||
            typeof email !== 'string' ||
            typeof password !== 'string' ||
            ![0, 1].includes(Number(is_active)) ||
            !Number.isInteger(Number(role_id)) ||
            Number(role_id) < 1
        ) {
            return res.status(400).json({ message: 'Invalid input' });
        }

        if (password.length < 8) {
            return res.status(400).json({
                message: 'Password must be at least 8 characters'
            });
        }

        const cleanUsername = username.trim();
        const cleanEmail = email.trim();

        const existingUser = await userModel.checkExistingUser(
            cleanUsername,
            cleanEmail
        );

        if (existingUser) {
            return res.status(409).json({
                message: 'Username or email already exists'
            });
        }

        const hashedPassword = await bcrypt.hash(password, 10);

        const result = await userModel.createUser(
            cleanUsername,
            cleanEmail,
            hashedPassword,
            Number(role_id),
            Number(is_active)
        );

        res.status(201).json({
            message: 'User created successfully',
            user_id: result.insertId
        });
    } catch (error) {
        console.error(error);
        res.status(500).json({ message: 'Failed to create user' });
    }
};

// Update user
const updateUser = async (req, res) => {
    try {
        const id = Number(req.params.id);
        const { username, email, role_id, password, is_active = 1 } = req.body;

        if (!Number.isInteger(id) || id < 1) {
            return res.status(400).json({ message: 'Invalid user ID' });
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
            Number(role_id) < 1 ||
            ![0, 1].includes(Number(is_active))
        ) {
            return res.status(400).json({ message: 'Invalid input' });
        }

        if (password && password.length < 8) {
            return res.status(400).json({
                message: 'Password must be at least 8 characters'
            });
        }

        const user = await userModel.getUserById(id);

        if (!user) {
            return res.status(404).json({ message: 'User not found' });
        }

        const cleanUsername = username.trim();
        const cleanEmail = email.trim();

        const existingUser = await userModel.checkExistingUser(
            cleanUsername,
            cleanEmail,
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
            cleanUsername,
            cleanEmail,
            Number(role_id),
            hashedPassword,
            Number(is_active)
        );

        res.json({ message: 'User updated successfully' });
    } catch (error) {
        console.error(error);
        res.status(500).json({ message: 'Failed to update user' });
    }
};

// Deactivate user
const deactivateUser = async (req, res) => {
    try {
        const id = Number(req.params.id);

        if (!Number.isInteger(id) || id < 1) {
            return res.status(400).json({ message: 'Invalid user ID' });
        }

        const result = await userModel.deactivateUser(id);

        if (result.affectedRows === 0) {
            return res.status(404).json({
                message: 'User not found or already inactive'
            });
        }

        res.json({ message: 'User deactivated successfully' });
    } catch (error) {
        console.error(error);
        res.status(500).json({ message: 'Failed to deactivate user' });
    }
};

// Get users with roles
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

// Get available roles
const getAllRoles = async (req, res) => {
    try {
        const roles = await userModel.getAllRoles();
        res.json(roles);
    } catch (error) {
        console.error(error);
        res.status(500).json({ message: 'Failed to retrieve roles' });
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