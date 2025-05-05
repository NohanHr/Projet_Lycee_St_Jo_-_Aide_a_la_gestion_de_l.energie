<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-Capteurs</title>
    
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
        }
        .sensor-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(600px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            overflow: hidden;
        }
        .card-title {
            background: #2c3e50;
            color: white;
            padding: 15px 25px;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 20px;
        }
        .chart-container {
            position: relative;
            height: 400px;
            min-height: 300px;
        }
        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 15px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div id="main-container"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Configuration et couleurs
        const colorPalette = {
            temp: '#FF6B6B',
            hum: '#4ECDC4',
            pm1_0: '#45B7D1',
            pm2_5: '#96CEB4',
            pm10_0: '#FF9F76'
        };

        const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
        tooltip: {
            mode: 'index',
            intersect: false,
            callbacks: {
                label: (context) => {
                    const label = context.dataset.label || '';
                    return `${label}: ${context.parsed.y}${context.dataset.unit}`;
                }
            }
        }
    },
    scales: {
        x: {
            type: 'linear',
            title: { 
                display: true, 
                text: 'ID Mesure',
                ticks: {
                    stepSize: 3 // Affiche un tick toutes les 3 mesures
                }
            }
        },
        y: {
            title: { display: true, text: 'Valeur' },
            beginAtZero: true
        }
    },
    elements: {
        point: {
            radius: 2 // Réduit la taille des points
        }
    }
};
        let charts = {};

        function createSensorSection(sensorId) {
            const section = document.createElement('div');
            section.className = 'sensor-container';
            section.innerHTML = `
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-microchip"></i>
                        <span>Capteur ${sensorId}</span>
                    </div>
                    <div class="charts-container">
                        <div class="chart-container">
                            <canvas id="${sensorId}-tempHumChart"></canvas>
                            <div class="legend" id="${sensorId}-tempHumLegend"></div>
                        </div>
                        <div class="chart-container">
                            <canvas id="${sensorId}-particlesChart"></canvas>
                            <div class="legend" id="${sensorId}-particlesLegend"></div>
                        </div>
                    </div>
                </div>
            `;
            return section;
        }

        function initializeSensorChart(sensorId) {
            // Graphique Température/Humidité
            const tempHumCtx = document.getElementById(`${sensorId}-tempHumChart`).getContext('2d');
            charts[`${sensorId}-tempHum`] = new Chart(tempHumCtx, {
                type: 'line',
                data: {
                    datasets: [
                        {
                            label: 'Température',
                            data: [],
                            borderColor: colorPalette.temp,
                            backgroundColor: colorPalette.temp + '20',
                            borderWidth: 2,
                            tension: 0.2,
                            unit: '°C'
                        },
                        {
                            label: 'Humidité',
                            data: [],
                            borderColor: colorPalette.hum,
                            backgroundColor: colorPalette.hum + '20',
                            borderWidth: 2,
                            tension: 0.2,
                            unit: '%'
                        }
                    ]
                },
                options: chartOptions
            });

            // Graphique Particules
            const particlesCtx = document.getElementById(`${sensorId}-particlesChart`).getContext('2d');
            charts[`${sensorId}-particles`] = new Chart(particlesCtx, {
                type: 'line',
                data: {
                    datasets: [
                        {
                            label: 'PM1.0',
                            data: [],
                            borderColor: colorPalette.pm1_0,
                            backgroundColor: colorPalette.pm1_0 + '20',
                            borderWidth: 2,
                            tension: 0.2,
                            unit: 'µm'
                        },
                        {
                            label: 'PM2.5',
                            data: [],
                            borderColor: colorPalette.pm2_5,
                            backgroundColor: colorPalette.pm2_5 + '20',
                            borderWidth: 2,
                            tension: 0.2,
                            unit: 'µm'
                        },
                        {
                            label: 'PM10',
                            data: [],
                            borderColor: colorPalette.pm10_0,
                            backgroundColor: colorPalette.pm10_0 + '20',
                            borderWidth: 2,
                            tension: 0.2,
                            unit: 'µm'
                        }
                    ]
                },
                options: chartOptions
            });
        }

        async function chargerDonnees() {
            try {
                const response = await fetch('getdata.php');
                const data = await response.json();

                // Mise à jour des graphiques pour chaque capteur
                Object.keys(data).forEach(sensorId => {
                    // Température et Humidité
                    const tempHumChart = charts[`${sensorId}-tempHum`];
                    tempHumChart.data.datasets[0].data = data[sensorId].tempHumidityData.map(d => ({ x: d.id, y: d.temp }));
                    tempHumChart.data.datasets[1].data = data[sensorId].tempHumidityData.map(d => ({ x: d.id, y: d.hum }));
                    tempHumChart.update();

                    // Particules
                    const particlesChart = charts[`${sensorId}-particles`];
                    particlesChart.data.datasets[0].data = data[sensorId].particlesData.map(d => ({ x: d.id, y: d.pm1_0 }));
                    particlesChart.data.datasets[1].data = data[sensorId].particlesData.map(d => ({ x: d.id, y: d.pm2_5 }));
                    particlesChart.data.datasets[2].data = data[sensorId].particlesData.map(d => ({ x: d.id, y: d.pm10_0 }));
                    particlesChart.update();
                });
                
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', async () => {
            const mainContainer = document.getElementById('main-container');
            
            // Récupération des capteurs existants
            const initialData = await fetch('getdata.php').then(res => res.json());
            const sensorIds = Object.keys(initialData);

            // Création des sections pour chaque capteur
            sensorIds.forEach(sensorId => {
                mainContainer.appendChild(createSensorSection(sensorId));
                initializeSensorChart(sensorId);
            });

            // Mise à jour initiale et intervalle
            chargerDonnees();
            setInterval(chargerDonnees, 60000);
        });
    </script>
</body>
</html>
