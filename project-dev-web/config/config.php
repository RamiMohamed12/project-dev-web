<?php


$host = "localhost"; 
$username = "root";
$password = "";
$dbname = "website" ; 
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
