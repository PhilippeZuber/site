# Add-in Release-Checkliste (Produktion)

## 1. Pflicht-Konfiguration

0. [api/v1/.htaccess.example](api/v1/.htaccess.example) nach [api/v1/.htaccess](api/v1/.htaccess) kopieren (nur Server, nicht committen).
1. `WORTLAB_ADDIN_ENV=production` setzen.
2. `WORTLAB_ADDIN_JWT_SECRET` setzen (starkes Secret, mindestens 32 Zeichen).
3. `WORTLAB_ADDIN_ALLOWED_ORIGINS` setzen (kommagetrennte Whitelist), Beispiel:
   - `https://addin.wortlab.ch,https://word.office.com`

## 2. Sicherheits-Checks

1. Verifizieren, dass kein Default-Secret verwendet wird.
2. Verifizieren, dass keine leere CORS-Whitelist in Produktion aktiv ist.
3. API-Aufruf mit fremdem Origin muss `403 origin_not_allowed` liefern.

Beispieltest (fremder Origin):

```bash
curl -i "https://wortlab.ch/api/v1/filter_options.php" \
   -H "Origin: https://evil.example" \
   -H "Authorization: Bearer <token>"
```

Erwartet: `HTTP/1.1 403` und JSON-Fehler `origin_not_allowed`.

## 3. Abo-/Entitlement-Betrieb

1. SQL-Migration aus [database_updates.md](database_updates.md) Abschnitt 9 ausführen.
2. In [subscription_management.php](subscription_management.php) Testuser auf `active` setzen.
3. Endpunkt `GET /api/v1/entitlement_status.php` mit Testtoken prüfen.

## 4. Add-in Smoke-Test

1. Login mit gültigem Benutzer in Add-in erfolgreich.
2. Login mit falschem Passwort liefert Fehler.
3. Suche/Einfügen funktioniert mit aktivem Abo.
4. Suche/Einfügen ist gesperrt ohne aktives Abo.

## 5. Marketplace-Vorbereitung

1. Support-URL, Privacy-Policy und Terms-URL erreichbar und aktuell.
2. Manifest-Version erhöht und mit Build-Stand synchronisiert.
3. Zertifizierungsnotizen mit Testkonto und Login-Schritten bereit.
