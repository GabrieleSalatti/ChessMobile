<?php
header('Content-Type: application/json');
include 'connessione.php';

// Verifica che i dati siano stati inviati tramite POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera i dati inviati dal client Android
    // Utilizziamo json_decode per gestire il payload JSON
    $data = json_decode(file_get_contents("php://input"), true);

    $giocatore1_id = $data['giocatore1_id'] ?? null;
    $giocatore2_id = $data['giocatore2_id'] ?? null;
    $vincitore_id = $data['vincitore_id'] ?? null;
    $durata = $data['durata'] ?? null; // Durata in secondi, minuti, ecc. a seconda di come la gestisci

    // Validazione base dei dati
    if ($giocatore1_id === null || $giocatore2_id === null || $durata === null) {
        echo json_encode(["success" => false, "message" => "Dati mancanti. Assicurati di inviare giocatore1_id, giocatore2_id e durata."]);
        $conn->close();
        exit();
    }

    // Prepara l'istruzione SQL per prevenire SQL Injection
    $stmt = $conn->prepare("INSERT INTO partite (giocatore1_id, giocatore2_id, vincitore_id, durata) VALUES (?, ?, ?, ?)");

    // Se il vincitore_id è null (es. pareggio o partita non conclusa), gestiamo il binding di conseguenza
    if ($vincitore_id === null) {
        $stmt->bind_param("iiii", $giocatore1_id, $giocatore2_id, $vincitore_id_null, $durata);
        $vincitore_id_null = null; // Assicurati che PHP tratti 'null' come NULL per il database
    } else {
        $stmt->bind_param("iiii", $giocatore1_id, $giocatore2_id, $vincitore_id, $durata);
    }

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Partita salvata con successo!"]);

        // Aggiorna le statistiche dei giocatori
        // Aggiorna partite_giocate per entrambi i giocatori
        $stmt_update_p1 = $conn->prepare("UPDATE statistiche SET partite_giocate = partite_giocate + 1 WHERE user_id = ?");
        $stmt_update_p1->bind_param("i", $giocatore1_id);
        $stmt_update_p1->execute();
        $stmt_update_p1->close();

        $stmt_update_p2 = $conn->prepare("UPDATE statistiche SET partite_giocate = partite_giocate + 1 WHERE user_id = ?");
        $stmt_update_p2->bind_param("i", $giocatore2_id);
        $stmt_update_p2->execute();
        $stmt_update_p2->close();

        // Aggiorna vittorie/sconfitte/pareggi e tempo medio
        if ($vincitore_id !== null) { // C'è un vincitore
            $perdente_id = ($vincitore_id == $giocatore1_id) ? $giocatore2_id : $giocatore1_id;

            $stmt_win = $conn->prepare("UPDATE statistiche SET vittorie = vittorie + 1, tempo_medio = (tempo_medio * (partite_giocate - 1) + ?) / partite_giocate WHERE user_id = ?");
            $stmt_win->bind_param("ii", $durata, $vincitore_id);
            $stmt_win->execute();
            $stmt_win->close();

            $stmt_loss = $conn->prepare("UPDATE statistiche SET sconfitte = sconfitte + 1, tempo_medio = (tempo_medio * (partite_giocate - 1) + ?) / partite_giocate WHERE user_id = ?");
            $stmt_loss->bind_param("ii", $durata, $perdente_id);
            $stmt_loss->execute();
            $stmt_loss->close();
        } else { // Pareggio (se decidi di gestirlo così, altrimenti puoi omettere)
            $stmt_draw1 = $conn->prepare("UPDATE statistiche SET pareggi = pareggi + 1, tempo_medio = (tempo_medio * (partite_giocate - 1) + ?) / partite_giocate WHERE user_id = ?");
            $stmt_draw1->bind_param("ii", $durata, $giocatore1_id);
            $stmt_draw1->execute();
            $stmt_draw1->close();

            $stmt_draw2 = $conn->prepare("UPDATE statistiche SET pareggi = pareggi + 1, tempo_medio = (tempo_medio * (partite_giocate - 1) + ?) / partite_giocate WHERE user_id = ?");
            $stmt_draw2->bind_param("ii", $durata, $giocatore2_id);
            $stmt_draw2->execute();
            $stmt_draw2->close();
        }
    } else {
        echo json_encode(["success" => false, "message" => "Errore durante il salvataggio della partita: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Metodo di richiesta non valido. Usa POST."]);
}

$conn->close();
?>