/**
 * ============================================================
 * GadungGuard - Firmware ESP32 v3.3 (SENSOR-ONLY)
 * Sistem Logging Data Sensor untuk Training Machine Learning
 * ============================================================
 *
 * PERUBAHAN BESAR dari versi sebelumnya:
 * [REMOVED] Semua logika AI lokal (estimateHCN, runLocalAIModel)
 *           dihapus total. Versi ini HANYA baca sensor & kirim
 *           data mentah ke server. Klasifikasi Aman/Proses/Bahaya
 *           akan ditentukan oleh model ML yang ditraining terpisah
 *           dari data yang dikumpulkan versi ini.
 *
 * CHANGELOG v3.3 (dari v3.1):
 * [FIX] AKAR MASALAH DITEMUKAN: ada gelembung udara terjebak di
 *       membran kaca probe pH, menyebabkan pembacaan sangat tidak
 *       stabil (melompat 0.13V - 1.77V). Setelah probe dikibas
 *       (gerakan seperti mengibas termometer air raksa), gelembung
 *       keluar dan pembacaan menjadi stabil di rentang 0.73-0.92V.
 *
 * [FIX] Kalibrasi diperbarui dengan data BARU pasca-perbaikan:
 *       Rata-rata voltase stabil di air keran = 0.8388V
 *       (diasumsikan air keran netral, ~pH 7.0)
 *
 * [INFO] PENTING — perawatan probe pH ke depan:
 *       - Jika probe didiamkan tanpa air terlalu lama, gelembung
 *         udara bisa terbentuk lagi di membran kaca
 *       - Jika pembacaan tiba-tiba liar lagi, coba kibas probe
 *         dulu (seperti mengibas termometer) sebelum curiga rusak
 *       - Selalu simpan probe terendam air/larutan, jangan kering
 *
 * CHANGELOG v3.1 (dari v3.0):
 * [FIX] pH masih terbaca 14 di air keran — root cause: probe pH
 *       DRIFT (bergeser) dari kalibrasi sebelumnya. Voltase di
 *       buffer pH 7 dulu = 1.61V, sekarang di air keran = 1.15V.
 *       Sensor pH kaca memang rawan drift jika sering pindah
 *       cairan tanpa dibilas/distabilkan.
 *
 * [FIX] Slope Nernst teoritis (-59.16 mV/pH) TERLALU CURAM untuk
 *       sensor generic/murah ini — pergeseran voltase kecil (0.1V)
 *       bisa membuat pH melompat 2-3 poin penuh. Diganti dengan
 *       slope landai empiris (-180 mV/pH) yang lebih toleran
 *       terhadap noise dan drift kecil, lebih cocok untuk modul
 *       pH non-presisi seperti ini.
 *
 * [FIX] Kalibrasi referensi diperbarui memakai voltase TERBARU
 *       (1.1516V) sebagai estimasi pH 7 dari air keran/PDAM
 *       (asumsi air keran Indonesia netral, pH 6.5-8.5), karena
 *       buffer pH 7.0 standar sudah habis.
 *
 * [PENTING] Kalibrasi ini BUKAN presisi laboratorium. Untuk hasil
 *           akurat, kalibrasi ulang dengan buffer pH 7.0/4.0 asli
 *           segera setelah tersedia kembali. Lihat panduan di
 *           bawah untuk update kalibrasi.
 *
 * [INFO] Jika nanti sudah punya buffer pH 4.0 & 7.0 baru, cukup
 *        ukur voltase di masing-masing larutan, isi ke
 *        PH_VOLT_AT_7 dan PH_VOLT_AT_4, lalu set
 *        USE_TWO_POINT_CALIBRATION = true untuk akurasi maksimal.
 *
 * KEBUTUHAN LIBRARY:
 * - WiFi.h, HTTPClient.h     (bawaan ESP32 board)
 * - ArduinoJson              (by Benoit Blanchon, v6.x)
 * - OneWire                  (by Paul Stoffregen)
 * - DallasTemperature        (by Miles Burton)
 *
 * SKEMA WIRING ESP32:
 * ┌─────────────────────────────────────────────────┐
 * │ SENSOR pH (Modul: To|Do|Po|G|G|U+)             │
 * │   Po   → GPIO34 (ADC1, hanya INPUT)             │
 * │   U+   → VIN/5V                                 │
 * │   G    → GND                                    │
 * ├─────────────────────────────────────────────────┤
 * │ SENSOR TURBIDITY (Merah, 3 pin)                 │
 * │   AO   → GPIO35 (ADC1, hanya INPUT)             │
 * │   VCC  → 5V                                     │
 * │   GND  → GND                                    │
 * │   ⚠️  Cek solderan probe-ke-board jika 0V terus │
 * ├─────────────────────────────────────────────────┤
 * │ SENSOR TDS (Hitam, JST 4 pin)                   │
 * │   AO   → GPIO32 (ADC1)                          │
 * │   VCC  → 3.3V                                   │
 * │   GND  → GND                                    │
 * ├─────────────────────────────────────────────────┤
 * │ SENSOR SUHU DS18B20 (Waterproof, 3 kabel)       │
 * │   Merah  → 3.3V (VCC)                           │
 * │   Kuning → GPIO5 (DATA / One-Wire)              │
 * │   Hitam  → GND                                  │
 * │   ⚠️  Resistor 4.7kΩ: antara GPIO5 dan 3.3V    │
 * └─────────────────────────────────────────────────┘
 *
 * KONFIGURASI WAJIB (sesuaikan sebelum upload):
 * - WIFI_SSID, WIFI_PASS  : Nama & password WiFi
 * - SERVER_URL            : IP/domain server Laravel
 * - DEVICE_ID             : ID device dari tabel `devices` di DB
 * ============================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <OneWire.h>
#include <DallasTemperature.h>

// ============================================================
// SECTION 1 — KONFIGURASI WAJIB
// ============================================================
const char* WIFI_SSID  = "hotspot R.57 - 2.4 GHz ++";
const char* WIFI_PASS  = "";
// [FIX] Pastikan TIDAK ADA double-slash // setelah port!
const char* SERVER_URL = "http://192.168.202.232:8000/api/sensor-logs";
const int   DEVICE_ID  = 1;

// ============================================================
// SECTION 2 — PIN MAPPING
// ============================================================
const int PIN_PH        = 34;
const int PIN_TURBIDITY = 35;
const int PIN_TDS       = 32;
const int PIN_TEMP      = 5;

// ============================================================
// SECTION 3 — KONSTANTA ADC & TIMING
// ============================================================
const float          VREF             = 3.3f;
const int             ADC_MAX_VALUE    = 4095;
const int             ADC_SAMPLES      = 10;
const int             ADC_SAMPLE_MS    = 5;
const int             TEMP_RETRY_MAX   = 3;
const unsigned long   SEND_INTERVAL    = 5000;   // kirim data tiap 5 detik
const unsigned long   TEMP_CONV_DELAY  = 800UL;

// ============================================================
// SECTION 4 — KALIBRASI pH (DATA ASLI HASIL PENGUKURAN)
// ============================================================

// [FIX v3.3] Hasil kalibrasi TERBARU pasca-perbaikan gelembung udara.
// Probe sebelumnya tidak stabil (0.13V-1.77V) karena gelembung udara
// di membran kaca. Setelah dikibas, stabil di rentang 0.73-0.92V.
// Diukur 30 Juni 2026, air keran dianggap netral (~pH 7.0),
// rata-rata stabil di 0.8388V. INI BUKAN BUFFER STANDAR — kalibrasi
// ulang dengan buffer pH 7.0/4.0 asli sangat disarankan saat tersedia.
const float PH_VOLT_AT_7 = 0.8388f;   // ← Volt saat probe di air keran (estimasi pH 7.0, pasca-fix)

// [FIX v3.1] Slope landai empiris, BUKAN Nernst teoritis.
// Sensor pH generic/murah sering punya respons lebih landai
// dari teori (-59.16 mV/pH). Nilai -180 mV/pH dipilih supaya
// pergeseran voltase kecil tidak membuat pH melompat ekstrem,
// lebih cocok untuk modul non-presisi seperti ini.
const float PH_NERNST_SLOPE = -0.18000f; // V per unit pH (empiris, landai)

// [OPSIONAL] Jika nanti sudah punya buffer pH 4.0 & 7.0 ASLI, set true
// dan isi PH_VOLT_AT_4 hasil pengukuran, untuk akurasi presisi lab
const bool  USE_TWO_POINT_CALIBRATION = false;
const float PH_VOLT_AT_4              = 0.0f;  // ← isi jika sudah ukur

// Slope & offset final (dihitung otomatis berdasarkan mode di atas)
float PH_SLOPE  = 1.0f / PH_NERNST_SLOPE;          // default: pakai Nernst
float PH_OFFSET = 7.0f - PH_SLOPE * PH_VOLT_AT_7;

// --- TDS ---
const float TDS_FACTOR = 0.5f;

// --- Turbidity auto-baseline ---
float TURBIDITY_BASELINE_VOLT = -1.0f;

// ============================================================
// SECTION 5 — INISIALISASI DS18B20
// ============================================================
OneWire           oneWire(PIN_TEMP);
DallasTemperature tempSensor(&oneWire);

// ============================================================
// SECTION 6 — VARIABEL GLOBAL
// ============================================================
float   ph_value          = 0.0f;
float   turbidity_value   = 0.0f;
float   tds_value         = 0.0f;
float   temperature_value = 25.0f;

bool          wifi_connected    = false;
bool          data_sent_ok      = false;
bool          temp_requested    = false;
unsigned long last_send_time    = 0;
unsigned long temp_request_time = 0;

// ============================================================
// SECTION 7 — SETUP
// ============================================================
void setup() {
    Serial.begin(115200);
    delay(500);

    Serial.println(F("\n===================================="));
    Serial.println(F("  GadungGuard v3.3 - SENSOR ONLY"));
    Serial.println(F("  (Mode logging untuk training ML)"));
    Serial.println(F("===================================="));

    // [v3.0] Hitung ulang kalibrasi pH berdasarkan mode yang dipilih
    setupPHCalibration();

    analogReadResolution(12);
    analogSetAttenuation(ADC_11db);
    Serial.println(F("[OK] ADC: 12-bit, range 0-3.3V"));

    tempSensor.begin();
    tempSensor.setWaitForConversion(false);
    scanDS18B20();

    calibrateTurbidityBaseline();

    tempSensor.requestTemperatures();
    temp_requested    = true;
    temp_request_time = millis();

    connectWiFi();
    Serial.println(F("[OK] System Ready! Logging data...\n"));
}

// ============================================================
// SECTION 8 — KALIBRASI pH SETUP
// ============================================================

/**
 * [v3.1] Menentukan slope & offset pH berdasarkan mode kalibrasi.
 * - 1 titik: pakai slope landai empiris (-180 mV/pH), lebih
 *   toleran untuk sensor generic dibanding Nernst teoritis
 * - 2 titik: pakai slope hasil pengukuran aktual (paling akurat,
 *   gunakan ini begitu buffer pH 4.0 & 7.0 standar tersedia)
 */
