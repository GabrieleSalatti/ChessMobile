<?php
include 'connessione.php';

// Verifica che i parametri siano corretti
if (isset($_POST['stanza_id'])) {
    $stanza_id = $_POST['stanza_id'];

    // Controlla se la stanza ha entrambi i giocatori
    $sql_check = "SELECT * FROM stanze WHERE id = '$stanza_id' AND giocatore2_id IS NOT NULL";
    $result = $conn->query($sql_check);

    if ($result->num_rows > 0) {
        // Avvia la partita e imposta il campo partita_id
        $sql = "UPDATE stanze SET stato = 'in_corso', partita_id = NULL WHERE id = '$stanza_id'"; // Associa l'ID partita se necessario

        if ($conn->query($sql) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'Partita avviata!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Errore durante l\'avvio della partita.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Impossibile avviare la partita.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Parametri mancanti.']);
}

$conn->close();
?>
