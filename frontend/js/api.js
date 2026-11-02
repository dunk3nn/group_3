
const API_URL = 'http://localhost:3000/api';

// Get all travelers
async function getTravelers() {
    const response = await fetch(`${API_URL}/travelers`);
    return await response.json();
}

// Add a traveler
async function addTraveler(data) {
    const response = await fetch(`${API_URL}/travelers`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    });

    return await response.json();
}

// Get all vehicles
async function getVehicles() {
    const response = await fetch(`${API_URL}/vehicles`);
    return await response.json();
}

// Add a vehicle
async function addVehicle(data) {
    const response = await fetch(`${API_URL}/vehicles`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    });

    return await response.json();
}

// Get all entry records
async function getEntryRecords() {
    const response = await fetch(`${API_URL}/entry-records`);
    return await response.json();
}

// Add an entry record
async function addEntryRecord(data) {
    const response = await fetch(`${API_URL}/entry-records`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    });

    return await response.json();
}
