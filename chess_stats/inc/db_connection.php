<?php
$servername = "localhost"; // Inserisci il tuo server MySQL
$username = "root"; // Inserisci il tuo username del database
$password = ""; // Inserisci la tua password del database
$dbname = "chess_db"; // Inserisci il nome del tuo database

// Crea la connessione
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica la connessione
if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}
?>