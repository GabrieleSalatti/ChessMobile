<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "chess_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Connessione fallita: " . $conn->connect_error]));
}
?>
