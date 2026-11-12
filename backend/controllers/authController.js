const bcrypt = require('bcrypt');
const db = require('../config/db');

// REGISTER ACCOUNT
const register = async (req, res) => {
    try {
        const { username, email, password } = req.body;

        if (!username || !email || !password) {
            return res.status(400).json({
                message: 'Please complete all fields.'
            });
        }

        const cleanUsername = username.trim();
        const cleanEmail = email.trim().toLowerCase();

        if (password.length < 8) {
            return res.status(400).json({
                message: 'Password must be at least 8 characters.'
            });
        }

        const [existing] = await db.query(
            'SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1',
            [cleanUsername, cleanEmail]
        );

        if (existing.length > 0) {
            return res.status(409).json({
                message: 'Username or email is already registered.'
            });
        }

        // Public registrations are always Personnel accounts.
        const [roles] = await db.query(
            'SELECT id FROM roles WHERE LOWER(role_name) = ? LIMIT 1',
            ['personnel']
        );

        if (roles.length === 0) {
            return res.status(500).json({
                message: 'Personnel role was not found in the database.'
            });
        }

        const hashedPassword = await bcrypt.hash(password, 10);

        const [result] = await db.query(
            `INSERT INTO users
             (username, email, password, role_id, is_active)
             VALUES (?, ?, ?, ?, 1)`,
            [cleanUsername, cleanEmail, hashedPassword, roles[0].id]
        );

        return res.status(201).json({
            message: 'Account created successfully. You can now sign in.',
            user_id: result.insertId
        });

    } catch (error) {
        console.error('Registration error:', error);

        return res.status(500).json({
            message: 'Unable to create account.'
        });
    }
};


// LOGIN ACCOUNT
const login = async (req, res) => {
    try {
        const { username, password } = req.body;

        if (!username || !password) {
            return res.status(400).json({
                message: 'Username and password are required.'
            });
        }

        const [users] = await db.query(
            `SELECT
                users.id,
                users.username,
                users.email,
                users.password,
                users.role_id,
                users.is_active,
                roles.role_name
             FROM users
             INNER JOIN roles ON users.role_id = roles.id
             WHERE users.username = ?
             LIMIT 1`,
            [username.trim()]
        );

        if (users.length === 0) {
            return res.status(401).json({
                message: 'Invalid username or password.'
            });
        }

        const user = users[0];

        if (!user.is_active) {
            return res.status(403).json({
                message: 'Your account is inactive. Contact your administrator.'
            });
        }

        const passwordMatches = await bcrypt.compare(
            password,
            user.password
        );

        if (!passwordMatches) {
            return res.status(401).json({
                message: 'Invalid username or password.'
            });
        }

        return res.json({
            message: 'Login successful.',
            user: {
                id: user.id,
                username: user.username,
                email: user.email,
                role_id: user.role_id,
                role: user.role_name
            }
        });

    } catch (error) {
        console.error('Login error:', error);

        return res.status(500).json({
            message: 'Unable to log in.'
        });
    }
};

module.exports = {
    register,
    login
};