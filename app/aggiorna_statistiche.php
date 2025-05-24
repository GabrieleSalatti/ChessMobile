<?php
header('Content-Type: application/json'); // Indica che la risposta sarà in JSON

// Funzione di logging per debug
function write_log($message) {
    // Il percorso del file di log. Assicurati che il server web abbia i permessi di scrittura in questa directory.
    // Potrebbe essere utile specificare un percorso assoluto per evitare problemi di permessi, es:
    // file_put_contents('/var/log/php_abandon_log.txt', date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
    file_put_contents('php_abandon_log.txt', date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
}

write_log("Richiesta ricevuta: " . json_encode($_POST)); // Logga i dati POST ricevuti

// Includi il tuo file di connessione al database
// Assicurati che 'connessione.php' contenga le credenziali e la logica per $conn
require_once 'connessione.php'; // Adatta il percorso se necessario

// Verifica che la connessione al database sia stabilita
if ($conn->connect_error) {
    write_log("Errore connessione DB: " . $conn->connect_error); // Logga l'errore di connessione
    echo json_encode(["success" => false, "message" => "Connessione al database fallita: " . $conn->connect_error]);
    exit();
}

// Verifica che i dati POST siano presenti
// Questi sono i dati che l'app Android invierà: match_id, abandoning_player_id, winning_player_id
if (!isset($_POST['match_id']) || !isset($_POST['abandoning_player_id']) || !isset($_POST['winning_player_id'])) {
    write_log("Dati POST mancanti."); // Logga i dati mancanti
    echo json_encode(["success" => false, "message" => "Errore: Dati mancanti nella richiesta POST (match_id, abandoning_player_id, winning_player_id)."]);
    $conn->close();
    exit();
}

// Sanitizza e recupera i dati
$matchId = $conn->real_escape_string($_POST['match_id']);
$abandoningPlayerId = $conn->real_escape_string($_POST['abandoning_player_id']);
$winningPlayerId = $conn->real_escape_string($_POST['winning_player_id']);

write_log("Dati estratti: MatchID=" . $matchId . ", AbandoningPlayerID=" . $abandoningPlayerId . ", WinningPlayerID=" . $winningPlayerId); // Logga i dati estratti

// Inizia una transazione per garantire che tutte le operazioni sul DB siano atomiche
// o tutte avvengono con successo o nessuna
$conn->begin_transaction();

try {
    // 1. Aggiorna le statistiche per il giocatore che ha abbandonato (incrementa le sconfitte)
    // Query sulla tabella 'statistiche'
    write_log("Esecuzione query sconfitta per user_id: " . $abandoningPlayerId); // Logga l'inizio della query
    $sql_loss = "UPDATE statistiche SET sconfitte = sconfitte + 1, partite_giocate = partite_giocate + 1 WHERE user_id = ?";
    $stmt_loss = $conn->prepare($sql_loss);
    if (!$stmt_loss) {
        throw new Exception("Errore nella preparazione della query di sconfitta: " . $conn->error);
    }
    $stmt_loss->bind_param("i", $abandoningPlayerId); // "i" indica che è un intero
    $stmt_loss->execute();
    write_log("Righe affette da query sconfitta: " . $stmt_loss->affected_rows); // Logga le righe affette
    $stmt_loss->close();

    // 2. Aggiorna le statistiche per il giocatore vincitore (incrementa le vittorie)
    // Query sulla tabella 'statistiche'
    write_log("Esecuzione query vittoria per user_id: " . $winningPlayerId); // Logga l'inizio della query
    $sql_win = "UPDATE statistiche SET vittorie = vittorie + 1, partite_giocate = partite_giocate + 1 WHERE user_id = ?";
    $stmt_win = $conn->prepare($sql_win);
    if (!$stmt_win) {
        throw new Exception("Errore nella preparazione della query di vittoria: " . $conn->error);
    }
    $stmt_win->bind_param("i", $winningPlayerId); // "i" indica che è un intero
    $stmt_win->execute();
    write_log("Righe affette da query vittoria: " . $stmt_win->affected_rows); // Logga le righe affette
    $stmt_win->close();

    // 3. Aggiorna lo stato della partita nella tabella 'partite'
    // Questo è cruciale per segnare la partita come conclusa e associare il vincitore
    // Assumiamo che il 'match_id' che ricevi sia l'ID della riga nella tabella 'partite'
    write_log("Esecuzione query aggiornamento partita per match_id: " . $matchId); // Logga l'inizio della query
    $sql_update_match = "UPDATE partite SET vincitore_id = ?, durata = ? WHERE id = ?";
    $stmt_match = $conn->prepare($sql_update_match);
    if (!$stmt_match) {
        throw new Exception("Errore nella preparazione della query di aggiornamento partita: " . $conn->error);
    }
    // Per 'durata', il tuo codice Android non invia la durata esatta dell'abbandono.
    // Puoi impostarla a NULL, oppure calcolare una durata approssimativa, o richiedere al client di inviarla.
    // Per ora, la impostiamo a 0 o a NULL. Se la invii, la riceverai via POST.
    $durata_partita_abbandono = 0; // O NULL, o un valore inviato dal client
    $stmt_match->bind_param("iii", $winningPlayerId, $durata_partita_abbandono, $matchId);
    $stmt_match->execute();
    write_log("Righe affette da query partita: " . $stmt_match->affected_rows); // Logga le righe affette
    $stmt_match->close();

    // 4. (Opzionale) Aggiorna lo stato della stanza (se la gestisci in questo modo)
    // Potrebbe essere gestito automaticamente da elimina_stanza.php o da qui.
    // Se vuoi segnare la stanza come 'chiusa' o 'completata' qui:
    /*
    $sql_update_stanza = "UPDATE stanze SET stato = 'chiusa', partita_id = ? WHERE id = ?";
    $stmt_stanza = $conn->prepare($sql_update_stanza);
    if (!$stmt_stanza) {
        throw new Exception("Errore nella preparazione della query di aggiornamento stanza: " . $conn->error);
    }
    $stmt_stanza->bind_param("ii", $matchId, $matchId); // Assumendo che matchId sia anche roomId
    $stmt_stanza->execute();
    $stmt_stanza->close();
    */
    // Poiché hai già elimina_stanza.php, è più probabile che sia quello a pulire le stanze.

    // Se tutto è andato bene, conferma la transazione
    $conn->commit();
    write_log("Transazione completata con successo."); // Logga il successo della transazione
    echo json_encode(["success" => true, "message" => "Statistiche e partita aggiornate con successo."]);

} catch (Exception $e) {
    // Se qualcosa va storto, annulla tutte le modifiche
    $conn->rollback();
    write_log("Errore nella transazione: " . $e->getMessage()); // Logga l'errore della transazione
    echo json_encode(["success" => false, "message" => "Errore durante l'aggiornamento delle statistiche: " . $e->getMessage()]);
}

// Chiudi la connessione al database
$conn->close();
write_log("Connessione al DB chiusa."); // Logga la chiusura della connessione
?>