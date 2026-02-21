# elmo-note

Evernote-ähnliche Notiz-App. Monorepo mit losem Frontend/Backend-Coupling.

## Stack

- **Frontend**: Vue 3 + Vite + Pinia + Vue Router + Tiptap — läuft auf Port 5173
- **Backend**: Laravel 11 + Laravel Sanctum (Bearer Token) — läuft auf Port 8000 via nginx
- **Datenbank**: PostgreSQL 16 — läuft auf Port 5432
- **Auth**: Token-basiert (kein Cookie/CSRF) — Token wird in `localStorage` gespeichert

## Docker

```bash
docker compose up -d          # alle Services starten
docker compose down           # stoppen
docker compose ps             # Status prüfen
docker compose logs -f        # Logs live
```

### Artisan-Befehle (immer im Container ausführen)

```bash
docker exec elmo-note-backend php artisan migrate
docker exec elmo-note-backend php artisan migrate:fresh --seed
docker exec elmo-note-backend php artisan config:clear
docker exec elmo-note-backend php artisan cache:clear
docker exec elmo-note-backend php artisan tinker
```

## Projektstruktur

```
elmo-note/
├── backend/                  # Laravel 11 API
│   ├── app/
│   │   ├── Http/Controllers/ # AuthController, NoteController, NotebookController, TagController, SearchController
│   │   ├── Models/           # User, Note, Notebook, Tag
│   │   └── Policies/         # nicht verwendet (abort_unless direkt in Controllern)
│   ├── database/migrations/
│   ├── routes/api.php
│   └── .env                  # DB_HOST=postgres, TOKEN-Auth, SESSION_DRIVER=cookie
│
├── frontend/                 # Vue 3 + Vite
│   ├── src/
│   │   ├── api/axios.js      # Axios-Instanz mit Bearer Token Interceptor
│   │   ├── stores/auth.js    # Pinia Auth Store
│   │   ├── stores/notes.js   # Pinia Notes/Notebooks/Tags Store
│   │   ├── router/index.js   # Vue Router mit Auth Guards
│   │   ├── views/            # LoginView, RegisterView, AppView
│   │   └── components/       # Sidebar, NoteList, TiptapEditor
│   └── .env                  # VITE_API_URL=http://localhost:8000/api
│
├── docker/
│   ├── php/Dockerfile        # PHP 8.4-fpm-alpine + pdo_pgsql
│   └── nginx/default.conf    # Laravel FastCGI zu backend:9000
└── docker-compose.yml
```

## API-Routen

```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout       (auth:sanctum)
GET    /api/auth/me           (auth:sanctum)

GET    /api/notebooks         (auth:sanctum)
POST   /api/notebooks
PUT    /api/notebooks/{id}
DELETE /api/notebooks/{id}

GET    /api/notes             (auth:sanctum) ?notebook_id=&tag_id=
POST   /api/notes
GET    /api/notes/{id}
PUT    /api/notes/{id}
DELETE /api/notes/{id}

GET    /api/tags              (auth:sanctum)
POST   /api/tags
DELETE /api/tags/{id}

GET    /api/search?q=         (auth:sanctum)  — PostgreSQL TSVECTOR Volltextsuche
```

## Wichtige Hinweise

- **Authorization**: `$this->authorize()` funktioniert nicht in Laravel 11 ohne Trait. Stattdessen `abort_unless((int) $model->user_id === $request->user()->id, 403)` verwenden.
- **Typ-Casting**: PostgreSQL gibt IDs als String zurück — beim Vergleich immer `(int)` casten.
- **CSRF**: `statefulApi()` in `bootstrap/app.php` darf NICHT verwendet werden (würde CSRF für Token-Auth erzwingen).
- **PHP-Version**: Dockerfile nutzt PHP 8.4 (wegen lokalem Herd PHP 8.4 bei `composer install`).
- **Volltextsuche**: Scope `Note::scopeSearch()` nutzt PostgreSQL `plainto_tsquery` — funktioniert nur mit PostgreSQL.
- **Auto-Save**: Frontend speichert Notizinhalt automatisch 1,2s nach dem letzten Tastendruck.

## Deployment (Railway / Render)

- Datenbank: PostgreSQL (Render/Railway nativ, MySQL nicht empfohlen)
- `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` aus Plattform-Env-Vars setzen
- `APP_URL` und `FRONTEND_URL` anpassen
- `SANCTUM_STATEFUL_DOMAINS` auf die Frontend-Domain setzen
