<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection parameters
$serveurNom = 'localhost';
$utilisateur = 'admin';
$motDePasse = 'ciel2';
$dbNom = 'energie';

// Function to establish database connection
function connectToDatabase($serveurNom, $utilisateur, $motDePasse, $dbNom) {
    try {
        $conn = new PDO("mysql:host=$serveurNom;dbname=$dbNom", $utilisateur, $motDePasse);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Function to calculate DJU (Heating Degree Days)
function calculateDJU($temperature, $baseTemp = 18) {
    // DJU = max(0, Tbase - Textérieure) pour le chauffage
    return max(0, $baseTemp - $temperature);
}

// Handle POST request to insert weather data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve POST data
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    if (!$latitude || !$longitude) {
        echo "Error: Latitude and longitude are required.";
        exit;
    }

    // Connect to database
    $conn = connectToDatabase($serveurNom, $utilisateur, $motDePasse, $dbNom);

    // Fetch weather data from OpenWeather API
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
        $temperature = $weatherData['main']['temp'];
        $vent = $weatherData['wind']['speed'];
        $humidite = $weatherData['main']['humidity'];
        $soleil = $weatherData['weather'][0]['description'];
        $dju = calculateDJU($temperature); // Calculate DJU
        
        // Insert weather data into the meteo table
        try {
            $sqlMeteo = "INSERT INTO meteo (lieu, latitude, longitude, temperature, vent, humidite, soleil, dju, date_enregistrement) 
             VALUES (:lieu, :latitude, :longitude, :temperature, :vent, :humidite, :soleil, :dju, NOW())";

$stmtMeteo = $conn->prepare($sqlMeteo);
$stmtMeteo->execute([
    ':lieu' => $lieu,
    ':latitude' => $latitude,
    ':longitude' => $longitude,
    ':temperature' => $temperature,
    ':vent' => $vent,
    ':humidite' => $humidite,
    ':soleil' => $soleil,
    ':dju' => $dju
]);

            
            echo "Weather data and DJU saved successfully!";
        } catch (PDOException $e) {
            echo "Error saving data: " . $e->getMessage();
        }
    } else {
        echo "Error: Invalid weather data received.";
    }
    
} else {
    // Handle non-POST request to retrieve weather data
    $conn = connectToDatabase($serveurNom, $utilisateur, $motDePasse, $dbNom);

    $sql = "SELECT lieu, latitude, longitude, temperature, vent, humidite, soleil, dju FROM meteo";
    $stmt = $conn->query($sql);
    
    $points = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $points[] = $row;
    }

    // Return data as JSON
    header('Content-Type: application/json');
    echo json_encode($points);
}
?>