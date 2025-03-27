#include <M5StickCPlus.h>

const int soundSensorPin = 36; // Pin du capteur sonore U055
const int buzzerPin = 26; // Pin du buzzer

void setup() {
    M5.begin();
    Wire.begin();
    M5.Lcd.setTextSize(2);
    M5.Lcd.setCursor(120, 0);
   M5.begin();
  M5.Lcd.setRotation(3);
  M5.Lcd.setCursor(0, 30, 4);
  M5.Lcd.println("Détection de bruit");
  pinMode(soundSensorPin, INPUT);
  pinMode(buzzerPin, OUTPUT);
}

void loop() {
    static uint16_t result;
    static float temperature;
    Wire.beginTransmission(0x5A);  
    Wire.write(0x07);  
    Wire.endTransmission(false);  
    Wire.requestFrom(
        0x5A,
        2);  
    result = Wire.read();        
    result |= Wire.read() << 8;  

    temperature = result * 0.02 - 273.15;
    M5.Lcd.setTextColor(TFT_RED, TFT_BLACK);
    M5.Lcd.setTextSize(2);
    M5.Lcd.setCursor(0, 0);
    M5.Lcd.print("Temperature:\n");
    M5.Lcd.printf("%.3f C", temperature);
    delay(500);
  int soundLevel = analogRead(soundSensorPin); // Lire le niveau sonore
  M5.Lcd.fillScreen(BLACK);
  M5.Lcd.setCursor(0, 30, 4);
  M5.Lcd.printf("Niveau sonore: %d", soundLevel);

  if (soundLevel > 500) { // Seuil de détection de bruit
    tone(buzzerPin, 650); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 730); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 820); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 650); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 1310); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 730); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 820); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 650); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 820); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 870); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 920); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 820); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 870); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 98); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 980); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 1100); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 980); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 65); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 73); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 82); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 65); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 131); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 73); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 82); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 65); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 82); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 87); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 92); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 82); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 87); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 98); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 98); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 110); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 98); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 0); // Émettre un son à 1000 Hz
    delay(10000);
    tone(buzzerPin, 650); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 730); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 820); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 650); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 1310); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 730); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 820); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 650); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 820); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 870); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 920); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 820); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 870); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 98); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 980); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 1100); // Émettre un son à 1000 Hz
    delay(1000);
    tone(buzzerPin, 980); // Émettre un son à 1000 Hz
    delay(1000);
  } else {
    noTone(buzzerPin); // Arrêter le son
  }
delay(500); // Attendre 500 ms avant la prochaine lecture
}
