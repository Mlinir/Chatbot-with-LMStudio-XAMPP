<?php
$servername = "localhost:3307";
$username = "root";
$password = "";
$dbname = "local_llm";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
