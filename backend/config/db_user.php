<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$username = "root";
$password = "";
$database = "kp201";

try {
    $conn = new mysqli($host, $username, $password, $database);
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    die("Database connection failed.");
}