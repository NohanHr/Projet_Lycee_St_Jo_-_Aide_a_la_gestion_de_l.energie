// === Bibliothèques nécessaires ===
#include <SD.h>                      // Pour lire la carte SD
#include <SPI.h>                     // Pour la communication SPI (utilisée par la carte SD)
#include <WiFi.h>                    // Pour se connecter au réseau Wi-Fi
#include <M5Stack.h>                 // Pour utiliser les fonctions spécifiques à la carte M5Stack
#include <Wire.h>                    // Pour la communication I2C (utilisée par le capteur SHT31)
#include <Adafruit_Sensor.h>        // Bibliothèque générique de capteurs Adafruit
#include <Adafruit_SHT31.h>         // Pour le capteur SHT31 (température/humidité)
#include <PMS.h>                     // Pour le capteur de particules PMS5003
#include <HTTPClient.h>             // Pour envoyer des requêtes HTTP

// === Déclaration des objets capteurs ===
Adafruit_SHT31 sht31 = Adafruit_SHT31();     // Objet pour le capteur de température/humidité
PMS pms(Serial2);                             // Objet pour le capteur de particules connecté au port série 2
PMS::DATA data;                               // Structure pour stocker les données du capteur de particules

// === Paramètres de connexion Wi-Fi et du serveur ===
String ssid;                                  // SSID du réseau Wi-Fi (lu depuis la carte SD)
String password;                              // Mot de passe Wi-Fi (lu depuis la carte SD)
const char* serverUrl = "http://10.5.25.5/insert.php";  // URL du serveur web pour l'envoi des données

// === Informations sur le capteur ===
const String id_capteur = "M5Stack-01";              // ID unique du capteur
const String nom_capteur = "Capteur_Salle_1";        // Nom/description du capteur

// === Fonction pour lire les identifiants Wi-Fi depuis la carte SD ===
bool lireWiFiDepuisSD(String &ssid, String &password) {
    // Tente d'initialiser la carte SD avec la broche CS (Chip Select) définie pour la carte M5Stack
    if (!SD.begin(TFCARD_CS_PIN)) {
        // Affiche un message d'erreur sur le port série si l'initialisation échoue
        Serial.println("Erreur d'initialisation de la carte SD.");
        return false; // Quitte la fonction avec une erreur
    }

    // Ouvre le fichier "wifi.txt" situé à la racine de la carte SD
    File fichier = SD.open("/wifi.txt");
    if (!fichier) {
        // Si le fichier ne peut pas être ouvert, affiche une erreur
        Serial.println("Impossible d'ouvrir wifi.txt");
        return false; // Quitte la fonction avec une erreur
    }

    // Lit le fichier ligne par ligne tant qu’il y a des données à lire
    while (fichier.available()) {
        // Lit une ligne jusqu’à la fin de ligne (caractère '\n')
        String ligne = fichier.readStringUntil('\n');
        ligne.trim(); // Supprime les espaces et caractères invisibles en début/fin de ligne

        // Si la ligne commence par "ssid=", c'est le nom du réseau Wi-Fi
        if (ligne.startsWith("ssid=")) {
            // On extrait le texte après "ssid=" (à partir du caractère 5)
            ssid = ligne.substring(5);
        } 
        // Si la ligne commence par "password=", c'est le mot de passe Wi-Fi
        else if (ligne.startsWith("password=")) {
            // On extrait le texte après "password=" (à partir du caractère 9)
            password = ligne.substring(9);
        }
    }

    // Ferme le fichier après la lecture complète
    fichier.close();
    return true; // Retourne true pour indiquer que la lecture a réussi
}

// === Fonction pour afficher un en-tête à l'écran ===
void header(const char *string, uint16_t color) {
    M5.Lcd.fillScreen(color);                     // Remplit l'écran avec la couleur donnée
    M5.Lcd.setTextSize(1);                        // Définit la taille du texte
    M5.Lcd.setTextColor(TFT_WHITE, TFT_BLACK);    // Texte blanc sur fond noir
    M5.Lcd.fillRect(0, 0, 320, 30, TFT_BLACK);     // Bande noire en haut de l'écran
    M5.Lcd.setTextDatum(TC_DATUM);                // Centre le texte horizontalement
    M5.Lcd.drawString(string, 160, 3, 4);          // Affiche le texte centré
}

// === Fonction d'initialisation ===
void setup() {
    M5.begin();                                       // Initialise M5Stack (écran, boutons, etc.)
    M5.Lcd.println("Connexion au Wi-Fi...");          // Message sur l'écran

    if (!lireWiFiDepuisSD(ssid, password)) {          // Lecture des identifiants depuis la carte SD
        M5.Lcd.println("Erreur lecture WiFi sur SD");
        while (1);  // Stoppe l'exécution si erreur
    }

    WiFi.begin(ssid.c_str(), password.c_str());       // Connexion au Wi-Fi

    while (WiFi.status() != WL_CONNECTED) {           // Attente jusqu'à connexion
        delay(1000);
        M5.Lcd.print(".");
    }

    M5.Lcd.println("\nConnecté au Wi-Fi");            // Affiche confirmation de connexion

    Serial.begin(115200);                             // Initialise la communication série
    Serial2.begin(9600);                              // Initialise le port série pour le PMS5003

    if (!sht31.begin(0x44)) {                         // Initialise le capteur SHT31
        M5.Lcd.println("Erreur SHT31!");
        while(1);  // Stoppe l'exécution si erreur
    }

    header("M5Stack PM2.5", TFT_BLACK);               // Affiche un titre sur l'écran
}

