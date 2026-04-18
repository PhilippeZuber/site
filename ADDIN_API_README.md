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

## 3) Wortsammlungen

**Endpoint**: `POST /api/v1/collections.php` (auch GET unterstuetzt)

**Header**:

`Authorization: Bearer <token>`

**Actions**:

- `action=list`
- `action=get` + `id`
- `action=create` + `name` + optional `word_ids` (CSV)
- `action=update` + `id` + `name` + optional `word_ids` (CSV)
- `action=delete` + `id`

**Response-Beispiel (list)**:

```json
{
  "collections": [
    {
      "id": 4,
      "user_id": 123,
      "name": "S-Laute",
      "word_ids": [1, 7, 9]
    }
  ]
}
```

## 4) Wortdetails

**Endpoint**: `GET /api/v1/word_details.php?id=<id>` (POST auch moeglich)

**Header**:

`Authorization: Bearer <token>`

**Response (200)**:

```json
{
  "meta": { "user_id": 123 },
  "data": {
    "id": 12,
    "name": "Banane",
    "category_id": 2,
    "semantic_ids": [3, 7],
    "alter_id": 1,
    "lauttreu": true,
    "image_local_standard_url": "https://example.com/images/banane.png",
    "image_local_ausmalbild_url": "https://example.com/images/banane_ausmalbild.png",
    "image_external_url": "https://..."
  }
}
```

## 5) Entitlement-Status (MVP-Platzhalter)

**Endpoint**: `GET /api/v1/entitlement_status.php` (POST auch moeglich)

**Header**:

`Authorization: Bearer <token>`

**Response (200)**:

```json
{
  "data": {
    "user_id": 123,
    "entitled": true,
    "plan_code": "trial",
    "billing_period": "yearly"
  }
}
```

Hinweis: Dieser Endpoint ist bewusst als MVP-Hook angelegt und muss spaeter mit echter Abo-/Freischaltlogik verbunden werden.

## 6) Filteroptionen laden

**Endpoint**: `GET /api/v1/filter_options.php` (POST auch moeglich)

**Header**:

`Authorization: Bearer <token>`

**Response (200)**:

```json
{
  "meta": { "user_id": 123 },
  "data": {
    "category": [{ "id": 1, "name": "Nomen" }],
    "semantic": [{ "id": 6, "name": "Kleidung" }],
    "alter": [{ "id": 2, "name": "2. Zyklus" }]
  }
}
```

## Hinweise fuer Produktion

1. `WORTLAB_ADDIN_JWT_SECRET` als Umgebungsvariable setzen.
2. CORS-Whitelist per `WORTLAB_ADDIN_ALLOWED_ORIGINS` setzen (kommagetrennt), z. B. `https://localhost:3000,https://word-addin.example.com`.
3. Optionales Rate-Limit aktivieren mit `WORTLAB_ADDIN_RATE_LIMIT_PER_MIN` (z. B. `120`).
4. Alle Responses enthalten eine `request_id` und den Header `X-Request-Id` fuer Tracing.
5. Token-Laufzeit (`expires_in`) bei Bedarf reduzieren.
6. Optional: Refresh-Token-Flow und Entitlement-Pruefung (5 CHF/Jahr) ergaenzen.
