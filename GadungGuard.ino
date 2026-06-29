/**
 * ============================================================
 * GadungGuard - Firmware ESP32 v2.1 (Minimal Hardware)
 * Sistem Monitoring Cerdas Perendaman Gadung (Dioscorea hispida)
 * ============================================================
 *
 * ALUR SISTEM:
 *  1. Sensor membaca perubahan fisikokimia air (pH, Turbidity, TDS, Suhu)
 *  2. ESP32 mengumpulkan & memproses data sensor secara lokal
 *  3. Model AI lokal (regresi multi-variabel) mengEstimasi kadar HCN
 *     & mengklasifikasikan status: Aman / Proses / Bahaya
 *  4. Hasil dikirim ke server Laravel (POST /api/sensor-logs)
 *     untuk ditampilkan di dashboard web
 *  5. Manusia mengkonfirmasi keputusan akhir lewat dashboard web
 *  6. Database menyimpan riwayat untuk transparansi komunitas
 *
 * KEBUTUHAN LIBRARY (Install via Arduino Library Manager):
 *  - WiFi.h           (bawaan ESP32 board)
 *  - HTTPClient.h     (bawaan ESP32 board)
 *  - ArduinoJson      (by Benoit Blanchon, v6.x)
 *  - OneWire          (by Paul Stoffregen)
 *  - DallasTemperature (by Miles Burton)
 *
 * SKEMA WIRING ESP32:
 * ┌─────────────────────────────────────────────────┐
 * │ SENSOR pH (Analog)                              │
 * │   OUT  → GPIO34 (ADC1_CH6, hanya INPUT)         │
 * │   VCC  → 5V (atau 3.3V tergantung modul)        │
 * │   GND  → GND                                    │
 * ├─────────────────────────────────────────────────┤
 * │ SENSOR TURBIDITY (Analog, e.g. SEN0189)         │
 * │   OUT  → GPIO35 (ADC1_CH7, hanya INPUT)         │
 * │   VCC  → 5V                                     │
 * │   GND  → GND                                    │
 * ├─────────────────────────────────────────────────┤
 * │ SENSOR TDS (Analog, e.g. TDS Gravity)           │
 * │   OUT  → GPIO32 (ADC1_CH4)                      │
 * │   VCC  → 3.3V                                   │
 * │   GND  → GND                                    │
 * ├─────────────────────────────────────────────────┤
 * │ SENSOR SUHU (DS18B20, One-Wire Digital)         │
 * │   DATA → GPIO4                                  │
 * │   VCC  → 3.3V                                   │
 * │   GND  → GND                                    │
 * │   (Resistor 4.7kΩ antara DATA dan VCC)          │
 * └─────────────────────────────────────────────────┘
 *
 * KONFIGURASI WAJIB (sesuaikan sebelum upload):
 *   - WIFI_SSID, WIFI_PASS  : Nama & password WiFi
 *   - SERVER_URL            : IP/domain server Laravel
 *   - DEVICE_ID             : ID device dari tabel `devices` di DB
 * ============================================================
 */

// ============================================================
// SECTION 1 — LIBRARY INCLUDES
// ============================================================
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <OneWire.h>
#include <DallasTemperature.h>

// ============================================================
// SECTION 2 — KONFIGURASI WAJIB (UBAH SESUAI ENVIRONMENT)
// ============================================================

// --- WiFi ---
const char* WIFI_SSID = "NAMA_WIFI_KAMU";      // ← Ganti ini
const char* WIFI_PASS = "PASSWORD_WIFI_KAMU";  // ← Ganti ini

// --- Server Laravel ---
// Jika menggunakan Laragon di PC lokal, cek IP dengan: ipconfig
// (contoh: http://192.168.1.10/gadungan/public/api/sensor-logs)
const char* SERVER_URL = "http://192.168.1.10/gadungan/public/api/sensor-logs"; // ← Ganti IP

// --- Device ---
// Sesuaikan dengan ID di tabel `devices` database (DEV001 = 1)
const int DEVICE_ID = 1;  // ← Ganti jika perlu

// ============================================================
// SECTION 3 — PIN MAPPING
// ============================================================
const int PIN_PH        = 34; // ADC1 - Sensor pH
const int PIN_TURBIDITY = 35; // ADC1 - Sensor Kekeruhan
const int PIN_TDS       = 32; // ADC1 - Sensor TDS
const int PIN_TEMP      = 4;  // One-Wire - Sensor Suhu DS18B20

