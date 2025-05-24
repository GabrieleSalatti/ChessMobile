<?php
function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getLoggedInUserId() {
    return $_SESSION['user_id'] ?? null;
}

?>