<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "border_systems";

$conn = new mysqli($host, $username, $password, $database, 3307);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>