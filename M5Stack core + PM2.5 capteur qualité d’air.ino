#include <WiFi.h>                      // Bibliothèque pour gérer la connexion Wi-Fi
#include <M5Stack.h>                   // Bibliothèque spécifique à la carte M5Stack pour l'affichage et le contrôle
#include <Wire.h>                      // Bibliothèque pour la communication I2C
#include <Adafruit_Sensor.h>          // Bibliothèque de base pour les capteurs Adafruit
#include <Adafruit_SHT31.h>           // Bibliothèque pour le capteur d'humidité/température SHT31
#include <PMS.h>                       // Bibliothèque pour le capteur de particules PMS5003
#include <HTTPClient.h>               // Bibliothèque pour faire des requêtes HTTP

Adafruit_SHT31 sht31 = Adafruit_SHT31();  // Création d'un objet pour le capteur SHT31
PMS pms(Serial2);                         // Création d'un objet PMS en utilisant le port série 2 (Serial2)
PMS::DATA data;                           // Structure pour stocker les données lues par le capteur de particules

// Informations de connexion Wi-Fi
const char* ssid = "Energie";                   // Nom du réseau Wi-Fi
const char* password = "gestionenergie";        // Mot de passe du réseau Wi-Fi
const char* serverUrl = "http://10.5.25.5/insert.php";  // URL du serveur de réception des données

// Informations sur le capteur
const String id_capteur = "M5Stack-01";          // Identifiant unique du capteur (peut être modifié)
const String nom_capteur = "Capteur_Salle_1";    // Nom descriptif du capteur (peut être modifié)

// Fonction pour afficher un en-tête sur l'écran M5Stack
void header(const char *string, uint16_t color) {
    M5.Lcd.fillScreen(color);                // Remplit l'écran avec une couleur de fond
    M5.Lcd.setTextSize(1);                   // Définit la taille du texte
    M5.Lcd.setTextColor(TFT_WHITE, TFT_BLACK); // Texte blanc sur fond noir
    M5.Lcd.fillRect(0, 0, 320, 30, TFT_BLACK);  // Rectangle noir en haut de l'écran
    M5.Lcd.setTextDatum(TC_DATUM);           // Centre horizontalement le texte
    M5.Lcd.drawString(string, 160, 3, 4);     // Affiche le texte centré
}

// Fonction d'initialisation
void setup() {
    M5.begin();                              // Initialise la carte M5Stack
    M5.Lcd.println("Connexion au Wi-Fi..."); // Affiche le message de connexion

    WiFi.begin(ssid, password);              // Démarre la connexion Wi-Fi

    while (WiFi.status() != WL_CONNECTED) {  // Boucle jusqu'à la connexion réussie
        delay(1000);
        M5.Lcd.print(".");
    }
    M5.Lcd.println("\nConnecté au Wi-Fi");   // Affiche le succès de la connexion

    Serial.begin(115200);                    // Initialise le port série pour le débogage
    Serial2.begin(9600);                     // Initialise le port série pour le capteur PMS

    if (!sht31.begin(0x44)) {                // Initialise le capteur SHT31 à l'adresse I2C 0x44
        M5.Lcd.println("Erreur SHT31!");     // Affiche une erreur si le capteur ne répond pas
        while(1);                            // Bloque le programme si erreur
    }

    header("M5Stack PM2.5", TFT_BLACK);      // Affiche un en-tête sur fond noir
}

