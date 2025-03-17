<?php
$host = "sql103.infinityfree.com";
$user = "if0_38218621";
$password = "SuOXcuacq0J";
$dbname = "if0_38218621_proj";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
