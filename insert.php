<?php
$db = new mysqli("localhost", "admin", "ciel2", "energie");

if ($db->connect_error) {
    die("Erreur connexion: " . $db->connect_error);
}

$params = [
    'temperature' => $_GET['temperature'],
    'humidite' => $_GET['humidite'],
    'particule_1.0' => $_GET['particule_1.0'],
    'particule_2.5' => $_GET['particule_2.5'],
    'particule_10.0' => $_GET['particule_10.0'],
    'id_capteur' => $_GET['id_capteur'],
    'nom' => $_GET['nom']
];

$stmt = $db->prepare("INSERT INTO mesure_pm25 (
    temperature,
    humidite,
    `particule_1.0`,
    `particule_2.5`,
    `particule_10.0`,
    id_capteur,
    nom
) VALUES (?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "ddiiiss",
    $params['temperature'],
    $params['humidite'],
    $params['particule_1.0'],
    $params['particule_2.5'],
    $params['particule_10.0'],
    $params['id_capteur'],
    $params['nom']
);

if($stmt->execute()) {
    echo "OK";
} else {
    echo "Erreur: " . $stmt->error;
}

$stmt->close();
$db->close();
?>
