<?php
require_once('auth.php');

if (!estConnecte()) {
    header("Location: login.php");
    exit();
}

if (estVisiteur()) {
    echo "<h1>Bienvenue, visiteur</h1>";
    echo "<p>Voici les courbes en lecture seule</p>";
    // affichage des courbes sans possibilité de modifier
}

if (estAdmin()) {
    echo "<h1>Bienvenue, admin</h1>";
    echo "<p>Accès complet</p>";
    // accès total à la configuration, édition, etc.
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
                <li class="nav-item"><a href="index.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
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
                    <span><i class="fas fa-edit"></i> Ajouter un capteur</span>
                </div>
                <form name="formulaire" action="getmeteo.php" method="POST">
                    <div class="form-group">
                        <label for="salle">Nom de la salle</label>
                        <input type="text" id="salle" name="salle" class="form-control" title="au moins 2 caractères alphabétiques" minlength="2" required>
                    </div>

                    <div class="form-group">
                        <label for="id_capteur">ID du capteur (1-100)</label>
                        <input type="number" id="id_capteur" name="id_capteur" class="form-control" min="1" max="100" required>
                    </div>

                    <div class="form-group">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <label for="latitude">Latitude</label>
                                <input type="number" id="latitude" name="latitude" class="form-control" min="-90" max="90" step="0.0000001" required>
                            </div>
                            <div>
                                <label for="longitude">Longitude</label>
                                <input type="number" id="longitude" name="longitude" class="form-control" min="-180" max="180" step="0.0000001" required>
                            </div>
                        </div>
                        <span id="info" style="display: block; font-size: 12px; color: #666; margin-top: 5px;"></span>
                    </div>

                    <div class="form-group">
                        <label for="temperature">Date</label>
                        <input type="number" id="temperature" name="temperature" class="form-control" min="0" max="90" step="0.01" required>
                    </div>

                    <button type="submit" class="btn btn-block">
                        <i class="fas fa-paper-plane"></i> valider l'enregistrement du capteur
                    </button>
                </form>
            </div>
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .card-title {
            background: #2c3e50;
            color: white;
            padding: 15px;
            font-size: 18px;
        }
        .card-title i {
            margin-right: 10px;
        }
        #mapid {
            height: 500px; /* Dimension essentielle */
            width: 100%;
        }
        .charts-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 15px;
        }
        .chart-container {
            flex: 1;
            min-width: 300px;
            height: 300px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-title">
            <span><i class="fas fa-map-marked-alt"></i> Localisation des capteurs</span>
        </div>
        <div id="mapid"></div>
    </div>

    <!-- Charts section -->
    <div class="card">
        <div class="card-title">
        </div>
        <div class="charts-container">
            <div class="chart-container">
                <canvas id="tempTimeChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="particlesChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script type="text/javascript">
        const cleApi = "6988f2000aa4f8bb860abfe5e68c58ca"; // Remplacez par votre clé OpenWeather

        // Initialisation de la carte
        const carte = L.map('mapid').setView([47.7485, -3.3668], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(carte);

        // Fonction pour ajouter un marqueur avec des données météo
        function ajouterMarqueur(lat, lon) {
            $.getJSON(`https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${cleApi}&units=metric&lang=fr`)
                .done(function(data) {
                    const temperature = data.main.temp;
                    const humidite = data.main.humidity;
                    const ville = data.name || "Inconnu";
                    const description = data.weather[0].description;

                    const infoMeteo = `
                        <b>${ville}</b><br>
                        Température: ${temperature} °C<br>
                        Humidité: ${humidite}%<br>
                        Conditions: ${description}
                    `;

                    L.marker([lat, lon]).addTo(carte)
                        .bindPopup(infoMeteo)
                        .openPopup();
                })
                .fail(function() {
                    L.marker([lat, lon]).addTo(carte)
                        .bindPopup("Impossible de récupérer les données météo.")
                        .openPopup();
                });
        }

        // Ajout d'un événement de clic sur la carte
        carte.on('click', function(e) {
            const lat = e.latlng.lat;
            const lon = e.latlng.lng;
            ajouterMarqueur(lat, lon);
        });

    </script>
</body>
</html>
