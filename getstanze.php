<?php
header('Content-Type: application/json'); // Assicurati che l'header JSON sia presente
include 'connessione.php';


// Query per recuperare le stanze disponibili con il nome utente dell'host
// Uniamo la tabella 'stanze' con la tabella 'utenti' sull'ID dell'host
$sql = "SELECT s.id, s.nome, s.host_id, u.username AS host_username, s.host_ip, s.giocatore2_id, s.stato
        FROM stanze s
        JOIN utenti u ON s.host_id = u.id
        WHERE s.stato = 'aperta' AND s.giocatore2_id IS NULL";

$result = $conn->query($sql);

$stanze = array();

if ($result) { // Controlla se la query ha avuto successo
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $stanze[] = $row;
        }
        // Includi un campo "success" nella risposta JSON
        echo json_encode(["success" => true, "rooms" => $stanze]);
    } else {
        // Nessuna stanza trovata, ma la query ha avuto successo
        echo json_encode(["success" => true, "rooms" => [], "message" => "Nessuna stanza disponibile."]);
    }
} else {
    // Errore nella query SQL
    echo json_encode(["success" => false, "message" => "Errore nella query SQL: " . $conn->error]);
}

$conn->close();
?>