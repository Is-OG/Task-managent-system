<?php
// Start session and protect pages that require login.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit();
}

function require_role($role) {
    if (!isset($_SESSION["role"]) || $_SESSION["role"] !== $role) {
        header("Location: ../index.php");
        exit();
    }
}
?>
