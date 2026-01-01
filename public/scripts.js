function showModal(errorMessage) {
    document.getElementById('modal-message').innerHTML = errorMessage;
    document.getElementById('modal').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

function validateForm() {

    let errorMessage = ""
    let userNameMessage = "";
    let passwordMessage = "";
    let emailMessage = "";
    let firstNameMessage = "";
    let lastNameMessage = "";

    var user_name = document.getElementById("user_name").value;
    var password = document.getElementById("password").value;
    var email = document.getElementById("email").value;
    var first_name = document.getElementById("first_name").value;
    var last_name = document.getElementById("last_name").value;

    if (!/^[a-zA-Z0-9]+$/.test(user_name)) {
        userNameMessage += "User Name:<br>";
        userNameMessage += "- Must Consist Of Only Letters And Numbers.<br>";
    }

    if (!/^[a-zA-Z]{3}/.test(user_name)) {
        if (!userNameMessage) {
            userNameMessage += "User Name:<br>";
        }
        userNameMessage += "- Must Start With At Least 3 Characters.<br>";
    }

    if (user_name.length < 6 || user_name.length > 10) {
        if (!userNameMessage) {
            userNameMessage += "User Name:<br>";
        }
        userNameMessage += "- Must Be 6 to 10 Characters In Length.<br>";
    }

    if (!/(?=.*[a-z])/.test(password)) {
        passwordMessage += "Password:<br>";
        passwordMessage += "- Requires at least one lowercase letter.<br>";
    }

    if (!/(?=.*[A-Z])/.test(password)) {
        if (!passwordMessage) {
            passwordMessage += "Password:<br>";
        }
        passwordMessage += "- Requires at least one uppercase letter.<br>";
    }

    if (!/(?=.*[0-9])/.test(password)) {
        if (!passwordMessage) {
            passwordMessage += "Password:<br>";
        }
        passwordMessage += "- Requires at least one number.<br>";
    }

    if (!/(?=.*[!@#$%^&*()_+=[\]{ }|\\;:'",.<>?/-])/.test(password)) {
        if (!passwordMessage) {
            passwordMessage += "Password:<br>";
        }
        passwordMessage += "- Requires at least one Special Character.<br>";
    }

    if (password.length < 8 || password.length > 25) {
        if (!passwordMessage) {
            passwordMessage += "Password:<br>";
        }
        passwordMessage += "- Must Be 8 to 25 Characters In Length.<br>";
    }

    if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) {
        emailMessage += "Email:<br>"
        emailMessage += "- Invalid Email Entered.<br>";
    }

    if (!/^[a-zA-Z]+$/.test(first_name)) {
        firstNameMessage += "First Name:<br>";
        firstNameMessage += "- First Name Must Consist Of Only Letters<br>";
    }

    if (!/^[a-zA-Z]+$/.test(last_name)) {
        lastNameMessage += "Last Name:<br>";
        lastNameMessage += "- Last Name Must Consist Of Only Letters<br>";
    }

    errorMessage = userNameMessage + passwordMessage + emailMessage + firstNameMessage + lastNameMessage;

    if (errorMessage) {
        showModal(errorMessage);
        return false; // Validation failed
    }

    return true; // Validation passed
}

function submitForm(event) {
    event.preventDefault(); // Prevent default form submission

    if (validateForm()) {
        const formData = {
            user_name: document.getElementById("user_name").value,
            password: document.getElementById("password").value,
            email: document.getElementById("email").value,
            first_name: document.getElementById("first_name").value,
            last_name: document.getElementById("last_name").value,
        };

        fetch("/api/signup", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(formData),
        })
            .then(response => response.json())
            .then(response => {
                if (response.status === "success") {
                    alert("Registration successful!");
                    document.getElementById("NewUserForm").reset(); // Reset form fields
                } else {
                    showModal(response.message || "An error occurred. Please try again.");
                }
            })
            .catch(error => {
                showModal("An unexpected error occurred. Please check your connection and try again.");
                console.error("Error submitting form:", error);
            });
    }
}

// Attach submitForm function to the form's submit event
document.getElementById("NewUserForm").addEventListener("submit", submitForm);
