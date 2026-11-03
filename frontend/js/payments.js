const express = require('express');
const mysql = require('mysql2/promise');
const cors = require('cors');
require('dotenv').config();

const app = express();
app.use(cors());
app.use(express.json());

// Database connection pool configuration
const db = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'border_systems',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// AUTO-INITIALIZE TABLES: This fixes the missing table error permanently on startup
async function initializeDatabase() {
    try {
        await db.query(`
            CREATE TABLE IF NOT EXISTS entry_records (
                entry_id INT AUTO_INCREMENT PRIMARY KEY,
                vehicle_type VARCHAR(50) NOT NULL,
                checkin_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);
        await db.query(`
            CREATE TABLE IF NOT EXISTS fee_rules (
                fee_rule_id INT AUTO_INCREMENT PRIMARY KEY,
                vehicle_type VARCHAR(50) NOT NULL,
                base_fee DECIMAL(10,2) NOT NULL,
                description TEXT
            )
        `);
        await db.query(`
            CREATE TABLE IF NOT EXISTS penalties (
                penalty_id INT AUTO_INCREMENT PRIMARY KEY,
                violation_type VARCHAR(100) NOT NULL,
                penalty_amount DECIMAL(10,2) NOT NULL,
                description TEXT
            )
        `);
        await db.query(`
            CREATE TABLE IF NOT EXISTS payments (
                payment_id INT AUTO_INCREMENT PRIMARY KEY,
                entry_record_id INT NOT NULL,
                fee_rule_id INT NOT NULL,
                penalty_id INT DEFAULT NULL,
                amount_paid DECIMAL(10,2) NOT NULL,
                payment_method VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);
        console.log("Database tables verified/created successfully.");
    } catch (err) {
        console.error("Database initialization error:", err.message);
    }
}

// --- FEE RULES CRUD ---
app.get('/api/fee-rules', async (req, res) => {
    try {
        const [rows] = await db.query('SELECT * FROM fee_rules');
        res.json(Array.isArray(rows) ? rows : []);
    } catch (err) {
        res.json([]);
    }
});

app.post('/api/fee-rules', async (req, res) => {
    try {
        const { vehicle_type, base_fee, description } = req.body;
        const query = 'INSERT INTO fee_rules (vehicle_type, base_fee, description) VALUES (?, ?, ?)';
        const [result] = await db.query(query, [vehicle_type, base_fee, description]);
        res.status(201).json({ message: "Fee rule added successfully", id: result.insertId });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// --- PENALTIES CRUD ---
app.get('/api/penalties', async (req, res) => {
    try {
        const [rows] = await db.query('SELECT * FROM penalties');
        res.json(Array.isArray(rows) ? rows : []);
    } catch (err) {
        res.json([]);
    }
});

app.post('/api/penalties', async (req, res) => {
    try {
        const { violation_type, penalty_amount, description } = req.body;
        const query = 'INSERT INTO penalties (violation_type, penalty_amount, description) VALUES (?, ?, ?)';
        const [result] = await db.query(query, [violation_type, penalty_amount, description]);
        res.status(201).json({ message: "Penalty added successfully", id: result.insertId });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// --- PAYMENTS CRUD ---
app.get('/api/payments', async (req, res) => {
    try {
        const [rows] = await db.query('SELECT * FROM payments');
        res.json(Array.isArray(rows) ? rows : []);
    } catch (err) {
        res.json([]);
    }
});

app.post('/api/payments', async (req, res) => {
    try {
        const { entry_record_id, fee_rule_id, penalty_id, amount_paid, payment_method } = req.body;
        const query = `
            INSERT INTO payments (entry_record_id, fee_rule_id, penalty_id, amount_paid, payment_method) 
            VALUES (?, ?, ?, ?, ?)
        `;
        const [result] = await db.query(query, [entry_record_id, fee_rule_id, penalty_id || null, amount_paid, payment_method]);
        res.status(201).json({ message: "Payment recorded successfully", payment_id: result.insertId });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// --- REPORTS (LEFT JOIN & UNION) ---
app.get('/api/reports/no-payment', async (req, res) => {
    try {
        const query = `
            SELECT e.entry_id, e.vehicle_type, e.checkin_time, 'Pending Payment' AS status
            FROM entry_records e
            LEFT JOIN payments p ON e.entry_id = p.entry_record_id
            WHERE p.payment_id IS NULL;
        `;
        const [rows] = await db.query(query);
        res.json(Array.isArray(rows) ? rows : []);
    } catch (err) {
        res.json([]);
    }
});

app.get('/api/reports/penalties-vs-payments', async (req, res) => {
    try {
        const query = `
            SELECT penalty_id AS record_id, penalty_amount AS penalty_pay, 0 AS amount_paid, 'Unpaid Penalty' AS balance_status FROM penalties
            UNION
            SELECT payment_id AS record_id, 0 AS penalty_pay, amount_paid, 'Paid Balance' AS balance_status FROM payments;
        `;
        const [rows] = await db.query(query);
        res.json(Array.isArray(rows) ? rows : []);
    } catch (err) {
        res.json([]);
    }
});

// Start server and initialize tables automatically
const PORT = process.env.PORT || 3000;
app.listen(PORT, async () => {
    await initializeDatabase();
    console.log(`Server is running on port ${PORT}`);
});