// ============================================================
// SECTION 4 — KONSTANTA KALIBRASI SENSOR
// ============================================================

// --- pH Sensor (kalibrasi 2 titik: buffer pH 4 & pH 7) ---
// Ukur tegangan output saat dicelup ke masing-masing larutan buffer,
// lalu isi nilai di bawah ini.
const float PH_VOLT_AT_7  = 2.50; // Volt saat pH = 7 (netral)
const float PH_VOLT_AT_4  = 3.05; // Volt saat pH = 4 (asam)
const float PH_SLOPE      = (7.0 - 4.0) / (PH_VOLT_AT_7 - PH_VOLT_AT_4);
const float PH_OFFSET     = 7.0 - PH_SLOPE * PH_VOLT_AT_7;

// --- TDS Sensor ---
const float TDS_VREF   = 3.3;  // Tegangan referensi ADC ESP32
const float TDS_FACTOR = 0.5;  // Faktor konversi (0.5 untuk air tawar)

// --- ADC ---
const int ADC_RESOLUTION = 4095; // ESP32 ADC: 12-bit (0–4095)
const int ADC_SAMPLES    = 10;   // Rata-rata N sample untuk kurangi noise

// ============================================================
// SECTION 5 — KONSTANTA TIMING
// ============================================================
const unsigned long SEND_INTERVAL = 5000; // Kirim data setiap 5 detik (ms)

// ============================================================
// SECTION 6 — INISIALISASI OBJEK LIBRARY
// ============================================================
OneWire oneWire(PIN_TEMP);
DallasTemperature tempSensor(&oneWire);

// ============================================================
// SECTION 7 — VARIABEL GLOBAL
// ============================================================
float ph_value          = 0.0;
float turbidity_value   = 0.0;
float tds_value         = 0.0;
float temperature_value = 0.0;
float hcn_estimated_ppm = 0.0;
String safety_status    = "INIT";

bool wifi_connected  = false;
bool data_sent_ok    = false;

// Status deteksi sensor (true = terdeteksi, false = tidak ada/rusak)
bool sensor_ph_detected        = false;
bool sensor_turbidity_detected = false;
bool sensor_tds_detected       = false;
bool sensor_temp_detected      = false;
unsigned long last_send_time = 0;

// ============================================================
// SECTION 8 — SETUP
// ============================================================
void setup() {
    Serial.begin(115200);
    Serial.println(F("\n===================================="));
    Serial.println(F("  GadungGuard v2.1 - BOOT"));
    Serial.println(F("===================================="));

    // Inisialisasi sensor suhu DS18B20
    tempSensor.begin();
    Serial.println(F("[OK] Sensor suhu DS18B20 siap."));

    // Sambungkan ke WiFi
    connectWiFi();

    Serial.println(F("[OK] System Ready! Mulai monitoring...\n"));
}

// ============================================================
// SECTION 9 — MAIN LOOP
// ============================================================
void loop() {
    unsigned long now = millis();

    if (now - last_send_time >= SEND_INTERVAL) {
        last_send_time = now;

        // LANGKAH 1 & 2: Baca semua sensor
        readAllSensors();

        // LANGKAH 3: Model AI lokal — estimasi HCN & klasifikasi
        runLocalAIModel();

        // Log ke Serial Monitor
        printSensorDataToSerial();

        // LANGKAH 6: Kirim data ke server
        if (WiFi.status() == WL_CONNECTED) {
            sendDataToServer();
        } else {
            Serial.println(F("[WiFi] Koneksi terputus, mencoba reconnect..."));
            connectWiFi();
        }
    }
}

// ============================================================
// SECTION 10 — KONEKSI WIFI
// ============================================================
void connectWiFi() {
    Serial.printf("[WiFi] Menghubungkan ke: %s\n", WIFI_SSID);
    WiFi.begin(WIFI_SSID, WIFI_PASS);

    int retries = 0;
    while (WiFi.status() != WL_CONNECTED && retries < 30) {
        delay(1000);
        Serial.print(".");
        retries++;
    }

    if (WiFi.status() == WL_CONNECTED) {
        wifi_connected = true;
        Serial.println(F("\n[WiFi] Terhubung!"));
        Serial.print(F("[WiFi] IP Address: "));
        Serial.println(WiFi.localIP());
    } else {
        wifi_connected = false;
        Serial.println(F("\n[WiFi] GAGAL terhubung. Lanjut mode offline."));
    }
}

