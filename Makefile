.DEFAULT_GOAL := help

# ─────────────────────────────────────────────
#  Docker
# ─────────────────────────────────────────────

up: ## Alle Container starten
	docker compose up -d

down: ## Alle Container stoppen
	docker compose down

restart: ## Alle Container neu starten
	docker compose restart

build: ## Images neu bauen (ohne Cache)
	docker compose build --no-cache

ps: ## Container-Status anzeigen
	docker compose ps

logs: ## Live-Logs aller Services
	docker compose logs -f

logs-backend: ## Live-Logs Backend
	docker compose logs -f backend

logs-frontend: ## Live-Logs Frontend (Vite)
	docker compose logs -f frontend

# ─────────────────────────────────────────────
#  Initialisierung
# ─────────────────────────────────────────────

init: up migrate ## Projekt initialisieren (Container starten + Migrations)
	@echo ""
	@echo "elmo-note läuft:"
	@echo "  Frontend  →  http://localhost:5173"
	@echo "  Backend   →  http://localhost:8000"

migrate: ## Datenbank-Migrations ausführen
	docker exec elmo-note-backend php artisan migrate --no-interaction

migrate-fresh: ## Datenbank zurücksetzen und neu migrieren
	docker exec elmo-note-backend php artisan migrate:fresh --no-interaction

seed: ## Datenbank mit Testdaten befüllen
	docker exec elmo-note-backend php artisan db:seed --no-interaction

migrate-seed: ## Neu migrieren und seeden
	docker exec elmo-note-backend php artisan migrate:fresh --seed --no-interaction

# ─────────────────────────────────────────────
#  Backend (Laravel)
# ─────────────────────────────────────────────

artisan: ## Artisan-Befehl ausführen: make artisan CMD="route:list"
	docker exec elmo-note-backend php artisan $(CMD)

tinker: ## Laravel Tinker starten
	docker exec -it elmo-note-backend php artisan tinker

cache-clear: ## Laravel Cache leeren
	docker exec elmo-note-backend php artisan config:clear
	docker exec elmo-note-backend php artisan cache:clear
	docker exec elmo-note-backend php artisan route:clear
	docker exec elmo-note-backend php artisan view:clear

routes: ## API-Routen auflisten
	docker exec elmo-note-backend php artisan route:list --path=api

backend-shell: ## Shell im Backend-Container öffnen
	docker exec -it elmo-note-backend sh

# ─────────────────────────────────────────────
#  Frontend (Vue)
# ─────────────────────────────────────────────

frontend-shell: ## Shell im Frontend-Container öffnen
	docker exec -it elmo-note-frontend sh

npm: ## npm-Befehl ausführen: make npm CMD="install axios"
	docker exec elmo-note-frontend npm $(CMD)

# ─────────────────────────────────────────────
#  Datenbank
# ─────────────────────────────────────────────

db-shell: ## PostgreSQL-Shell öffnen
	docker exec -it elmo-note-postgres psql -U elmo -d elmo_note

# ─────────────────────────────────────────────
#  Hilfe
# ─────────────────────────────────────────────

help: ## Alle verfügbaren Befehle anzeigen
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

.PHONY: up down restart build ps logs logs-backend logs-frontend \
        init migrate migrate-fresh seed migrate-seed \
        artisan tinker cache-clear routes backend-shell \
        frontend-shell npm db-shell help
