<?php
session_start();

// Redirige vers login.php si non connecté
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}
?>