// ============================================================
// SECTION 11 — PEMBACAAN SENSOR
// ============================================================

/**
 * Membaca nilai ADC rata-rata dari N sampel untuk mengurangi noise.
 */
int readADCSmoothed(int pin) {
    long sum = 0;
    for (int i = 0; i < ADC_SAMPLES; i++) {
        sum += analogRead(pin);
        delay(10);
    }
    return (int)(sum / ADC_SAMPLES);
}

/**
 * Membaca dan mengkonversi nilai sensor pH ke skala 0–14.
 * Menggunakan kalibrasi 2 titik (buffer pH 4 & pH 7).
 */
float readPH() {
    int   raw     = readADCSmoothed(PIN_PH);
    float voltage = (float)raw * (3.3 / ADC_RESOLUTION);
    float ph      = PH_SLOPE * voltage + PH_OFFSET;
    return constrain(ph, 0.0, 14.0);
}

/**
 * Membaca nilai kekeruhan (turbidity) dalam NTU.
 * Persamaan konversi kubik dari datasheet SEN0189.
 */
float readTurbidity() {
    int   raw     = readADCSmoothed(PIN_TURBIDITY);
    float voltage = (float)raw * (5.0 / ADC_RESOLUTION);

    if (voltage >= 4.5) return 0.0;

    float ntu = -1120.4 * voltage * voltage + 5742.3 * voltage - 4353.8;
    return max(0.0f, ntu);
}

/**
 * Membaca nilai TDS dalam ppm dengan kompensasi suhu.
 */
float readTDS(float tempC) {
    int   raw                 = readADCSmoothed(PIN_TDS);
    float voltage             = (float)raw * (TDS_VREF / ADC_RESOLUTION);
    float compensationCoeff   = 1.0 + 0.02 * (tempC - 25.0);
    float voltageCompensated  = voltage / compensationCoeff;

    float tds = (133.42 * pow(voltageCompensated, 3)
               - 255.86 * pow(voltageCompensated, 2)
               + 857.39 * voltageCompensated) * TDS_FACTOR;

    return max(0.0f, tds);
}

/**
 * Membaca suhu dari sensor DS18B20.
 * Mengembalikan 25.0°C jika sensor tidak terbaca.
 */
float readTemperature() {
    tempSensor.requestTemperatures();
    float tempC = tempSensor.getTempCByIndex(0);

    if (tempC == DEVICE_DISCONNECTED_C) {
        Serial.println(F("  [Temp] SENSOR TIDAK TERBACA, pakai default 25.0°C"));
        return 25.0;
    }
    return tempC;
}

/**
 * Mendeteksi keberadaan sensor berdasarkan nilai ADC dan respons.
 * Sensor analog: jika raw ADC = 0 atau 4095, kemungkinan tidak terpasang.
 * Sensor DS18B20: jika mengembalikan DEVICE_DISCONNECTED_C, tidak ada.
 */
void detectSensors() {
    // Deteksi pH sensor (ADC harusnya antara 50-4000 jika terpasang)
    int ph_raw = analogRead(PIN_PH);
    sensor_ph_detected = (ph_raw > 50 && ph_raw < 4000);

    // Deteksi Turbidity sensor
    int turb_raw = analogRead(PIN_TURBIDITY);
    sensor_turbidity_detected = (turb_raw > 50 && turb_raw < 4000);

    // Deteksi TDS sensor
    int tds_raw = analogRead(PIN_TDS);
    sensor_tds_detected = (tds_raw > 50 && tds_raw < 4000);

    // Deteksi DS18B20 (suhu)
    tempSensor.requestTemperatures();
    float testTemp = tempSensor.getTempCByIndex(0);
    sensor_temp_detected = (testTemp != DEVICE_DISCONNECTED_C);

    Serial.println(F("--- Status Deteksi Sensor ---"));
    Serial.printf("  pH Sensor       : %s\n", sensor_ph_detected        ? "TERDETEKSI" : "TIDAK ADA");
    Serial.printf("  Turbidity Sensor: %s\n", sensor_turbidity_detected ? "TERDETEKSI" : "TIDAK ADA");
    Serial.printf("  TDS Sensor      : %s\n", sensor_tds_detected       ? "TERDETEKSI" : "TIDAK ADA");
    Serial.printf("  Temp DS18B20    : %s\n", sensor_temp_detected      ? "TERDETEKSI" : "TIDAK ADA");
    Serial.println(F("-----------------------------"));
}

