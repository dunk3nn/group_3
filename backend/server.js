const express = require('express');
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
const authRoutes = require('./routes/authRoutes');

app.use('/api/auth', authRoutes);

// API ROUTES
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

// HOME ROUTE
app.get('/', (req, res) => {
    res.json({
        message: 'Border Entry Payment and Personnel Tracking System API is running!'
    });
});

// TEST ROUTE
app.get('/test', (req, res) => {
    res.send('TEST ROUTE IS WORKING!');
});

// DATABASE CONNECTION
db.getConnection()
    .then(connection => {
        console.log('MySQL connected successfully!');
        connection.release();
    })
    .catch(error => {
        console.error('MySQL connection failed:', error.message);
    });

// FEE RULES
app.get('/api/fee-rules', async (req, res) => {
    try {
        const [rows] = await db.query('SELECT * FROM fee_rules');
        res.json(rows);
    } catch (err) {
        console.error('Fee Rules GET Error:', err.message);
        res.status(500).json({ error: err.message });
    }
});

app.post('/api/fee-rules', async (req, res) => {
    try {
        const {
            vehicle_type_id,
            entry_type_id,
            free_hours,
            overstay_rate_per_hour,
            effective_date
        } = req.body;

        const query = `
            INSERT INTO fee_rules
            (vehicle_type_id, entry_type_id, free_hours,
             overstay_rate_per_hour, effective_date)
            VALUES (?, ?, ?, ?, ?)
        `;

        const [result] = await db.query(query, [
            vehicle_type_id,
            entry_type_id,
            free_hours,
            overstay_rate_per_hour,
            effective_date
        ]);

        res.status(201).json({
            message: 'Fee rule added successfully',
            id: result.insertId
        });
    } catch (err) {
        console.error('Fee Rules POST Error:', err.message);
        res.status(500).json({ error: err.message });
    }
});

// PENALTIES
app.get('/api/penalties', async (req, res) => {
    try {
        const [rows] = await db.query('SELECT * FROM penalties');
        res.json(rows);
    } catch (err) {
        console.error('Penalties GET Error:', err.message);
        res.status(500).json({ error: err.message });
    }
});

app.post('/api/penalties', async (req, res) => {
    try {
        const {
            entry_id,
            fee_rule_id,
            overstay_hours,
            penalty_amount,
            date_issued
        } = req.body;

        const query = `
            INSERT INTO penalties
            (entry_id, fee_rule_id, overstay_hours, penalty_amount, date_issued)
            VALUES (?, ?, ?, ?, ?)
        `;

        const [result] = await db.query(query, [
            entry_id,
            fee_rule_id,
            overstay_hours,
            penalty_amount,
            date_issued
        ]);

        res.status(201).json({
            message: 'Penalty added successfully',
            id: result.insertId
        });
    } catch (err) {
        console.error('Penalties POST Error:', err.message);
        res.status(500).json({ error: err.message });
    }
});

// PAYMENTS
app.get('/api/payments', async (req, res) => {
    try {
        const [rows] = await db.query(`
            SELECT
                id,
                entry_record_id AS entry_id,
                fee_rule_id,
                penalty_id,
                amount_paid,
                payment_method,
                payment_date
            FROM payments
            ORDER BY payment_date DESC
        `);

        res.json(rows);
    } catch (err) {
        console.error('Payments GET Error:', err.message);
        res.status(500).json({ error: err.message });
    }
});

app.post('/api/payments', async (req, res) => {
    try {
        const {
            entry_id,
            fee_rule_id,
            penalty_id,
            amount_paid,
            payment_method
        } = req.body;

        if (
            !entry_id ||
            !fee_rule_id ||
            amount_paid === undefined ||
            amount_paid === null ||
            !payment_method
        ) {
            return res.status(400).json({
                error: 'Please provide all required payment details.'
            });
        }

        if (!Number.isFinite(Number(amount_paid)) || Number(amount_paid) <= 0) {
            return res.status(400).json({
                error: 'Amount must be greater than zero.'
            });
        }

        const query = `
            INSERT INTO payments
            (
                entry_record_id,
                fee_rule_id,
                penalty_id,
                amount_paid,
                payment_method,
                payment_date
            )
            VALUES (?, ?, ?, ?, ?, NOW())
        `;

        const [result] = await db.query(query, [
            entry_id,
            fee_rule_id,
            penalty_id || null,
            amount_paid,
            payment_method
        ]);

        res.status(201).json({
            message: 'Payment recorded successfully!',
            id: result.insertId
        });
    } catch (err) {
        console.error('Payments POST Error:', err.message);
        res.status(500).json({ error: err.message });
    }
});

// REPORT: ENTRIES WITHOUT PAYMENTS
app.get('/api/reports/no-payment', async (req, res) => {
    try {
        const query = `
            SELECT
                e.id AS entry_id,
                'Vehicle' AS vehicle_type,
                e.entry_date AS checkin_time,
                'Pending Payment' AS status
            FROM entry_records e
            LEFT JOIN payments p
                ON e.id = p.entry_record_id
            WHERE p.id IS NULL
        `;

        const [rows] = await db.query(query);
        res.json(rows);
    } catch (err) {
        console.error('No Payment Report Error:', err.message);
        res.status(500).json({ error: err.message });
    }
});

// REPORT: PENALTIES VS PAYMENTS
app.get('/api/reports/penalties-vs-payments', async (req, res) => {
    try {
        const query = `
            SELECT
                id AS record_id,
                0 AS penalty_pay,
                amount_paid,
                'Paid Balance' AS balance_status
            FROM payments

            UNION

            SELECT
                id AS record_id,
                0 AS penalty_pay,
                amount_paid,
                'Active Record' AS balance_status
            FROM payments
        `;

        const [rows] = await db.query(query);
        res.json(rows);
    } catch (err) {
        console.error('Penalties vs Payments Report Error:', err.message);
        res.status(500).json({ error: err.message });
    }
});

// START SERVER
const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});