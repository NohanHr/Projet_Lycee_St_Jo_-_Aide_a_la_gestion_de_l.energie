<?php

session_start();
if (isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=energie;charset=utf8", "admin", "ciel2", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);

        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                // Création de la session avec le rôle
                $_SESSION["user"] = [
                    "username" => $user["username"],
                    "role" => $user["role"]
                ];
                header("Location: index.php");
                exit();
            } elseif ($password === $user['password']) {
                // Mise à jour du mot de passe et création de session
                $_SESSION["user"] = [
                    "username" => $user["username"],
                    "role" => $user["role"]
                ];

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE username = ?");
                $updateStmt->execute([$hashedPassword, $user['username']]);

                header("Location: index.php");
                exit();
            } else {
                $error = "Identifiants incorrects.";
            }
        } else {
            $error = "Identifiants incorrects.";
        }
    } catch (PDOException $e) {
        $error = "Erreur de connexion : " . htmlspecialchars($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        /* Style général */
        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: url('https://www.linfodurable.fr/sites/linfodurable/files/2017-11/shutterstock_662332231.jpg'); /* Remplacez par votre image */
            background-size: cover; /* Couvre toute la page */
            background-position: center; /* Centre l'image */
            background-repeat: no-repeat; /* Empêche la répétition */
            color: #333;
        }

        /* Conteneur du formulaire */
        .container {
            background: rgba(255, 255, 255, 0.9); /* Arrière-plan semi-transparent */
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 350px;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        /* Style des champs de formulaire */
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        input:focus {
            border-color:rgb(17, 203, 64);
            outline: none;
        }

        /* Style du bouton */
        button {
            background: linear-gradient(135deg,rgb(17, 203, 17),rgb(37, 252, 66));
            color: white;
            padding: 12px;
            border: none;
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        button:hover {
            background: linear-gradient(135deg,rgb(123, 252, 37),rgb(70, 203, 17));
        }

        /* Message d'erreur */
        .error {
            color: #ff4757;
            font-size: 0.9em;
            margin-top: 10px;
        }

        /* Lien pour s'inscrire */
        .register-link {
            margin-top: 15px;
            font-size: 14px;
        }

        .register-link a {
            color: #6a11cb;
            text-decoration: none;
            font-weight: bold;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Connexion</h2>
    <form method="POST" action="login.php">
        <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
    </form>
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
</div>

</body>
</html>


