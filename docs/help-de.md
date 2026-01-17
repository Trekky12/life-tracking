# Hilfe

- [Allgemein](#general)
- [Modul: Standortverlauf](#locationhistory)
- [Modul: Finanzen](#finances)
- [Modul: Autos](#cars)
- [Modul: Boards](#boards)
- [Modul: Crawlers](#crawlers)
- [Modul: geteilte Ausgaben](#splittedbills)
- [Modul: Reisen](#trips)
- [Modul: Zeiterfassung](#timesheets)
- [Modul: Workouts](#workouts)
- [Modul: Rezepte](#recipes)
- [Spezielle Admin-Funktionen](#admin)

## <a name="general"></a>Allgemein

Die Anmeldung erfolgt mit dem Benutzernamen und dem Passwort. Der "Code" ist für die Zweifaktor-Authentifizierung und muss nur angegeben werden, wenn diese aktiviert wurde.

<a href="../public/static/help/de/1_login.jpg"><img src="../public/static/help/de/1_login.jpg" style="width: 300px"></a>


### Startseite

Nach der Anmeldung wird die Standard-Startseite angezeigt. Die Startseite lässt sich individuell mit Widgets anpassen.  
Dazu muss zuerst der Nutzername im Menü angeklickt und anschließend "Startseite" gewählt werden.

Anschließend können verschiedene Widgets hinzugefügt werden. Wenn ein Widget noch weitere Eingaben benötigt, müssen diese im entsprechenden Fenster eingetragen werden.

Die Widgets können durch Klicken auf den Titel und Ziehen der Maus verschoben werden. Über die Symbole können Einstellungen angepasst oder das Widget entfernt werden.


<a href="../public/static/help/de/2_frontpage.jpg"><img src="../public/static/help/de/2_frontpage.jpg" style="width: 300px"></a>


<a href="../public/static/help/de/2_frontpage-1.jpg"><img src="../public/static/help/de/2_frontpage-1.jpg" style="width: 300px"></a>


<a href="../public/static/help/de/2_frontpage-2.jpg"><img src="../public/static/help/de/2_frontpage-2.jpg" style="width: 300px"></a>


<a href="../public/static/help/de/2_frontpage-3.jpg"><img src="../public/static/help/de/2_frontpage-3.jpg" style="width: 300px"></a>


### Profil

Durch Klick auf den Benutzernamen kann das eigene Profil angepasst werden. Dabei können verschiedene Module zugeordnet werden.

Unterhalb des Benutzernamens finden sich weitere Menüeinträge zur Passwortänderung, zum Einstellen des Profilbilds, zur Aktivierung der Zweifaktor-Authentifizierung, zur Anzeige der eingeloggten Clients (Login Tokens) und zur Anzeige des Aktivitätenverlaufs.

<a href="../public/static/help/de/2_profile.jpg"><img src="../public/static/help/de/2_profile.jpg" style="width: 300px"></a>


<a href="../public/static/help/de/2_password.jpg"><img src="../public/static/help/de/2_password.jpg" style="width: 300px"></a>


<a href="../public/static/help/de/2_profilepic.jpg"><img src="../public/static/help/de/2_profilepic.jpg" style="width: 300px"></a>


<a href="../public/static/help/de/2_twofactor.jpg"><img src="../public/static/help/de/2_twofactor.jpg" style="width: 300px"></a>


<a href="../public/static/help/de/2_logintokens.jpg"><img src="../public/static/help/de/2_logintokens.jpg" style="width: 300px"></a>


<a href="../public/static/help/de/2_activities.jpg"><img src="../public/static/help/de/2_activities.jpg" style="width: 300px"></a>


### Anwendungspasswörter

Anwendungspasswörter sind notwendig, damit Anwendungen auf spezielle Funktionen automatisiert zugreifen können.

Diese Funktionen umfassen:

  - Übermitteln des Standortverlaufs
  - Übertragen von Crawler-Daten
  - Auslösen von Benachrichtigungen für spezielle Kategorien

Anwendungspasswörter müssen jeweils angelegt und der Login muss dann mit dem Benutzernamen und dem Anwendungspasswort erfolgen.


<a href="../public/static/help/de/2_applicationpasswords.jpg"><img src="../public/static/help/de/2_applicationpasswords.jpg" style="width: 300px"></a>


<a href="../public/static/help/de/2_applicationpasswords-1.jpg"><img src="../public/static/help/de/2_applicationpasswords-1.jpg" style="width: 300px"></a>


### Web App

Die Anwendung kann als Progressive Web App (PWA) auf unterstützen Geräten installiert werden.

Dies kann in Chrome für Android beispielsweise im Menü unter "App installieren" oder über den Banner "App zum Startbildschirm hinzufügen" erfolgen.

<a href="../public/static/help/101_PWA-1.jpg"><img src="../public/static/help/101_PWA-1.jpg" style="height: 300px"></a>


Über die Profileinstellungen (Start URL) lässt sich festlegen, welche Unterseite beim Aufruf über die Progressive Web App gestartet werden soll. So lässt sich die Startseite für eine häufig verwendete Seite festlegen.

Die Seite muss dabei ohne den Domainnamen festgelegt werden, beispielsweise `/finances` für die Übersichtsseite des Finanzmoduls.

### mobile Favoriten

Zusätzlich zur Start URL lassen sich mobile Favoriten festlegen.

Hierzu können in der mobilen Ansicht die Symbole in der Navigationsleiste individuell festgelegt werden.

Die Reihenfolge wird über die numerische Position festgelegt. Das Icon muss der Bezeichnung eines Fontawesome-Icons (<https://fontawesome.com/icons?d=gallery&m=free>) entsprechen. Die URL ist analog zur Start URL relativ zur Domain anzugeben.

Das Beispiel-Icon der Uhr (`fas fa-clock`) ist [hier](https://fontawesome.com/icons/clock?style=solid) aufrufbar.

<a href="../public/static/help/de/2_mobilefavorites.jpg"><img src="../public/static/help/de/2_mobilefavorites.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/2_mobilefavorites-1.jpg"><img src="../public/static/help/de/2_mobilefavorites-1.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/2_mobilefavorites-3.jpg"><img src="../public/static/help/de/2_mobilefavorites-3.jpg" style="width: 300px"></a>

### Benachrichtigungen

Es ist es möglich sich über verschiedene Ereignisse per E-Mail benachrichtigen zu lassen. Zusätzlich können für verschiedene Aktionen innerhalb der Seite und für individuelle Benachrichtigungskategorien Benachrichtigungen abonniert werden.

Alle Events können als Benachrichtigung innerhalb der Webseite und/oder als Push Benachrichtigung abonniert werden.

Interne Benachrichtigungen können über die Klingel im Menü aufgerufen werden und werden automatisch als gelesen markiert, sobald die Seite aufgerufen wurde.

Push Benachrichtigungen müssen innerhalb des verwendeten Browsers unterstützt werden und können dann direkt abonniert werden.


<a href="../public/static/help/de/2_notifications.jpg"><img src="../public/static/help/de/2_notifications.jpg" style="width: 300px"></a>


<a href="../public/static/help/de/2_notifications-1.jpg"><img src="../public/static/help/de/2_notifications-1.jpg" style="width: 300px"></a>


<a href="../public/static/help/de/2_notifications-2.jpg"><img src="../public/static/help/de/2_notifications-2.jpg" style="width: 300px"></a>


Unterstützt ein Gerät keine Web Push-Benachrichtigungen (z.B. iOS) kann alternativ die IFTTT App für Benachrichtigungen verwendet werden.

Hierzu muss ein IFTTT Account angelegt werden und die IFTTT App auf dem Gerät installiert werden. Anschließend muss der Service "Webhooks" aktiviert und ein neues Applet angelegt werden.

Die Web-Hook URL für das angelegte Applet muss dann in das entsprechende Feld der Webseite eingetragen werden.


<a href="../public/static/help/100_IFTTT-1.png"><img src="../public/static/help/100_IFTTT-1.png" style="width: 300px"></a>


<a href="../public/static/help/100_IFTTT-2.png"><img src="../public/static/help/100_IFTTT-2.png" style="width: 300px"></a>


<a href="../public/static/help/100_IFTTT-3.png"><img src="../public/static/help/100_IFTTT-3.png" style="width: 300px"></a>


<a href="../public/static/help/100_IFTTT-4.png"><img src="../public/static/help/100_IFTTT-4.png" style="width: 300px"></a>


<a href="../public/static/help/100_IFTTT-5.png"><img src="../public/static/help/100_IFTTT-5.png" style="width: 300px"></a>


<a href="../public/static/help/100_IFTTT-6.png"><img src="../public/static/help/100_IFTTT-6.png" style="width: 300px"></a>


<a href="../public/static/help/100_IFTTT-7.png"><img src="../public/static/help/100_IFTTT-7.png" style="width: 300px"></a>


<a href="../public/static/help/100_IFTTT-8.png"><img src="../public/static/help/100_IFTTT-8.png" style="width: 300px"></a>


<a href="../public/static/help/100_IFTTT-9.png"><img src="../public/static/help/100_IFTTT-9.png" style="width: 300px"></a>

<a href="../public/static/help/100_IFTTT-10.png"><img src="../public/static/help/100_IFTTT-10.png" style="width: 300px"></a>

<a href="../public/static/help/100_IFTTT-11.png"><img src="../public/static/help/100_IFTTT-11.png" style="width: 300px"></a>

<a href="../public/static/help/100_IFTTT-12.png"><img src="../public/static/help/100_IFTTT-12.png" style="width: 300px"></a>

<a href="../public/static/help/100_IFTTT-13.png"><img src="../public/static/help/100_IFTTT-13.png" style="width: 300px"></a>

<a href="../public/static/help/100_IFTTT-14.png"><img src="../public/static/help/100_IFTTT-14.png" style="width: 300px"></a>

<a href="../public/static/help/100_IFTTT-15.png"><img src="../public/static/help/100_IFTTT-15.png" style="width: 300px"></a>

<a href="../public/static/help/100_IFTTT-16.png"><img src="../public/static/help/100_IFTTT-16.png" style="width: 300px"></a>

<a href="../public/static/help/de/2_notifications-3.jpg"><img src="../public/static/help/de/2_notifications-3.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/2_notifications-4.jpg"><img src="../public/static/help/de/2_notifications-4.jpg" style="width: 300px"></a>

## <a name="locationhistory"></a>Modul Standortverlauf

Beim Modul Standortverlauf können alle erfassten Standorte angezeigt werden. Dabei lassen sich Standorte nach Datum filtern.  
Außerdem können verschiedene Filter (rechter Rand) eingesetzt werden:

  - Standorte gruppiert anzeigen
  - Allgemeine Standorte anzeigen
  - Standorte im Zusammenhang mit Finanzeinträgen anzeigen
  - Standorte im Zusammenhang mit Autos anzeigen
  - Allgemeine Standorte anhand des Datums mit Linien verbinden

<a href="../public/static/help/de/3_location_history.jpg"><img src="../public/static/help/de/3_location_history.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/3_location_history-2.jpg"><img src="../public/static/help/de/3_location_history-2.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/3_location_history-3.jpg"><img src="../public/static/help/de/3_location_history-3.jpg" style="width: 300px"></a>

### Standorte hinzufügen

Es ist möglich allgemeine Standorte manuell oder automatisch zu erfassen.

#### Manuell hinzufügen

Um Standorte manuell hinzufügen zu können muss im Menü "Ort hinzufügen" gewählt werden. Anschließend kann der Standort festgelegt werden.

<a href="../public/static/help/de/3_location_history-4.jpg"><img src="../public/static/help/de/3_location_history-4.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/3_location_history-1.jpg"><img src="../public/static/help/de/3_location_history-1.jpg" style="width: 300px"></a>

#### Standort automatisch hinzufügen

Es ist möglich verschiedene Daten regelmäßig an die Web Anwendung automatisiert zu übermitteln.

Dazu muss mindestens der Standort als kommaseparierte Liste mit Latitude und Longitude als Wert `gps_loc` übermittelt werden:

``` 
curl --request POST \
  --data 'gps_loc=10,20' \
  http://<user>:<password>@<domain>/api/location/record
            
```

Mit Hilfe der Android App "Tasker" lassen sich Standortdaten und Schritte regelmäßig automatisiert übermitteln.  
Hierzu lassen sich beispielsweise folgende Profile nutzen:

#### Schritte zählen

``` 
Profil: Schritte (14)
        Ereignis: Ausgeführte Schritte [ Nummer:1 ]
    Eingang: Schritte (23)
        A1: Variable Setzen [ Name:%STEPS Zu:%evtprm1 Recurse Variables:Aus Mathematisch:Aus Hinzufügen:Aus Max Rounding Digits:3 ]
            
```

#### Schritte zurücksetzen

``` 
Profil: Schritte zurücksetzen (21)
        Variables: [  ]
        Zeit: 00:00
    Eingang: Schritte Reset (22)
        A1: Profil Status [ Name:Schritte setzen:Aus ] 
        A2: Profil Status [ Name:Schritte setzen:An ] 
        A3: Variable Setzen [ Name:%STEPS Zu:0 Recurse Variables:Aus Mathematisch:Aus Hinzufügen:Aus Max Rounding Digits:3 ]
            
```

#### Standort erfassen

``` 
    Profil: Standort erfassen (2)
        Zeit:  Jede 30m
    Eingang: Standort senden 2 (19)
        A1: If [ %TIMES > %LASTLOC+600 ]
        A2: Get Location v2 [  Timeout (Sekunden):20 Minimum Accuracy (meters): Speed (meters/second): Altitude (meters): Near Location: Enable Location If Needed:Aus Last Location If Timeout:Aus Min Speed Accuracy (m/s): ] 
        A3: HTTP Auth [  Method:Username and Password Client ID: Client Secret: Endpoint To Get Code: Endpoint To Get Refresh Token: Scopes: Force Re-Authentication:Aus Timeout (Sekunden):30 Username:my-username Password:my-application-password ] 
        A4: HTTP Request [  Method:POST URL:my-url/api/location/record Headers:%http_auth_headers Query Parameters: Body:{
    "identifier":"my-device", 
    "device":"%DEVID",
    "date":"%DATE",
    "time":"%TIME",
    "batt":"%BATT",
    "times":"%TIMES",
    "wifi_state":"%WIFI",
    "gps_state":"%GPS",
    "mfield":"%MFIELD",
    "screen_state":"%SCREEN",
    "ups":"%UPS",
    "gps_loc":"%gl_coordinates",
    "gps_acc":"%gl_coordinates_accuracy",
    "gps_alt":"%gl_altitude",
    "gps_alt_acc":"%gl_altitude_accuracy",
    "gps_spd":"%gl_speed",
    "gps_spd_acc":"%gl_speed_accuracy",
    "gps_bearing":"%gl_bearing",
    "gps_bearing_acc":"%gl_bearing_accuracy", 
    "gps_tms":"%gl_time_seconds",
    "cell_id":"%CELLID",
    "cell_sig":"%CELLSIG",
    "cell_srv":"%CELLSRV", 
    "steps":"%STEPS" 
    } File To Send: File/Directory To Save With Output: Timeout (Sekunden):10 Trust Any Certificate:Aus Automatically Follow Redirects:Aus Use Cookies:Aus ] 
        A5: If [ %http_response_code = 200 ]
        A6: Variable Setzen [ Name:%LASTLOC Zu:%TIMES Recurse Variables:Aus Mathematisch:Aus Hinzufügen:Aus Max Rounding Digits:3 ] 
        A7: Else 
        A8: Benachrichtigung [ Titel:Response %http_response_code Text:%http_data Icon:null Nummer:0 Dauerhaft:Aus Priorität:3 Repeat Alert:Aus LED Colour:Lila LED Rate:0 Sound Datei: Vibration Pattern: Category:test ] 
            
```

### Schritte Statistik

Wenn automatisiert die Schritte übermittelt werden, dann lässt sich darüber eine Statistik anzeigen. Dabei lassen sich die Schritte pro Tag auch manuell korrigieren.

<a href="../public/static/help/de/3_location_history-5.jpg"><img src="../public/static/help/de/3_location_history-5.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/3_location_history-6.jpg"><img src="../public/static/help/de/3_location_history-6.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/3_location_history-7.jpg"><img src="../public/static/help/de/3_location_history-7.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/3_location_history-8.jpg"><img src="../public/static/help/de/3_location_history-8.jpg" style="width: 300px"></a>

## <a name="finances"></a>Modul Finanzen

Mit dem Modul Finanzen lassen sich Einnahmen und Ausgaben verwalten. Hierzu ist es möglich Einnahmen und Ausgaben zu erfassen.

<a href="../public/static/help/de/4_finances.jpg"><img src="../public/static/help/de/4_finances.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-1.jpg"><img src="../public/static/help/de/4_finances-1.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-2.jpg"><img src="../public/static/help/de/4_finances-2.jpg" style="width: 300px"></a>

### Kategorien verwalten und Einnahmen/Ausgaben kategorisieren

Einem Finanzeintrag kann eine Kategorie hinzugefügt werden. Die verfügbaren Kategorien lassen sich individuell festlegen.

Die Standard-Kategorie wird beim Anlegen eines Finanzeintrags automatisch vorselektiert.

<a href="../public/static/help/de/4_finances-3.jpg"><img src="../public/static/help/de/4_finances-3.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-4.jpg"><img src="../public/static/help/de/4_finances-4.jpg" style="width: 300px"></a>

### Kategorien automatisch anhand des Namens zuordnen lassen

Über den Namen und den Betrag eines Finanzeintrags lassen sich auch automatisiert Kategorien zuweisen.

Hierzu können verschiedene Regeln festgelegt werden, wann einem Finanzeintrag eine bestimmte Kategorie zugewiesen werden soll.

<a href="../public/static/help/de/4_finances-5.jpg"><img src="../public/static/help/de/4_finances-5.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-6.jpg"><img src="../public/static/help/de/4_finances-6.jpg" style="width: 300px"></a>

### wiederkehrende Einnahmen und Ausgaben automatisch erfassen

Wiederkehrende Einnahmen oder Ausgaben können automatisiert angelegt werden.

Hierzu müssen diese Finanzeinträge, das Start-/Enddatum und die Intervalle (alle X Tage/Wochen/Monate/Jahre) festgelegt werden.

Jeden Tag um 6 Uhr werden die fälligen Finanzeinträge dann automatisch angelegt.

<a href="../public/static/help/de/4_finances-7.jpg"><img src="../public/static/help/de/4_finances-7.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-8.jpg"><img src="../public/static/help/de/4_finances-8.jpg" style="width: 300px"></a>

### Zahlungsmethoden verwalten

Einzelnen Finanzeinträgen können Zahlungsmethoden hinterlegt werden.

Die Standard-Zahlungsmethode wird beim Anlegen eines Finanzeintrags automatisch vorselektiert.

Zusätzlich kann "Kleingeld-Sparen" aktiviert werden. Dabei wird der mit dieser Zahlungsmethode eingetragene Wert auf 1€/5€ aufgerundet und dieser Rest als Guthaben auf das festgelegte Konto gebucht.

<a href="../public/static/help/de/4_finances-9.jpg"><img src="../public/static/help/de/4_finances-9.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-10.jpg"><img src="../public/static/help/de/4_finances-10.jpg" style="width: 300px"></a>

### Konten verwalten

Zahlungsmethoden sind Konten zugeordnet. Beim Erstellen eines Finanzeintrags wird automatisch eine entsprechende Buchung auf dem Konto des gewählten Zahlungsmittels durchgeführt.

<a href="../public/static/help/de/4_finances-25.jpg"><img src="../public/static/help/de/4_finances-25.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-31.jpg"><img src="../public/static/help/de/4_finances-31.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-26.jpg"><img src="../public/static/help/de/4_finances-26.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-32.jpg"><img src="../public/static/help/de/4_finances-32.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-33.jpg"><img src="../public/static/help/de/4_finances-33.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-27.jpg"><img src="../public/static/help/de/4_finances-27.jpg" style="width: 300px"></a>

Zusätzlich lassen sich auch wiederkehrende Buchungen festlegen

<a href="../public/static/help/de/4_finances-28.jpg"><img src="../public/static/help/de/4_finances-28.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-29.jpg"><img src="../public/static/help/de/4_finances-29.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-30.jpg"><img src="../public/static/help/de/4_finances-30.jpg" style="width: 300px"></a>

### monatliches Budget verwalten

Es kann ein monatliches Budget für unterschiedliche Gruppen festgelegt werden.

Hierzu muss eine Beschreibung und die zugeordneten Gruppen ausgewählt werden. Alle Ausgaben in anderen Gruppen werden dem Budget "Rest" (Name änderbar) zugeordnet.

Einzelne Budgeteinträge können auch aus der Gesamtübersicht ausgeblendet werden.

Das monatliche Budget und der aktuelle Stand innerhalb des Monats lassen sich dann in der Übersicht anzeigen.  
Das Budget wird immer beim Anlegen und Aktualisieren einer Ausgabe angezeigt.

Das aktuelle Budget wird immer temporär berechnet und ist daher immer nur für den aktuellen Monat verfügbar.

<a href="../public/static/help/de/4_finances-11.jpg"><img src="../public/static/help/de/4_finances-11.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-12.jpg"><img src="../public/static/help/de/4_finances-12.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-13.jpg"><img src="../public/static/help/de/4_finances-13.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-14.jpg"><img src="../public/static/help/de/4_finances-14.jpg" style="width: 300px"></a>

### verschiedene Statistiken

Im Modul Finanzen sind verschiedene Statistiken aufrufbar.

Eine Gesamtübersicht über die Finanzen aller Jahre lässt sich direkt aufrufen.  
Durch den Klick auf das Chart-Symbol lassen sich Details der Einnahmen/Ausgaben eines Jahres anzeigen

<a href="../public/static/help/de/4_finances-15.jpg"><img src="../public/static/help/de/4_finances-15.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-20.jpg"><img src="../public/static/help/de/4_finances-20.jpg" style="width: 300px"></a>

Innerhalb eines Jahres kann durch Klick auf den Wert der Einnahmen oder Ausgaben eines Monats eine Übersicht angezeigt werden.

<a href="../public/static/help/de/4_finances-21.jpg"><img src="../public/static/help/de/4_finances-21.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-23.jpg"><img src="../public/static/help/de/4_finances-23.jpg" style="width: 300px"></a>

Innerhalb der Einnahmen oder Ausgaben eines Monats kann einer Übersicht über die Finanzeinträge der einzelnen Kategorien über das Chart-Symbol angezeigt werden.

<a href="../public/static/help/de/4_finances-22.jpg"><img src="../public/static/help/de/4_finances-22.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-24.jpg"><img src="../public/static/help/de/4_finances-24.jpg" style="width: 300px"></a>

Die Übersicht über Einnahmen und Ausgaben eines Jahres lassen sich in der Jahresübersicht durch Klick auf den Wert der Einnahmen oder Ausgaben eines Jahres anzeigen.

<a href="../public/static/help/de/4_finances-16.jpg"><img src="../public/static/help/de/4_finances-16.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-18.jpg"><img src="../public/static/help/de/4_finances-18.jpg" style="width: 300px"></a>

Die Übersicht der Kategorien der Finanzeinträge eines Jahres kann über einen Klick auf Chart-Symbol angezeigt werden.

<a href="../public/static/help/de/4_finances-17.jpg"><img src="../public/static/help/de/4_finances-17.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/4_finances-19.jpg"><img src="../public/static/help/de/4_finances-19.jpg" style="width: 300px"></a>

### monatliche Statistiken per E-Mail

Es ist möglich eine monatliche Statistik der Einnahmen und Ausgaben des vergangenen Monats zu erhalten. Diese E-Mail wird immer am 1. des Monats um 8 Uhr versendet.

<a href="../public/static/help/100_finances-25.png"><img src="../public/static/help/100_finances-25.png" style="width: 300px"></a>

## <a name="cars"></a>Modul Autos

Im Modul Autos lassen sich Tanken und Wartungen erfassen und verschiedene Statistiken anzeigen.

<a href="../public/static/help/de/5_cars.jpg"><img src="../public/static/help/de/5_cars.jpg" style="width: 300px"></a>

### Autos verwalten

Die verfügbaren Autos lassen sich individuell festlegen und für andere Nutzer freischalten, so dass auch mehrere Nutzer Daten zu einem Auto erfassen können.

Für die Berechnung der Laufleistung können die möglichen Kilometer pro Jahr, die Laufzeit, das Startdatum für die Berechnung der aktuell möglichen Kilometer und der Start Kilometerstand festgelegt werden.

<a href="../public/static/help/de/5_cars-1.jpg"><img src="../public/static/help/de/5_cars-1.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/5_cars-2.jpg"><img src="../public/static/help/de/5_cars-2.jpg" style="width: 300px"></a>

### Tanken erfassen und Benzinverbrauch berechnen

Nach dem Tanken können die Daten erfasst werden. Wenn "Spritverbrauch berechnen" ausgewählt wird, dann wird der Spritverbrauch seit dem letzten Volltanken automatisch berechnet.

<a href="../public/static/help/de/5_cars-3.jpg"><img src="../public/static/help/de/5_cars-3.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/5_cars-5.jpg"><img src="../public/static/help/de/5_cars-5.jpg" style="width: 300px"></a>

### Wartungen erfassen

Zur Dokumentation lassen sich auch Wartungen erfassen. Dabei können der Ölstand, das Scheibenwischwasser und der Luftdruck erfasst werden. Außerdem lässt sich dokumentieren ob ein Werkstattbesuch oder ein Reifenwechsel stattgefunden hat.

<a href="../public/static/help/de/5_cars-4.jpg"><img src="../public/static/help/de/5_cars-4.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/5_cars-6.jpg"><img src="../public/static/help/de/5_cars-6.jpg" style="width: 300px"></a>

### Benzinverbrauchsstatistik und Laufleistung

Der Verlauf des Benzinverbrauchs lässt sich graphisch darstellen.

Zusätzlich lässt sich die Laufleistung bei Leasing oder Finanzierung eines Autos anzeigen.  
Hierzu wird anhand des zuletzt erfassten Kilometerstands (Tanken oder Wartung), dem Start der Laufzeit, der Laufzeit in Jahren und dem Start Kilometerstand der heutige maximale Kilometerstand angezeigt.

<a href="../public/static/help/de/5_cars-7.jpg"><img src="../public/static/help/de/5_cars-7.jpg" style="width: 300px"></a>

## <a name="boards"></a>Modul Boards

Im Modul Boards lassen sich Aufgaben über die Methode "Kanban" verwalten.

Hierzu können mehrere Nutzer zu Boards eingeladen werden.

Innerhalb eines Boards können Stapel mit Karten angelegt werden. Einzelnen Karten können Label und Nutzer zugeordnet werden. Außerdem lassen sich Fälligkeitsdaten und eine Beschreibung eingeben.

Karten und Stapel lassen sich löschen oder archivieren.

<a href="../public/static/help/de/6_boards.jpg"><img src="../public/static/help/de/6_boards.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/6_boards-1.jpg"><img src="../public/static/help/de/6_boards-1.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/6_boards-2.jpg"><img src="../public/static/help/de/6_boards-2.jpg" style="width: 300px"></a>

## <a name="crawlers"></a>Modul Crawlers

Das Modul Crawlers ermöglicht es eigene Listen automatisiert anzulegen und zu befüllen. Dies kann beispielsweise zum automatisierten Abrufen von Informationen von Webseiten (Crawling) genutzt werden.

Die angezeigten Daten können dann nach Abrufdatum gefiltert werden. Außerdem lässt sich anzeigen ob nur neue Daten (anhand des Abrufdatums) oder alle Informationen (anhand des Abruf- und Aktualisierungsdatums) angezeigt werden.

<a href="../public/static/help/de/7_crawlers-7.jpg"><img src="../public/static/help/de/7_crawlers-7.jpg" style="width: 300px"></a>

Über einen Klick auf den Stern können Einträge auf die Merkliste gesetzt werden. Die Merkliste speichert alle markierten Einträge.

<a href="../public/static/help/de/7_crawlers-8.jpg"><img src="../public/static/help/de/7_crawlers-8.jpg" style="width: 300px"></a>

### Crawler verwalten

Damit Informationen als Listen angezeigt werden können muss ein Crawler angelegt werden. Dabei kann ein Standard-Filter gesetzt werden.

<a href="../public/static/help/de/7_crawlers.jpg"><img src="../public/static/help/de/7_crawlers.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/7_crawlers-1.jpg"><img src="../public/static/help/de/7_crawlers-1.jpg" style="width: 300px"></a>

### Datensatz hinzufügen

Ein neuer Datensatz kann über die Web Schnittstelle wie folgt hinzugefügt werden.

``` 
curl --header "Content-Type: application/json" \
  --request POST \
  --data '{"crawler": "<hash>", "identifier":"offer1", "data":{"name":"job offer 1", "company":"company 2", "pay":50000, "url":"http://www.google.com"}}' \
  http://<user>:<password>@<domain>/api/crawlers/record
            
```

Dabei müssen der Hash des Crawlers, ein eindeutiger Identifikator für den Datensatz zum Abgleich ob es sich um einen neuen Datensatz oder ein Update handelt und die Daten übermittelt werden. Die Daten können mehrere Datenfelder enthalten (hier beispielsweise name, company, pay und url).

### Crawler Überschriften/Spalten

Die einzelnen Spalten der Tabelle können pro Crawler festgelegt werden. Dazu muss festgelegt werden wie die Überschrift lautet und welches Datenfeld eines Datensatzes in der Spalte angezeigt werden soll (Feldname). Zusätzlich kann das Datenfeld für die Verlinkung gesetzt werden. Dies ist beispielsweise im Beispiel oben sinnvoll. Das Feld `name` kann als Feldname festgelegt werden und das Feld `url` als Feldverlinkung.

Es ist außerdem möglich statische Inhalte über Feldinhalt festzulegen.

Die Position gibt die Position innerhalb der Tabelle an und der Wert "sortierbar" legt fest ob diese Spalte sortierbar ist. Für die Sortierung kann der Datentyp der Spalte festgelegt werden.

Innerhalb eines Crawlers ist außerdem möglich bei maximal einer Spalte die initiale Sortierung (aufsteigend oder absteigend) festzulegen. Die Tabelle wird dann beim Aufruf nach dieser Spalte sortiert.

Soll eine Spalte den ursprünglichen Wert vor einer Aktualisierung enthalten, so kann dies über die entsprechende Checkbox ausgewählt werden. In dieser Spalte wird dann nur für aktualisierte Datensätze der vorherige Wert angezeigt. Beispielsweise können so zwei Spalten für den Feldnamen `price` festgelegt werden, um Preisänderungen zu sehen. Dabei ist zu beachten, dass immer nur der direkt vorherige Wert gespeichert wird. Änderungen über einen längeren Zeitraum lassen sich nicht anzeigen.

Über Prefix und Suffix lassen sich Präfix und Suffixe anwenden. Beispielsweise kann so die Währung hinter einen Wert gesetzt werden.

<a href="../public/static/help/de/7_crawlers-2.jpg"><img src="../public/static/help/de/7_crawlers-2.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/7_crawlers-3.jpg"><img src="../public/static/help/de/7_crawlers-3.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/7_crawlers-4.jpg"><img src="../public/static/help/de/7_crawlers-4.jpg" style="width: 300px"></a>

### Crawler Links

Unterhalb der Liste lassen sich zusätzlich noch Links für den Schnellzugriff anlegen. Dabei können maximal zwei Ebenen abgebildet werden.

<a href="../public/static/help/de/7_crawlers-5.jpg"><img src="../public/static/help/de/7_crawlers-5.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/7_crawlers-5b.jpg"><img src="../public/static/help/de/7_crawlers-5b.jpg" style="width: 300px"></a>

## <a name="splittedbills"></a>Modul geteilte Ausgaben

Im Modul geteilte Ausgaben können verschiedene Ausgaben, die mit anderen Nutzern geteilt oder von anderen Nutzern bezahlt wurden erfasst werden.

<a href="../public/static/help/de/8_splittedbills.jpg"><img src="../public/static/help/de/8_splittedbills.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/8_splittedbills-1.jpg"><img src="../public/static/help/de/8_splittedbills-1.jpg" style="width: 300px"></a>

Innerhalb der Gruppe werden dann die Ausgaben verrechnet.

Eine geteilte Ausgaben hat immer einen oder mehrere Nutzer, die etwas bezahlt haben und einen oder mehrere Nutzer die einen Anteil an den Kosten tragen müssen (ausgegeben).

Schulden lassen sich auch als beglichen erfassen.

<a href="../public/static/help/de/8_splittedbills-2.jpg"><img src="../public/static/help/de/8_splittedbills-2.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/8_splittedbills-3.jpg"><img src="../public/static/help/de/8_splittedbills-3.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/8_splittedbills-4.jpg"><img src="../public/static/help/de/8_splittedbills-4.jpg" style="width: 300px"></a>

Es ist auch möglich eine Gruppe für das Teilen von Ausgaben im Ausland festzulegen. Dazu besteht die Möglichkeit eine Fremdwährung, ein Umrechnungskurs und eine Gebühr (in Prozent) festzulegen.

<a href="../public/static/help/de/8_splittedbills-5.jpg"><img src="../public/static/help/de/8_splittedbills-5.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/8_splittedbills-6.jpg"><img src="../public/static/help/de/8_splittedbills-6.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/8_splittedbills-7.jpg"><img src="../public/static/help/de/8_splittedbills-7.jpg" style="width: 300px"></a>

### Wiederkehrende Ausgaben

Wie bei nutzerspezifischen Finanzeinträgen lassen sich auch bei Gruppen mit geteilten Ausgaben wiederkehrende Ausgaben festlegen.

<a href="../public/static/help/de/8_splittedbills-9.jpg"><img src="../public/static/help/de/8_splittedbills-9.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/8_splittedbills-10.jpg"><img src="../public/static/help/de/8_splittedbills-10.jpg" style="width: 300px"></a>

## <a name="trips"></a>Modul Reisen

Im Modul Reisen lassen sich Reisepläne erfassen und in einer Karte anzeigen.

<a href="../public/static/help/de/9_trips.jpg"><img src="../public/static/help/de/9_trips.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/9_trips-1.jpg"><img src="../public/static/help/de/9_trips-1.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/9_trips-3.jpg"><img src="../public/static/help/de/9_trips-3.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/9_trips-4.jpg"><img src="../public/static/help/de/9_trips-4.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/9_trips-5.jpg"><img src="../public/static/help/de/9_trips-5.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/9_trips-6.jpg"><img src="../public/static/help/de/9_trips-6.jpg" style="width: 300px"></a>

## <a name="timesheets"></a>Modul Zeiterfassung

Im Modul Zeiterfassung lassen sich projektspezifische Beginn und Endezeiten erfassen. Dabei kann zwischen projekt-(Beginn/Ende) und tagesbasiert (Kommen/Gehen) unterschieden werden.

<a href="../public/static/help/de/10_timesheets-4.jpg"><img src="../public/static/help/de/10_timesheets-4.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-5.jpg"><img src="../public/static/help/de/10_timesheets-5.jpg" style="width: 300px"></a>

Zeiterfassungeinträge können als berechnet, bezahlt oder geplant markiert werden. Zusätzlich können beim Erstellen oder Aktualisieren Zeiterfassungseinträge als Serie angelegt werden.

<a href="../public/static/help/de/10_timesheets-31.jpg"><img src="../public/static/help/de/10_timesheets-31.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-30.jpg"><img src="../public/static/help/de/10_timesheets-30.jpg" style="width: 300px"></a>

### Projekte

<a href="../public/static/help/de/10_timesheets.jpg"><img src="../public/static/help/de/10_timesheets.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-1.jpg"><img src="../public/static/help/de/10_timesheets-1.jpg" style="width: 300px"></a>

### Kunden

Zeiterfassungeinträge können einzelnen Kunden zugeordnet werden. Diese können archiviert und damit in der Auswahlliste ausgeblendet werden. Außerdem lassen sich Hintergrund- und Textfarbe für die Kalenderansicht festlegen.

<a href="../public/static/help/de/10_timesheets-15.jpg"><img src="../public/static/help/de/10_timesheets-15.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-16.jpg"><img src="../public/static/help/de/10_timesheets-16.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-17.jpg"><img src="../public/static/help/de/10_timesheets-17.jpg" style="width: 300px"></a>

### Schnellerfassung

Über die Schnellerfassung kann durch einen einzelnen Klick der aktuelle Zeitpunkt als Beginn oder Ende erfasst werden.

Wird der Link zu dieser Seite als Start-URL oder als mobiler Favorit angelegt, so kann sehr schnell der Beginn oder das Ende erfasst werden.

<a href="../public/static/help/de/10_timesheets-6.jpg"><img src="../public/static/help/de/10_timesheets-6.jpg" style="width: 300px"></a>

### Zeiterfassungskategorien

Erfasste Zeiten können zusätzlich noch Kategorien zugeordnet werden.

<a href="../public/static/help/de/10_timesheets-2.jpg"><img src="../public/static/help/de/10_timesheets-2.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-3.jpg"><img src="../public/static/help/de/10_timesheets-3.jpg" style="width: 300px"></a>

### Projektbudgets

Für die einzelnen Projekte können Projektbudgets definiert werden. Dazu werden zugehörige Kategorien definiert. Wird eine neue Zeit erfasst und die gleichen (oder mehr) Kategorien werden vergeben, so wird dieser Zeiteintrag zum Budget hinzugerechnet.

<a href="../public/static/help/de/10_timesheets-8.jpg"><img src="../public/static/help/de/10_timesheets-8.jpg" style="width: 300px"></a>

Das Projektbudget kann anhand der gesamten Zeit, der modifierten Zeit oder der Anzahl der zugeordneten Zeiteinträge berechnet werden. Über die Hauptkategorie können mehrere Budgets gruppiert werden. Außerdem lassen sich drei Grenzwerte für die farbliche Hervorhebung (gelb, orange, rot) definieren.

<a href="../public/static/help/de/10_timesheets-9.jpg"><img src="../public/static/help/de/10_timesheets-9.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-10.jpg"><img src="../public/static/help/de/10_timesheets-10.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-11.jpg"><img src="../public/static/help/de/10_timesheets-11.jpg" style="width: 300px"></a>

### Nachweise

Es ist möglich Nachweisarten, welche eine bestimmte Gültigkeit haben, festzulegen.

<a href="../public/static/help/de/10_timesheets-39.jpg"><img src="../public/static/help/de/10_timesheets-39.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-40.jpg"><img src="../public/static/help/de/10_timesheets-40.jpg" style="width: 300px"></a>

Für Kunden kann dann erfasst werden ob der entsprechende Nachweis im festgelegten Zeitraum erbracht wurde.

<a href="../public/static/help/de/10_timesheets-42.jpg"><img src="../public/static/help/de/10_timesheets-42.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-41.jpg"><img src="../public/static/help/de/10_timesheets-41.jpg" style="width: 300px"></a>

### Erinnerungen

Es ist möglich verschiedene Erinnerungen nach jedem Eintrag, nach dem letzten Eintrag eines Tages oder eine Stunde nach dem letzten Eintrag eines Tages festzulegen. Diese können dann bei den internen und Push-Benachrichtigungen abonniert werden.

<a href="../public/static/help/de/10_timesheets-43.jpg"><img src="../public/static/help/de/10_timesheets-43.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-44.jpg"><img src="../public/static/help/de/10_timesheets-44.jpg" style="width: 300px"></a>

### Bemerkungen

Bei Zeiterfassungseinträgen, Kunden und Zeiterfassungsprojekten können end-to-end verschlüsselte (E2EE) Bemerkungen und Dateien gespeichert werden. Diese Daten verlassen das Gerät nie unverschlüsselt und werden ausschließlich verschlüsselt vom Server empfangen und gespeichert.

<a href="../public/static/help/de/10_timesheets-27.jpg"><img src="../public/static/help/de/10_timesheets-27.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-28.jpg"><img src="../public/static/help/de/10_timesheets-28.jpg" style="width: 300px"></a>

Um Bemerkungen nutzen zu können muss beim Zeiterfassungsprojekt zuerst ein Passwort festgelegt werden.

<a href="../public/static/help/de/10_timesheets-18.jpg"><img src="../public/static/help/de/10_timesheets-18.jpg" style="width: 300px"></a>

Das hinterlegte Passwort kann später auch geändert werden.

<a href="../public/static/help/de/10_timesheets-19.jpg"><img src="../public/static/help/de/10_timesheets-19.jpg" style="width: 300px"></a>

Wenn das Passwort vergessen wird kann der Wiederherstellungsschlüssel für das Zurücksetzen des Passworts verwendet werden.

<a href="../public/static/help/de/10_timesheets-25.jpg"><img src="../public/static/help/de/10_timesheets-25.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-32.jpg"><img src="../public/static/help/de/10_timesheets-32.jpg" style="width: 300px"></a>

Sollte sowohl Passwort vergessen werden als auch der Wiederherstellungsschlüssel nicht gespeichert sein können keine Daten wiederhergestellt werden!

Bevor Bemerkungen eingetragen oder ausgelesen werden können muss das Passwort eingegeben werden. Dieses Passwort wird im Browser zwischengespeichert.

<a href="../public/static/help/de/10_timesheets-20.jpg"><img src="../public/static/help/de/10_timesheets-20.jpg" style="width: 300px"></a>

Bemerkungen können entweder in ein generisches Textfeld oder in individuell konfigurierbare Felder eingetragen werden.

Die frei konfigurierbaren Felder können als Textfeld, Textbereich, Dropdown oder HTML festgelegt werden.

<a href="../public/static/help/de/10_timesheets-12.jpg"><img src="../public/static/help/de/10_timesheets-12.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-13.jpg"><img src="../public/static/help/de/10_timesheets-13.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-14.jpg"><img src="../public/static/help/de/10_timesheets-14.jpg" style="width: 300px"></a>

Bemerkungen können dann bei Zeiterfassungseinträgen und Kunden gespeichert werden.

<a href="../public/static/help/de/10_timesheets-21.jpg"><img src="../public/static/help/de/10_timesheets-21.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-22.jpg"><img src="../public/static/help/de/10_timesheets-22.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-24.jpg"><img src="../public/static/help/de/10_timesheets-24.jpg" style="width: 300px"></a>

Bei Projekten wird derzeit lediglich das spezielle Feld "legend" beim Projekt in der Kalenderansicht angezeigt.

<a href="../public/static/help/de/10_timesheets-23.jpg"><img src="../public/static/help/de/10_timesheets-23.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-26.jpg"><img src="../public/static/help/de/10_timesheets-26.jpg" style="width: 300px"></a>

### Kalenderansicht

In der Kalenderansicht können die Termine in einer Monats-, Wochen- und Tagesansicht oder als Liste dargestellt werden. Dabei können die Hauptzeiten sowie ausblendbare Wochentage (nur Wochen- und Tagesansicht) beim Projekt festgelegt werden.

Beim Klick in den Kalender kann ein neuer Termin angelegt werden. Ein Klick auf ein Ereignis öffnet einen Dialog mit Details. Dort besteht ein Link zur Bemerkung des Kunden und des Zeiterfassungseintrags sowie um den Eintrag zu bearbeiten. Ein Eintrag kann direkt gelöscht werden oder bei Einträgen, die Teil einer Serie sind kann dieser und nachfolgende Einträge gelöscht werden.

Wurden Hintergrund- und Textfarbe eines Kunden festgelegt so werden die Einträge entsprechend eingefärbt. Außerdem wird unter dem Kalender die verschlüsselte Legende angezeigt.

Die zuletzt aufgerufene Ansicht sowie das Datum werden für den nächsten Aufruf gespeichert.

<a href="../public/static/help/de/10_timesheets-29.jpg"><img src="../public/static/help/de/10_timesheets-29.jpg" style="width: 300px"></a>

### Export

Die erfassten Stunden und Bemerkungen können in verschiedene Formate exportiert werden.

Der Export nach Excel ermöglicht eine Übersicht über die einzelnen Stunden im ausgewählten Zeitraum.

<a href="../public/static/help/de/10_timesheets-33.jpg"><img src="../public/static/help/de/10_timesheets-33.jpg" style="width: 300px"></a>

<a href="../public/static/help/100_timesheets-7.png"><img src="../public/static/help/100_timesheets-7.png" style="width: 300px"></a>

Der HTML Export ermöglicht das Anzeigen und Exportieren als Zeiterfassungeinträge inklusive verschlüsster Bemerkungen. Die Notizen können nach der Entschlüsselung nach Word exportiert werden.

<a href="../public/static/help/de/10_timesheets-34.jpg"><img src="../public/static/help/de/10_timesheets-34.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-35.jpg"><img src="../public/static/help/de/10_timesheets-35.jpg" style="width: 300px"></a>

Die Kunden Übersicht ermöglicht eine tabellarische Übersicht aller Kunden inklusive gewählter verschlüsselten Bemerkungen. Diese Übersicht kann nach der Entschlüsselung nach Excel exportiert werden.

<a href="../public/static/help/de/10_timesheets-36.jpg"><img src="../public/static/help/de/10_timesheets-36.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-37.jpg"><img src="../public/static/help/de/10_timesheets-37.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/10_timesheets-38.jpg"><img src="../public/static/help/de/10_timesheets-38.jpg" style="width: 300px"></a>

## <a name="workouts"></a>Modul Workouts

Im Modul Workouts lassen sich Fitness-Trainings anlegen und verwalten.

Beim Anlegen eines Trainingsplan kann aus den verfügbaren Übungen ausgewählt werden. Die Liste kann durchsucht und nach Körperbereich gefiltert werden. Die Anzahl der durchzuführenden Sätze kann unten festgelegt werden. Durch Anklicken des Plus-Symbols wird die Übung dann in den Trainingsplan übernommen. Dort können dann eine Bemerkung und die Sätze festgelegt werden.

Übungen lassen sich über das Minus-Symbol wieder aus dem Trainingsplan entfernen und über das Kreuz-Symbol verschieben.

Zusätzlich können Trainingstage und Supersätze festgelegt werden.

<a href="../public/static/help/de/11_workouts.jpg"><img src="../public/static/help/de/11_workouts.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/11_workouts-1.jpg"><img src="../public/static/help/de/11_workouts-1.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/11_workouts-18.jpg"><img src="../public/static/help/de/11_workouts-18.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/11_workouts-14.jpg"><img src="../public/static/help/de/11_workouts-14.jpg" style="width: 300px"></a>

Durch einen Klick auf den Trainingsplan in der Übersichtsliste lassen sich dieser anzeigen.

<a href="../public/static/help/de/11_workouts-2.jpg"><img src="../public/static/help/de/11_workouts-2.jpg" style="width: 300px"></a>

### Übungen anzeigen

Alle verfügbaren Übungen lassen sich im Menü unter "Übungen" anzeigen.

<a href="../public/static/help/de/11_workouts-15.jpg"><img src="../public/static/help/de/11_workouts-15.jpg" style="width: 300px"></a>

### Trainingseinheit erfassen

Nach dem Anklicken eines Trainingsplan lassen sich die Trainingseinheiten anzeigen und erfassen.

Wenn in einem Trainingsplan Trainingstage festgelegt wurden, muss zuerst ein Tag ausgewählt werden. Anschließend werden alle Übungen aus dem Trainingsplan angezeigt.

Einzelne Übungen lassen sich entfernen und verschieben. Außerdem ist es möglich manuell Übungen hinzuzufügen. Bei jeder Übung können die Sätze bearbeitet und eine Notiz hinterlegt werden.

<a href="../public/static/help/de/11_workouts-16.jpg"><img src="../public/static/help/de/11_workouts-16.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/11_workouts-26.jpg"><img src="../public/static/help/de/11_workouts-26.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/11_workouts-17.jpg"><img src="../public/static/help/de/11_workouts-17.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/11_workouts-19.jpg"><img src="../public/static/help/de/11_workouts-19.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/11_workouts-21.jpg"><img src="../public/static/help/de/11_workouts-21.jpg" style="width: 300px"></a>

Während dem Training kann eine Trainingseinheit interaktiv erfasst werden:

<a href="../public/static/help/de/11_workouts-22.jpg"><img src="../public/static/help/de/11_workouts-22.jpg" style="width: 300px"></a>

### Vorlagen anwenden

Unter Trainingsplan Vorlagen können vom Administrator vordefinierte Vorlagen angezeigt und als eigenen Trainingsplan übernommen werden.

<a href="../public/static/help/de/11_workouts-3.jpg"><img src="../public/static/help/de/11_workouts-3.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/11_workouts-23.jpg"><img src="../public/static/help/de/11_workouts-23.jpg" style="width: 300px"></a>

### Trainingsstatistiken

Es können Trainingsstatistiken für den aktuellen Plan oder für alle Pläne angezeigt werden.

<a href="../public/static/help/de/11_workouts-25.jpg"><img src="../public/static/help/de/11_workouts-25.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/11_workouts-24.jpg"><img src="../public/static/help/de/11_workouts-24.jpg" style="width: 300px"></a>

### Administrator-Funktionen

Als Administrator ist es möglich verschiedene Daten anzupassen und zu ergänzen.

#### Übungen verwalten

<a href="../public/static/help/de/11_workouts-6.jpg"><img src="../public/static/help/de/11_workouts-6.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/11_workouts-7.jpg"><img src="../public/static/help/de/11_workouts-7.jpg" style="width: 300px"></a>

#### Vorlagen verwalten

<a href="../public/static/help/de/11_workouts-3.jpg"><img src="../public/static/help/de/11_workouts-3.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/11_workouts-4.jpg"><img src="../public/static/help/de/11_workouts-4.jpg" style="width: 300px"></a>

#### Muskelgruppen verwalten

Es ist möglich die einzelnen Muskelgruppen und die Bilder zu verwalten. Dabei gibt es ein Hauptbild und pro Muskel ein primär und sekundär Bild, welches über das Hauptbild gelegt werden kann (Transparenz).

<a href="../public/static/help/de/11_workouts-8.jpg"><img src="../public/static/help/de/11_workouts-8.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/11_workouts-10.jpg"><img src="../public/static/help/de/11_workouts-10.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/11_workouts-9.jpg"><img src="../public/static/help/de/11_workouts-9.jpg" style="width: 300px"></a>

#### Körperbereiche verwalten

<a href="../public/static/help/de/11_workouts-11.jpg"><img src="../public/static/help/de/11_workouts-11.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/11_workouts-12.jpg"><img src="../public/static/help/de/11_workouts-12.jpg" style="width: 300px"></a>

## <a name="recipes"></a>Modul Rezepte

Im Modul Rezepte können Rezepte erfasst und zu Kochbüchern oder Speisepläne hinzugefügt werden.

<a href="../public/static/help/de/12_recipes-1.jpg"><img src="../public/static/help/de/12_recipes-1.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/12_recipes-2.jpg"><img src="../public/static/help/de/12_recipes-2.jpg" style="width: 300px"></a>

### Zutaten verwalten

Für die einzelnen Schritte bei Rezepten können Zutaten hinterlegt werden. Diese müssen zuerst angelegt werden.

<a href="../public/static/help/de/12_recipes-9.jpg"><img src="../public/static/help/de/12_recipes-9.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/12_recipes-10.jpg"><img src="../public/static/help/de/12_recipes-10.jpg" style="width: 300px"></a>

### Rezept anlegen/bearbeiten

Beim Anlegen oder Bearbeiten eines Rezepts müssen einzelne Arbeitsschritte erstellt werden. Den einzelnen Schritten können dann Beschreibungen und Zutaten hinzugefügt werden.

<a href="../public/static/help/de/12_recipes-3.jpg"><img src="../public/static/help/de/12_recipes-3.jpg" style="width: 300px"></a>

### Kochbücher

Es können Kochbücher als Rezeptsammlung angelegt werden. Diese können auch mit anderen Nutzern geteilt werden.

<a href="../public/static/help/de/12_recipes-5.jpg"><img src="../public/static/help/de/12_recipes-5.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/12_recipes-6.jpg"><img src="../public/static/help/de/12_recipes-6.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/12_recipes-7.jpg"><img src="../public/static/help/de/12_recipes-7.jpg" style="width: 300px"></a>

In der Rezeptansicht kann ein Rezept zu einem Kochbuch hinzugefügt werden.

<a href="../public/static/help/de/12_recipes-2.jpg"><img src="../public/static/help/de/12_recipes-2.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/12_recipes-4.jpg"><img src="../public/static/help/de/12_recipes-4.jpg" style="width: 300px"></a>

Wird ein Rezept aus dem Kochbuch heraus aufgerufen kann dieses wieder aus dem Kochbuch entfernt werden.

<a href="../public/static/help/de/12_recipes-8.jpg"><img src="../public/static/help/de/12_recipes-8.jpg" style="width: 300px"></a>

### Speisepläne

Es ist möglich für einzelne Wochentage Rezepte und Notizen als Speiseplan zu hinterlegen.

<a href="../public/static/help/de/12_recipes-11.jpg"><img src="../public/static/help/de/12_recipes-11.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/12_recipes-12.jpg"><img src="../public/static/help/de/12_recipes-12.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/12_recipes-13.jpg"><img src="../public/static/help/de/12_recipes-13.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/12_recipes-14.jpg"><img src="../public/static/help/de/12_recipes-14.jpg" style="width: 300px"></a>

### Einkaufslisten

Über Einkaufslisten können notwendige Zutaten/Lebensmittel oder sonstige notwendige Einkäufe erfasst werden.

<a href="../public/static/help/de/12_recipes-15.jpg"><img src="../public/static/help/de/12_recipes-15.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/12_recipes-16.jpg"><img src="../public/static/help/de/12_recipes-16.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/12_recipes-17.jpg"><img src="../public/static/help/de/12_recipes-17.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/12_recipes-18.jpg"><img src="../public/static/help/de/12_recipes-18.jpg" style="width: 300px"></a>

## <a name="admin"></a>Spezielle Admin-Funktionen

Als Administrator können verschiedene weitere Funktionen innerhalb der Web Anwendung angezeigt und bearbeitet werden.

  - Übersicht über alle gespeicherten Logins
  - Übersicht über gesperrte IP-Adressen (nach drei fehlerhaften Login Versuchen)
  - Verwalten von Benutzern
  - Testen von Push-Benachrichtigungen

<a href="../public/static/help/de/2_settings.jpg"><img src="../public/static/help/de/2_settings.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/2_logintokens-1.jpg"><img src="../public/static/help/de/2_logintokens-1.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/2_banlist.jpg"><img src="../public/static/help/de/2_banlist.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/2_users.jpg"><img src="../public/static/help/de/2_users.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/2_notifications-5.jpg"><img src="../public/static/help/de/2_notifications-5.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/2_notifications-6.jpg"><img src="../public/static/help/de/2_notifications-6.jpg" style="width: 300px"></a>

### Festlegen individueller Benachrichtigskategorien

Es ist möglich nutzerspezifische individuelle Benachrichtigungskategorien festzulegen.

<a href="../public/static/help/de/2_notifications-7.jpg"><img src="../public/static/help/de/2_notifications-7.jpg" style="width: 300px"></a>

<a href="../public/static/help/de/2_notifications-8.jpg"><img src="../public/static/help/de/2_notifications-8.jpg" style="width: 300px"></a>

Über den folgenden Aufruf können so automatisiert Benachrichtigungen an Nutzer, die die entsprechende Kategorie abonniert haben, gesendet werden.

``` 
curl "http://<user>:<password>@<domain>/api/notifications/notify?category=test_notification_cat&title=Test%20title&message=Test%20message" 
```

oder mit [apprise](https://appriseit.com/services/json/):

``` 
apprise -vv -t "Test title" -b "Test message" \
   "jsons://<user>:<password>@<domain>/api/notifications/notify?:category=test_notification_cat"
```