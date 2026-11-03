// Database connection
const db = require('../config/db');

// Get all assignments
exports.getAllAssignments = async () => {
    const [rows] = await db.query(`
        SELECT
            ca.id,
            ca.personnel_id,
            p.name AS personnel_name,
            ca.shift_id,
            s.shift_name,
            ca.checkpoint_id,
            c.checkpoint_name,
            ca.assignment_date
        FROM Checkpoint_Assignments ca
        INNER JOIN Personnel p ON ca.personnel_id = p.id
        INNER JOIN Shifts s ON ca.shift_id = s.id
        INNER JOIN Checkpoints c ON ca.checkpoint_id = c.id
        ORDER BY ca.id
    `);

    return rows;
};

// Get assignment by ID
exports.getAssignmentById = async (id) => {
    const [rows] = await db.query(
        'SELECT * FROM Checkpoint_Assignments WHERE id = ?',
        [id]
    );

    return rows[0];
};

// Add assignment
exports.addAssignment = async (assignment) => {
    const {
        personnel_id,
        shift_id,
        checkpoint_id,
        assignment_date
    } = assignment;

    const [result] = await db.query(
        `INSERT INTO Checkpoint_Assignments
        (personnel_id, shift_id, checkpoint_id, assignment_date)
        VALUES (?, ?, ?, ?)`,
        [personnel_id, shift_id, checkpoint_id, assignment_date]
    );

    return result;
};

// Update assignment
exports.updateAssignment = async (id, assignment) => {
    const {
        personnel_id,
        shift_id,
        checkpoint_id,
        assignment_date
    } = assignment;

    const [result] = await db.query(
        `UPDATE Checkpoint_Assignments
        SET personnel_id = ?, shift_id = ?, checkpoint_id = ?, assignment_date = ?
        WHERE id = ?`,
        [personnel_id, shift_id, checkpoint_id, assignment_date, id]
    );

    return result;
};

// Delete assignment
exports.deleteAssignment = async (id) => {
    const [result] = await db.query(
        'DELETE FROM Checkpoint_Assignments WHERE id = ?',
        [id]
    );

    return result;
};