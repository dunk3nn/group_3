
// Load all data when the page opens
document.addEventListener('DOMContentLoaded', () => {
    loadTravelers();
    loadVehicles();
    loadEntryRecords();

    document.getElementById('travelerForm')
        .addEventListener('submit', saveTraveler);

    document.getElementById('vehicleForm')
        .addEventListener('submit', saveVehicle);

    document.getElementById('entryForm')
        .addEventListener('submit', saveEntryRecord);
});

// Display travelers
async function loadTravelers() {
    try {
        const result = await getTravelers();

        if (!result.success) throw new Error(result.message);

        const table = document.getElementById('travelerTable');
        const vehicleDropdown = document.getElementById('vehicle_traveler_id');
        const entryDropdown = document.getElementById('entry_traveler_id');

        table.innerHTML = '';
        vehicleDropdown.innerHTML = '<option value="">Select Traveler</option>';
        entryDropdown.innerHTML = '<option value="">Select Traveler</option>';

        result.data.forEach(traveler => {
            table.innerHTML += `
                <tr>
                    <td>${traveler.id}</td>
                    <td>${traveler.first_name}</td>
                    <td>${traveler.last_name}</td>
                    <td>${traveler.contact_number}</td>
                    <td>
                        <button onclick="deleteTraveler(${traveler.id})">Delete</button>
                    </td>
                </tr>
            `;

            const name = `${traveler.first_name} ${traveler.last_name}`;
            vehicleDropdown.innerHTML += `<option value="${traveler.id}">${name}</option>`;
            entryDropdown.innerHTML += `<option value="${traveler.id}">${name}</option>`;
        });
    } catch (error) {
        alert('Unable to load travelers: ' + error.message);
    }
}

// Save traveler
async function saveTraveler(event) {
    event.preventDefault();

    const data = {
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        contact_number: document.getElementById('contact_number').value
    };

    try {
        const result = await addTraveler(data);
        if (!result.success) throw new Error(result.message);

        alert('Traveler added successfully!');
        event.target.reset();
        loadTravelers();
    } catch (error) {
        alert('Unable to add traveler: ' + error.message);
    }
}

// Delete traveler
async function deleteTraveler(id) {
    if (!confirm('Delete this traveler?')) return;

    try {
        const response = await fetch(`${API_URL}/travelers/${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();

        if (!result.success) throw new Error(result.message);

        alert('Traveler deleted!');
        loadTravelers();
    } catch (error) {
        alert('Unable to delete traveler: ' + error.message);
    }
}

// Display vehicles
async function loadVehicles() {
    try {
        const result = await getVehicles();
        if (!result.success) throw new Error(result.message);

        const table = document.getElementById('vehicleTable');
        const dropdown = document.getElementById('entry_vehicle_id');

        table.innerHTML = '';
        dropdown.innerHTML = '<option value="">Select Vehicle</option>';

        result.data.forEach(vehicle => {
            table.innerHTML += `
                <tr>
                    <td>${vehicle.id}</td>
                    <td>${vehicle.traveler_id}</td>
                    <td>${vehicle.vehicle_name}</td>
                    <td>${vehicle.plate_number}</td>
                    <td>${vehicle.capacity}</td>
                    <td>
                        <button onclick="deleteVehicle(${vehicle.id})">Delete</button>
                    </td>
                </tr>
            `;

            dropdown.innerHTML += `
                <option value="${vehicle.id}">
                    ${vehicle.vehicle_name} - ${vehicle.plate_number}
                </option>
            `;
        });
    } catch (error) {
        alert('Unable to load vehicles: ' + error.message);
    }
}

// Save vehicle
async function saveVehicle(event) {
    event.preventDefault();

    const data = {
        traveler_id: document.getElementById('vehicle_traveler_id').value,
        vehicle_name: document.getElementById('vehicle_name').value,
        plate_number: document.getElementById('plate_number').value,
        capacity: document.getElementById('capacity').value
    };

    try {
        const result = await addVehicle(data);
        if (!result.success) throw new Error(result.message);

        alert('Vehicle added successfully!');
        event.target.reset();
        loadVehicles();
    } catch (error) {
        alert('Unable to add vehicle: ' + error.message);
    }
}

// Delete vehicle
async function deleteVehicle(id) {
    if (!confirm('Delete this vehicle?')) return;

    try {
        const response = await fetch(`${API_URL}/vehicles/${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();

        if (!result.success) throw new Error(result.message);

        alert('Vehicle deleted!');
        loadVehicles();
    } catch (error) {
        alert('Unable to delete vehicle: ' + error.message);
    }
}

// Display entry records
async function loadEntryRecords() {
    try {
        const result = await getEntryRecords();
        if (!result.success) throw new Error(result.message);

        const table = document.getElementById('entryTable');
        const report = document.getElementById('entryReport');

        table.innerHTML = '';
        report.innerHTML = '';

        result.data.forEach(record => {
            const traveler = `${record.first_name} ${record.last_name}`;

            table.innerHTML += `
                <tr>
                    <td>${record.id}</td>
                    <td>${traveler}</td>
                    <td>${record.vehicle_name} - ${record.plate_number}</td>
                    <td>${record.entry_date}</td>
                    <td>${record.entry_time}</td>
                    <td>${record.purpose}</td>
                    <td>
                        <button onclick="deleteEntryRecord(${record.id})">Delete</button>
                    </td>
                </tr>
            `;

            report.innerHTML += `
                <p>
                    Entry #${record.id}: ${traveler} |
                    ${record.vehicle_name} |
                    ${record.entry_date} ${record.entry_time} |
                    ${record.purpose}
                </p>
            `;
        });
    } catch (error) {
        alert('Unable to load entry records: ' + error.message);
    }
}

// Save entry record
async function saveEntryRecord(event) {
    event.preventDefault();

    const data = {
        traveler_id: document.getElementById('entry_traveler_id').value,
        vehicle_id: document.getElementById('entry_vehicle_id').value,
        entry_date: document.getElementById('entry_date').value,
        entry_time: document.getElementById('entry_time').value,
        purpose: document.getElementById('purpose').value
    };

    try {
        const result = await addEntryRecord(data);
        if (!result.success) throw new Error(result.message);

        alert('Entry recorded successfully!');
        event.target.reset();
        loadEntryRecords();
    } catch (error) {
        alert('Unable to record entry: ' + error.message);
    }
}

// Delete entry record
async function deleteEntryRecord(id) {
    if (!confirm('Delete this entry record?')) return;

    try {
        const response = await fetch(`${API_URL}/entry-records/${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();

        if (!result.success) throw new Error(result.message);

        alert('Entry record deleted!');
        loadEntryRecords();
    } catch (error) {
        alert('Unable to delete entry record: ' + error.message);
    }
}