void setupPHCalibration() {
    if (USE_TWO_POINT_CALIBRATION && PH_VOLT_AT_4 != 0.0f) {
        // Kalibrasi 2 titik — lebih akurat, pakai data aktual sensor
        PH_SLOPE  = (7.0f - 4.0f) / (PH_VOLT_AT_7 - PH_VOLT_AT_4);
        PH_OFFSET = 7.0f - PH_SLOPE * PH_VOLT_AT_7;
        Serial.println(F("[pH] Mode: Kalibrasi 2 titik (pH 4 & pH 7)"));
    } else {
        // Kalibrasi 1 titik — pakai slope landai empiris
        PH_SLOPE  = 1.0f / PH_NERNST_SLOPE;
        PH_OFFSET = 7.0f - PH_SLOPE * PH_VOLT_AT_7;
        Serial.println(F("[pH] Mode: Kalibrasi 1 titik (slope landai empiris)"));
        Serial.println(F("[pH] ⚠️  Estimasi, bukan presisi lab. Kalibrasi ulang"));
        Serial.println(F("[pH]     dengan buffer asli saat tersedia."));
    }
    Serial.printf("[pH] Slope: %.4f | Offset: %.4f\n", PH_SLOPE, PH_OFFSET);
}

// ============================================================
// SECTION 9 — KALIBRASI TURBIDITY BASELINE
// ============================================================
void calibrateTurbidityBaseline() {
    Serial.println(F("\n[Turbidity] Mengambil baseline air bersih..."));
    Serial.println(F("           Pastikan probe tercelup air bersih!"));
    delay(2000);

    long sum = 0;
    for (int i = 0; i < 20; i++) {
        sum += analogRead(PIN_TURBIDITY);
        delay(10);
    }
    float raw = (float)(sum / 20);
    TURBIDITY_BASELINE_VOLT = raw * (VREF / ADC_MAX_VALUE);

    Serial.printf("[Turbidity] Baseline: %.3f V (RAW: %.0f)\n",
                  TURBIDITY_BASELINE_VOLT, raw);

    if (raw < 5) {
        Serial.println(F("[Turbidity] ⚠️  RAW sangat rendah! Cek solderan probe-ke-board."));
    }
}

