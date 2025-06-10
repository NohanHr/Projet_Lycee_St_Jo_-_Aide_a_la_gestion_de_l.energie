<?php
// Connexion à la base de données
$servername = "localhost";
$username = "admin";
$password = "ciel2";
$dbname = "energie";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Récupération des données envoyées par la requête AJAX
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];
$temperature = $_POST['temperature'];
$humidite = $_POST['humidite'];
$vent = $_POST['vent'];
$date = $_POST['date'];
$ville = $_POST['ville']; // Récupération du nom de la ville

// Requête SQL pour insérer les données dans la table
$sql = "INSERT INTO meteo (latitude, longitude, temperature, humidite, vent, date, ville)
        VALUES ('$latitude', '$longitude', '$temperature', '$humidite', '$vent', '$date', '$ville')";

if ($conn->query($sql) === TRUE) {
    echo "Nouvel enregistrement créé avec succès";
} else {
    echo "Erreur: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
