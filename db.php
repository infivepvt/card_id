<?php
$host = "localhost";
$user = "u263749830_card_id"; 
$pass = "9Nzprz^&"; 
$dbname = "u263749830_id";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