// ============================================================
// SECTION 10 — SCAN DS18B20
// ============================================================
void scanDS18B20() {
    int count = tempSensor.getDeviceCount();
    Serial.printf("[DS18B20] Device ditemukan: %d\n", count);

    if (count == 0) {
        Serial.println(F("[DS18B20] ⚠️  Tidak ada sensor! Cek wiring GPIO5."));
        return;
    }

    DeviceAddress addr;
    for (int i = 0; i < count; i++) {
        if (tempSensor.getAddress(addr, i)) {
            Serial.printf("  Sensor[%d]: ", i);
            for (int j = 0; j < 8; j++) {
                if (addr[j] < 16) Serial.print("0");
                Serial.print(addr[j], HEX);
                if (j < 7) Serial.print(":");
            }
            Serial.println();
        }
    }
    Serial.println(F("[DS18B20] ✓ Siap"));
}

// ============================================================
// SECTION 11 — MAIN LOOP
// ============================================================
void loop() {
    unsigned long now = millis();

    if (!temp_requested) {
        tempSensor.requestTemperatures();
        temp_requested    = true;
        temp_request_time = now;
    }

    if (now - last_send_time >= SEND_INTERVAL) {
        last_send_time = now;

        if (temp_requested && (now - temp_request_time < TEMP_CONV_DELAY)) {
            delay(TEMP_CONV_DELAY - (now - temp_request_time));
        }
        temperature_value = readTemperature();
        temp_requested    = false;

        readAnalogSensors();

        // [v3.0] Tidak ada lagi runLocalAIModel() — langsung print & kirim
        printSensorDataToSerial();

        if (WiFi.status() == WL_CONNECTED) {
            sendDataToServer();
        } else {
            Serial.println(F("[WiFi] Terputus, reconnecting..."));
            connectWiFi();
        }

        tempSensor.requestTemperatures();
        temp_requested    = true;
        temp_request_time = millis();
    }
}

