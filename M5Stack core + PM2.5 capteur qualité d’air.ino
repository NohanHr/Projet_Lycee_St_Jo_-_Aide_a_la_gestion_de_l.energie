#include <Wire.h> // Inclut la bibliothèque Wire pour la communication I2C
#include <Adafruit_Sensor.h> // Inclut la bibliothèque Adafruit Sensor pour les capteurs
#include <Adafruit_SHT31.h> // Inclut la bibliothèque Adafruit pour le capteur SHT31
#include <M5Stack.h> // Inclut la bibliothèque M5Stack pour utiliser les fonctionnalités de la carte M5Stack
#include <PMS.h> // Inclut la bibliothèque PMS pour le capteur de particules PMSA003

// Initialisation des capteurs
Adafruit_SHT31 sht31 = Adafruit_SHT31(); // Crée une instance du capteur SHT31
PMS pms(Serial2); // Crée une instance du capteur PMSA003 en utilisant le port série 2
PMS::DATA data; // Crée une structure pour stocker les données du capteur PMSA003

void header(const char *string, uint16_t color) {
    M5.Lcd.fillScreen(color); // Remplit l'écran avec la couleur spécifiée
    M5.Lcd.setTextSize(1); // Définit la taille du texte à 1
    M5.Lcd.setTextColor(TFT_WHITE, TFT_BLACK); // Définit la couleur du texte en blanc et le fond en noir
    M5.Lcd.fillRect(0, 0, 320, 30, TFT_BLACK); // Remplit un rectangle en haut de l'écran avec du noir
    M5.Lcd.setTextDatum(TC_DATUM); // Définit l'alignement du texte au centre
    M5.Lcd.drawString(string, 160, 3, 4); // Affiche la chaîne de caractères au centre de l'écran
}

void setup() {
  M5.begin(); // Initialise la carte M5Stack
  Serial.begin(115200); // Initialise la communication série à 115200 bauds
  Serial2.begin(9600); // Initialise la communication série 2 à 9600 bauds pour le capteur PMSA003

  // Initialisation du capteur SHT31
  if (!sht31.begin(0x44)) { // Tente de démarrer le capteur SHT31 à l'adresse I2C 0x44
    Serial.println("Impossible de trouver le capteur SHT31"); // Affiche un message d'erreur si le capteur n'est pas trouvé
    while (1) delay(1); // Boucle infinie pour arrêter le programme
  }

  // Initialisation de l'écran
  M5.Lcd.setTextSize(2); // Définit la taille du texte à 2
  M5.Lcd.setCursor(0, 0); // Positionne le curseur en haut à gauche de l'écran
  M5.Lcd.println("PM2.5 Air Quality Kit"); // Affiche le titre sur l'écran

  M5.Lcd.fillScreen(TFT_BLACK); // Remplit l'écran avec du noir
  header("M5Stack PM2.5", TFT_BLACK); // Affiche l'en-tête avec le titre "M5Stack PM2.5"
}

void loop() {
  // Lecture des données du capteur SHT31
  float t = sht31.readTemperature(); // Lit la température du capteur SHT31
  float h = sht31.readHumidity(); // Lit l'humidité du capteur SHT31

  // Lecture des données du capteur PMSA003
  pms.wakeUp(); // Réveille le capteur PMSA003
  delay(30000); // Attendre 30 secondes pour stabiliser les lectures
  pms.readUntil(data); // Lit les données du capteur PMSA003 jusqu'à ce qu'elles soient disponibles
  pms.sleep(); // Met le capteur PMSA003 en veille

  // Affichage des données sur l'écran
  M5.Lcd.fillScreen(BLACK); // Remplit l'écran avec du noir
  M5.Lcd.setTextSize(2); // Définit la taille du texte à 2

  M5.Lcd.setCursor(0, 0); // Positionne le curseur en haut à gauche de l'écran
  M5.Lcd.setTextColor(TFT_YELLOW, TFT_BLACK); // Définit la couleur du texte en jaune et le fond en noir
  M5.Lcd.printf("Temperature:"); // Affiche "Temperature:"
  M5.Lcd.setCursor(210, 0); // Positionne le curseur à la colonne 210, ligne 0
  M5.Lcd.printf("%.2f C", t); // Affiche la température en degrés Celsius

  M5.Lcd.setCursor(0, 50); // Positionne le curseur à la colonne 0, ligne 50
  M5.Lcd.setTextColor(TFT_WHITE, TFT_BLACK); // Définit la couleur du texte en blanc et le fond en noir
  M5.Lcd.printf("Humidite:"); // Affiche "Humidite:"
  M5.Lcd.setCursor(210, 50); // Positionne le curseur à la colonne 210, ligne 50
  M5.Lcd.printf("%.2f %%", h); // Affiche l'humidité en pourcentage

  M5.Lcd.setTextColor(TFT_BLUE, TFT_BLACK); // Définit la couleur du texte en bleu et le fond en noir
  M5.Lcd.setCursor(0, 100); // Positionne le curseur à la colonne 0, ligne 100
  M5.Lcd.printf("Qualite de l'air:"); // Affiche "Qualite de l'air:"

  M5.Lcd.setCursor(0, 120); // Positionne le curseur à la colonne 0, ligne 120
  M5.Lcd.setTextColor(TFT_GREEN, TFT_BLACK); // Définit la couleur du texte en vert et le fond en noir
  M5.Lcd.printf("PM1.0:"); // Affiche "PM1.0:"
  M5.Lcd.setCursor(210, 120); // Positionne le curseur à la colonne 210, ligne 120
  M5.Lcd.printf("%d ug/m3", data.PM_AE_UG_1_0); // Affiche la concentration de PM1.0 en microgrammes par mètre cube

  M5.Lcd.setCursor(0, 140); // Positionne le curseur à la colonne 0, ligne 140
  M5.Lcd.setTextColor(TFT_OLIVE, TFT_BLACK); // Définit la couleur du texte en olive et le fond en noir
  M5.Lcd.printf("PM2.5:"); // Affiche "PM2.5:"
  M5.Lcd.setCursor(210, 140); // Positionne le curseur à la colonne 210, ligne 140
  M5.Lcd.printf("%d ug/m3", data.PM_AE_UG_2_5); // Affiche la concentration de PM2.5 en microgrammes par mètre cube

  M5.Lcd.setCursor(0, 160); // Positionne le curseur à la colonne 0, ligne 160
  M5.Lcd.setTextColor(TFT_MAROON, TFT_BLACK); // Définit la couleur du texte en marron et le fond en noir
  M5.Lcd.printf("PM10:"); // Affiche "PM10:"
  M5.Lcd.setCursor(210, 160); // Positionne le curseur à la colonne 210, ligne 160
  M5.Lcd.printf("%d ug/m3", data.PM_AE_UG_10_0); // Affiche la concentration de PM10 en microgrammes par mètre cube

  delay(1000); // Attendre 1 seconde avant la prochaine lecture
}
