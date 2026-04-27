# Newsletter-System

Dieses Verzeichnis enthält alle Newsletter-Templates für Wortlab. Das System wurde überarbeitet, um mehrere Newsletter einfacher verwalten zu können.

## 📁 Struktur

```
newsletters/
├── memory_game/              # Memory-Spiel Ankündigung
│   ├── memory_game.html      # HTML-Version
│   └── memory_game.txt       # Text-Version
├── jobs_announcement/        # Stellenplattform - User
│   ├── jobs_announcement.html
│   └── jobs_announcement.txt
├── jobs_contacts/            # Stellenplattform - Kontakte
│   ├── jobs_contacts.html
│   └── jobs_contacts.txt
└── README.md                 # Diese Datei
```

## 📧 Verfügbare Newsletter

### 1. Memory-Spiel (`memory_game`)
- **Name:** Memory-Spiel
- **Betreff:** Neu bei WORTLAB: Memory-Spiel 🎮
- **Standard-Zielgruppe:** Nur Wortlab-Benutzer
- **Beschreibung:** Ankündigung des Memory-Spiels für registrierte Benutzer

### 2. Stellenplattform - User (`jobs_announcement`)
- **Name:** Stellenplattform - User
- **Betreff:** Neu bei WORTLAB: Stellenplattform 💼
- **Standard-Zielgruppe:** Nur Wortlab-Benutzer
- **Beschreibung:** Ankündigung der neuen Stellenplattform für User

### 3. Stellenplattform - Kontakte (`jobs_contacts`)
- **Name:** Stellenplattform - Kontakte
- **Betreff:** WORTLAB Stellenplattform für Ihre Ausschreibung
- **Standard-Zielgruppe:** PDF-Kontakte von Stellenanzeigen
- **Beschreibung:** Ankündigung für Arbeitgeber und Stellenausschreibende

## 🚀 Neuen Newsletter hinzufügen

### Schritt 1: Ordner erstellen
```bash
mkdir newsletters/NEWSLETTER_ID
```

### Schritt 2: Template-Dateien erstellen
Erstellen Sie zwei Dateien im neuen Ordner:
- `NEWSLETTER_ID.html` (HTML-Version)
- `NEWSLETTER_ID.txt` (Text-Version)

### Schritt 3: Konfiguration in send_newsletter.php
Bearbeiten Sie `send_newsletter.php` und fügen Sie Ihren Newsletter in das `$newsletter_config` Array ein:

```php
$newsletter_config = array(
    'memory_game' => array(
        'name' => 'Memory-Spiel',
        'subject' => 'Neu bei WORTLAB: Memory-Spiel 🎮',
        'default_mode' => 'users',
        'description' => 'Ankündigung des Memory-Spiels für Benutzer'
    ),
    'YOUR_NEWSLETTER_ID' => array(
        'name' => 'Ihr Newsletter Name',
        'subject' => 'Newsletter Betreff',
        'default_mode' => 'users',  // oder 'jobs' oder 'both'
        'description' => 'Kurze Beschreibung'
    ),
);
```

**Parameter erklären:**
- `name`: Anzeigename im Admin-Dropdown
- `subject`: E-Mail-Betreff
- `default_mode`: Standard-Zielgruppe (`users`, `jobs`, oder `both`)
- `description`: Kurze Beschreibung unter dem Namen

### Schritt 4: Fertig!
Der neue Newsletter erscheint sofort im Dropdown-Menü unter "Newsletter versenden".

## 📝 Template-Richtlinien

### HTML-Templates
- Verwenden Sie **Inline-CSS** (für E-Mail-Kompatibilität)
- Verwenden Sie `{{UNSUBSCRIBE_URL}}` als Platzhalter für den Abmelde-Link
- Verwenden Sie `{{GREETING}}` oder `Liebe WORTLAB-Nutzer/innen und -Nutzer` für die Anrede
- Responsive Design mit max. 600px Breite
- WORTLAB Header und Footer einbinden