// ============================================================
// SECTION 12 — KONEKSI WIFI
// ============================================================
void connectWiFi() {
    Serial.printf("[WiFi] Menghubungkan ke: %s\n", WIFI_SSID);
    WiFi.begin(WIFI_SSID, WIFI_PASS);
    int retries = 0;
    while (WiFi.status() != WL_CONNECTED && retries < 30) {
        delay(1000); Serial.print("."); retries++;
    }
    if (WiFi.status() == WL_CONNECTED) {
        wifi_connected = true;
        Serial.println(F("\n[WiFi] Terhubung!"));
        Serial.print(F("[WiFi] IP: "));
        Serial.println(WiFi.localIP());
    } else {
        wifi_connected = false;
        Serial.println(F("\n[WiFi] GAGAL. Mode offline."));
    }
}

// ============================================================
// SECTION 13 — PEMBACAAN SENSOR
// ============================================================
int readADCSmoothed(int pin) {
    long sum = 0;
    for (int i = 0; i < ADC_SAMPLES; i++) {
        sum += analogRead(pin);
        delay(ADC_SAMPLE_MS);
    }
    return (int)(sum / ADC_SAMPLES);
}

float adcToVoltage(int raw) {
    return (float)raw * (VREF / (float)ADC_MAX_VALUE);
}

float readPH() {
    int   raw  = readADCSmoothed(PIN_PH);
    float volt = adcToVoltage(raw);
    float ph   = PH_SLOPE * volt + PH_OFFSET;

    Serial.printf("  [pH]   Volt: %.4f V → pH: %.2f\n", volt, constrain(ph, 0.0f, 14.0f));
    return constrain(ph, 0.0f, 14.0f);
}

float readTurbidity() {
    int   raw  = readADCSmoothed(PIN_TURBIDITY);
    float volt = adcToVoltage(raw);

    float ntu = 0.0f;

    if (TURBIDITY_BASELINE_VOLT < 0.0f) {
        if (volt >= 2.5f) return 0.0f;
        ntu = -1120.4f * volt * volt
              + 5742.3f * volt * (3.3f / 5.0f)
              - 4353.8f * (3.3f / 5.0f) * (3.3f / 5.0f);
    } else {
        float delta = TURBIDITY_BASELINE_VOLT - volt;
        ntu = (delta <= 0.0f) ? 0.0f : delta * 1000.0f;
    }

    ntu = max(0.0f, ntu);
    Serial.printf("  [Turb] Volt: %.4f V | Baseline: %.4f V → %.1f NTU\n",
                  volt, TURBIDITY_BASELINE_VOLT, ntu);
    return ntu;
}

