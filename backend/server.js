const express = require('express');
const cors = require('cors');
require('dotenv').config();

const db = require('./config/db');

const travelerRoutes = require('./routes/travelerRoutes');
const vehicleRoutes = require('./routes/vehicleRoutes');
const entryRecordRoutes = require('./routes/entryRecordRoutes');
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

const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});