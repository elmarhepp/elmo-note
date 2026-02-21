# elmo-note

Evernote-ähnliche Notiz-App. Monorepo — Laravel serviert das Vue-Frontend mit aus.

## Stack

- **Frontend**: Vue 3 + Vite + Pinia + Vue Router + Tiptap — läuft auf Port 5173 (dev)
- **Backend**: Laravel 11 + Laravel Sanctum (Bearer Token) — läuft auf Port 8000
- **Datenbank**: PostgreSQL 16 — läuft auf Port 5432
- **Auth**: Token-basiert (kein Cookie/CSRF) — Token wird in `localStorage` gespeichert

## Deployment-Architektur

In **Production** serviert Laravel alles:
- `GET /api/*` → Laravel API
- `GET /*` → Vue SPA (`public/spa/index.html`) via Catch-All Route in `web.php`

Das Dockerfile ist ein Multi-Stage-Build:
1. Node 20 baut das Vue-Frontend (`npm run build`)
2. PHP 8.4 installiert Laravel-Dependencies + kopiert Vue-`dist/` nach `public/spa/`

## Docker (lokal)

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
│   ├── database/migrations/
│   ├── routes/
│   │   ├── api.php           # API-Routen
│   │   └── web.php           # Catch-All → Vue SPA
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
│   ├── .env                  # VITE_API_URL=http://localhost:8000/api (nur lokal)
│   └── .env.production       # VITE_API_URL= (leer → /api relativ, same-origin)
│
├── docker/
│   ├── entrypoint.sh         # config:cache, migrate, php artisan serve
│   └── nginx/default.conf    # für lokale Docker-Compose (nginx + php-fpm)
├── Dockerfile                # Multi-Stage: Node → PHP (für Railway)
├── railway.json              # Railway: Dockerfile-Builder
└── docker-compose.yml        # Lokale Entwicklung: 4 Services
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

## Deployment (Railway) — Ein Service

Ein einziges Railway-Projekt mit einem Service + PostgreSQL-Plugin.

### Setup

1. Railway Projekt erstellen → "Add Service" → "GitHub Repo"
2. Root Directory: leer lassen (Repo-Root)
3. Railway erkennt `railway.json` → nutzt `Dockerfile`
4. "Add Service" → "Database" → PostgreSQL hinzufügen

### Environment Variables (Railway Service)

```
APP_KEY=           # php artisan key:generate --show
APP_ENV=production
APP_DEBUG=false
APP_URL=           # https://deine-url.railway.app
DB_HOST=           # aus Railway PostgreSQL Plugin (${{Postgres.PGHOST}})
DB_PORT=5432
DB_DATABASE=       # aus Railway PostgreSQL Plugin (${{Postgres.PGDATABASE}})
DB_USERNAME=       # aus Railway PostgreSQL Plugin (${{Postgres.PGUSER}})
DB_PASSWORD=       # aus Railway PostgreSQL Plugin (${{Postgres.PGPASSWORD}})
```

Kein `VITE_API_URL` nötig — Frontend und Backend laufen unter derselben URL.
