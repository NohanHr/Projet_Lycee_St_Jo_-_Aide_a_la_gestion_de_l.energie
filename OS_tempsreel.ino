#include <M5StickCPlus.h>
#include "M5UnitENV.h"
#include <freertos/FreeRTOS.h>
#include <freertos/task.h>
#include <freertos/semphr.h>

// Déclarations des capteurs
SHT3X sht3x;
QMP6988 qmp;

// Adresses I2C
#define MLX90614_ADDR 0x5A
#define QMP6988_I2C_ADDR 0x70
#define SHT30_I2C_ADDR 0x44

// Variables partagées (protégées par sémaphore)
SemaphoreHandle_t xMutex;
float ncirTemp = 0.0;
float ambientTemp = 0.0;
float humidity = 0.0;
float pressure = 0.0;
float altitude = 0.0;

// Tâche 1 : Lecture NCIR (MLX90614)
void Task_NCIR(void *pvParameters) {
    while (1) {
        uint16_t result;
        Wire.beginTransmission(MLX90614_ADDR);
        Wire.write(0x07);
        Wire.endTransmission(false);
        Wire.requestFrom(MLX90614_ADDR, 2);
        result = Wire.read();
        result |= Wire.read() << 8;

        // Mise à jour de la variable partagée
        if (xSemaphoreTake(xMutex, portMAX_DELAY)) { 
            ncirTemp = result * 0.02 - 273.15;
            xSemaphoreGive(xMutex);
        }
        vTaskDelay(pdMS_TO_TICKS(500)); // Période : 500ms
    }
}

// Tâche 2 : Lecture ENV (SHT3X + QMP6988)
void Task_ENV(void *pvParameters) {
    while (1) {
        if (sht3x.update()) {
            if (xSemaphoreTake(xMutex, portMAX_DELAY)) {
                ambientTemp = sht3x.cTemp;
                humidity = sht3x.humidity;
                xSemaphoreGive(xMutex);
            }
        }

        if (qmp.update()) {
            if (xSemaphoreTake(xMutex, portMAX_DELAY)) {
                pressure = qmp.pressure;
                altitude = qmp.altitude;
                xSemaphoreGive(xMutex);
            }
        }
        vTaskDelay(pdMS_TO_TICKS(1000)); 
}


void Task_Display(void *pvParameters) {
    M5.Lcd.setTextSize(2);
    M5.Lcd.setRotation(1);

    while (1) {
        M5.Lcd.fillScreen(BLACK);

        
        float currentNCIR, currentAmbient, currentHumidity, currentPressure, currentAltitude;
        if (xSemaphoreTake(xMutex, portMAX_DELAY)) {
            currentNCIR = ncirTemp;
            currentAmbient = ambientTemp;
            currentHumidity = humidity;
            currentPressure = pressure;
            currentAltitude = altitude;
            xSemaphoreGive(xMutex);
        }

        
        M5.Lcd.setTextColor(TFT_RED);
        M5.Lcd.setCursor(0, 0);
        M5.Lcd.printf("NCIR: %.2f C\n", currentNCIR);

        M5.Lcd.setTextColor(TFT_GREEN);
        M5.Lcd.printf("Ambient: %.2f C\n", currentAmbient);
        M5.Lcd.printf("Humidity: %.2f %%\n", currentHumidity);

        M5.Lcd.setTextColor(TFT_BLUE);
        M5.Lcd.printf("Pressure: %.2f Pa\n", currentPressure);
        M5.Lcd.printf("Altitude: %.2f m\n", currentAltitude);

        vTaskDelay(pdMS_TO_TICKS(200)); 
    }
}

void setup() {
    M5.begin();
    Wire.begin();

    
    while (!qmp.begin(&Wire, QMP6988_I2C_ADDR, 0, 26, 100000U)) delay(500);
    while (!sht3x.begin(&Wire, SHT30_I2C_ADDR, 0, 26, 10000U)) delay(500);

    // Création du sémaphore
    xMutex = xSemaphoreCreateMutex();

    
    xTaskCreate(Task_NCIR, "NCIR", 2048, NULL, 3, NULL);       
    xTaskCreate(Task_ENV, "ENV", 2048, NULL, 2, NULL);         
    xTaskCreate(Task_Display, "Display", 2048, NULL, 1, NULL); 

    
    vTaskStartScheduler();
}

void loop() {} 
