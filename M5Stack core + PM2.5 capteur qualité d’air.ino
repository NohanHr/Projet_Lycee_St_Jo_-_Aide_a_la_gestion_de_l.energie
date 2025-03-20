#include <WiFi.h>
#include <M5Stack.h>
#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_SHT31.h>
#include <PMS.h>
#include <HTTPClient.h>

Adafruit_SHT31 sht31 = Adafruit_SHT31();
PMS pms(Serial2);
PMS::DATA data;

// Configuration réseau
const char* ssid = "Energie";
const char* password = "gestionenergie";
const char* serverUrl = "http://10.0.200.5/insert.php";
const String id_capteur = "M5Stack-01";
const String nom_capteur = "Capteur_Salle_1";

void header(const char *string, uint16_t color) {
    M5.Lcd.fillScreen(color);
    M5.Lcd.setTextSize(1);
    M5.Lcd.setTextColor(TFT_WHITE, TFT_BLACK);
    M5.Lcd.fillRect(0, 0, 320, 30, TFT_BLACK);
    M5.Lcd.setTextDatum(TC_DATUM);
    M5.Lcd.drawString(string, 160, 3, 4);
}

void setup() {
    M5.begin();
    M5.Lcd.println("Connexion au Wi-Fi...");
    WiFi.begin(ssid, password);

    while (WiFi.status() != WL_CONNECTED) {
        delay(1000);
        M5.Lcd.print(".");
    }
    M5.Lcd.println("\nConnecté au Wi-Fi");

    Serial.begin(115200);
    Serial2.begin(9600);

    if (!sht31.begin(0x44)) {
        M5.Lcd.println("Erreur SHT31!");
        while(1);
    }

    header("M5Stack PM2.5", TFT_BLACK);
}

void envoyerDonnees(float temp, float hum, float pm1, float pm25, float pm10) {
    if(WiFi.status() != WL_CONNECTED) return;

    HTTPClient http;
    
    // Encodage URL des paramètres
    String requete = String(serverUrl) + 
        "?temperature=" + String(temp) +
        "&humidite=" + String(hum) +
        "&particule_1_0=" + String(pm1) +  // Remplacement du point par _
        "&particule_2_5=" + String(pm25) +
        "&particule_10_0=" + String(pm10) +
        "&id_capteur=" + id_capteur +
        "&nom=" + nom_capteur;

    requete.replace(" ", "%20"); // Encodage des espaces
    
    http.begin(requete);
    int codeReponse = http.GET();
    
    // Debug série
    Serial.println("URL: " + requete);
    Serial.println("Code réponse: " + String(codeReponse));
    
    if(codeReponse == HTTP_CODE_OK) {
        String reponse = http.getString();
        Serial.println("Réponse: " + reponse);
    }
    
    http.end();
}

void loop() {
    // Affichage original inchangé
    M5.Lcd.fillScreen(BLACK);
    M5.Lcd.setTextSize(2);

    float t = sht31.readTemperature();
    float h = sht31.readHumidity();

    M5.Lcd.setCursor(0, 0);
    M5.Lcd.setTextColor(TFT_YELLOW, TFT_BLACK);
    M5.Lcd.printf("Temperature:");
    M5.Lcd.setCursor(210, 0);
    M5.Lcd.printf("%.2f C", t);

    M5.Lcd.setCursor(0, 50);
    M5.Lcd.setTextColor(TFT_WHITE, TFT_BLACK);
    M5.Lcd.printf("Humidite:");
    M5.Lcd.setCursor(210, 50);
    M5.Lcd.printf("%.2f %%", h);

    M5.Lcd.setTextColor(TFT_BLUE, TFT_BLACK);
    M5.Lcd.setCursor(0, 100);
    M5.Lcd.printf("Qualite de l'air:");

    M5.Lcd.setCursor(0, 120);
    M5.Lcd.setTextColor(TFT_GREEN, TFT_BLACK);
    M5.Lcd.printf("PM1.0:");
    M5.Lcd.setCursor(210, 120);
    M5.Lcd.printf("%d ug/m3", data.PM_AE_UG_1_0);

    M5.Lcd.setCursor(0, 140);
    M5.Lcd.setTextColor(TFT_OLIVE, TFT_BLACK);
    M5.Lcd.printf("PM2.5:");
    M5.Lcd.setCursor(210, 140);
    M5.Lcd.printf("%d ug/m3", data.PM_AE_UG_2_5);

    M5.Lcd.setCursor(0, 160);
    M5.Lcd.setTextColor(TFT_MAROON, TFT_BLACK);
    M5.Lcd.printf("PM10:");
    M5.Lcd.setCursor(210, 160);
    M5.Lcd.printf("%d ug/m3", data.PM_AE_UG_10_0);

    // Envoi des données après l'affichage
    envoyerDonnees(t, h, data.PM_AE_UG_1_0, data.PM_AE_UG_2_5, data.PM_AE_UG_10_0);

    delay(1000);
    pms.wakeUp();
    delay(30000);
    pms.readUntil(data);
    pms.sleep();


}
