#include <M5StickCPlus.h>

void setup() {
    M5.begin();
    Wire.begin();
    M5.Lcd.setTextSize(2);
    M5.Lcd.setCursor(120, 0);
   
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
}