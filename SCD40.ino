#include <Wire.h>
#include <M5StickCPlus.h>
#include <SensirionI2cScd4x.h>

SensirionI2cScd4x scd40;

void setup() {
  M5.begin();
  Wire.begin();
  M5.Lcd.setRotation(3);
  M5.Lcd.fillScreen(BLACK);
  M5.Lcd.setTextColor(WHITE);
  M5.Lcd.setTextSize(2);
  
  scd40.reinit();
  uint16_t error = scd40.startPeriodicMeasurement();
  if (error) {
    M5.Lcd.println("Erreur de démarrage de la mesure");
    while (1);
  }
  M5.Lcd.println("SCD40 détecté");
}

void loop() {
  uint16_t co2;
  float temperature, humidity;
  uint16_t error = scd40.readMeasurement(co2, temperature, humidity);

  if (error == 0) {
    M5.Lcd.fillScreen(BLACK);
    M5.Lcd.setCursor(0, 0);
    M5.Lcd.printf("CO2: %d ppm\n", co2);
    M5.Lcd.printf("Temp: %.2f C\n", temperature);
    M5.Lcd.printf("Hum: %.2f %%\n", humidity);
  } else {
    M5.Lcd.println("Erreur de lecture");
  }

  delay(2000); 
}