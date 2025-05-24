<?php
    session_start();
    session_destroy();
    header("Location: index.php"); // Reindirizza alla homepage dopo il logout
    exit();
?>