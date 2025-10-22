<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the selected option
    $selected_option = $_POST['options'];

    // Process the selected option
    echo "You have selected: " . htmlspecialchars($selected_option);
} else {
    echo "No form data submitted.";
}

