<?php
include 'inc/header.php';
include 'inc/db_connection.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT id, password_hash FROM utenti WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Errore nella preparazione della query : " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row["password_hash"])) {
            $_SESSION['user_id'] = $row["id"];
            header("Location: profilo.php"); // Reindirizza alla pagina del profilo dopo il login
            exit();
        } else {
            $error_message = "Email o password non validi.";
        }
    } else {
        $error_message = "Email o password non validi.";
    }
    $stmt->close();
}

$conn->close();
?>

<h1>Login</h1>

<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="text" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary">Accedi</button>
</form>

<?php include 'inc/footer.php'; ?>