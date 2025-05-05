<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Gestion énergétique</title>
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
                <h2><i class="fas fa-leaf"></i> EcoTrack</h2>
                <p>Gestion énergétique</p>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="#" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-map-marker-alt"></i> Cartographie</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-chart-line"></i> Statistiques</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-cog"></i> Paramètres</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-users"></i> Utilisateurs</a></li>
            </ul>
        </aside>

        <!-- Header -->
        <header class="header">
            <h1>Tableau de bord</h1>
            <div class="user-info">
                <a href="logout.php" class="logout-button"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </header>

        <!-- Main content -->
        <main class="main-content">
            <!-- Data entry card -->
            <div class="card">
                <div class="card-title">
                    <span><i class="fas fa-edit"></i> Saisie des données</span>
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
                        <label for="temperature">Température (°C)</label>
                        <input type="number" id="temperature" name="temperature" class="form-control" min="0" max="90" step="0.01" required>
                    </div>
                    
                    <button type="submit" class="btn btn-block">
                        <i class="fas fa-paper-plane"></i> Envoyer les données
                    </button>
                </form>
            </div>

            <!-- Map card -->
            <div class="card">
                <div class="card-title">
                    <span><i class="fas fa-map-marked-alt"></i> Localisation des capteurs</span>
                </div>
                <div id="mapid"></div>
            </div>

            <!-- Charts section -->
            <div class="card">
                <div class="card-title">
                    <span><i class="fas fa-chart-area"></i> Visualisation des données</span>
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

    <script type="text/javascript">
        // Initialisation de la carte
        const carte = L.map('mapid').setView([47.7485, -3.3668], 13); // Centré sur Lorient par défaut
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(carte);

        let marqueurs = [];
        const cleApi = '6988f2000aa4f8bb860abfe5e68c58ca';

        // Fonction pour ajouter un marqueur à la carte
        function ajouterMarqueur(lat, lon) {
            $.getJSON(`https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${cleApi}&units=metric`)
                .done(function(data) {
                    const temperature = data.main.temp;
                    const ville = data.name;
                    const infoMeteo = `
                        <b>${ville}</b><br>
                        Température: ${temperature} °C<br>
                        Humidité: ${data.main.humidity}%<br>
                        Conditions: ${data.weather[0].description}
                    `;

                    const marqueur = L.marker([lat, lon]).addTo(carte)
                        .bindPopup(infoMeteo)
                        .openPopup();
                    marqueurs.push(marqueur);

                    document.getElementById('temperature').value = temperature;
                    carte.setView([lat, lon], 13);
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("Erreur météo:", textStatus, errorThrown);
                    // Ajouter le marqueur même sans données météo
                    const marqueur = L.marker([lat, lon]).addTo(carte)
                        .bindPopup(`Localisation: ${lat.toFixed(6)}, ${lon.toFixed(6)}`)
                        .openPopup();
                    marqueurs.push(marqueur);
                });
        }

        // Fonction pour charger les points de la base de données
        function chargerPoints() {
            $.getJSON('getPoints.php')
                .done(function(points) {
                    points.forEach(point => {
                        ajouterMarqueur(point.latitude, point.longitude);
                    });
                    if (points.length > 0) {
                        const lastPoint = points[points.length - 1];
                        carte.setView([lastPoint.latitude, lastPoint.longitude], 13);
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("Erreur chargement points:", textStatus, errorThrown);
                });
        }

        // Écouteur d'événement pour le clic sur la carte
        carte.on('click', function(e) {
            const lat = e.latlng.lat;
            const lon = e.latlng.lng;

            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lon.toFixed(6);
            ajouterMarqueur(lat, lon);
        });

        // Fonction pour récupérer la géolocalisation
        function obtenirLocalisation() {
            const infoElement = document.getElementById('info');
            infoElement.textContent = 'Localisation en cours...';
            infoElement.style.color = '#666';

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        document.getElementById('latitude').value = lat;
                        document.getElementById('longitude').value = lon;
                        carte.setView([lat, lon], 13);
                        ajouterMarqueur(lat, lon);
                        infoElement.textContent = 'Localisation réussie';
                        infoElement.style.color = 'var(--success-color)';
                    },
                    function(error) {
                        let message = "Erreur de géolocalisation: ";
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                message += "Permission refusée";
                                break;
                            case error.POSITION_UNAVAILABLE:
                                message += "Position indisponible";
                                break;
                            case error.TIMEOUT:
                                message += "Délai dépassé";
                                break;
                            default:
                                message += "Erreur inconnue";
                        }
                        infoElement.textContent = message;
                        infoElement.style.color = 'var(--danger-color)';
                        console.error("Erreur géolocalisation:", error);
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else {
                infoElement.textContent = "Géolocalisation non supportée par ce navigateur";
                infoElement.style.color = 'var(--danger-color)';
            }
        }

        // Initialisation des graphiques
