const express = require('express');
const mysql = require('mysql2/promise');
const cors = require('cors');
require('dotenv').config();

const db = require('./config/db');

const travelerRoutes = require('./routes/travelerRoutes');
const vehicleRoutes = require('./routes/vehicleRoutes');
const entryRecordRoutes = require('./routes/entryRecordRoutes');
const violationRoutes = require('./routes/violationRoutes');
const alertRoutes = require('./routes/alertRoutes');
const auditLogRoutes = require('./routes/auditLogRoutes');
const userRoutes = require('./routes/userRoutes');
const personnelRoutes = require('./routes/personnelRoutes');
const shiftRoutes = require('./routes/shiftRoutes');
const assignmentRoutes = require('./routes/assignmentRoutes');
const checkpointRoutes = require('./routes/checkpointRoutes');

const app = express();
app.use(cors());
app.use(express.json());

// API routes
app.use('/api/travelers', travelerRoutes);
app.use('/api/vehicles', vehicleRoutes);
app.use('/api/entry-records', entryRecordRoutes);
app.use('/api/violations', violationRoutes);
app.use('/api/alerts', alertRoutes);
app.use('/api/audit-logs', auditLogRoutes);
app.use('/api/users', userRoutes);
app.use('/api/personnel', personnelRoutes);
app.use('/api/shifts', shiftRoutes);
app.use('/api/assignments', assignmentRoutes);
app.use('/api/checkpoints', checkpointRoutes);

// Home route
app.get('/', (req, res) => {
    res.json({
        message: 'Border Entry Payment and Personnel Tracking System API is running!'
    });
});

// Test route
app.get('/test', (req, res) => {
    res.send('TEST ROUTE IS WORKING!');
});

// Check MySQL connection
db.getConnection()
    .then(connection => {
        console.log('MySQL connected successfully!');
        connection.release();
    })
    .catch(error => {
        console.error('MySQL connection failed:', error.message);
    });

// --- 1. FEE RULES ROUTES (/api/fee-rules) ---
app.get('/api/fee-rules', async (req, res) => {
    try {
        const [rows] = await db.query('SELECT * FROM fee_rules');
        res.json(Array.isArray(rows) ? rows : []);
    } catch (err) {
        console.error("Fee Rules GET Error:", err.message);
        res.json([]);
    }
});

app.post('/api/fee-rules', async (req, res) => {
    try {
        const { vehicle_type_id, entry_type_id, free_hours, overstay_rate_per_hour, effective_date } = req.body;
        const query = `
            INSERT INTO fee_rules (vehicle_type_id, entry_type_id, free_hours, overstay_rate_per_hour, effective_date) 
            VALUES (?, ?, ?, ?, ?)
        `;
        const [result] = await db.query(query, [vehicle_type_id, entry_type_id, free_hours, overstay_rate_per_hour, effective_date]);
        res.status(201).json({ message: "Fee rule added successfully", id: result.insertId });
    } catch (err) {
        console.error("Fee Rules POST Error:", err.message);
        res.status(500).json({ error: err.message });
    }
});

// --- 2. PENALTIES / VIOLATIONS ROUTES (/api/penalties) ---
app.get('/api/penalties', async (req, res) => {
    try {
        const [rows] = await db.query('SELECT * FROM penalties');
        res.json(Array.isArray(rows) ? rows : []);
    } catch (err) {
        console.error("Penalties GET Error:", err.message);
        res.json([]);
    }
});

app.post('/api/penalties', async (req, res) => {
    try {
        const { entry_id, fee_rule_id, overstay_hours, penalty_amount, date_issued } = req.body;
        const query = `
            INSERT INTO penalties (entry_id, fee_rule_id, overstay_hours, penalty_amount, date_issued) 
            VALUES (?, ?, ?, ?, ?)
        `;
        const [result] = await db.query(query, [entry_id, fee_rule_id, overstay_hours, penalty_amount, date_issued]);
        res.status(201).json({ message: "Penalty added successfully", id: result.insertId });
    } catch (err) {
        console.error("Penalties POST Error:", err.message);
        res.status(500).json({ error: err.message });
    }
});

// --- 3. PAYMENTS ROUTES (/api/payments) ---
app.get('/api/payments', async (req, res) => {
    try {
        const [rows] = await db.query('SELECT * FROM payments');
        res.json(Array.isArray(rows) ? rows : []);
    } catch (err) {
        console.error("Payments GET Error:", err.message);
        res.json([]);
    }
});

app.post('/api/payments', async (req, res) => {
    try {
        const { transaction_id, amount_paid, payment_method, status } = req.body;
        const query = `
            INSERT INTO payments (transaction_id, amount_paid, payment_method, status) 
            VALUES (?, ?, ?, ?)
        `;
        const [result] = await db.query(query, [transaction_id, amount_paid, payment_method, status || 'Pending']);
        res.status(201).json({ message: "Payment recorded successfully", id: result.insertId });
    } catch (err) {
        console.error("Payments POST Error:", err.message);
        res.status(500).json({ error: err.message });
    }
});

// --- 4. REPORTS (/api/reports/no-payment & /api/reports/penalties-vs-payments) ---
app.get('/api/reports/no-payment', async (req, res) => {
    try {
        const query = `
            SELECT e.id AS entry_id, 'Vehicle' AS vehicle_type, e.entry_date AS checkin_time, 'Pending Payment' AS status
            FROM entry_records e
            LEFT JOIN payments p ON e.id = p.id
            WHERE p.id IS NULL;
        `;
        const [rows] = await db.query(query);
        res.json(Array.isArray(rows) ? rows : []);
    } catch (err) {
        console.error("No Payment Report Error:", err.message);
        res.json([]);
    }
});

app.get('/api/reports/penalties-vs-payments', async (req, res) => {
    try {
        const query = `
            SELECT id AS record_id, 0 AS penalty_pay, amount_paid, 'Paid Balance' AS balance_status FROM payments
            UNION
            SELECT id AS record_id, 0 AS penalty_pay, amount_paid, 'Active Record' AS balance_status FROM payments;
        `;
        const [rows] = await db.query(query);
        res.json(Array.isArray(rows) ? rows : []);
    } catch (err) {
        console.error("Penalties vs Payments Report Error:", err.message);
        res.json([]);
    }
});

// Start the server
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});