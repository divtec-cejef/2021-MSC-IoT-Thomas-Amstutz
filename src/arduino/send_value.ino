#include <DHT_U.h>
#include <DHT.h>
#include <Adafruit_Sensor.h>
#include <SigFox.h>
#include <ArduinoLowPower.h>

#define DHTPIN 2

#define DHTTYPE DHT11   // DHT 11

DHT dht(DHTPIN, DHTTYPE);

void setup() {
  dht.begin();
  Serial.begin(9600);
  if (!SigFox.begin()) {
    Serial.println("Shield error or not present!");
    return;
  }

  // Enable debug led and disable automatic deep sleep
  // Comment this line when shipping your project :)
  SigFox.debug();

  // // Get the ID, PAC and version of the board
  // String version = SigFox.SigVersion();
  // String ID = SigFox.ID();
  // String PAC = SigFox.PAC();
  // // Display module informations
  // Serial.println("SigFox FW version " + version);
  // Serial.println("ID  = " + ID);
  // Serial.println("PAC = " + PAC);
  // delay(100);
  // SigFox.end();
}

void loop() {
  //  Read the ambient temperature, humidity.
  float temp = dht.readTemperature();
  float humidity = dht.readHumidity();

  String tinyTemp = "";
  String tinyHumidity = "";

  if (temp < 10.00){ tinyTemp = "0"; }

  if (humidity < 10.00){ tinyHumidity = "0"; }

  // Prepare the message
  String message = tinyTemp + String(temp) + tinyHumidity + String(humidity);

  static int counter = 0, successCount = 0, failCount = 0;  //  Count messages sent and failed.
  Serial.print(F("\nRunning loop #")); Serial.println(counter);

  // Start the module
  SigFox.begin();

  // Wait at least 30mS after first configuration
  delay(30);
  // Clears all pending interrupts
  SigFox.status();

  // Send the message to Sigfox servers
  SigFox.beginPacket();
  SigFox.print(message);
  Serial.println(message);

  int ret = SigFox.endPacket();  // send buffer to SIGFOX network
  if (ret > 0) {
    // Message wasn't sent
    Serial.println("No transmission");
    failCount++;
  } else {
    // Message was sent
    Serial.println("Transmission ok");
    successCount++;
  }
  counter++;

  // Print the status
  Serial.print(SigFox.status(SIGFOX)); Serial.print(" "); Serial.println(SigFox.status(ATMEL));
  SigFox.end();

  // Flash the LED on and off at every iteration so we know the sketch is still running.
  if (counter % 2 == 0) {
    digitalWrite(LED_BUILTIN, HIGH);  // Turn the LED on.
    delay(5);
    digitalWrite(LED_BUILTIN, LOW);   // Turn the LED off.
  } else {
    digitalWrite(LED_BUILTIN, LOW);   // Turn the LED off.
  }
  
  // Show updates every 10 messages.
  if (counter % 10 == 0) {
    Serial.print(F("Messages sent successfully: ")); Serial.print(successCount);
    Serial.print(F(", failed: ")); Serial.println(failCount);
  }

  // // Wait a while before looping. (600000 milliseconds = 10 minutes)
  // Serial.println(F("Waiting 10 minutes..."));
  // delay(600000);

  // Wait a while before looping. (3600000 milliseconds = 1 hour)
  Serial.println(F("Waiting 1 hour..."));
  delay(3600000);
}