// Fonction pour envoyer les données au serveur via HTTP GET
void envoyerDonnees(float temp, float hum, float pm1, float pm25, float pm10) {
    if(WiFi.status() != WL_CONNECTED) return;  // Ne rien faire si pas connecté au Wi-Fi

    HTTPClient http;                           // Création d’un client HTTP

    // Construction de la requête GET avec les données
    String requete = String(serverUrl) + 
        "?temperature=" + String(temp) +
        "&humidite=" + String(hum) +
        "&particule_1_0=" + String(pm1) +
        "&particule_2_5=" + String(pm25) +
        "&particule_10_0=" + String(pm10) +
        "&id_capteur=" + id_capteur +
        "&nom=" + nom_capteur;

    requete.replace(" ", "%20");               // Encode les espaces pour l’URL

    http.begin(requete);                       // Lance la requête HTTP
    int codeReponse = http.GET();              // Exécute un GET et stocke le code de réponse

    Serial.println("URL: " + requete);         // Affiche l’URL complète pour le débogage
    Serial.println("Code réponse: " + String(codeReponse));  // Affiche le code HTTP

    if(codeReponse == HTTP_CODE_OK) {          // Si la requête a réussi
        String reponse = http.getString();     // Récupère le corps de la réponse
        Serial.println("Réponse: " + reponse); // Affiche la réponse
    }

    http.end();                                // Termine la connexion HTTP
}

// Boucle principale exécutée en continu
void loop() {
    float t = sht31.readTemperature() - 2;     // Lecture de la température corrigée (-2°C pour calibrage)
    float h = sht31.readHumidity() -10;        // Lecture de l'humidité corrigée (-10% pour calibrage)

    M5.Lcd.fillScreen(BLACK);                  // Efface l'écran (noir)
    M5.Lcd.setTextSize(2);                     // Définit la taille du texte

    M5.Lcd.setCursor(0, 0);                    // Positionne le curseur
    M5.Lcd.setTextColor(TFT_YELLOW, TFT_BLACK);
    M5.Lcd.printf("Temperature:");             // Affiche "Temperature:"
    M5.Lcd.setCursor(210, 0);
    M5.Lcd.printf("%.2f C", t);                // Affiche la température

    M5.Lcd.setCursor(0, 50);
    M5.Lcd.setTextColor(TFT_WHITE, TFT_BLACK);
    M5.Lcd.printf("Humidite:");                // Affiche "Humidité:"
    M5.Lcd.setCursor(210, 50);
    M5.Lcd.printf("%.2f %%", h);               // Affiche l'humidité

    M5.Lcd.setTextColor(TFT_BLUE, TFT_BLACK);
    M5.Lcd.setCursor(0, 100);
    M5.Lcd.printf("Qualite de l'air:");        // Affiche la section de qualité de l’air

    M5.Lcd.setCursor(0, 120);
    M5.Lcd.setTextColor(TFT_GREEN, TFT_BLACK);
    M5.Lcd.printf("PM1.0:");                   // Affiche "PM1.0:"
    M5.Lcd.setCursor(210, 120);
    M5.Lcd.printf("%d ug/m3", data.PM_AE_UG_1_0); // Affiche la valeur PM1.0

    M5.Lcd.setCursor(0, 140);
    M5.Lcd.setTextColor(TFT_OLIVE, TFT_BLACK);
    M5.Lcd.printf("PM2.5:");                   // Affiche "PM2.5:"
    M5.Lcd.setCursor(210, 140);
    M5.Lcd.printf("%d ug/m3", data.PM_AE_UG_2_5); // Affiche la valeur PM2.5

    M5.Lcd.setCursor(0, 160);
    M5.Lcd.setTextColor(TFT_MAROON, TFT_BLACK);
    M5.Lcd.printf("PM10:");                    // Affiche "PM10:"
    M5.Lcd.setCursor(210, 160);
    M5.Lcd.printf("%d ug/m3", data.PM_AE_UG_10_0); // Affiche la valeur PM10

    // Envoi des données au serveur
    envoyerDonnees(t, h, data.PM_AE_UG_1_0, data.PM_AE_UG_2_5, data.PM_AE_UG_10_0);

    delay(1000);                               // Pause de 1 seconde

    pms.wakeUp();                              // Réveille le capteur PMS
    M5.Lcd.fillScreen(BLACK);                  // Efface l'écran
    delay(1200000);                            // Attente de 20 minutes (20 * 60 * 1000 ms)
    pms.readUntil(data);                       // Lit les données du capteur de particules
    pms.sleep();                               // Met le capteur en veille pour économiser de l'énergie
}
