<?php
header('Content-Type: application/json');
include 'connessione.php';

$response = ['success' => false, 'message' => 'Errore sconosciuto.'];

// Leggi il corpo della richiesta POST
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

// Verifica che i parametri siano corretti
if (!isset($input['room_id']) || !isset($input['user_id'])) {
    $response = ['success' => false, 'message' => 'Parametri mancanti.'];
    echo json_encode($response);
    exit();
}

$stanza_id = intval($input['room_id']);
$giocatore2_id = intval($input['user_id']);

// Prima, controlliamo se la stanza è disponibile (giocatore2_id IS NULL e stato = 'aperta')
// e recuperiamo l'host_id e host_ip
$stmt_check = $conn->prepare("SELECT host_id, host_ip FROM stanze WHERE id = ? AND giocatore2_id IS NULL AND stato = 'aperta'");
$stmt_check->bind_param("i", $stanza_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    $row_check = $result_check->fetch_assoc();
    $host_id_della_stanza = $row_check['host_id'];
    $host_ip_della_stanza = $row_check['host_ip'];

    // Controlla che il giocatore che si unisce non sia l'host stesso
    if ($giocatore2_id == $host_id_della_stanza) {
        $response = ['success' => false, 'message' => 'Non puoi unirti alla tua stessa stanza.'];
    } else {
        // Uniamo il giocatore alla stanza e aggiorniamo lo stato a 'in_partita'
        // 'in_partita' significa che la partita può iniziare
        $stmt_update = $conn->prepare("UPDATE stanze SET giocatore2_id = ?, stato = 'in_partita' WHERE id = ? AND stato = 'aperta'");
        $stmt_update->bind_param("ii", $giocatore2_id, $stanza_id);

        if ($stmt_update->execute() && $stmt_update->affected_rows > 0) {
            $response = [
                'success' => true,
                'message' => 'Sei entrato nella stanza con successo!',
                'host_ip' => $host_ip_della_stanza, // AGGIUNTO IL CAMPO host_ip QUI
                'host_user_id' => $host_id_della_stanza, // Potrebbe essere utile anche per il client
                'client_user_id' => $giocatore2_id // Per coerenza
            ];
        } else {
            $response = ['success' => false, 'message' => 'Errore durante l\'unione alla stanza o stanza non più disponibile: ' . $stmt_update->error];
        }
        $stmt_update->close();
    }
} else {
    $response = ['success' => false, 'message' => 'La stanza non è disponibile, già piena o non esiste.'];
}
$stmt_check->close();

echo json_encode($response);
$conn->close();
?>