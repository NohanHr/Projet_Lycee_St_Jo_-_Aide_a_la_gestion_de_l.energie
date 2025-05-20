<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$serveurNom = 'localhost';
$utilisateur = 'elouan';
$motDePasse = 'Elouan29**';
$dbNom = 'energie';

function connectToDatabase($serveurNom, $utilisateur, $motDePasse, $dbNom) {
    try {
        $conn = new PDO("mysql:host=$serveurNom;dbname=$dbNom", $utilisateur, $motDePasse);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

function calculateDJU($temperature, $baseTemp = 18) {
    return max(0, $baseTemp - $temperature);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    if (!$latitude || !$longitude) {
        echo "Error: Latitude and longitude are required.";
        exit;
    }

    $conn = connectToDatabase($serveurNom, $utilisateur, $motDePasse, $dbNom);

    $cleApi = "6988f2000aa4f8bb860abfe5e68c58ca";
    $url = "https://api.openweathermap.org/data/2.5/weather?lat=$latitude&lon=$longitude&appid=$cleApi&units=metric";

    $response = @file_get_contents($url);
    if ($response === FALSE) {
        echo "Error: Failed to retrieve weather data.";
        exit;
    }

    $weatherData = json_decode($response, true);
    
    if ($weatherData && isset($weatherData['main']) && isset($weatherData['weather'][0])) {
        $lieu = $weatherData['name'] ?? "Unknown";
        $temperature = $weatherData['main']['temp']; // Température utilisée uniquement pour le calcul DJU
        $date_releve = date('Y-m-d H:i:s', $weatherData['dt']); // Conversion du timestamp UNIX
        $vent = $weatherData['wind']['speed'];
        $humidite = $weatherData['main']['humidity'];
        $soleil = $weatherData['weather'][0]['description'];
        $dju = calculateDJU($temperature);
        
        try {
            $sqlMeteo = "INSERT INTO meteo (lieu, latitude, longitude, date_releve, vent, humidite, soleil, dju, date_enregistrement) 
             VALUES (:lieu, :latitude, :longitude, :date_releve, :vent, :humidite, :soleil, :dju, NOW())";

            $stmtMeteo = $conn->prepare($sqlMeteo);
            $stmtMeteo->execute([
                ':lieu' => $lieu,
                ':latitude' => $latitude,
                ':longitude' => $longitude,
                ':date_releve' => $date_releve,
                ':vent' => $vent,
                ':humidite' => $humidite,
                ':soleil' => $soleil,
                ':dju' => $dju
            ]);

            echo "Données météo et DJU enregistrées avec succès !";
        } catch (PDOException $e) {
            echo "Erreur d'enregistrement : " . $e->getMessage();
        }
    } else {
        echo "Error: Invalid weather data received.";
    }
    
} else {
    $conn = connectToDatabase($serveurNom, $utilisateur, $motDePasse, $dbNom);

    $sql = "SELECT lieu, latitude, longitude, vent, humidite, soleil, dju FROM meteo";
    $stmt = $conn->query($sql);
    
    $points = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $points[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($points);
}
?>
