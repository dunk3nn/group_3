const db = require('../config/db');

// Get all entry records
exports.getAllEntryRecords = async () => {
    const [rows] = await db.query(`
        SELECT 
            entry_records.*,
            travelers.first_name,
            travelers.last_name,
            vehicles.vehicle_name,
            vehicles.plate_number
        FROM entry_records
        INNER JOIN travelers
            ON entry_records.traveler_id = travelers.id
        INNER JOIN vehicles
            ON entry_records.vehicle_id = vehicles.id
    `);

    return rows;
};

// Add an entry record
exports.addEntryRecord = async (record) => {
    const {
        traveler_id,
        vehicle_id,
        entry_date,
        entry_time,
        purpose
    } = record;

    const [result] = await db.query(
        `INSERT INTO entry_records
        (traveler_id, vehicle_id, entry_date, entry_time, purpose)
        VALUES (?, ?, ?, ?, ?)`,
        [traveler_id, vehicle_id, entry_date, entry_time, purpose]
    );

    return result;
};

// Delete an entry record
exports.deleteEntryRecord = async (id) => {
    const [result] = await db.query(
        'DELETE FROM entry_records WHERE id = ?',
        [id]
    );

    return result;
};