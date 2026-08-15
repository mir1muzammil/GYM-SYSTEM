<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitness_club";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");
?>