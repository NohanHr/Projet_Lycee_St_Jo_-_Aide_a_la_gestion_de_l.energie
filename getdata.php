<?php
header('Content-Type: application/json');

$db = new mysqli("localhost", "admin", "ciel2", "energie");

if ($db->connect_error) {
    die(json_encode(["error" => "Erreur connexion: " . $db->connect_error]));
}

// Récupérer les données pour les graphiques
$result = $db->query("SELECT id, temperature, humidite, particule_1_0, particule_2_5, particule_10_0 FROM mesure_pm25 ORDER BY id");
if (!$result) {
    die(json_encode(["error" => "Erreur requête: " . $db->error]));
}

$data = [
    'tempHumidityData' => [],
    'particlesData' => []
];

while ($row = $result->fetch_assoc()) {
    $data['tempHumidityData'][] = [
        'id' => (int)$row['id'],
        'temp' => (float)$row['temperature'],
        'hum' => (float)$row['humidite']
    ];
    
    $data['particlesData'][] = [
        'id' => (int)$row['id'],
        'pm1_0' => (float)$row['particule_1_0'],
        'pm2_5' => (float)$row['particule_2_5'],
        'pm10_0' => (float)$row['particule_10_0']
    ];
}

echo json_encode($data);
$db->close();
?>