// === Fonction pour envoyer les données au serveur HTTP ===
void envoyerDonnees(float temp, float hum, float pm1, float pm25, float pm10) {
    if(WiFi.status() != WL_CONNECTED) return;         // Ne rien faire si déconnecté

    HTTPClient http;                                  // Création d'un client HTTP

    // Construction de la requête GET avec les données
    String requete = String(serverUrl) + 
        "?temperature=" + String(temp) +
        "&humidite=" + String(hum) +
        "&particule_1_0=" + String(pm1) +
        "&particule_2_5=" + String(pm25) +
        "&particule_10_0=" + String(pm10) +
        "&id_capteur=" + id_capteur +
        "&nom=" + nom_capteur;

    requete.replace(" ", "%20");                      // Encodage des espaces

    http.begin(requete);                              // Prépare la requête HTTP
    int codeReponse = http.GET();                     // Envoie la requête GET

    Serial.println("URL: " + requete);                // Affiche l'URL pour debug
    Serial.println("Code réponse: " + String(codeReponse));

    if(codeReponse == HTTP_CODE_OK) {                 // Si tout va bien (200 OK)
        String reponse = http.getString();            // Lecture de la réponse
        Serial.println("Réponse: " + reponse);
    }

    http.end();                                       // Ferme la connexion HTTP
}

// === Boucle principale ===
void loop() {
    float t = sht31.readTemperature() - 2;        // Lecture température corrigée (-2°C)
    float h = sht31.readHumidity() - 10;          // Lecture humidité corrigée (-10%)

    M5.Lcd.fillScreen(BLACK);                     // Nettoie l'écran
    M5.Lcd.setTextSize(2);                        // Définir taille du texte

    // Affiche la température sur l'écran
    M5.Lcd.setCursor(0, 0);                            // Positionne le curseur en haut à gauche
    M5.Lcd.setTextColor(TFT_YELLOW, TFT_BLACK);        // Texte jaune sur fond noir
    M5.Lcd.printf("Temperature:");                     // Affiche le texte "Temperature:"
    M5.Lcd.setCursor(210, 0);                          // Positionne le curseur à droite de la ligne
    M5.Lcd.printf("%.2f C", t);                        // Affiche la température mesurée avec 2 décimales

    // Affiche l'humidité relative
    M5.Lcd.setCursor(0, 50);                           // Positionne le curseur plus bas
    M5.Lcd.setTextColor(TFT_WHITE, TFT_BLACK);         // Texte blanc sur fond noir
    M5.Lcd.printf("Humidite:");                        // Affiche le texte "Humidite:"
    M5.Lcd.setCursor(210, 50);                         // Positionne le curseur à droite
    M5.Lcd.printf("%.2f %%", h);                       // Affiche l'humidité mesurée avec 2 décimales

    // Préparation pour afficher la qualité de l'air
    M5.Lcd.setTextColor(TFT_BLUE, TFT_BLACK);          // Texte bleu pour titre de la section
    M5.Lcd.setCursor(0, 100);                          // Positionne le curseur plus bas
    M5.Lcd.printf("Qualite de l'air:");                // Affiche "Qualite de l'air:"

    // Affiche la concentration de particules PM1.0
    M5.Lcd.setCursor(0, 120);                          // Positionne le curseur ligne suivante
    M5.Lcd.setTextColor(TFT_GREEN, TFT_BLACK);         // Texte vert
    M5.Lcd.printf("PM1.0:");                           // Affiche "PM1.0:"
    M5.Lcd.setCursor(210, 120);                        // Curseur à droite
    M5.Lcd.printf("%d ug/m3", data.PM_AE_UG_1_0);      // Affiche la valeur PM1.0 en µg/m³

    // Affiche la concentration de particules PM2.5
    M5.Lcd.setCursor(0, 140);                          // Positionne le curseur ligne suivante
    M5.Lcd.setTextColor(TFT_OLIVE, TFT_BLACK);         // Texte olive
    M5.Lcd.printf("PM2.5:");                           // Affiche "PM2.5:"
    M5.Lcd.setCursor(210, 140);                        // Curseur à droite
    M5.Lcd.printf("%d ug/m3", data.PM_AE_UG_2_5);      // Affiche la valeur PM2.5 en µg/m³

    // Affiche la concentration de particules PM10
    M5.Lcd.setCursor(0, 160);                          // Positionne le curseur ligne suivante
    M5.Lcd.setTextColor(TFT_MAROON, TFT_BLACK);        // Texte marron
    M5.Lcd.printf("PM10:");                            // Affiche "PM10:"
    M5.Lcd.setCursor(210, 160);                        // Curseur à droite
    M5.Lcd.printf("%d ug/m3", data.PM_AE_UG_10_0);     // Affiche la valeur PM10 en µg/m³

    // Envoi des données au serveur HTTP via une requête GET
    envoyerDonnees(
        t,                                 // Température
        h,                                 // Humidité
        data.PM_AE_UG_1_0,                 // Particules PM1.0
        data.PM_AE_UG_2_5,                 // Particules PM2.5
        data.PM_AE_UG_10_0                 // Particules PM10
    );


    delay(1000);                 // Petite pause

    pms.wakeUp();                // Réveille le capteur PMS
    M5.Lcd.fillScreen(BLACK);    // Efface l’écran
    delay(1200000);              // Pause de 20 minutes
    pms.readUntil(data);         // Lit les nouvelles données de particules
    pms.sleep();                 // Remet le capteur PMS en veille
}
