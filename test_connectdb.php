<?php

// Database credentials
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'pshomeco_admin');
define('DB_PASS', 'AsdZxc007');
define('DB_NAME', 'pshomeco_house_dbs');

$dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                // Disable emulation for better performance and security
];

try {
    // Attempt to establish a database connection using PDO
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // Connection successful
    echo "<h2>Database Connection Successful!</h2>";
    echo "<p>You are successfully connected to the database '<b>" . DB_NAME . "</b>' on host '<b>" . DB_HOST . ":" . DB_PORT . "</b>'.</p>";

} catch (PDOException $e) {
    // Connection failed
    echo "<h2>Database Connection Failed!</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Please check the following:</p>";
    echo "<ul>";
    echo "<li>Is the <b>Host</b> (DB_HOST) and <b>Port</b> (DB_PORT) correct?</li>";
    echo "<li>Are the <b>Username</b> (DB_USER) and <b>Password</b> (DB_PASS) correct?</li>";
    echo "<li>Is the <b>Database Name</b> (DB_NAME) correct?</li>";
    echo "<li>Is the MySQL server running?</li>";
    echo "</ul>";
}

// In PDO, you don't typically need to explicitly close the connection.
// It will close automatically when the script finishes or when the PDO object is unset.
// If you need to explicitly close it (e.g., for long-running scripts), you can set $pdo = null;
$pdo = null;

?>