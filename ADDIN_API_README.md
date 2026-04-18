# Wortlab Add-in API (MVP)

Diese Endpunkte bilden das API-Fundament fuer ein Word-Add-in.

## 1) Token beziehen

**Endpoint**: `POST /api/v1/auth_token.php`

**Voraussetzung**: bestehende Wortlab-Session (Login im Browser).

**Response (200)**:

```json
{
  "token": "<jwt>",
  "token_type": "Bearer",
  "expires_in": 28800,
  "user_id": 123
}
```

**Fehler (401)**:

```json
{ "error": "not_authenticated" }
```

## 2) Woerter suchen

**Endpoint**: `POST /api/v1/search_words.php` (auch GET unterstuetzt)

**Header**:

`Authorization: Bearer <token>`

**Request-Felder**:

- `search_text` (string, optional)
- `not_letter` (string, optional)
- `category` (array/int/csv, optional)
- `semantic` (array/int/csv, optional)
- `alter` (array/int/csv, optional)
- `lauttreu` (bool/string, optional)
- `image_mode` (`standard` | `ausmalbild`, optional)
- `page` (int, optional, default 1)
- `page_size` (int, optional, default 25, max 100)

### Sternchen-Suche

- `abc*` -> beginnt mit `abc`
- `*abc` -> endet auf `abc`
- `*abc*` -> enthaelt `abc`
- `abc` -> enthaelt `abc`

**Response (200)**:

```json
{
  "meta": {
    "user_id": 123,
    "page": 1,
    "page_size": 25,
    "total": 1200,
    "total_filtered": 37
  },
  "data": [
    {
      "id": 12,
      "name": "Banane",
      "category_id": 2,
      "semantic_ids": [3, 7],
      "alter_id": 1,
      "lauttreu": true,
      "image_local_url": "https://example.com/images/banane.png",
      "image_external_url": "https://...",
      "image_mode": "standard"
    }
  ]
}
```

**Fehler**:

- `401` -> `{ "error": "invalid_or_missing_token" }`
- `405` -> `{ "error": "method_not_allowed" }`
- `500` -> `{ "error": "count_prepare_failed" }` oder `{ "error": "data_prepare_failed" }`

## Hinweise fuer Produktion

1. `WORTLAB_ADDIN_JWT_SECRET` als Umgebungsvariable setzen.
2. CORS-Whitelist per `WORTLAB_ADDIN_ALLOWED_ORIGINS` setzen (kommagetrennt), z. B. `https://localhost:3000,https://word-addin.example.com`.
3. Token-Laufzeit (`expires_in`) bei Bedarf reduzieren.
4. Optional: Refresh-Token-Flow und Entitlement-Pruefung (5 CHF/Jahr) ergaenzen.
