# Cloudflare-Workflow fuer lokale Wortbilder

Dieses Setup sorgt dafuer, dass pro Wort ein lokal gehostetes Standardbild in words.image vorhanden ist.

## Verhalten

- Vorhandene lokale Bilder bleiben unveraendert.
- image_url bleibt bewusst unangetastet und darf extern gehostet bleiben.
- Wenn in words.image noch kein lokales Bild vorhanden ist, wird ein neues Bild via Cloudflare Workers AI generiert.
- Ein zufaellig gewaaehltes Referenzbild aus `reference_img/` dient als Stil-Vorlage (img2img).
- Sind keine Referenzbilder vorhanden, wird automatisch auf `stable-diffusion-xl-base-1.0` zurueckgegriffen.
- Der lokale Dateiname wird nach words.image geschrieben.

Ausmalbilder sind bewusst nicht Teil dieses ersten Skripts. Das bestehende System erwartet fuer image_ausmalbild lokale Dateien; dafuer waere ein zweites, promptseitig anderes Skript sinnvoll. Bild2 bleibt also extern, Bild bleibt selbst gehostet.

## Voraussetzungen

- PHP CLI mit mysqli und cURL
- Schreibrechte auf images/
- Cloudflare API Token mit Workers AI Read oder Workers AI Write
- Mindestens ein Referenzbild in `reference_img/` (PNG oder JPG)

## Umgebungsvariablen

PowerShell-Beispiel:

```powershell
$env:CLOUDFLARE_ACCOUNT_ID = "deine-account-id"
$env:CLOUDFLARE_API_TOKEN = "dein-token"
```

Optional – Modell ueberschreiben (Standard: `@cf/runwayml/stable-diffusion-v1-5-img2img`):

```powershell
$env:CLOUDFLARE_AI_MODEL = "@cf/runwayml/stable-diffusion-v1-5-img2img"
```

Optional – eigenen Stil-Prompt setzen:

```powershell
$env:WORDLAB_IMAGE_STYLE_PROMPT = "Clean cartoon illustration, bold dark outlines, flat colors, white background."
```

Optional – eigenen Negativprompt setzen:

```powershell
$env:WORDLAB_IMAGE_NEGATIVE_PROMPT = "text, letters, numbers, watermark, photo, realistic, dark background"
```

## Aufruf

Trockenlauf:

```powershell
php .\sync_word_images_cloudflare.php --dry-run --limit=10
```

Ein einzelnes Wort:

```powershell
php .\sync_word_images_cloudflare.php --word-id=49
```

Batch:

```powershell
php .\sync_word_images_cloudflare.php --limit=50
```

## Empfehlung fuer den Betrieb

- Zuerst immer mit --dry-run testen.
- Danach mit kleinem Limit starten, zum Beispiel 10 oder 20.
- Die generierten Bilder fachlich pruefen, bevor groessere Batches laufen.
- Falls du einen sehr festen Stil willst, den Stilprompt schrittweise schaerfen statt sofort grosse Mengen zu erzeugen.