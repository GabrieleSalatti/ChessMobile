<?php
// Include il file di connessione al database
require_once 'connessione.php'; // Assicurati che il percorso sia corretto

header('Content-Type: application/json');

$response = array();

// Verifica se la richiesta è di tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera l'ID della stanza dalla richiesta POST
    $room_id = isset($_POST['id_stanza']) ? $_POST['id_stanza'] : '';

    if (!empty($room_id) && is_numeric($room_id)) {
        // Prepara la query SQL per eliminare la stanza
        $stmt = $conn->prepare("DELETE FROM stanze WHERE id = ?");
        $stmt->bind_param("i", $room_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = "Stanza eliminata con successo.";
            } else {
                $response['success'] = false;
                $response['message'] = "Nessuna stanza trovata con l'ID specificato.";
            }
        } else {
            $response['success'] = false;
            $response['message'] = "Errore durante l'eliminazione della stanza: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $response['success'] = false;
        $response['message'] = "ID stanza non valido o mancante.";
    }
} else {
    $response['success'] = false;
    $response['message'] = "Metodo di richiesta non consentito.";
}

// Chiudi la connessione al database
$conn->close();

echo json_encode($response);
?>