// USER MANAGEMENT
const API = "http://localhost:3000/api";

const userForm = document.getElementById("userForm");
const userMessage = document.getElementById("userMessage");
const userTableBody = document.getElementById("userTableBody");
const tableMessage = document.getElementById("tableMessage");

const totalUsers = document.getElementById("totalUsers");
const activeUsers = document.getElementById("activeUsers");
const inactiveUsers = document.getElementById("inactiveUsers");

const refreshUsersBtn = document.getElementById("refreshUsersBtn");
const searchUser = document.getElementById("searchUser");
const loadReportBtn = document.getElementById("loadReport");
const reportTableBody = document.getElementById("reportTableBody");

const userId = document.getElementById("userId");
const username = document.getElementById("username");
const email = document.getElementById("email");
const password = document.getElementById("password");
const role = document.getElementById("role");
const status = document.getElementById("status");

const formTitle = document.getElementById("formTitle");
const passwordHint = document.getElementById("passwordHint");
const cancelEditBtn = document.getElementById("cancelEditBtn");

let users = [];
let roles = [];

// Show message
function showMessage(message, isError = false) {
    userMessage.textContent = message;
    userMessage.style.color = isError ? "red" : "green";
}

// Load roles
async function loadRoles() {
    const response = await fetch(`${API}/users/roles`);

    if (!response.ok) {
        throw new Error("Failed to load user roles.");
    }

    roles = await response.json();
}

// Load users
async function loadUsers() {
    tableMessage.textContent = "Loading users...";

    try {
        const response = await fetch(`${API}/users`);

        if (!response.ok) {
            throw new Error("Failed to load users.");
        }

        users = await response.json();

        displayUsers(users);
        updateStatistics();

        tableMessage.textContent = `${users.length} user(s) loaded.`;
    } catch (error) {
        tableMessage.textContent = error.message;
        console.error(error);
    }
}

// Display users
function displayUsers(userList) {
    userTableBody.innerHTML = "";

    if (!userList.length) {
        userTableBody.innerHTML =
            '<tr><td colspan="7">No users available.</td></tr>';
        return;
    }

    userList.forEach(user => {
        const roleName = roles.find(
            item => Number(item.id) === Number(user.role_id)
        )?.role_name || "Unknown";

        const row = document.createElement("tr");

        const values = [
            user.id,
            user.username,
            user.email,
            roleName,
            Number(user.is_active) === 1 ? "Active" : "Inactive",
            user.created_at
                ? new Date(user.created_at).toLocaleString()
                : ""
        ];

        values.forEach(value => {
            const cell = document.createElement("td");
            cell.textContent = value ?? "";
            row.appendChild(cell);
        });

        const actions = document.createElement("td");

        const editButton = document.createElement("button");
        editButton.textContent = "Edit";
        editButton.addEventListener("click", () => editUser(user.id));

        const statusButton = document.createElement("button");
        statusButton.textContent =
            Number(user.is_active) === 1 ? "Deactivate" : "Activate";

        statusButton.addEventListener("click", () => toggleStatus(user));

        actions.append(editButton, statusButton);
        row.appendChild(actions);

        userTableBody.appendChild(row);
    });
}

// Update statistics
function updateStatistics() {
    totalUsers.textContent = users.length;

    activeUsers.textContent = users.filter(
        user => Number(user.is_active) === 1
    ).length;

    inactiveUsers.textContent = users.filter(
        user => Number(user.is_active) === 0
    ).length;
}

// Create or update user
userForm.addEventListener("submit", async event => {
    event.preventDefault();

    const editing = Boolean(userId.value);

    if (username.value.trim().length < 3) {
        showMessage("Username must contain at least 3 characters.", true);
        return;
    }

    if (!email.checkValidity()) {
        showMessage("Enter a valid email address.", true);
        return;
    }

    if (!editing && password.value.length < 8) {
        showMessage("Password must contain at least 8 characters.", true);
        return;
    }

    if (editing && password.value && password.value.length < 8) {
        showMessage("Password must contain at least 8 characters.", true);
        return;
    }

    const userData = {
        username: username.value.trim(),
        email: email.value.trim(),
        role_id: Number(role.value),
        is_active: Number(status.value)
    };

    if (password.value) {
        userData.password = password.value;
    }

    if (!userData.role_id) {
        showMessage("Please select a user role.", true);
        return;
    }

    try {
        const response = await fetch(
            editing ? `${API}/users/${userId.value}` : `${API}/users`,
            {
                method: editing ? "PUT" : "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(userData)
            }
        );

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || "Unable to save user.");
        }

        showMessage(result.message);
        resetForm();
        await loadUsers();
    } catch (error) {
        showMessage(error.message, true);
    }
});

