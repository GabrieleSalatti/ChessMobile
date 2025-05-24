<?php
include 'inc/header.php';
include 'inc/db_connection.php';

// Query per recuperare le statistiche degli utenti dalla tabella dedicata
$sql = "SELECT u.username, s.partite_giocate, s.vittorie, s.sconfitte, s.pareggi
        FROM statistiche s
        JOIN utenti u ON s.user_id = u.id
        ORDER BY s.vittorie DESC"; // Esempio: ordina per numero di vittorie
$result = $conn->query($sql);
?>

<h1 class="mt-4">Statistiche Globali</h1>

<?php if ($result && $result->num_rows > 0): ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Username</th>
                <th>Partite Giocate</th>
                <th>Vittorie</th>
                <th>Sconfitte</th>
                <th>Pareggi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["username"]); ?></td>
                    <td><?php echo $row["partite_giocate"]; ?></td>
                    <td><?php echo $row["vittorie"]; ?></td>
                    <td><?php echo $row["sconfitte"]; ?></td>
                    <td><?php echo $row["pareggi"]; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="alert alert-info">Non ci sono ancora statistiche disponibili.</p>
<?php endif; ?>

<?php
$conn->close();
include 'inc/footer.php';
?>