float readTDS(float tempC) {
    int   raw   = readADCSmoothed(PIN_TDS);
    float volt  = adcToVoltage(raw);
    float coeff = 1.0f + 0.02f * (tempC - 25.0f);
    float voltC = volt / coeff;

    float tds = (133.42f * pow(voltC, 3)
               - 255.86f * pow(voltC, 2)
               + 857.39f * voltC) * TDS_FACTOR;

    tds = max(0.0f, tds);
    Serial.printf("  [TDS]  Volt: %.4f V → %.1f ppm\n", volt, tds);
    return tds;
}

float readTemperature() {
    for (int attempt = 1; attempt <= TEMP_RETRY_MAX; attempt++) {
        float tempC = tempSensor.getTempCByIndex(0);
        if (tempC != DEVICE_DISCONNECTED_C && tempC != -127.0f && tempC > -55.0f) {
            Serial.printf("  [Suhu] %.2f C\n", tempC);
            return tempC;
        }
        Serial.printf("  [Suhu] Gagal baca (%d/%d)\n", attempt, TEMP_RETRY_MAX);
        if (attempt < TEMP_RETRY_MAX) {
            tempSensor.requestTemperatures();
            delay(TEMP_CONV_DELAY);
        }
    }
    Serial.println(F("  [Suhu] ⚠️ Pakai nilai terakhir."));
    return temperature_value > 0.0f ? temperature_value : 25.0f;
}

void readAnalogSensors() {
    Serial.println(F("\n--- Membaca Sensor ---"));
    tds_value       = readTDS(temperature_value);
    ph_value        = readPH();
    turbidity_value = readTurbidity();
    Serial.println(F("----------------------"));
}

// ============================================================
// SECTION 14 — KIRIM DATA KE SERVER
// ============================================================
/**
 * [v3.0] Payload disederhanakan — hanya data sensor mentah.
 * Tidak ada lagi hcn_estimated atau safety_status karena
 * klasifikasi akan dilakukan oleh model ML terpisah dari
 * data yang terkumpul di database.
 */
void sendDataToServer() {
    Serial.println(F("\n--- Mengirim Data ke Server ---"));

    StaticJsonDocument<512> doc;
    doc["device_id"]         = DEVICE_ID;
    doc["chip_id"]           = WiFi.macAddress();  // Identitas unik modul sensor
    doc["ph_value"]          = round(ph_value          * 100.0f) / 100.0f;
    doc["turbidity_value"]   = round(turbidity_value   * 100.0f) / 100.0f;
    doc["tds_value"]         = round(tds_value         * 100.0f) / 100.0f;
    doc["temperature_value"] = round(temperature_value * 100.0f) / 100.0f;

    String json_body;
    serializeJson(doc, json_body);
    Serial.print(F("  Payload: "));
    Serial.println(json_body);

    HTTPClient http;
    http.begin(SERVER_URL);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Accept",       "application/json");
    http.setTimeout(5000);

    int http_code = http.POST(json_body);

    if (http_code == HTTP_CODE_CREATED || http_code == HTTP_CODE_OK) {
        Serial.printf("  [OK] HTTP %d - Data tersimpan\n", http_code);
        data_sent_ok = true;
    } else {
        Serial.printf("  [ERR] HTTP %d\n", http_code);
        data_sent_ok = false;
    }

    http.end();
    Serial.println(F("-------------------------------"));
}

// ============================================================
// SECTION 15 — SERIAL MONITOR OUTPUT
// ============================================================
void printSensorDataToSerial() {
    Serial.println(F("\n╔══════════════════════════════════╗"));
    Serial.println(F("║   GADUNGGUARD v3.3 - SENSOR DATA ║"));
    Serial.println(F("╠══════════════════════════════════╣"));
    Serial.printf( "║  pH        : %-6.2f               ║\n", ph_value);
    Serial.printf( "║  Turbidity : %-8.2f NTU          ║\n", turbidity_value);
    Serial.printf( "║  TDS       : %-8.2f ppm          ║\n", tds_value);
    Serial.printf( "║  Suhu      : %-6.2f C             ║\n", temperature_value);
    Serial.println(F("╠══════════════════════════════════╣"));
    Serial.printf( "║  WiFi      : %-20s ║\n", WiFi.status() == WL_CONNECTED ? "Terhubung" : "Offline");
    Serial.printf( "║  Terkirim  : %-20s ║\n", data_sent_ok ? "Ya (ke server)" : "Tidak");
    Serial.println(F("╚══════════════════════════════════╝"));
}