// Edit user
function editUser(id) {
    const user = users.find(item => Number(item.id) === Number(id));

    if (!user) return;

    userId.value = user.id;
    username.value = user.username;
    email.value = user.email;
    role.value = user.role_id;
    status.value = user.is_active;

    password.value = "";
    password.required = false;

    formTitle.textContent = "Edit User";
    passwordHint.textContent = "Leave blank to keep the current password.";
    cancelEditBtn.hidden = false;

    window.scrollTo({ top: 0, behavior: "smooth" });
}

// Activate or deactivate user
async function toggleStatus(user) {
    const newStatus = Number(user.is_active) === 1 ? 0 : 1;
    const action = newStatus === 1 ? "activate" : "deactivate";

    if (!confirm(`Are you sure you want to ${action} this account?`)) {
        return;
    }

    try {
        const response = await fetch(`${API}/users/${user.id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                username: user.username,
                email: user.email,
                role_id: Number(user.role_id),
                is_active: newStatus
            })
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || "Unable to change account status.");
        }

        showMessage(result.message);
        await loadUsers();
    } catch (error) {
        showMessage(error.message, true);
    }
}

// Reset form
function resetForm() {
    userForm.reset();
    userId.value = "";
    password.required = true;

    formTitle.textContent = "Add New User";
    passwordHint.textContent =
        "Password must contain at least 8 characters.";

    cancelEditBtn.hidden = true;
}

// Cancel editing
cancelEditBtn.addEventListener("click", resetForm);

// Clear form
userForm.addEventListener("reset", () => {
    setTimeout(() => {
        userId.value = "";
        password.required = true;
        formTitle.textContent = "Add New User";
        passwordHint.textContent =
            "Password must contain at least 8 characters.";
        cancelEditBtn.hidden = true;
    }, 0);
});

// Search users
searchUser.addEventListener("input", () => {
    const search = searchUser.value.toLowerCase();

    const filteredUsers = users.filter(user => {
        const roleName = roles.find(
            item => Number(item.id) === Number(user.role_id)
        )?.role_name || "";

        return (
            String(user.username).toLowerCase().includes(search) ||
            String(user.email).toLowerCase().includes(search) ||
            roleName.toLowerCase().includes(search)
        );
    });

    displayUsers(filteredUsers);
});

// Refresh users
refreshUsersBtn.addEventListener("click", loadUsers);

// Generate report
loadReportBtn.addEventListener("click", async () => {
    reportTableBody.innerHTML =
        '<tr><td colspan="5">Loading report...</td></tr>';

    try {
        const response = await fetch(`${API}/users/report`);

        if (!response.ok) {
            throw new Error("Failed to generate report.");
        }

        const report = await response.json();
        reportTableBody.innerHTML = "";

        if (!report.length) {
            reportTableBody.innerHTML =
                '<tr><td colspan="5">No report data available.</td></tr>';
            return;
        }

        report.forEach(user => {
            const row = document.createElement("tr");

            [
                user.id,
                user.username,
                user.role_id,
                user.role_name,
                Number(user.is_active) === 1 ? "Active" : "Inactive"
            ].forEach(value => {
                const cell = document.createElement("td");
                cell.textContent = value ?? "";
                row.appendChild(cell);
            });

            reportTableBody.appendChild(row);
        });
    } catch (error) {
        reportTableBody.innerHTML =
            `<tr><td colspan="5">${error.message}</td></tr>`;
    }
});

// Initialize page
async function initializeUsersPage() {
    try {
        await loadRoles();
        await loadUsers();
    } catch (error) {
        showMessage(error.message, true);
    }
}

initializeUsersPage();