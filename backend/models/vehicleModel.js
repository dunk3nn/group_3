const db = require('../config/db');

// Get all vehicles
exports.getAllVehicles = async () => {
    const [rows] = await db.query(
        'SELECT * FROM vehicles'
    );
    return rows;
};

// Add a vehicle
exports.addVehicle = async (vehicle) => {
    const { traveler_id, vehicle_name, plate_number, capacity } = vehicle;

    const [result] = await db.query(
        'INSERT INTO vehicles (traveler_id, vehicle_name, plate_number, capacity) VALUES (?, ?, ?, ?)',
        [traveler_id, vehicle_name, plate_number, capacity]
    );

    return result;
};

// Update a vehicle
exports.updateVehicle = async (id, vehicle) => {
    const { traveler_id, vehicle_name, plate_number, capacity } = vehicle;

    const [result] = await db.query(
        'UPDATE vehicles SET traveler_id = ?, vehicle_name = ?, plate_number = ?, capacity = ? WHERE id = ?',
        [traveler_id, vehicle_name, plate_number, capacity, id]
    );

    return result;
};

// Delete a vehicle
exports.deleteVehicle = async (id) => {
    const [result] = await db.query(
        'DELETE FROM vehicles WHERE id = ?',
        [id]
    );

    return result;
};