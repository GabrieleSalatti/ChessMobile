<?php
header('Content-Type: application/json');

include 'connessione.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Campi mancanti']);
    exit;
}

$stmt = $conn->prepare("SELECT id, username, password_hash, elo FROM utenti WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($id, $username, $password_hash, $elo);

if ($stmt->fetch()) {
    if (password_verify($password, $password_hash)) {
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $id,
                'username' => $username,
                'elo' => $elo
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Password errata']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Utente non trovato']);
}

$stmt->close();
$conn->close();
?>