/**
 * Membaca semua sensor sekaligus dan simpan ke variabel global.
 * Urutan: Deteksi → Suhu → TDS (butuh nilai suhu) → pH → Turbidity
 */
void readAllSensors() {
    Serial.println(F("\n--- Membaca Sensor ---"));
    
    // Deteksi keberadaan sensor terlebih dahulu
    detectSensors();
    
    temperature_value = readTemperature();
    tds_value         = readTDS(temperature_value);
    ph_value          = readPH();
    turbidity_value   = readTurbidity();
    Serial.println(F("----------------------"));
}

// ============================================================
// SECTION 12 — MODEL AI LOKAL (ESTIMASI HCN & KLASIFIKASI)
// ============================================================

/**
 * Mengestimasi konsentrasi HCN (asam sianida) dalam air rendaman gadung
 * menggunakan model regresi proxy multi-variabel berbasis pengetahuan domain.
 *
 * DASAR ILMIAH:
 * Gadung (Dioscorea hispida) mengandung glikosida sianogenik (dioscorine)
 * yang terhidrolisis menjadi HCN saat kontak air.
 * Korelasi perubahan fisikokimia:
 *   TDS ↑       → Lebih banyak zat terlarut termasuk ion CN⁻
 *   Turbidity ↑ → Partikel koloid dari pemecahan sel umbi
 *   pH ↓ (asam) → Hidrolisis glikosida lebih aktif
 *   Suhu ↑      → Laju hidrolisis enzimatik meningkat
 *
 * Output: estimasi HCN dalam mg/L
 */
float estimateHCN(float ph, float turb, float tds, float temp) {
    // Bobot kontribusi tiap parameter
    float contrib_tds  = 0.0005f * tds;
    float contrib_turb = 0.0003f * turb;
    float contrib_ph   = (ph < 7.0f) ? 0.08f * (7.0f - ph) : 0.0f;
    float contrib_temp = (temp > 25.0f) ? 0.003f * (temp - 25.0f) : 0.0f;

    float hcn = contrib_tds + contrib_turb + contrib_ph + contrib_temp;
    return constrain(hcn, 0.0f, 15.0f);
}

/**
 * Menjalankan model AI lokal:
 *  1. Estimasi kadar HCN via model proxy regresi
 *  2. Klasifikasi status: Aman / Proses / Bahaya
 *
 * THRESHOLD KLASIFIKASI:
 *  BAHAYA → HCN > 3.0 mg/L ATAU pH < 5.5/> 9.0 | Turb > 600 | TDS > 700
 *  PROSES → HCN 0.5–3.0 mg/L ATAU parameter dalam transisi
 *  AMAN   → HCN < 0.5 mg/L DAN semua parameter dalam batas normal
 */
void runLocalAIModel() {
    hcn_estimated_ppm = estimateHCN(ph_value, turbidity_value, tds_value, temperature_value);

    bool kondisi_bahaya =
        (turbidity_value > 600.0f) ||
        (tds_value       > 700.0f) ||
        (ph_value        < 5.5f)   ||
        (ph_value        > 9.0f)   ||
        (hcn_estimated_ppm > 3.0f);

    bool kondisi_proses =
        (turbidity_value > 100.0f) ||
        (tds_value       > 150.0f) ||
        (ph_value        < 6.5f && ph_value >= 5.5f) ||
        (ph_value        > 7.5f && ph_value <= 9.0f) ||
        (hcn_estimated_ppm >= 0.5f);

    if (kondisi_bahaya) {
        safety_status = "Bahaya";
    } else if (kondisi_proses) {
        safety_status = "Proses";
    } else {
        safety_status = "Aman";
    }
}

// ============================================================
// SECTION 13 — KIRIM DATA KE SERVER (HTTP POST)
// ============================================================

/**
 * Mengirim data sensor dan estimasi HCN ke server Laravel
 * via HTTP POST ke endpoint: POST /api/sensor-logs
 *
 * Payload JSON:
 * {
 *   "device_id"         : 1,
 *   "ph_value"          : 7.1,
 *   "turbidity_value"   : 45.3,
 *   "tds_value"         : 120.5,
 *   "temperature_value" : 28.4,
 *   "hcn_estimated"     : 0.0872
 * }
 *
 * Server akan memvalidasi, menyimpan ke DB, dan mengembalikan
 * JSON {status, safety_status_result, recommendation}.
 */
