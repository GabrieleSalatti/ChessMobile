<?php
header('Content-Type: application/json');

include 'connessione.php';

$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$username || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Campi mancanti']);
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Controllo se utente esiste
$stmt = $conn->prepare("SELECT id FROM utenti WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email già registrata']);
    exit;
}
$stmt->close();

// Inserimento utente
$stmt = $conn->prepare("INSERT INTO utenti (username, email, password_hash) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $password_hash);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    // Crea riga statistiche
    $stat = $conn->prepare("INSERT INTO statistiche (user_id) VALUES (?)");
    $stat->bind_param("i", $user_id);
    $stat->execute();
    echo json_encode(['success' => true, 'message' => 'Registrazione completata']);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore registrazione']);
}

$stmt->close();
$conn->close();
?>
