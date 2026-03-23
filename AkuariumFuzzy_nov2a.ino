#include <OneWire.h>
#include <DallasTemperature.h>
#include <WiFi.h>
#include <HTTPClient.h> // *** TAMBAHAN UTAMA UNTUK MENGIRIM DATA WEB ***
// =============================================
// KONFIGURASI JARINGAN & SERVER
// =============================================
const char* ssid = "ipuliot";          // Ganti dengan SSID WiFi Anda
const char* password = "pisanGG158"; // Ganti dengan Password WiFi Anda

// Ganti dengan IP atau Domain Server Anda (Contoh: IP Lokal PC Anda)
// Pastikan folder 'aquarium-monitoring' sudah ada di web server
const char* serverName = "http://saifullah.rafkycreative.site/post-data.php"; 

// =============================================
// KONFIGURASI PIN & SENSOR
// =============================================
// LED Configuration - Traffic Light
#define LED_MERAH 12    // GPIO12 - Kondisi BURUK
#define LED_KUNING 13   // GPIO13 - Kondisi NORMAL  
#define LED_HIJAU 14    // GPIO14 - Kondisi BAIK

// Sensor Configuration
const int oneWireBus = 25;
#define TdsSensorPin 35
#define VREF 3.3
#define SCOUNT 30

// Global Variables
float tdsValue = 0;
float temperature = 0;
float kualitasAir = 0; // Output dari Fuzzy Mamdani (0-100)

// Instance Sensor
OneWire oneWire(oneWireBus);
DallasTemperature sensors(&oneWire);

// Buffer untuk perhitungan Median TDS
int analogBuffer[SCOUNT];
int analogBufferIndex = 0;

// =============================================
// FUZZY MEMBERSHIP FUNCTIONS (INPUT & OUTPUT)
// =============================================

// TDS untuk akuarium AIR TAWAR
float fuzzyTDS_Rendah(float x) {
  if (x <= 100) return 1.0;
  else if (x >= 200) return 0.0;
  else return (200 - x) / (200 - 100);
}

float fuzzyTDS_Sedang(float x) {
  if (x <= 150 || x >= 400) return 0.0;
  else if (x > 150 && x <= 275) return (x - 150) / (275 - 150);
  else return (400 - x) / (400 - 275);
}

float fuzzyTDS_Tinggi(float x) {
  if (x <= 350) return 0.0;
  else if (x >= 450) return 1.0;
  else return (x - 350) / (450 - 350);
}

// Suhu untuk ikan TROPIS
float fuzzySuhu_Dingin(float x) {
  if (x <= 22) return 1.0;
  else if (x >= 26) return 0.0;
  else return (26 - x) / (26 - 22);
}

float fuzzySuhu_Normal(float x) {
  if (x <= 24 || x >= 30) return 0.0;
  else if (x > 24 && x <= 27) return (x - 24) / (27 - 24);
  else return (30 - x) / (30 - 27);
}

float fuzzySuhu_Panas(float x) {
  if (x <= 28) return 0.0;
  else if (x >= 32) return 1.0;
  else return (x - 28) / (32 - 28);
}

// Output 5 Linguistik (0-100)
float fuzzyOutput_SangatBuruk(float x) {
  if (x <= 15) return 1.0;
  else if (x >= 30) return 0.0;
  else return (30 - x) / (30 - 15);
}

float fuzzyOutput_Buruk(float x) {
  if (x <= 20 || x >= 50) return 0.0;
  else if (x > 20 && x <= 35) return (x - 20) / (35 - 20);
  else return (50 - x) / (50 - 35);
}

float fuzzyOutput_Normal(float x) {
  if (x <= 40 || x >= 70) return 0.0;
  else if (x > 40 && x <= 55) return (x - 40) / (55 - 40);
  else return (70 - x) / (70 - 55);
}

float fuzzyOutput_Baik(float x) {
  if (x <= 60 || x >= 90) return 0.0;
  else if (x > 60 && x <= 75) return (x - 60) / (75 - 60);
  else return (90 - x) / (90 - 75);
}

float fuzzyOutput_SangatBaik(float x) {
  if (x <= 80) return 0.0;
  else if (x >= 95) return 1.0;
  else return (x - 80) / (95 - 80);
}

// =============================================
// FUNGSI UTAMA
// =============================================

void setup() {
  Serial.begin(115200);
  
  // Initialize LED Pins
  pinMode(LED_MERAH, OUTPUT);
  pinMode(LED_KUNING, OUTPUT);
  pinMode(LED_HIJAU, OUTPUT);
  
  matikanSemuaLED();
  
  // Sensor Setup
  pinMode(TdsSensorPin, INPUT);
  sensors.begin();
  
  // Connect to WiFi
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("\nConnected to WiFi!");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());
  
  Serial.println("\n=== SISTEM MONITORING AKUARIUM AIR TAWAR ===");
}

