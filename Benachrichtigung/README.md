# Benachrichtigung

Zur Verwendung dieses Moduls als Privatperson, Einrichter oder Integrator wenden Sie sich bitte zunächst an den Autor.

Für dieses Modul besteht kein Anspruch auf Fehlerfreiheit, Weiterentwicklung, sonstige Unterstützung oder Support.  
Bevor das Modul installiert wird, sollte unbedingt ein Backup von IP-Symcon durchgeführt werden.  
Der Entwickler haftet nicht für eventuell auftretende Datenverluste oder sonstige Schäden.  
Der Nutzer stimmt den o.a. Bedingungen, sowie den Lizenzbedingungen ausdrücklich zu.

### Inhaltsverzeichnis

1. [Modulbeschreibung](#1-modulbeschreibung)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Schaubild](#3-schaubild)
4. [Externe Aktion](#4-externe-aktion)
5. [PHP-Befehlsreferenz](#5-php-befehlsreferenz)
   1. [WebFront Nachricht](#51-webfront-nachricht)
   2. [WebFront Push-Nachricht](#52-webfront-push-nachricht)
   3. [E-Mail](#53-e-mail)
   4. [SMS Nexxt Mobile](#54-sms-nexxt-mobile)
   5. [SMS Sipgate](#55-sms-sipgate)
   6. [Telegram](#56-telegram)

### 1. Modulbeschreibung

Dieses Modul versendet Benachrichtigungen.

### 2. Voraussetzungen

- IP-Symcon ab Version 6.1
- WebFront Instanz
- Mailer Instanz
- SMS Nexxt Mobile Instanz
- SMS Sipgate Instanz
- Telegram Bot Instanz

### 3. Schaubild

```
                       +--------------------------+
Auslöser <-------------| Benachrichtigung (Modul) |<------------- Externe Aktion
                       |                          |
                       | WebFront Nachricht       |-------------> WebFront Instanz
                       |                          |
                       | WebFront Push-Nachricht  |-------------> WebFront Instanz
                       |                          |
                       | E-Mail                   |-------------> Mailer Instanz -------------> SMTP Instanz
                       |                          |
                       | SMS Nexxt Mobile         |-------------> SMS Nexxt Mobile Instanz
                       |                          |
                       | SMS Sipgate              |-------------> SMS Sipgate Instanz
                       |                          |
                       | Telegram                 |-------------> Telegram Bot Instanz
                       +--------------------------+             
```

### 4. Externe Aktion

Das Modul kann über eine externe Aktion gesteuert werden.  
Nachfolgendes Beispiel versendet eine Nachricht an alle aktivierten WebFront Instanzen.
> BN_SendWebFrontNotification(12345, 'Title', 'Text', 'Warning', 0);

### 5. PHP-Befehlsreferenz

#### 5.1 WebFront Nachricht

```
BN_SendWebFrontNotification(integer INSTANCE_ID, string TITLE, string TEXT, string ICON, integer DISPLAY_DURATION);
```

Der Befehl liefert keinen Rückgabewert.

| Parameter          | Beschreibung        |
|--------------------|---------------------|
| `INSTANCE_ID`      | ID der Instanz      |
| `TITLE`            | Titel der Nachricht |
| `TEXT`             | Text der Nachricht  |
| `ICON`             | Icon                |
| `DISPLAY_DURATION` | Anzeigedauer        |

Beispiel:  
> BN_SendWebFrontNotification(12345, 'Hinweis', 'Dies ist eine Nachricht', 'Information', 0);  

---

#### 5.2 WebFront Push-Nachricht

```
BN_SendWebFrontPushNotification(integer INSTANCE_ID, string TITLE, string TEXT, string SOUND, integer TARGET_ID);
```

Der Befehl liefert keinen Rückgabewert.

| Parameter     | Beschreibung                         |
|---------------|--------------------------------------|
| `INSTANCE_ID` | ID der Instanz                       |
| `TITLE`       | Titel der Nachricht                  |
| `TEXT`        | Text der Nachricht                   |
| `SOUND`       | Icon                                 |
| `TARGET_ID`   | Objekt zu dem gesprungen werden soll |

Beispiel:
> BN_SendWebFrontPushNotification(12345, 'Hinweis', 'Dies ist eine Nachricht', 'alarm', 0);

---

#### 5.3 E-Mail

```
BN_SendMailNotification(integer INSTANCE_ID, string SUBJECT, string TEXT);
```

Der Befehl liefert keinen Rückgabewert.

| Parameter     | Beschreibung            |
|---------------|-------------------------|
| `INSTANCE_ID` | ID der Instanz          |
| `SUBJECT`     | Betreff der Nachricht   |
| `TEXT`        | Text der Nachricht      |


Beispiel:
> BN_SendMailNotification(12345, 'Hinweis', 'Dies ist eine Nachricht');

---

#### 5.4 SMS Nexxt Mobile

```
BN_SendNexxtMobileSMS(integer INSTANCE_ID, string TEXT);
```

Der Befehl liefert keinen Rückgabewert.

| Parameter     | Beschreibung            |
|---------------|-------------------------|
| `INSTANCE_ID` | ID der Instanz          |
| `TEXT`        | Text der Nachricht      |


Beispiel:
> BN_SendNexxtMobileSMS(12345, 'Dies ist eine Nachricht');

---

#### 5.5 SMS Sipgate

```
BN_SendSipgateSMS(integer INSTANCE_ID, string TEXT);
```

Der Befehl liefert keinen Rückgabewert.

| Parameter            | Beschreibung            |
|----------------------|-------------------------|
| `MODULE_INSTANCE_ID` | ID der Instanz          |
| `TEXT`               | Text der Nachricht      |


Beispiel:
> BN_SendSipgateSMS(12345, 'Dies ist eine Nachricht');

---

#### 5.6 Telegram

```
BN_SendTelegramMessage(integer INSTANCE_ID, string TEXT);
```

Der Befehl liefert keinen Rückgabewert.

| Parameter     | Beschreibung            |
|---------------|-------------------------|
| `INSTANCE_ID` | ID der Instanz          |
| `TEXT`        | Text der Nachricht      |


Beispiel:
> BN_SendTelegramMessage(12345, 'Dies ist eine Nachricht');

---


