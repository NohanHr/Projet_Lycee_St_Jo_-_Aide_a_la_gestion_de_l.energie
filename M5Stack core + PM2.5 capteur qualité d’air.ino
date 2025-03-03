#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_SHT31.h>
#include <M5Stack.h>
#include <PMS.h>

// Initialisation des capteurs
Adafruit_SHT31 sht31 = Adafruit_SHT31();
PMS pms(Serial2);
PMS::DATA data;

void setup() {
  M5.begin();
  Serial.begin(115200);
  Serial2.begin(9600); // PMSA003 utilise 9600 baud rate

  // Initialisation du capteur SHT31
  if (!sht31.begin(0x44)) {
    Serial.println("Impossible de trouver le capteur SHT31");
    while (1) delay(1);
  }

  // Initialisation de l'écran
  M5.Lcd.setTextSize(2);
  M5.Lcd.setCursor(0, 0);
  M5.Lcd.println("PM2.5 Air Quality Kit");

  M5.Lcd.fillScreen(TFT_BLACK);
  header("M5Stack PM2.5 Monitor", TFT_BLACK);

}



void loop() {
  // Lecture des données du capteur SHT31
  float t = sht31.readTemperature();
  float h = sht31.readHumidity();

  // Lecture des données du capteur PMSA003
  pms.wakeUp();
  delay(30000); // Attendre 30 secondes pour stabiliser les lectures
  pms.readUntil(data);
  pms.sleep();

  // Affichage des données sur l'écran
  M5.Lcd.fillScreen(BLACK);
  M5.Lcd.setCursor(0, 0);
  M5.Lcd.printf("Temperature: %.2f C\n", t);
  M5.Lcd.printf("Humidite: %.2f %%\n", h);
  M5.Lcd.printf("PM1.0: %d ug/m3\n", data.PM_AE_UG_1_0);
  M5.Lcd.printf("PM2.5: %d ug/m3\n", data.PM_AE_UG_2_5);
  M5.Lcd.printf("PM10: %d ug/m3\n", data.PM_AE_UG_10_0);

  delay(1000); // Attendre 1 secondes avant la prochaine lecture
}
