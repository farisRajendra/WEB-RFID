#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <Adafruit_PN532.h>
#include <ArduinoJson.h>
#include <time.h>

// === LCD I2C ===
LiquidCrystal_I2C lcd(0x27, 16, 2);

// === PN532 via I2C ===
#define SDA_PIN 21
#define SCL_PIN 22
#define PN532_IRQ 4
#define PN532_RESET 5
Adafruit_PN532 nfc(PN532_IRQ, PN532_RESET, &Wire);

// === WiFi Config ===
const char* ssid = "sumsangs22";
const char* password = "duapuluhjuni78";

// === Laravel API Endpoint ===
const char* serverName = "http://192.168.1.3:8000/api/absensi";

void setup() {
  Serial.begin(115200);
  Wire.begin(SDA_PIN, SCL_PIN);
  lcd.init();
  lcd.backlight();
  lcd.setCursor(0, 0);
  lcd.print("Menghubungkan...");

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  lcd.clear();
  lcd.print("WiFi Terhubung");

  configTime(7 * 3600, 0, "pool.ntp.org", "time.nist.gov");
  setenv("TZ", "WIB-7", 1);
  tzset();

  struct tm timeinfo;
  if (getLocalTime(&timeinfo)) {
    Serial.print("Waktu Lokal (WIB): ");
    Serial.println(&timeinfo, "%d/%m/%Y %H:%M:%S");
  } else {
    Serial.println("Gagal mendapatkan waktu NTP");
  }

  nfc.begin();
  uint32_t versiondata = nfc.getFirmwareVersion();
  if (!versiondata) {
    Serial.println("PN532 tidak terdeteksi");
    lcd.setCursor(0, 1);
    lcd.print("PN532 ERROR");
    while (1);
  }

  nfc.SAMConfig();
  Serial.println("Siap scan kartu");
  lcd.clear();
  lcd.print("Sistem Siap");
  lcd.setCursor(0, 1);
  lcd.print("Scan kartu Anda");
}

void loop() {
  uint8_t uid[7];
  uint8_t uidLength;

  if (nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, uid, &uidLength)) {
    String uidStr = "";
    for (uint8_t i = 0; i < uidLength; i++) {
      if (uid[i] < 0x10) uidStr += "0";
      uidStr += String(uid[i], HEX);
    }
    uidStr.toUpperCase();

    Serial.println("=== RFID DETECTED ===");
    Serial.println("UID: " + uidStr);

    lcd.clear();
    lcd.print("Memproses...");
    lcd.setCursor(0, 1);
    lcd.print(uidStr);

    kirimKeLaravel(uidStr);

    delay(1000);
    lcd.clear();
    lcd.print("Sistem Siap");
    lcd.setCursor(0, 1);
    lcd.print("Scan kartu Anda");
  }
}

void kirimKeLaravel(String uid) {
  if (WiFi.status() == WL_CONNECTED) {
    String jsonData = "{\"rfid_id\":\"" + uid + "\"}";

    HTTPClient http;
    http.begin(serverName);
    http.addHeader("Content-Type", "application/json");
    http.setTimeout(10000);

    int httpResponseCode = http.POST(jsonData);

    if (httpResponseCode <= 0) {
      lcd.clear();
      lcd.print("Server Error");
      lcd.setCursor(0, 1);
      lcd.print("Hubungi Admin");
      http.end();
      return;
    }

    String response = http.getString();
    http.end();

    struct tm timeinfo;
    bool waktuTersedia = getLocalTime(&timeinfo);
    char waktu[20];
    if (waktuTersedia) {
      strftime(waktu, sizeof(waktu), "%d/%m %H:%M:%S", &timeinfo);
      Serial.print("Waktu Absensi (WIB): ");
      Serial.println(waktu);
    }

    lcd.clear();

    if (httpResponseCode == 200) {
      handleSuccessResponse(response, waktu, waktuTersedia);
    } else if (httpResponseCode == 400) {
      handleErrorResponse(response, waktu, waktuTersedia);
    } else if (httpResponseCode == 404) {
      handleNotFoundResponse();
    } else if (httpResponseCode == 500) {
      handleServerErrorResponse();
    } else {
      handleUnexpectedError(httpResponseCode);
    }
  } else {
    lcd.clear();
    lcd.print("WiFi Terputus!");
    lcd.setCursor(0, 1);
    lcd.print("Hubungi Admin");
  }
}

void handleSuccessResponse(String response, char* waktu, bool waktuTersedia) {
  DynamicJsonDocument doc(1024);
  deserializeJson(doc, response);

  String message = doc["message"];
  String nama = doc["data"]["nama"];
  String status = doc["data"]["status"];

  Serial.println("=== ABSENSI BERHASIL ===");
  Serial.println("Nama: " + nama);
  Serial.println("Status: " + status);
  Serial.println("Message: " + message);

  message.toLowerCase();

  if (message.indexOf("masuk berhasil") > -1) {
    lcd.print("Masuk Berhasil");
  } else if (message.indexOf("masuk terlambat") > -1) {
    lcd.print("Masuk Terlambat");
  } else if (message.indexOf("pulang berhasil") > -1) {
    lcd.print("Pulang Berhasil");
  } else if (message.indexOf("pulang awal") > -1) {
    lcd.print("Pulang Awal");
  } else if (message.indexOf("pulang tidak hadir") > -1) {
    lcd.print("Pulang Tidak");
    lcd.setCursor(0, 1);
    lcd.print("Hadir");
    return;
  } else {
    lcd.print("Absen Berhasil");
  }

  lcd.setCursor(0, 1);
  if (waktuTersedia) lcd.print(waktu);
}

void handleErrorResponse(String response, char* waktu, bool waktuTersedia) {
  DynamicJsonDocument doc(1024);
  deserializeJson(doc, response);
  String message = doc["message"];

  Serial.println("=== ABSENSI DITOLAK ===");
  Serial.println("Error: " + message);

  if (message.indexOf("sudah melakukan absensi masuk") > -1) {
    lcd.print("Sudah Absen");
    lcd.setCursor(0, 1);
    lcd.print("Masuk Hari Ini");
  } else if (message.indexOf("sudah melakukan absensi pulang") > -1) {
    lcd.print("Sudah Absen");
    lcd.setCursor(0, 1);
    lcd.print("Pulang Hari Ini");
  } else if (message.indexOf("masuk dan pulang") > -1) {
    lcd.print("Absen Lengkap");
    lcd.setCursor(0, 1);
    lcd.print("Hari Ini");
  } else {
    lcd.print("Absen Ditolak");
    lcd.setCursor(0, 1);
    if (waktuTersedia) lcd.print(waktu);
  }
}

void handleNotFoundResponse() {
  Serial.println("=== RFID TIDAK TERDAFTAR ===");
  lcd.print("RFID Belum");
  lcd.setCursor(0, 1);
  lcd.print("Terdaftar");
}

void handleServerErrorResponse() {
  Serial.println("=== SERVER ERROR ===");
  lcd.print("Server Error");
  lcd.setCursor(0, 1);
  lcd.print("Hubungi Admin");
}

void handleUnexpectedError(int httpCode) {
  Serial.println("=== UNEXPECTED ERROR: " + String(httpCode) + " ===");
  lcd.print("Error: " + String(httpCode));
  lcd.setCursor(0, 1);
  lcd.print("Coba Lagi");
}