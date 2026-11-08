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

const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});