void sendDataToServer() {
    Serial.println(F("\n--- Mengirim Data ke Server ---"));

    // Susun payload JSON (diperbesar untuk menampung status sensor)
    StaticJsonDocument<384> doc;
    doc["device_id"]         = DEVICE_ID;
    doc["ph_value"]          = round(ph_value          * 100.0) / 100.0;
    doc["turbidity_value"]   = round(turbidity_value   * 100.0) / 100.0;
    doc["tds_value"]         = round(tds_value         * 100.0) / 100.0;
    doc["temperature_value"] = round(temperature_value * 100.0) / 100.0;
    doc["hcn_estimated"]     = round(hcn_estimated_ppm * 10000.0) / 10000.0;

    // Status keberadaan sensor
    doc["sensor_ph_detected"]        = sensor_ph_detected;
    doc["sensor_turbidity_detected"] = sensor_turbidity_detected;
    doc["sensor_tds_detected"]       = sensor_tds_detected;
    doc["sensor_temp_detected"]      = sensor_temp_detected;

    String json_body;
    serializeJson(doc, json_body);
    Serial.print(F("  Payload : "));
    Serial.println(json_body);

    // Kirim HTTP POST
    HTTPClient http;
    http.begin(SERVER_URL);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Accept",       "application/json");
    http.setTimeout(5000);

    int http_code = http.POST(json_body);

    if (http_code == HTTP_CODE_CREATED || http_code == HTTP_CODE_OK) {
        // Parse respon dari server
        StaticJsonDocument<512> res;
        DeserializationError err = deserializeJson(res, http.getString());

        if (!err) {
            const char* server_status = res["safety_status_result"] | "--";
            const char* recommendation = res["recommendation"]      | "";
            long        log_id         = res["log_id"]              | 0;

            Serial.printf("  Log ID  : %ld\n",  log_id);
            Serial.printf("  Status  : %s\n",   server_status);
            Serial.printf("  Rekomend: %s\n",   recommendation);

            // Sinkronkan status lokal dengan keputusan server
            if (safety_status != String(server_status)) {
                Serial.println(F("  [INFO] AI lokal vs server berbeda, pakai status server."));
                safety_status = String(server_status);
            }

            data_sent_ok = true;
            Serial.printf("  [OK] Data #%ld berhasil disimpan ke database.\n", log_id);
        }
    } else {
        Serial.printf("  [ERR] HTTP %d: %s\n", http_code, http.errorToString(http_code).c_str());
        data_sent_ok = false;
    }

    http.end();
    Serial.println(F("-------------------------------"));
}

// ============================================================
// SECTION 14 — DEBUG SERIAL MONITOR
// ============================================================

/**
 * Mencetak ringkasan data sensor dan hasil AI ke Serial Monitor.
 * Buka Serial Monitor di Arduino IDE dengan baud rate 115200.
 */
void printSensorDataToSerial() {
    Serial.println(F("\n╔══════════════════════════════════╗"));
    Serial.println(F("║     GADUNGGUARD - DATA SENSOR    ║"));
    Serial.println(F("╠══════════════════════════════════╣"));
    Serial.printf( "║  pH        : %-6.2f               ║\n", ph_value);
    Serial.printf( "║  Turbidity : %-8.2f NTU          ║\n", turbidity_value);
    Serial.printf( "║  TDS       : %-8.2f ppm          ║\n", tds_value);
    Serial.printf( "║  Suhu      : %-6.2f C             ║\n", temperature_value);
    Serial.println(F("╠══════════════════════════════════╣"));
    Serial.printf( "║  HCN Est.  : %-8.4f mg/L        ║\n", hcn_estimated_ppm);
    Serial.printf( "║  AI Status : %-20s ║\n", safety_status.c_str());
    Serial.printf( "║  WiFi      : %-20s ║\n", WiFi.status() == WL_CONNECTED ? "Terhubung" : "Offline");
    Serial.printf( "║  Terkirim  : %-20s ║\n", data_sent_ok ? "Ya (ke server)" : "Tidak");
    Serial.println(F("╚══════════════════════════════════╝"));
}
