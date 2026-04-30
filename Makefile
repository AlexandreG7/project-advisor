.PHONY: help dev prod build-front install migrate logs down restart php-shell

# Default
help:
	@echo ""
	@echo "  ProjectAdvisor — Commandes disponibles"
	@echo "  ======================================="
	@echo ""
	@echo "  make install      → Première installation complète (build + migrate)"
	@echo "  make dev          → Lance en mode développement (hot reload Vite sur :5173)"
	@echo "  make prod         → Lance en mode production (tout sur :8080)"
	@echo "  make build-front  → Build le frontend React (prod)"
	@echo "  make migrate      → Lance les migrations Doctrine"
	@echo "  make logs         → Affiche les logs des conteneurs"
	@echo "  make down         → Arrête tous les conteneurs"
	@echo "  make restart      → Redémarre les conteneurs"
	@echo "  make php-shell    → Ouvre un shell dans le conteneur PHP"
	@echo ""

# ─── Installation initiale ───────────────────────────────────────────────────

install: build-front
	@echo "→ Build des conteneurs Docker..."
	docker compose build
	@echo "→ Démarrage temporaire pour migration..."
	docker compose up -d php
	@sleep 3
	@echo "→ Création de la base de données..."
	docker compose exec php php bin/console doctrine:database:create --if-not-exists --no-interaction
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
	docker compose down
	@echo ""
	@echo "✅ Installation terminée ! Lance 'make dev' ou 'make prod'"

# ─── Développement ───────────────────────────────────────────────────────────

dev:
	@echo "→ Mode DEV — Backend :8080 | Frontend Vite :5173"
	docker compose -f docker-compose.yml -f docker-compose.dev.yml up --build

# ─── Production ──────────────────────────────────────────────────────────────

prod: build-front
	@echo "→ Mode PROD — App disponible sur http://localhost:8080"
	docker compose up --build -d
	@echo "✅ Site disponible sur http://localhost:8080/app/"

# ─── Frontend ────────────────────────────────────────────────────────────────

build-front:
	@echo "→ Build du frontend React..."
	@if command -v node > /dev/null; then \
		cd frontend && npm install && npm run build; \
	else \
		docker run --rm -v $(PWD)/frontend:/app -w /app node:20-alpine sh -c "npm install && npm run build"; \
	fi
	@echo "✅ Frontend buildé dans frontend/dist/"

# ─── Utilitaires ─────────────────────────────────────────────────────────────

migrate:
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

logs:
	docker compose logs -f

down:
	docker compose down

restart:
	docker compose restart

php-shell:
	docker compose exec php bash

# ─── Helpers ─────────────────────────────────────────────────────────────────

cache-clear:
	docker compose exec php php bin/console cache:clear

routes:
	docker compose exec php php bin/console debug:router
