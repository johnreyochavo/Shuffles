<?php
$servername = "sv97.ifastnet.com";
$username = "wohroxas_ochavo";
$password = "ochavo2025";
$dbname = "wohroxas_shuffleDB";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>