# Newsletter: Memory-Funktion Promotion

## 📁 Dateien

### 1. newsletter_memory.html
Professioneller HTML-Newsletter mit:
- Responsivem Design
- Inline-CSS für E-Mail-Client-Kompatibilität
- Gradient-Headern und Call-to-Action-Buttons
- Statistik-Sektion
- Footer mit Links

**Verwendung:** Kann direkt in E-Mail-Clients oder als Webseite angezeigt werden

### 2. newsletter_memory.txt
Plain-Text-Version des Newsletters für:
- E-Mail-Clients ohne HTML-Unterstützung
- Barrierefreiheit
- Alternative Darstellung

### 3. send_newsletter.php
Admin-Panel zum Versenden des Newsletters mit:
- Benutzerstatistik (Gesamt-User, Newsletter-Abonnenten)
- Vorschau-Funktion
- Test-E-Mail-Funktion
- Sicherheitsabfrage vor Massenversand
- Personalisierung (Vorname/Nachname)

**Zugriff:** Nur für Admins ($_SESSION['role'] == 1)

## 🚀 Newsletter versenden

### Schritt 1: Test-E-Mail senden
1. In der WORTLAB-Plattform anmelden (als Admin)
2. Menü: **"Newsletter"** aufrufen
3. Im Bereich "Test-Empfänger": E-Mail-Adresse eingeben
4. Auf **"Test senden"** klicken
5. Newsletter in Ihrem Posteingang überprüfen

### Schritt 2: Massenversand
1. Statistik überprüfen (wie viele Empfänger?)
2. Vorschau ansehen (Button: "Vorschau anzeigen")
3. Sicherheitsfrage bestätigen
4. Auf **"Newsletter jetzt versenden"** klicken
5. Warten bis Bestätigungsmeldung erscheint

## 🎯 Empfänger

Der Newsletter wird nur an Benutzer versendet, die:
- In der Datenbank registriert sind (`user` Tabelle)
- Newsletter abonniert haben (`news = 'on'`)

## 📧 E-Mail-Konfiguration

**Absender:** WORTLAB <noreply@wortlab.ch>
**Betreff:** Neu bei WORTLAB: Memory-Spiel 🎮
**Format:** HTML (mit Plain-Text Fallback empfohlen)

## ✏️ Newsletter anpassen

### HTML-Version bearbeiten
Datei: `newsletter_memory.html`

Wichtige Bereiche:
- **Header:** Zeile 19-24 (Titel und Untertitel)
- **Hero-Section:** Zeile 28-33 (Hauptüberschrift)
- **Hauptinhalt:** Zeile 38-110 (Text, Listen, Bilder)
- **Call-to-Action:** Zeile 113-120 (Button-Link)
- **Footer:** Zeile 142-170 (Links, Impressum)

**Tipp:** Verwenden Sie immer Inline-CSS für maximale Kompatibilität!

### Text-Version bearbeiten
Datei: `newsletter_memory.txt`

Einfache Textdatei mit ASCII-Art-Formatierung

## 🎨 Design-Elemente

### Farben
- **Primary Gradient:** #667eea → #764ba2 (Lila-Töne)
- **Accent Gradient:** #f093fb → #f5576c (Pink-Töne)
- **Hintergrund:** #f4f4f4 (Hellgrau)
- **Text:** #333333 (Dunkelgrau)

### Icons
- 🎮 Gaming/Spielen
- ✨ Besondere Features
- 🎯 Anleitung/Schritte
- 💡 Tipps
- 📊 Statistik

## 📋 Checkliste vor Versand

- [ ] Test-E-Mail an sich selbst senden
- [ ] Newsletter in verschiedenen E-Mail-Clients testen (Gmail, Outlook, etc.)
- [ ] Links überprüfen (funktionieren alle?)
- [ ] Rechtschreibung/Grammatik checken
- [ ] Empfänger-Anzahl kontrollieren
- [ ] Versandzeitpunkt wählen (z.B. Dienstagvormittag)
- [ ] Sicherstellen, dass `newsletter_memory.html` im Root-Verzeichnis liegt

## 🔧 Technische Details

### PHP-Mail-Funktion
```php
mail($to, $subject, $message, $headers)
```

### Header
```
MIME-Version: 1.0
Content-Type: text/html; charset=UTF-8
From: WORTLAB <noreply@wortlab.ch>
```

### Personalisierung
Der Newsletter ersetzt automatisch:
```
"Liebe WORTLAB-Nutzer/innen und -Nutzer"
→ "Liebe/r [Vorname] [Nachname]"
```

## 📈 Best Practices

1. **Timing:** Versenden Sie Newsletter zu optimalen Zeiten:
   - Dienstag - Donnerstag
   - 9-11 Uhr oder 14-16 Uhr

2. **Frequenz:** Nicht mehr als 1-2 Newsletter pro Monat

3. **Betreffzeile:** Klar, prägnant, mit Emoji für Aufmerksamkeit

4. **Mobil-optimiert:** HTML-Newsletter ist responsiv

5. **Call-to-Action:** Ein klarer Hauptbutton

6. **Abmeldelink:** Immer im Footer vorhanden

## 🐛 Troubleshooting

**Newsletter kommt nicht an:**
- Prüfen Sie Spam-Ordner
- Server-Mailkonfiguration überprüfen
- SMTP-Einstellungen kontrollieren

**HTML wird nicht korrekt angezeigt:**
- Verwenden Sie Inline-CSS (nicht externe Stylesheets)
- Testen Sie in verschiedenen E-Mail-Clients
- Vermeiden Sie JavaScript

**Personalisierung funktioniert nicht:**
- Überprüfen Sie Datenbankfelder: `firstname`, `lastname`
- Stellen Sie sicher, dass User-Daten vollständig sind

## 📞 Support

Bei Fragen oder Problemen:
- Dokumentation: [README.md](README.md)
- Technische Unterstützung: WORTLAB-Admin

---
**Version:** 1.0
**Letzte Aktualisierung:** 5. Februar 2026
**Erstellt für:** WORTLAB Memory-Funktion Launch
