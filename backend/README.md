# ProjectAdvisor - Backend API

Backend Symfony 8 pour l'application ProjectAdvisor - recommandeur de stacks technologiques.

## Prérequis

- PHP 8.5+
- Composer
- SQLite3

## Installation

### 1. Installer les dépendances

```bash
composer install
```

### 2. Configurer l'environnement

```bash
cp .env .env.local
# Éditer .env.local avec vos paramètres locaux
```

### 3. Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 4. Démarrer le serveur

```bash
symfony serve
# Ou
php -S localhost:8000 -t public/
```

L'API est disponible à `http://localhost:8000/api`

## API Endpoints

### Recommandations

#### POST /api/recommendations
Générer une recommandation de stack

```bash
curl -X POST http://localhost:8000/api/recommendations \
  -H "Content-Type: application/json" \
  -d '{
    "objective": "mvp",
    "profile": "intermediate",
    "useCases": ["web-app", "e-commerce"],
    "features": ["typescript", "tailwind"],
    "constraints": [],
    "preferences": []
  }'
```

#### GET /api/recommendations/{id}
Récupérer une recommandation

```bash
curl http://localhost:8000/api/recommendations/{id}
```

#### GET /api/recommendations/{id}/files
Obtenir les fichiers markdown générés

```bash
curl http://localhost:8000/api/recommendations/{id}/files
```

#### GET /api/recommendations/{id}/download
Télécharger un fichier ZIP des ressources

```bash
curl -O http://localhost:8000/api/recommendations/{id}/download
```

### Technologies

#### GET /api/technologies
Lister toutes les technologies

```bash
curl http://localhost:8000/api/technologies
```

#### GET /api/technologies/{id}
Obtenir les détails d'une technologie

```bash
curl http://localhost:8000/api/technologies/nextjs
```

#### POST /api/compare
Comparer plusieurs technologies

```bash
curl -X POST http://localhost:8000/api/compare \
  -H "Content-Type: application/json" \
  -d '{
    "ids": ["nextjs", "nuxt", "astro"]
  }'
```

### Demandes de Conseil

#### POST /api/advice-requests
Créer une demande de conseil

```bash
curl -X POST http://localhost:8000/api/advice-requests \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "subject": "Question sur la stack",
    "message": "Votre message ici",
    "recommendationId": null
  }'
```

#### GET /api/advice-requests
Lister les demandes de conseil

```bash
curl http://localhost:8000/api/advice-requests
curl http://localhost:8000/api/advice-requests?status=pending
curl http://localhost:8000/api/advice-requests?email=john@example.com
```

#### GET /api/advice-requests/{id}
Obtenir une demande de conseil

```bash
curl http://localhost:8000/api/advice-requests/{id}
```

### Santé

#### GET /api/health
Vérifier l'état de l'API

```bash
curl http://localhost:8000/api/health
```

## Structure du Projet

```
backend/
├── src/
│   ├── Controller/          # Controllers Symfony
│   ├── Entity/              # Entités Doctrine
│   ├── Repository/          # Repositories
│   └── Service/             # Services métier
│       ├── RuleEngine/      # StackRecommender
│       └── MarkdownGenerator/
├── config/                  # Configuration
│   ├── packages/           # Configuration des bundles
│   └── routes.yaml         # Routage
├── data/                   # Données
│   └── knowledge/
│       └── technologies.json
├── public/                 # Entrée de l'application
├── var/                    # Cache et données (ignoré en git)
└── migrations/             # Migrations Doctrine
```

## Architecture

### StackRecommender Service

Charge les technologies depuis `data/knowledge/technologies.json` et génère des recommandations basées sur :

1. **Objective** (learning, mvp, performance)
2. **Profile** (beginner, intermediate, advanced)
3. **Use Cases** (web-app, api, etc.)
4. **Features** (typescript, tailwind, etc.)
5. **Constraints** (no-database, no-backend, etc.)

Retourne un score pour chaque technologie compatible.

### MarkdownGenerator Service

Génère 5 fichiers markdown dynamiques :
- **PROJECT.md** - Aperçu du projet
- **STACK.md** - Détails de la stack
- **CONVENTIONS.md** - Conventions de code
- **SETUP.md** - Guide d'installation
- **LIBRARIES.md** - Librairies recommandées

### Entités

#### Recommendation
- `id` (UUID)
- `answersJson` (réponses au questionnaire)
- `resultJson` (résultat de la recommandation)
- `createdAt`

#### AdviceRequest
- `id` (int)
- `name`, `email`, `subject`, `message`
- `recommendationId` (référence optionnelle)
- `status` (pending, answered, archived)
- `createdAt`, `answeredAt`

## Tests

```bash
php bin/phpunit
```

## Déploiement

### Production

1. Installer les dépendances en prod
```bash
composer install --no-dev --optimize-autoloader
```

2. Compiler le cache
```bash
php bin/console cache:warmup --env=prod
```

3. Migrer la base de données
```bash
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

## Troubleshooting

### La base de données ne se crée pas
```bash
php bin/console doctrine:database:create --if-not-exists
```

### Erreurs de permissions
```bash
chmod -R 777 var/
```

### Vider le cache
```bash
php bin/console cache:clear
```

## License

MIT
