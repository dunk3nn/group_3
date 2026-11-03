// DATABASE CONNECTION
const db = require('../config/db');


// ==============================
// SHIFT FUNCTIONS
// ==============================

// Get all shifts
exports.getAllShifts = async () => {
    const [rows] = await db.query(
        'SELECT * FROM Shifts'
    );

    return rows;
};

// Get shift by ID
exports.getShiftById = async (id) => {
    const [rows] = await db.query(
        'SELECT * FROM Shifts WHERE id = ?',
        [id]
    );

    return rows[0];
};

// Add a shift
exports.addShift = async (shift) => {
    const { shift_name, start_time, end_time } = shift;

    const [result] = await db.query(
        'INSERT INTO Shifts (shift_name, start_time, end_time) VALUES (?, ?, ?)',
        [shift_name, start_time, end_time]
    );

    return result;
};

// Update a shift
exports.updateShift = async (id, shift) => {
    const { shift_name, start_time, end_time } = shift;

    const [result] = await db.query(
        'UPDATE Shifts SET shift_name = ?, start_time = ?, end_time = ? WHERE id = ?',
        [shift_name, start_time, end_time, id]
    );

    return result;
};

// Delete a shift
exports.deleteShift = async (id) => {
    const [result] = await db.query(
        'DELETE FROM Shifts WHERE id = ?',
        [id]
    );

    return result;
};