void loop() {
  // 1. Baca Sensor
  sensors.requestTemperatures();
  temperature = sensors.getTempCByIndex(0);
  bacaTDS();
  
  // 2. Fuzzy Logic Processing
  kualitasAir = fuzzyMamdani(tdsValue, temperature);
  
  // 3. Kontrol Output (LED)
  kontrolLED(kualitasAir);
  
  // 4. Tampilkan Data di Serial Monitor
  tampilkanData();
  
  // 5. Kirim Data ke Web Server
  kirimDataWeb(tdsValue, temperature, kualitasAir); 

  delay(5000); // Delay 5 detik
}

// =============================================
// FUNGSI PENGIRIMAN DATA KE WEB SERVER
// =============================================

void kirimDataWeb(float tds, float suhu, float kualitas) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverName); // URL ke post-data.php
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    // Format data yang akan dikirim (key=value&key2=value2)
    String httpRequestData = "tds=" + String(tds, 1) + 
                             "&suhu=" + String(suhu, 1) + 
                             "&kualitas=" + String(kualitas, 1);
    
    Serial.print("\n[HTTP POST] Mengirim data: ");
    Serial.println(httpRequestData);

    int httpResponseCode = http.POST(httpRequestData);

    if (httpResponseCode > 0) {
      Serial.print("✅ HTTP Success: ");
      Serial.print(httpResponseCode);
      Serial.print(" -> ");
      Serial.println(http.getString());
    } else {
      Serial.print("❌ HTTP Error code: ");
      Serial.println(httpResponseCode);
    }
    http.end();
  } else {
    Serial.println("❌ WiFi Disconnected. Tidak dapat mengirim data.");
  }
}

// =============================================
// FUNGSI LAINNYA
// =============================================

void kontrolLED(float kualitas) {
  matikanSemuaLED();
  
  if (kualitas >= 60) {
    digitalWrite(LED_HIJAU, HIGH);
    // Serial.println("💡 LED HIJAU - Kondisi BAIK");
  } else if (kualitas >= 40) {
    digitalWrite(LED_KUNING, HIGH);
    // Serial.println("💡 LED KUNING - Kondisi NORMAL");
  } else {
    digitalWrite(LED_MERAH, HIGH);
    // Serial.println("💡 LED MERAH - Kondisi BURUK");
  }
}

void matikanSemuaLED() {
  digitalWrite(LED_MERAH, LOW);
  digitalWrite(LED_KUNING, LOW);
  digitalWrite(LED_HIJAU, LOW);
}

void bacaTDS() {

  const int sampleCount = 30;
  float totalVoltage = 0;

  for (int i = 0; i < sampleCount; i++) {

    int adcValue = analogRead(TdsSensorPin);

    float voltage = adcValue * VREF / 4095.0;

    totalVoltage += voltage;

    delay(40);
  }

  float avgVoltage = totalVoltage / sampleCount;

  // Temperature compensation
  float compensationCoefficient = 1.0 + 0.02 * (temperature - 25.0);

  float compensationVoltage = avgVoltage / compensationCoefficient;

  // Polynomial conversion (standar DFRobot)
  float rawTds = (133.42 * pow(compensationVoltage, 3)
                - 255.86 * pow(compensationVoltage, 2)
                + 857.39 * compensationVoltage);

  // Faktor kalibrasi
  tdsValue = rawTds * 0.68;

  if (tdsValue < 0) tdsValue = 0;
}

