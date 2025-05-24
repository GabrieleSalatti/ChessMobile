<?php
include 'inc/header.php';
include 'inc/db_connection.php';

// Verifica se l'utente è loggato
if (!isUserLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = getLoggedInUserId();

// Query per recuperare i dati dell'utente
$sql_user = "SELECT username, email FROM utenti WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$stmt_user->close();

// Query per recuperare le statistiche dell'utente dalla tabella dedicata
$sql_stats = "SELECT partite_giocate, vittorie, sconfitte, pareggi
              FROM statistiche
              WHERE user_id = ?";
$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->bind_param("i", $user_id);
$stmt_stats->execute();
$result_stats = $stmt_stats->get_result();
$stats = $result_stats->fetch_assoc();
$stmt_stats->close();
?>

<h1>Il Mio Profilo</h1>

<?php if ($user): ?>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($user["username"]); ?></h5>
            <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($user["email"]); ?></p>
            <p class="card-text"><strong>Partite Giocate:</strong> <?php echo $stats["partite_giocate"] ?? 0; ?></p>
            <p class="card-text"><strong>Vittorie:</strong> <?php echo $stats["vittorie"] ?? 0; ?></p>
            <p class="card-text"><strong>Sconfitte:</strong> <?php echo $stats["sconfitte"] ?? 0; ?></p>
            <p class="card-text"><strong>Pareggi:</strong> <?php echo $stats["pareggi"] ?? 0; ?></p>
            </div>
    </div>
<?php else: ?>
    <p class="alert alert-danger">Errore nel caricamento dei dati del profilo.</p>
<?php endif; ?>

<?php
$conn->close();
include 'inc/footer.php';
?>