<?php
include 'inc/header.php';
include 'inc/db_connection.php';

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Esegui delle validazioni qui (es. lunghezza password, formato email, username univoco)

    // Verifica se l'username o l'email sono già in uso
    $check_sql = "SELECT id FROM utenti WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    if ($check_stmt === false) {
        die("Errore nella preparazione della query (verifica il nome della tabella): " . $conn->error);
    }
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $error_message = "Nome utente o email già esistenti.";
    } else {
        // Hash della password per la sicurezza
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert_sql = "INSERT INTO utenti (username, email, password_hash) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        if ($insert_stmt === false) {
            die("Errore nella preparazione della query (verifica il nome della tabella): " . $conn->error);
        }
        $insert_stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($insert_stmt->execute()) {
            $success_message = "Registrazione avvenuta con successo! Puoi effettuare il <a href='login.php'>login</a>.";
        } else {
            $error_message = "Errore durante la registrazione: " . $conn->error;
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
}

$conn->close();
?>

<h1>Registrazione</h1>

<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <div class="mb-3">
        <label for="username" class="form-label">Nome Utente</label>
        <input type="text" class="form-control" id="username" name="username" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary">Registrati</button>
</form>

<?php include 'inc/footer.php'; ?>