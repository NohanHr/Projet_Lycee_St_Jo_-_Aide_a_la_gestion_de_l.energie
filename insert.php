<?php
$db = new mysqli("localhost", "admin", "ciel2", "energie");

if ($db->connect_error) {
    die("Erreur connexion: " . $db->connect_error);
}

$params = [
    'temperature' => $_GET['temperature'],
    'humidite' => $_GET['humidite'],
    'particule_1_0' => $_GET['particule_1_0'],
    'particule_2_5' => $_GET['particule_2_5'],
    'particule_10_0' => $_GET['particule_10_0'],
    'id_capteur' => $_GET['id_capteur'],
    'nom' => $_GET['nom'],
    'timestamp' => date('Y-m-d H:i:s') // Ajoute l'horodatage actuel
];

$stmt = $db->prepare("INSERT INTO mesure_pm25 (
    temperature,
    humidite,
    `particule_1_0`,
    `particule_2_5`,
    `particule_10_0`,
    id_capteur,
    nom,
    timestamp
) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "dddddsss",
    $params['temperature'],
    $params['humidite'],
    $params['particule_1_0'],
    $params['particule_2_5'],
    $params['particule_10_0'],
    $params['id_capteur'],
    $params['nom'],
    $params['timestamp']
);

if($stmt->execute()) {
    echo "OK";
} else {
    echo "Erreur: " . $stmt->error;
}

$stmt->close();
$db->close();
?>