const tempTimeCtx = document.getElementById('tempTimeChart').getContext('2d');
const tempTimeChart = new Chart(tempTimeCtx, {
    type: 'line',
    data: {
        datasets: [
            {
                label: 'Température (°C)',
                data: [],
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                borderWidth: 2,
                tension: 0.1,
                yAxisID: 'y'
            },
            {
                label: 'Humidité (%)',
                data: [],
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderWidth: 2,
                tension: 0.1,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += context.parsed.y;
                        if (context.dataset.label === 'Température (°C)') {
                            label += '°C';
                        } else {
                            label += '%';
                        }
                        label += ` (ID: ${context.parsed.x})`;
                        return label;
                    }
                }
            }
        },
        scales: {
            x: {
                title: { 
                    display: true, 
                    text: 'Temps (mesures toutes les 20 minutes)', 
                    color: '#666' 
                },
                type: 'linear',
                grid: { color: 'rgba(0, 0, 0, 0.05)' }
            },
            y: {
                title: { 
                    display: true, 
                    text: 'Température (°C)', 
                    color: 'rgba(255, 99, 132, 1)' 
                },
                position: 'left',
                min: 0,
                grid: { color: 'rgba(0, 0, 0, 0.05)' }
            },
            y1: {
                title: { 
                    display: true, 
                    text: 'Humidité (%)', 
                    color: 'rgba(54, 162, 235, 1)' 
                },
                position: 'right',
                min: 0,
                max: 100,
                grid: { drawOnChartArea: false }
            }
        }
    }
});

const particlesCtx = document.getElementById('particlesChart').getContext('2d');
const particlesChart = new Chart(particlesCtx, {
    type: 'line',
    data: {
        datasets: [
            { 
                label: 'PM 1.0 (µm)', 
                data: [], 
                borderColor: 'rgba(255, 99, 132, 1)', 
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                borderWidth: 2,
                tension: 0.1,
                fill: true
            },
            { 
                label: 'PM 2.5 (µm)', 
                data: [], 
                borderColor: 'rgba(54, 162, 235, 1)', 
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderWidth: 2,
                tension: 0.1,
                fill: true
            },
            { 
                label: 'PM 10.0 (µm)', 
                data: [], 
                borderColor: 'rgba(75, 192, 192, 1)', 
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                borderWidth: 2,
                tension: 0.1,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        return `${context.dataset.label}: ${context.parsed.y} µg/m³ (ID: ${context.parsed.x})`;
                    }
                }
            }
        },
        scales: {
            x: {
                title: { 
                    display: true, 
                    text: 'Temps (mesures toutes les 20 minutes)', 
                    color: '#666' 
                },
                type: 'linear',
                grid: { color: 'rgba(0, 0, 0, 0.05)' }
            },
            y: {
                title: { display: true, text: 'Concentration (µg/m³)', color: '#666' },
                min: 0,
                grid: { color: 'rgba(0, 0, 0, 0.05)' }
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        }
    }
});

// Fonction pour charger les données des graphiques
function chargerDonneesGraphiques() {
    fetch('getdata.php')
        .then(response => {
            if (!response.ok) throw new Error('Erreur réseau');
            return response.json();
        })
        .then(data => {
            // Graphique Température et Humidité vs Temps
            if (data.tempHumidityData && data.tempHumidityData.length > 0) {
                tempTimeChart.data.datasets[0].data = data.tempHumidityData.map(item => ({x: item.id, y: item.temp}));
                tempTimeChart.data.datasets[1].data = data.tempHumidityData.map(item => ({x: item.id, y: item.hum}));
                tempTimeChart.update();
            }

            // Graphique Particules
            if (data.particlesData && data.particlesData.length > 0) {
                particlesChart.data.datasets[0].data = data.particlesData.map(item => ({x: item.id, y: item.pm1_0}));
                particlesChart.data.datasets[1].data = data.particlesData.map(item => ({x: item.id, y: item.pm2_5}));
                particlesChart.data.datasets[2].data = data.particlesData.map(item => ({x: item.id, y: item.pm10_0}));
                particlesChart.update();
            }
        })
        .catch(error => {
            console.error('Erreur chargement données:', error);
        });
}

        // Chargement initial
        document.addEventListener('DOMContentLoaded', function() {
            chargerPoints();
            chargerDonneesGraphiques();
            
            // Actualisation périodique
            setInterval(chargerDonneesGraphiques, 60000);
            setInterval(chargerPoints, 300000); // Toutes les 5 minutes
        });
    </script>
</body>
</html>
