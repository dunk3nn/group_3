const db = require('../config/db');

// Get all travelers
exports.getAllTravelers = async () => {
    const [rows] = await db.query(
        'SELECT * FROM travelers'
    );
    return rows;
};

// Add a traveler
exports.addTraveler = async (traveler) => {
    const { first_name, last_name, contact_number } = traveler;

    const [result] = await db.query(
        'INSERT INTO travelers (first_name, last_name, contact_number) VALUES (?, ?, ?)',
        [first_name, last_name, contact_number]
    );

    return result;
};

// Update a traveler
exports.updateTraveler = async (id, traveler) => {
    const { first_name, last_name, contact_number } = traveler;

    const [result] = await db.query(
        'UPDATE travelers SET first_name = ?, last_name = ?, contact_number = ? WHERE id = ?',
        [first_name, last_name, contact_number, id]
    );

    return result;
};

// Delete a traveler
exports.deleteTraveler = async (id) => {
    const [result] = await db.query(
        'DELETE FROM travelers WHERE id = ?',
        [id]
    );

    return result;
};