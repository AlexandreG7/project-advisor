# ProjectAdvisor

Site de recommandation de stack technique pour développeurs.
**Stack :** React 18 + Symfony 8 + PHP 8.5 + SQLite + Docker

---

## Lancement rapide

### Prérequis
- Docker Desktop lancé
- Make (optionnel mais pratique)

### 1. Première installation

```bash
cd project-advisor

# Option A — avec Make
make install   # build front, build Docker, migrations
make dev       # lance en mode dev

# Option B — manuellement
cd frontend && npm install && npm run build && cd ..
docker compose build
docker compose up -d php nginx
docker compose exec php php bin/console doctrine:database:create --if-not-exists
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

### 2. Modes de lancement

**Mode production** (tout sur http://localhost:8080)
```bash
make prod
# ou
docker compose up --build -d
```

**Mode développement** (hot reload Vite sur :5173, API sur :8080)
```bash
make dev
# ou
docker compose -f docker-compose.yml -f docker-compose.dev.yml up --build
```

### Accès
- **Frontend** → http://localhost:8080/app/ (prod) ou http://localhost:5173/app/ (dev)
- **API** → http://localhost:8080/api/

---

## Commandes utiles

```bash
make logs        # voir les logs
make migrate     # lancer les migrations
make php-shell   # shell dans le conteneur PHP
make routes      # lister les routes Symfony
make down        # arrêter tout
```

---

## Structure

```
project-advisor/
├── docker/                  # Dockerfiles + configs Nginx
├── docker-compose.yml       # Production
├── docker-compose.dev.yml   # Développement
├── Makefile                 # Commandes pratiques
├── backend/                 # Symfony 8 (PHP 8.5)
│   ├── src/
│   │   ├── Controller/      # RecommendationController, AdviceRequestController
│   │   ├── Entity/          # Recommendation, AdviceRequest
│   │   ├── Service/
│   │   │   ├── RuleEngine/  # StackRecommender (moteur de scoring)
│   │   │   └── MarkdownGenerator/
│   │   └── Repository/
│   ├── data/knowledge/      # technologies.json, rules/
│   └── var/data.db          # SQLite (généré)
└── frontend/                # React 18 + Vite + Tailwind
    └── src/
        ├── pages/           # Home, Questionnaire, Results, Comparator
        ├── components/      # Layout
        └── services/        # api.js (Axios)
```

---

## API Endpoints

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/api/technologies` | Liste les 31 technos du catalogue |
| `POST` | `/api/recommendations` | Soumet les réponses → génère recommandation |
| `GET` | `/api/recommendations/{id}` | Récupère une recommandation |
| `GET` | `/api/recommendations/{id}/files` | Génère les 5 fichiers .md (JSON) |
| `GET` | `/api/recommendations/{id}/download` | Télécharge le ZIP des .md |
| `POST` | `/api/compare` | Compare 2-3 technos |
| `POST` | `/api/advice-requests` | Soumet une demande de conseil |

---

## Note sur les versions

> Si PHP 8.5 ou Symfony 8 ne sont pas encore disponibles sur Docker Hub,
> remplace `php:8.5-fpm` → `php:8.4-fpm` dans `docker/php/Dockerfile`
> et `"symfony/framework-bundle": "8.*"` → `"7.*"` dans `backend/composer.json`.
