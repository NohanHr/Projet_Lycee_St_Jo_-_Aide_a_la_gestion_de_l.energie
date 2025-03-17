#include <M5StickC.h>
#include "Adafruit_Sensor.h"
#include <Adafruit_SHT31.h>
#include <QMP6988.h>

Adafruit_SHT31 sht30 = Adafruit_SHT31();
QMP6988 qmp6988;

float temperature = 0.0;
float humidity = 0.0;
float pressure = 0.0;

void setup() {
  M5.begin();
  Wire.begin(0, 26);
  if (!sht30.begin(0x44)) {
    Serial.println("Couldn't find SHT31");
    while (1) delay(1);
  }
  qmp6988.begin();
  M5.Lcd.setRotation(3);
  M5.Lcd.fillScreen(BLACK);
  M5.Lcd.setTextSize(1); // Réduire la taille de la police
}

void loop() {
  temperature = sht30.readTemperature();
  humidity = sht30.readHumidity();
  pressure = qmp6988.calcPressure();

  // Efface l'écran avant d'afficher les nouvelles valeurs
  M5.Lcd.fillScreen(BLACK);

  // Positionne le curseur et affiche les valeurs
  M5.Lcd.setCursor(0, 0);
  M5.Lcd.print("Temp: " + String(temperature, 1) + " C");
  M5.Lcd.setCursor(0, 20);
  M5.Lcd.print("Humidity: " + String(humidity, 1) + " %");
  M5.Lcd.setCursor(0, 40);
  M5.Lcd.print("Pressure: " + String(pressure / 100.0, 2) + " hPa");

  delay(1000);
}