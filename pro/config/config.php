<?php

function getEnvValue ($key) {
    $path = __DIR__ . '/../.env';
    if (file_exists($path)) {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, "$key=") === 0) {
                return trim(substr($line, strlen($key)+1));
            }
        }
    }
    return null;
}

$host = getEnvValue('DB_HOST');  
$username =getEnvValue('DB_USERNAME');
$password = getEnvValue('DB_PASSWORD');
$dbname = getEnvValue('DB_NAME');

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4"; 

try {
    $conn = new PDO($dsn, $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $info = "Database connection successful!";
} catch (PDOException $e) {
    $info = "Connection failed: " . $e->getMessage();
}

//echo $info;


?>