<?php
$host = "sql302.infinityfree.com";
$username = "if0_39173108";
$password = "UhZxp9jQSSXv";
$database = "if0_39173108_login";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
