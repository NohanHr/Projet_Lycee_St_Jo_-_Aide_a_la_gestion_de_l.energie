<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Cartographique</title>
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
        .back-button {
            background-color: #2c3e50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #1a252f;
        }
    </style>
</head>
<body>
    <button class="back-button" onclick="window.location.href='index.php'">
        <i class="fas fa-arrow-left"></i> Retour à l'accueil
    </button>

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
            const vent = data.wind.speed;
            const ville = data.name || "Inconnu";
            const description = data.weather[0].description;

            const infoMeteo = `
                <b>${ville}</b><br>
                Température: ${temperature} °C<br>
                Humidité: ${humidite}%<br>
                Vitesse du vent: ${vent} m/s<br>
                Conditions: ${description}
            `;

            L.marker([lat, lon]).addTo(carte)
                .bindPopup(infoMeteo)
                .openPopup();

            // Envoi des données au serveur
            $.post('getmeteo.php', {
                latitude: lat,
                longitude: lon,
                temperature: temperature,
                humidite: humidite,
                vent: vent,
                date: new Date().toISOString().slice(0, 19).replace('T', ' ')
            }, function(response) {
                console.log('Données enregistrées avec succès');
            }).fail(function() {
                console.error('Erreur lors de l\'enregistrement des données');
            });
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
