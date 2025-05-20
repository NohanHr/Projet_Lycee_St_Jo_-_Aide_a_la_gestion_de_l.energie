<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=energie;charset=utf8', 'admin', 'ciel2');

// Vérifie si l'utilisateur est connecté
function estConnecte() {
    return isset($_SESSION['user']);
}

// Vérifie si l'utilisateur est admin
function estAdmin() {
    return estConnecte() && $_SESSION['user']['role'] === 'admin';
}

// Vérifie si l'utilisateur est visiteur
function estVisiteur() {
    return estConnecte() && $_SESSION['user']['role'] === 'user';
}
?>
