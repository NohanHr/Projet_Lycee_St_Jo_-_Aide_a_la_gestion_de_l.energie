<?php
require_once('auth.php');

if (!estConnecte()) {
    header("Location: login.php");
    exit();
}

if (estVisiteur()) {
    echo "<h1>Bienvenue, utilisateur</h1>";
    // affichage des courbes sans possibilité de modifier
}

if (estAdmin()) {
    echo "<h1>Bienvenue, admin</h1>";
    // accès total à la configuration, édition, etc.
}

function calculateDJU($temperature, $baseTemp) {
    return max(0, $baseTemp - $temperature);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['baseTemp'])) {
    $baseTemp = $_POST['baseTemp'];

    $cleApi = "6988f2000aa4f8bb860abfe5e68c58ca";
    $url = "https://api.openweathermap.org/data/2.5/weather?lat=47.7485&lon=-3.3668&appid=$cleApi&units=metric";

    $response = @file_get_contents($url);
    if ($response === FALSE) {
        echo "Error: Failed to retrieve weather data.";
        exit;
    }

    $weatherData = json_decode($response, true);

    if ($weatherData && isset($weatherData['main']) && isset($weatherData['weather'][0])) {
        $temperature = $weatherData['main']['temp'];
        $dju = calculateDJU($temperature, $baseTemp);

        $result = [
            'temperature' => $temperature,
            'baseTemp' => $baseTemp,
            'dju' => $dju
        ];
    } else {
        $result = [
            'error' => "Error: Invalid weather data received."
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Accueil - Gestion énergétique</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --danger-color: #ff4757;
            --success-color: #2ed573;
            --light-color: #f1f2f6;
            --dark-color: #2f3542;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: #f5f6fa;
        }

        .container {
            display: grid;
            grid-template-columns: 300px 1fr;
            grid-template-rows: auto 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            grid-row: 1 / -1;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 10px;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .nav-link i {
            margin-right: 10px;
        }

        /* Header */
        .header {
            background-color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            grid-column: 2;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .logout-button {
            background-color: var(--danger-color);
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .logout-button:hover {
            background-color: #ff6b81;
        }

        /* Main content */
        .main-content {
            padding: 30px;
            grid-column: 2;
            overflow-y: auto;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: var(--primary-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title i {
            color: var(--secondary-color);
        }

        /* Form styles */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        /* Map container */
        #mapid {
            width: 100%;
            height: 400px;
            border-radius: 10px;
        }

        /* Charts container */
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        /* Result container */
        .result-container {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                grid-column: 1;
                grid-row: 1;
            }

            .header, .main-content {
                grid-column: 1;
            }
        }

        @media (max-width: 576px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <h2><i class="fas fa-leaf"></i> Gestion énergie</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Accueil</a></li>
                <li class="nav-item"><a href="carte.php" class="nav-link"><i class="fas fa-map-marker-alt"></i> Cartographie</a></li>
                <li class="nav-item"><a href="graphique.php" class="nav-link"><i class="fas fa-chart-pie"></i> Graphiques</a></li>
            </ul>
        </aside>

        <!-- Header -->
        <header class="header">
            <h1>Accueil</h1>
            <div class="user-info">
                <a href="logout.php" class="logout-button"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </header>

        <!-- Main content -->
        <main class="main-content">
            <!-- Data entry card -->
            <div class="card">
                <div class="card-title">
                    <span><i class="fas fa-edit"></i> Calculer les DJU</span>
                </div>
                <form name="formulaire" action="index.php" method="POST">
                    <div class="form-group">
                        <label for="baseTemp">Température de référence (°C)</label>
                        <input type="number" id="baseTemp" name="baseTemp" class="form-control" min="0" max="50" step="0.1" required>
                    </div>
                    <button type="submit" class="btn btn-block">
                        <i class="fas fa-calculator"></i> Calculer les DJU
                    </button>
                </form>
                <?php if (isset($result)): ?>
                    <div class="result-container">
                        <?php if (isset($result['error'])): ?> 
                            <p><?php echo $result['error']; ?></p>
                        <?php else: ?>
                            <p>Température actuelle: <?php echo $result['temperature']; ?> °C</p>
                            <p>Température de référence: <?php echo $result['baseTemp']; ?> °C</p>
                            <p>DJU calculé: <?php echo $result['dju']; ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
