<?php
header('Content-Type: application/json');

$db = new mysqli("localhost", "admin", "ciel2", "energie");

if ($db->connect_error) {
    die(json_encode(["error" => "Erreur connexion: " . $db->connect_error]));
}

// Récupération des 24 dernières mesures par capteur
$result = $db->query("
    SELECT m.* 
    FROM mesure_pm25 m
    JOIN (
        SELECT id_capteur, MAX(id) as max_id
        FROM mesure_pm25
        GROUP BY id_capteur
    ) last_ids
    ON m.id_capteur = last_ids.id_capteur 
    AND m.id > last_ids.max_id - 24
    ORDER BY m.id_capteur, m.id ASC
");

if (!$result) {
    die(json_encode(["error" => "Erreur requête: " . $db->error]));
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $sensorId = $row['id_capteur'];
    
    if (!isset($data[$sensorId])) {
        $data[$sensorId] = [
            'tempHumidityData' => [],
            'particlesData' => []
        ];
    }
    
    // Ajout des données température/humidité
    $data[$sensorId]['tempHumidityData'][] = [
        'id' => (int)$row['id'],
        'temp' => (float)$row['temperature'],
        'hum' => (float)$row['humidite']
    ];
    
    // Ajout des données particules
    $data[$sensorId]['particlesData'][] = [
        'id' => (int)$row['id'],
        'pm1_0' => (float)$row['particule_1_0'],
        'pm2_5' => (float)$row['particule_2_5'],
        'pm10_0' => (float)$row['particule_10_0']
    ];
}

// Assure qu'on a bien 24 points maximum par capteur
foreach ($data as $sensorId => &$sensorData) {
    $sensorData['tempHumidityData'] = array_slice($sensorData['tempHumidityData'], -24);
    $sensorData['particlesData'] = array_slice($sensorData['particlesData'], -24);
}

echo json_encode($data);
$db->close();
?>