### Text-Templates
- ASCII-Art für Formatierung
- Gleiche Platzhalter wie HTML: `{{UNSUBSCRIBE_URL}}`
- Einfache, gut lesbare Struktur

### Beispiel-Platzhalter in Templates

```html
<!-- HTML -->
Hallo {{GREETING}},
<!-- oder -->
Liebe/r {{GREETING}},

<!-- Abmelde-Link -->
<a href="{{UNSUBSCRIBE_URL}}">Abmelden</a>
```

```text
# Text
Hallo {{GREETING}},

Abmelden: {{UNSUBSCRIBE_URL}}
```

## 🔧 Admin-Interface

### Newsletter versenden
1. Login als Admin
2. Navigiere zu: **Newsletter**
3. Wähle einen Newsletter aus dem Dropdown
4. Wähle die Zielgruppe (Benutzer / Stellenkontakte / Beide)
5. Klicke "Newsletter jetzt versenden"

### Verfügbare Zielgruppen

| Option | Beschreibung |
|--------|-------------|
| **Nur Wortlab-Benutzer** | Registrierte Benutzer mit Newsletter-Opt-In (`news = 'on'`) |
| **Nur Stellen-Ausschreibende** | PDF-Kontakte aus Stellenanzeigen |
| **Beide Gruppen** | Kombiniert beide mit automatischer Duplikat-Entfernung |

### Stellenkontakt-Status-Filter (nur bei Jobs-Zielgruppe)
- **Alle aktiven**: `new` + `contacted` Status
- **Nur neue**: Nur `new` Status
- **Nur kontaktierte**: Nur `status = contacted`

## 📊 Versand-Log

Alle Versände werden in der `newsletter_send_log` Tabelle dokumentiert:
- Datum/Zeit
- Empfänger-E-Mail
- Empfänger-Typ (user / job_contact)
- Empfänger-Modus
- Betreff
- Template-Datei
- Erfolg (ja/nein)
- Fehlermeldungen

## 🔐 Sicherheit

- Nur Admins können Newsletter versenden
- Eingaben werden mit `filter_data()` bereinigt
- Duplikate werden per E-Mail-Adresse entfernt
- Jeder Versand wird protokolliert

## ⚠️ Wichtige Hinweise

1. **Platzhalter korrekt setzen:**
   - `{{UNSUBSCRIBE_URL}}` ist erforderlich!
   - Verwenden Sie entweder `{{GREETING}}` oder direkten Text

2. **Dateinamenskonvention:**
   - Ordner-Name = Template-Name
   - Dateien: `ORDERNAME.html` und `ORDERNAME.txt`

3. **E-Mail-Kompatibilität:**
   - Testen Sie HTML-Templates in verschiedenen E-Mail-Clients
   - Verwenden Sie Inline-CSS, keine externen Stylesheets

4. **Versand-Timing:**
   - Der Versand kann mehrere Minuten dauern
   - Es gibt einen 600ms Verzögerung pro E-Mail zur Systemschonung

## 🆘 Troubleshooting

### Newsletter wird nicht gefunden
- Überprüfen Sie, dass der Ordner unter `newsletters/` existiert
- Überprüfen Sie die Ordner- und Dateinamen (Groß-/Kleinschreibung!)
- Überprüfen Sie die Konfiguration in `send_newsletter.php`

### Template wird nicht geladen
- Überprüfen Sie, dass `NEWSLETTER_ID.html` existiert
- Überprüfen Sie, dass die Datei lesbar ist (644 Permissions)
- Überprüfen Sie auf Syntaxfehler im Template

### E-Mails erscheinen falsch formatiert
- Verwenden Sie Inline-CSS, keine separaten `<style>` Tags
- Testen Sie in verschiedenen E-Mail-Clients
- Überprüfen Sie auf unverschlossene HTML-Tags

---

**Zuletzt aktualisiert:** 27.04.2026
**Newsletter-System Version:** 2.0
