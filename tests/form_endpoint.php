<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Perform your database operations here (e.g., insert data, validate user, etc.)

    echo "Form submitted successfully. Username: $username, Password: $password";
} else {
    http_response_code(405); // Method Not Allowed
    echo "Invalid request method.";
}