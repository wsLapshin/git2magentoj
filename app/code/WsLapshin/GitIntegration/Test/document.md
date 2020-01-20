# MEGA 2560 ETH, R3, with PoE, ATmega2560+W5500, Micro-SD card reader, USB-UART CP2104

[Description](#description)
[Specifications](#specifications)
[User Manual](#user-manual)
[Documents](#documents)


<!-- doctype:tutorial SKU:test_sku,225db671-8d99-11e8-9100-901b0ebb3621 -->
<!-- doctype:project sku:test_sku -->

- Ошибочный тег (skip)
<!- doctype:project sku:xxxxxxxxxx_skip,yyyyyyyyyy_skip,zzzzzzzzzz_skip -->
<- doctype:project sku:xxxxxxxxxx_skip,yyyyyyyyyy_skip,zzzzzzzzzz_skip -->
<-- doctype:project sku:xxxxxxxxxx_skip,yyyyyyyyyy_skip,zzzzzzzzzz_skip -->

- Тег найден, невалидные атрибуты или символы (notice then process)

<!-- doctype:project sku:xxxxxxxxxx_not_sym,yyyyyyyyyy_not_sym,zzzzzzzzzz_not_sym -> -->
<- Найден
<!--doctype:project sku:xxxxxxxxxx4,yyyyyyyyyy5,zzzzzzzzzz6 -->

!-- doctype:project sku:xxxxxxxxxx_not_sym,yyyyyyyyyy_not_sym,zzzzzzzzzz_not_sym foo:bazz -->
<!-- doctype:project sku:xxxxxxxxxx_warn_sym, yyyyyyyyyy_warn_sym, zzzzzzzzzz_warn_sym -->

- Тег найден, отсутствуют обязательные атрибуты (notice or warn then skip)
<!-- sku:xxxxxxxxxx_warn_req,yyyyyyyyyy_warn_req,zzzzzzzzzz_warn_req -->
<!-- doctype:project -->
<!-- doctype;project sku:xxxxxxxxxx_not_sym,yyyyyyyyyy_not_sym,zzzzzzzzzz_not_sym -->
<!-- dotype:project sku:xxxxxxxxxx_not_sym,yyyyyyyyyy_not_sym,zzzzzzzzzz_not_sym -->
<!-- doctype:project su:xxxxxxxxxx_not_sym,yyyyyyyyyy_not_sym,zzzzzzzzzz_not_sym -->
<!-- some another         random comment         in document -->    

- Тег найден, ошибка типа документа (warn then skip)
<!-- doctype:not_allowed sku:xxxxxxxxxx_warn_doctype,yyyyyyyyyy_warn_doctype,zzzzzzzzzz_warn_doctype -->

- Тег найден, невалидный артикул (висячая запятая warn then skip)
<!-- doctype:project sku:xxxxxxxxxx_warn_sku,yyyyyyyyyy_warn_sku, -->

- Тег найден (для проверки включить режим $processAll, иначе споткнется на первом
<!-- doctype:project sku:xxxxxxxxxx1,yyyyyyyyyy1,zzzzzzzzzz1 -->
<!--doctype:project sku:xxxxxxxxxx2,yyyyyyyyyy2,zzzzzzzzzz2-->

<!-- doctype:project sku:xxxxxxxxxx3,yyyyyyyyyy3,zzzzzzzzzz3-->
<!--doctype:project sku:xxxxxxxxxx4,yyyyyyyyyy5,zzzzzzzzzz6 -->
<!--     doctype:project     sku:xxxxxxxxxx7,yyyyyyyyyy8,zzzzzzzzzz9    -->

<!-- sku:xxxxxxxxxx10,yyyyyyyyyy10,zzzzzzzzzz10 doctype:project -->
<!--sku:xxxxxxxxxx11,yyyyyyyyyy11,zzzzzzzzzz11 doctype:project-->

<!-- sku:xxxxxxxxxx12,yyyyyyyyyy12,zzzzzzzzzz12 doctype:project-->
<!--sku:xxxxxxxxxx13,yyyyyyyyyy13,zzzzzzzzzz13 doctype:project -->
<!--         sku:xxxxxxxxxx144,yyyyyyyyyy144,zzzzzzzzzz144           doctype:project        -->

- Дубль артикулов в теге (process then warn)
<!-- doctype:project sku:xxxxxxxxxx16,yyyyyyyyyy16,xxxxxxxxxx16 -->

- @todo Тег найден внутри текста
**If you’re looking to build a network-connected device, <!-- doctype:project sku:xxxxxxxxxx15,yyyyyyyyyy1,zzzzzzzzzz15 --> RobotDyn MEGA 2560 ETH R3 with LAN and additional Wi-Fi connection, is the best solution to use Arduino Mega 2560 R3 and Ethernet controller W5500 and with Wi-Fi module ESP-01!**

- @todo Тег не найден внутри текста (должен располагаться на одной строке, warn then skip)
**If you’re looking to build a network-connected device, <!-- doctype:project sku:xxxxxxxxxx_warn_line,yyyyyyyyyy_warn_line,
zzzzzzzzzz_warn_line --> RobotDyn MEGA 2560 ETH R3 with LAN and additional Wi-Fi connection, is the best solution to use Arduino Mega 2560 R3 and Ethernet controller W5500 and with Wi-Fi module ESP-01!**

- @todo Тег найден, невалидный артикул (  если есть формат артикула, чтобы не дергать базу на поиск невалида)
<!-- doctype:project sku:xxxxxxxxxx.yyyyyyyyyy;zzzzzzzzzz -->




## Description

### Graphical diagram

If you’re looking to build a network-connected device, RobotDyn MEGA 2560 ETH R3 with LAN and additional Wi-Fi connection, is the best solution to use Arduino Mega 2560 R3 and Ethernet controller W5500 and with Wi-Fi module ESP-01!

RobotDyn MEGA 2560 ETH R3 is a fully featured Ethernet-connected device via LAN or Wi-Fi. Depending on your use case, it can act as a server or a net member. In a server role, it will receive requests from other devices and services in local network and internet, and respond or react accordingly. In a net member role, the RobotDyn MEGA 2560 ETH R3 can collect data from different devices or industrial equipment and send it through the local network or Internet. It can also receive data from local network and internet, and control connected devices and equipment. The RobotDyn MEGA 2560 ETH R3 can also act as stand-alone unit, providing autonomous reaction based on the defined algorithm in response to network changes or other triggers.

RobotDyn MEGA 2560 ETH R3 features 5 devices on one board:

- Mega 2560 based on an ATmega2560 with 70 I/O.
- Ethernet controller based on WizNet W5500 TCP/IP.
- Optional PoE function (receiving power directly through the Ethernet cable). Can select passive PoE or active (intellect) PoE.
- Micro SD card module.
- Additional Wi-Fi module ESP-01, based on ESP8266 microcontroller.

### Ports and buses

RobotDyn MEGA 2560 ETH R3 has 70 input/output ports, 12 of which can be used for PWM, and 16 analog ports with 10-bits resolution (0 — 1023). The board is equipped with an RJ45, micro USB, DC power jack, and a reset button.

### W5500 Ethernet

WizNet W5500 is a TCP/IP embedded LAN Ethernet controller. It provides TCP/IP Stack, 10BaseT/100BaseTX Ethernet with full or half-duplex, MAC and PHY. W5500 is using a highly efficient SPI protocol, with 80 MHz clocks for high-speed connectivity to Atmega2560. For lower energy consumption, W5500 provides WOL (Wake on LAN) and Power Off modes.

### ESP-01 Wi-Fi

On board have socket for connecting Wi-Fi ESP-01 module. Wi-Fi module connecting to Serial3 interface via RX3(D15)/TX3(D14) I/O.

In Arduino code, for control a Wi-Fi module need to use Serial3 port.

### Micro-SD card reader

The board also features the Micro SD card socket. The micro SD card can be used to save and store the data, which can later be transferred over the network. Digital port D4 (CS-CS2) of the ATmega2560 is dedicated to work with the micro SD. For card detect used D9, but you can cut off soldering pads for disconnect this function.

** Digital port D10 (SS-CS2) is used by WizNet W5500. The Ethernet controller and microSD card are connected through the SPI bus.

### Programming in Arduino IDE

RobotDyn MEGA 2560 ETH R3 can be programmed via an Arduino IDE. Select Arduino Mega 2560 in the «Boards» menu, and use Ethernet3 / Ethernet4 library.

### Power supply

RobotDyn MEGA 2560 ETH R3 can be powered via:

- USB port (5V, 500mA);
- DC-IN jack, PWC 2.1mm, (supporting 7-24V DC input voltage);
- Through an Ethernet cable using PoE (Power over Ethernet) — requires PoE-enabled switch or a PoE injector. Note: PoE module is optional. There are several PoE options available, depending on the PoE equipment you use.

The Active PoE is compliant with 802.3af or 802.3at. It will check the power coming in, and, if it doesn’t meet the device requirements, it just won’t power up.

The Passive PoE is a simplified version that does not perform a handshake, so it is important to know what PoE voltage your device supplies requires before plugging in the Ethernet cable and powering it up. If you connect the wrong voltage you may cause permanent electrical damage to the device.

Make sure to select the correct board option with relevant PoE module.

### Kit Contents

1 x MEGA2560-EthernetW5500 board

### See also


## Specifications

|<!-- -->|<!-- -->|
|---|---|
|Input Voltage (VIN/DCВ jack)|7~12V|
|PowerВ IN (USB)    |5V-limit 500mAВ |
|PoE Type    |No PoE/Active PoE/Passive PoE|
|PowerВ IN (PoE) |Optional module, 48V(Input), 9V(Output)|
|Digital I/O |54|
|PWM Output  |12|
|Analog I/O  |16|
|Reserved Pins   | &bull; D4В is used forВ SD card select;<br/> &bull; D10В is used for W5500 CS;<br/> &bull; *Optional: D8В is used for W5500В interrupting, D7В is used for W5500 initialization, D9В is used forВ SD card detect|
|USB socket  |Micro-USB|
|Ethernet socket |RJ45|
|PCB Size    |53.35Г—101.61mm|
|Card Reader |Micro SDВ card, with logic level convertor|
|Weight  |63g|

### Mega MCU

|<!-- -->|<!-- -->|
|---|---|
|Microcontroller |ATmega2560(AVR 8-bit)|
|Operating Voltage   |5VВ |
|Memory Size |256KB|
|SRAM    |8KB|
|EEPROM  |4KB|
|Clock Speed |16MHz|

### Ethernet MCU

|<!-- -->|<!-- -->|
|---|---|
|Microcontroller |Wiznet W5500|
|PHY compliance  |10BaseT/100BaseTX Ethernet. Full and half duplexВ|
|Operating Voltage   |3.3V|
|Memory  |Internal 32Kbytes Memory for Tx/Rx Buffers|
|TCP/IP Protocols    |TCP, UDP, ICMP, IPv4, ARP, IGMP, PPPoE|
|PHY Clock Speed |25MHz|

### Passive PoE(optional)

|<!-- -->|<!-- -->|
|---|---|
|Ethernet Power (IN) |12~48V DC|
|Output  |DCВ 9V В|

### Active PoE(optional)

|<!-- -->|<!-- -->|
|---|---|
|Ethernet Power (IN) |12~48V DC|
|Output  |DCВ 12V|

### WiFi(optional)

|<!-- -->|<!-- -->|
|---|---|
|WiFI module |ESP-01(ESP8266)|
|Reserved Pins   | &bull; D14/TX3<br/> &bull; D15/RX3|


## User Manual

### I/O defines on Mega ETH

For Ethernet W5500 Chipset:

* D10 — SS (CS1)
* D51 — MOSI
* D50 — MISO
* D52 — SCK
* D8 — INT (select D8 with W5500-INT pin jumper, pin solder jumper at bottom layer, default: not connected)
D7/3.3V — RST (select D7 with W5500-RST pin solder jumper at the bottom layer, default: connected to 3.3V)

For SD card reader:

* D4 — CS(CS2)
* D9 – Card detect (pin solder jumper at bottom layer, default: not connected)
* D51 — MOSI/DI
* D50 — MISO/DO
* D52 — SCK/CLK

**NOTE:** Both W5500 and SD card communicate with ATmega2560 via SPI bus. Pin D10 and pin D4 are chip Selection pins for W5500 and SD slot. They cannot be used as general I/O.

**Steps for Arduino IDE:**

1. Select port: 
![](MEGA2560-EthernetW5500/en/port.jpg)
2. Select board: 
![](MEGA2560-EthernetW5500/en/port.jpg)

Please install the libraries:

* SD.h — [SD Library](https://www.arduino.cc/en/Reference/SD)
* SPI.h — [SPI library](https://www.arduino.cc/en/Reference/SD)
* Ethernet.h/Ethernet2.h/ Ethernet3.h — Ethernet / Ethernet2 / Ethernet3 libraries

**Copy below code into the sketch and then upload:**

```cpp
#include <Ethernet3.h> 
#include <SPI.h>
 
#include <EEPROM.h>
#include <SD.h>
#define SS 10    //W5500 CS
#define RST 7    //W5500 RST
#define CS 4     //SD CS pin
 
// enter MAC-address and IP-address of your controller below;
// IP-address depends on your local network:
byte mac[] = {0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
char macstr[18];
IPAddress ip(192,168,1,111);
 
// initialize Ethernet Server library by designating a port
// (default port for HTTP is «80»):
EthernetServer server(80);
 
// specifying a contact and default state for LED:
String LED1State = "Off";
const int LED = 13;
 
// specifying variables for the client:
char linebuf[80];
int charcount=0;
 
void eeprom_read()
{
  if (EEPROM.read(1) == '#') 
  {
    for (int i = 2; i < 6; i++) 
    {
       mac[i] = EEPROM.read(i);
    }
  }
}
 
void eeprom_write()
{
   EEPROM.write(1, '#');
   snprintf(macstr, 18, "%02x:%02x:%02x:%02x:%02x:%02x", mac[0], mac[1], mac[2], mac[3], mac[4], mac[5]);
}
 
void setup() {
  // preparing LED-module:
 
  pinMode(LED_BUILTIN, OUTPUT);
  digitalWrite(LED_BUILTIN, HIGH);
  pinMode(SS, OUTPUT);
  pinMode(RST, OUTPUT);
  pinMode(CS, OUTPUT);
  digitalWrite(SS, LOW);
  digitalWrite(CS, HIGH);
  /* If you want to control Reset function of W5500 Ethernet controller */
  digitalWrite(RST,HIGH);
 
  pinMode(LED, OUTPUT);
  digitalWrite(LED, HIGH);
 
  // opening a  sequential communication with 9600 baud speed:
  Serial.begin(9600);
 
  eeprom_read();
  eeprom_write();
 
  // initialising Ethernet-communication and server:
  Ethernet.begin(mac, ip);
  server.begin();
  Serial.print("server is at ");  //  "server at "
  Serial.println(Ethernet.localIP());
  Serial.println(Ethernet.macAddressReport());
}
 
// Displaying a webpage with a «ON/OFF» button for LED:
void dashboardPage(EthernetClient &client) {
  client.println("<!DOCTYPE HTML><html><head>");
  client.println("<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"></head><body>");                                                            
  client.println("<h3>Arduino Web Server - <a href=\"/\">Refresh</a></h3>");
  client.println("<h3>local IP<h3>");
  client.println(Ethernet.localIP());
  client.println("<h3>");
  client.println("<h3>mac Address<h3>");
  client.println(Ethernet.macAddressReport());
   client.println("<h3>");
  client.println("<h3>TEXT<h3>");
  client.println("<section id=\"contact\"><div class=\"content\"><div id=\"form\"><form action=""id=\"contactForm\"method=\"GET\"><textarea class=\"message\"placeholder=\"Enter your message\"tabindex=4></textarea><input type=\"submit\"name=\"submit\"value=\"Send to Serial\"class=\"submit\"tabindex=5></form></div></section>");
  // generating a button to control LED:
  client.println("<h4>LED 13 - State: " + LED1State + "</h4>");
  // if LED is off, Displaying an «ON» button:          
  if(LED1State == "Off"){
    client.println("<a href=\"/LED13on\"><button>ON</button></a>");
  }
  // if LED is on, Displaying an «OFF» button:
  else if(LED1State == "On"){
    client.println("<a href=\"/LED13off\"><button>OFF</button></a>");                                                                    
  }
  client.println("</body></html>");
}
 
 
void loop() {
  // reading the incoming clients:
  EthernetClient client = server.available();
  if (client) {
    //Serial.print (client.read());
    //Serial.println("new client");  //  "new client"
    memset(linebuf,0,sizeof(linebuf));
    charcount=0;
    // HTTP-request is ending with blank line:
    boolean currentLineIsBlank = true;
    while (client.connected()) {
      if (client.available()) {
       char c = client.read();
        // reading a HTTP-request, one symbol at a time:
        linebuf[charcount]=c;
        if (charcount<sizeof(linebuf)-1) charcount++;
        // if you reached the end of the line (i.e. if you recieved
        // symbol form a new line), it means that
        // HTTP-request is completed, and you can send the answer:
 
        if (c == '\n' && currentLineIsBlank) {
          dashboardPage(client);
          break;
        }
 
        if (c == '\n') {
          if (strstr(linebuf,"GET /id=") > 0)Serial.println(linebuf);         
          if (strstr(linebuf,"GET /LED13off") > 0){
            digitalWrite(LED, HIGH);
            LED1State = "Off";
          }
          else if (strstr(linebuf,"GET /LED13on") > 0){
            digitalWrite(LED, LOW);
            LED1State = "On";
          }
          // if you recieved a symbol form a new line
          currentLineIsBlank = true;
          memset(linebuf,0,sizeof(linebuf));
          charcount=0;          
        }
        else if (c != '\r') {
          // if you recieved any other symbol
          currentLineIsBlank = false;
        }
      }
    }
    // providing a time for a borwser to recieve the data:
    delay(1);
    // closing the connection:
    client.stop();
    //Serial.println("client disonnected");  //  "Client is disconnected"
  }
}
```

Result:

![](MEGA2560-EthernetW5500/en/result111_3.jpg)

## Documents

* Dimensional drawing (DIM): [JPG](https://robotdyn.com/pub/media/GR-00000027==MEGA2560-EthernetW5500/DOCS/DIM==GR-00000027==MEGA2560-EthernetW5500.jpg) [PDF](https://robotdyn.com/pub/media/GR-00000027==MEGA2560-EthernetW5500/DOCS/DIM==GR-00000027==MEGA2560-EthernetW5500.pdf)
* Input and Output (I/O) diagram: [JPG](https://robotdyn.com/pub/media/GR-00000027==MEGA2560-EthernetW5500/DOCS/PINOUT==GR-00000027==MEGA2560-EthernetW5500.jpg) [PDF](https://robotdyn.com/pub/media/GR-00000027==MEGA2560-EthernetW5500/DOCS/PINOUT==GR-00000027==MEGA2560-EthernetW5500.pdf)
* Schematic: [PDF](https://robotdyn.com/pub/media/GR-00000027==MEGA2560-EthernetW5500/DOCS/Schematic==GR-00000027==MEGA2560-EthernetW5500.pdf)
* Arduino Lib Ethernet3: [Github](https://github.com/sstaub/Ethernet3)