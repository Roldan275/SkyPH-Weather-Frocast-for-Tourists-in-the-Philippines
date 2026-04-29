<?php
$conn = new mysqli("localhost", "root", "", "sky-ph");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>