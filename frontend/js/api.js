// API connection
const API_URL = 'http://localhost:3000/api';

// Traveler functions
async function getTravelers() {
    const response = await fetch(`${API_URL}/travelers`);
    return await response.json();
}

async function addTraveler(data) {
    const response = await fetch(`${API_URL}/travelers`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}

// Vehicle functions
async function getVehicles() {
    const response = await fetch(`${API_URL}/vehicles`);
    return await response.json();
}

async function addVehicle(data) {
    const response = await fetch(`${API_URL}/vehicles`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}

// Entry record functions
async function getEntryRecords() {
    const response = await fetch(`${API_URL}/entry-records`);
    return await response.json();
}

async function addEntryRecord(data) {
    const response = await fetch(`${API_URL}/entry-records`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}

// Personnel functions
async function getPersonnel() {
    const response = await fetch(`${API_URL}/personnel`);
    return await response.json();
}

async function addPersonnel(data) {
    const response = await fetch(`${API_URL}/personnel`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}

async function updatePersonnel(id, data) {
    const response = await fetch(`${API_URL}/personnel/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}

async function deletePersonnel(id) {
    const response = await fetch(`${API_URL}/personnel/${id}`, {
        method: 'DELETE'
    });
    return await response.json();
}

// Shift functions
async function getShifts() {
    const response = await fetch(`${API_URL}/shifts`);
    return await response.json();
}

async function addShift(data) {
    const response = await fetch(`${API_URL}/shifts`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}

async function updateShift(id, data) {
    const response = await fetch(`${API_URL}/shifts/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}

async function deleteShift(id) {
    const response = await fetch(`${API_URL}/shifts/${id}`, {
        method: 'DELETE'
    });
    return await response.json();
}

// Assignment functions
async function getAssignments() {
    const response = await fetch(`${API_URL}/assignments`);
    return await response.json();
}

async function addAssignment(data) {
    const response = await fetch(`${API_URL}/assignments`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}

async function updateAssignment(id, data) {
    const response = await fetch(`${API_URL}/assignments/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}

async function deleteAssignment(id) {
    const response = await fetch(`${API_URL}/assignments/${id}`, {
        method: 'DELETE'
    });
    return await response.json();
}

// Checkpoint functions
async function getCheckpoints() {
    const response = await fetch(`${API_URL}/checkpoints`);
    return await response.json();
}

// User Management functions
async function getUsers() {
    const response = await fetch(`${API_URL}/users`);
    return await response.json();
}

async function getUserRoles() {
    const response = await fetch(`${API_URL}/users/roles`);
    return await response.json();
}

async function addUser(data) {
    const response = await fetch(`${API_URL}/users`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}

async function updateUser(id, data) {
    const response = await fetch(`${API_URL}/users/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}

async function deactivateUser(id) {
    const response = await fetch(`${API_URL}/users/${id}`, {
        method: 'DELETE'
    });
    return await response.json();
}

async function getUsersWithRoles() {
    const response = await fetch(`${API_URL}/users/report`);
    return await response.json();
}