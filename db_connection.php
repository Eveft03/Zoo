<?php
$host = 'localhost';
$user = 'root'; // Αν δεν έχετε ορίσει κωδικό για τη MySQL, αφήστε το κενό.
$password = ''; 
$database = 'zoologikos_kipos';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Σφάλμα σύνδεσης: " . $conn->connect_error);
}
?>
