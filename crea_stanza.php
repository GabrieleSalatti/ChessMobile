<?php
header('Content-Type: application/json');
include 'connessione.php';

$response = ['success' => false, 'message' => 'Errore sconosciuto.'];

if (isset($_POST['azione']) && $_POST['azione'] === 'crea_stanza' && isset($_POST['host_id'])) {
    $host_id = intval($_POST['host_id']);
    $nome_stanza = "Stanza " . rand(1000, 9999); // O un nome più significativo
    $host_ip = $_SERVER['REMOTE_ADDR'];

    // Assicurati che la colonna `host_ip` esista nella tabella `stanze`!
    // Aggiungo anche 'giocatore2_id' a NULL e 'stato' a 'aperta' o 'in_attesa'
    $stmt = $conn->prepare("INSERT INTO stanze (nome, host_id, host_ip, giocatore2_id, stato) VALUES (?, ?, ?, NULL, 'aperta')");
    $stmt->bind_param("sis", $nome_stanza, $host_id, $host_ip);

    if ($stmt->execute()) {
        $new_room_id = $stmt->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Stanza creata con successo!',
            'room_id' => $new_room_id,
            'host_ip' => $host_ip // Invia l'IP dell'host al client che ha creato la stanza
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Errore durante la creazione della stanza: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Parametri mancanti o azione non valida.']);
}

$conn->close();
?>