<?php
session_start();
if (isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pdo = new PDO("mysql:host=localhost;dbname=ton_database", "root", "password");

    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user"] = $username;
        header("Location: index.php");
        exit();
    } else {
        $error = "Identifiants incorrects";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
</head>
<body>
    <h2>Connexion</h2>
    <form method="POST">
        <p><input type="text" name="username" placeholder="Nom d'utilisateur" required></p>
        <p><input type="password" name="password" placeholder="Mot de passe" required></p>
        <p><button type="submit">Se connecter</button></p>
    </form>
    <p style="color:red;"><?php echo $error; ?></p>
</body>
</html>
