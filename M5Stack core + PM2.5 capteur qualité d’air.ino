#include <Wire.h>                // Bibliothèque pour la communication I2C
#include <Adafruit_Sensor.h>     // Bibliothèque de base pour les capteurs Adafruit
#include <Adafruit_SHT31.h>      // Bibliothèque pour le capteur de température et d'humidité SHT31
#include <M5Stack.h>             // Bibliothèque pour le contrôle du M5Stack
#include <PMS.h>                 // Bibliothèque pour le capteur de particules fines PMSA003

// Initialisation des capteurs
Adafruit_SHT31 sht31 = Adafruit_SHT31();  // Création de l'objet pour le capteur SHT31
PMS pms(Serial2);                         // Création de l'objet pour le capteur PMSA003, utilisant le port série 2
PMS::DATA data;                           // Structure pour stocker les données lues par le capteur PMSA003

void setup() {
  M5.begin();                             // Initialisation du M5Stack
  Serial.begin(115200);                   // Initialisation de la communication série pour le moniteur série
  Serial2.begin(9600);                    // Initialisation de la communication série pour le capteur PMSA003 (9600 baud rate)

  // Initialisation du capteur SHT31
  if (!sht31.begin(0x44)) {               // Tente d'initialiser le capteur SHT31 à l'adresse I2C 0x44
    Serial.println("Impossible de trouver le capteur SHT31");  // Affiche un message d'erreur si l'initialisation échoue
    while (1) delay(1);                   // Boucle infinie pour arrêter le programme
  }

  // Initialisation de l'écran
  M5.Lcd.setTextSize(2);                  // Définit la taille du texte sur l'écran
  M5.Lcd.setCursor(0, 0);                 // Positionne le curseur en haut à gauche de l'écran
  M5.Lcd.println("PM2.5 Air Quality Kit");// Affiche le titre sur l'écran

  M5.Lcd.fillScreen(TFT_BLACK);           // Remplit l'écran avec la couleur noire
  header("M5Stack PM2.5 Monitor", TFT_BLACK); // Affiche un en-tête sur l'écran
}

void loop() {
  // Lecture des données du capteur SHT31
  float t = sht31.readTemperature();      // Lit la température du capteur SHT31
  float h = sht31.readHumidity();         // Lit l'humidité du capteur SHT31

  // Lecture des données du capteur PMSA003
  pms.wakeUp();                           // Réveille le capteur PMSA003
  delay(30000);                           // Attendre 30 secondes pour stabiliser les lectures
  pms.readUntil(data);                    // Lit les données du capteur PMSA003 et les stocke dans la structure 'data'
  pms.sleep();                            // Met le capteur PMSA003 en veille

  // Affichage des données sur l'écran
  M5.Lcd.fillScreen(BLACK);               // Remplit l'écran avec la couleur noire
  M5.Lcd.setCursor(0, 0);                 // Positionne le curseur en haut à gauche de l'écran
  M5.Lcd.printf("Temperature: %.2f C\n", t); // Affiche la température
  M5.Lcd.printf("Humidite: %.2f %%\n", h);   // Affiche l'humidité
  M5.Lcd.printf("PM1.0: %d ug/m3\n", data.PM_AE_UG_1_0); // Affiche la concentration de PM1.0
  M5.Lcd.printf("PM2.5: %d ug/m3\n", data.PM_AE_UG_2_5); // Affiche la concentration de PM2.5
  M5.Lcd.printf("PM10: %d ug/m3\n", data.PM_AE_UG_10_0); // Affiche la concentration de PM10

  delay(1000);                            // Attendre 1 seconde avant la prochaine lecture
}
