
// USER MANAGEMENT - INPUT VALIDATION

const userForm = document.getElementById("userForm");
const userMessage = document.getElementById("userMessage");

userForm.addEventListener("submit", function(event) {

    // Prevent form from submitting for now
    event.preventDefault();

    // Get form values
    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value;
    const role = document.getElementById("role").value;
    const status = document.getElementById("status").value;

    // Check username
    if (username.length < 3 || username.length > 50) {
        userMessage.textContent =
            "Username must be between 3 and 50 characters.";
        return;
    }

    // Check password
    if (password.length < 8) {
        userMessage.textContent =
            "Password must contain at least 8 characters.";
        return;
    }

    // Check role
    if (role === "") {
        userMessage.textContent =
            "Please select a user role.";
        return;
    }

    // Check account status
    if (status !== "1" && status !== "0") {
        userMessage.textContent =
            "Please select an account status.";
        return;
    }

    userMessage.textContent =
        "Input is valid! Backend connection is still needed.";

});