float fuzzyMamdani(float tds, float suhu) {
  // Fuzzifikasi input
  float tds_rendah = fuzzyTDS_Rendah(tds);
  float tds_sedang = fuzzyTDS_Sedang(tds);
  float tds_tinggi = fuzzyTDS_Tinggi(tds);
  
  float suhu_dingin = fuzzySuhu_Dingin(suhu);
  float suhu_normal = fuzzySuhu_Normal(suhu);
  float suhu_panas = fuzzySuhu_Panas(suhu);
  
  // Rule Base untuk 5 linguistik output (9 Rules)
  float rule[9];
  
  // Rule 1: If TDS=Rendah AND Suhu=Dingin THEN Kualitas=Normal
  rule[0] = min(tds_rendah, suhu_dingin);
  
  // Rule 2: If TDS=Rendah AND Suhu=Normal THEN Kualitas=Baik
  rule[1] = min(tds_rendah, suhu_normal);
  
  // Rule 3: If TDS=Rendah AND Suhu=Panas THEN Kualitas=Normal
  rule[2] = min(tds_rendah, suhu_panas);
  
  // Rule 4: If TDS=Sedang AND Suhu=Dingin THEN Kualitas=Normal
  rule[3] = min(tds_sedang, suhu_dingin);
  
  // Rule 5: If TDS=Sedang AND Suhu=Normal THEN Kualitas=SangatBaik
  rule[4] = min(tds_sedang, suhu_normal);
  
  // Rule 6: If TDS=Sedang AND Suhu=Panas THEN Kualitas=Buruk
  rule[5] = min(tds_sedang, suhu_panas);
  
  // Rule 7: If TDS=Tinggi AND Suhu=Dingin THEN Kualitas=SangatBuruk
  rule[6] = min(tds_tinggi, suhu_dingin);
  
  // Rule 8: If TDS=Tinggi AND Suhu=Normal THEN Kualitas=Buruk
  rule[7] = min(tds_tinggi, suhu_normal);
  
  // Rule 9: If TDS=Tinggi AND Suhu=Panas THEN Kualitas=SangatBuruk
  rule[8] = min(tds_tinggi, suhu_panas);
  
  // Defuzzifikasi dengan metode Centroid
  float numerator = 0;
  float denominator = 0;
  
  // Sample points (0-100 dengan step 2)
  for (int x = 0; x <= 100; x += 2) {
    float membership = 0;
    
    // Cari membership tertinggi (Max) untuk setiap point x
    // Aggregate Output (Implikasi min, Agregasi max)
    for (int i = 0; i < 9; i++) {
      float outputMembership = 0;
      
      // Tentukan output membership berdasarkan rule index
      if (i == 4) { // Rule 5: Sangat Baik
        outputMembership = fuzzyOutput_SangatBaik(x);
      } else if (i == 1) { // Rule 2: Baik
        outputMembership = fuzzyOutput_Baik(x);
      } else if (i == 0 || i == 2 || i == 3) { // Rule 1, 3, 4: Normal
        outputMembership = fuzzyOutput_Normal(x);
      } else if (i == 5 || i == 7) { // Rule 6, 8: Buruk
        outputMembership = fuzzyOutput_Buruk(x);
      } else { // Rule 7, 9: Sangat Buruk (i == 6 || i == 8)
        outputMembership = fuzzyOutput_SangatBuruk(x);
      }
      
      membership = max(membership, min(rule[i], outputMembership)); // Agregasi (MAX)
    }
    
    numerator += x * membership;
    denominator += membership;
  }
  
  if (denominator == 0) return 50.0; // Default jika semua membership 0
  
  // Centroid = Penjumlahan(x * µ(x)) / Penjumlahan(µ(x))
  return numerator / denominator;
}

void tampilkanData() {
  Serial.println("=== SISTEM MONITORING AIR TAWAR ===");
  Serial.print("TDS Value: ");
  Serial.print(tdsValue, 0);
  Serial.println(" ppm");
  
  Serial.print("Suhu: ");
  Serial.print(temperature, 1);
  Serial.println(" °C");
  
  Serial.print("Kualitas Air (Fuzzy): ");
  Serial.print(kualitasAir, 1);
  Serial.print(" - ");
  
  // Kategori
  if (kualitasAir >= 80) {
    Serial.println("SANGAT BAIK 💚");
  } else if (kualitasAir >= 60) {
    Serial.println("BAIK 🟢");
  } else if (kualitasAir >= 40) {
    Serial.println("NORMAL 🟡");
  } else if (kualitasAir >= 20) {
    Serial.println("BURUK 🟠");
  } else {
    Serial.println("SANGAT BURUK 🔴");
  }
  
  Serial.println("===================================");
}

int getMedianNum(int bArray[], int iFilterLen) {
  int bTab[iFilterLen];
  for (byte i = 0; i < iFilterLen; i++)
    bTab[i] = bArray[i];
  int i, j, bTemp;
  for (j = 0; j < iFilterLen - 1; j++) {
    for (i = 0; i < iFilterLen - j - 1; i++) {
      if (bTab[i] > bTab[i + 1]) {
        bTemp = bTab[i];
        bTab[i] = bTab[i + 1];
        bTab[i + 1] = bTemp;
      }
    }
  }
  if ((iFilterLen & 1) > 0)
    bTemp = bTab[(iFilterLen - 1) / 2];
  else
    bTemp = (bTab[iFilterLen / 2] + bTab[iFilterLen / 2 - 1]) / 2;
  return bTemp;
}