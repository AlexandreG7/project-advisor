# ProjectAdvisor Backend - Project Structure

## Directory Tree

```
backend/
├── bin/
│   └── setup.sh                 # Automated setup script
├── config/
│   ├── bundles.php              # Bundle configuration
│   ├── routes.yaml              # Route definitions
│   ├── services.yaml            # Service configuration
│   └── packages/
│       ├── dev/
│       │   └── debug.yaml       # Debug configuration (dev)
│       ├── prod/
│       │   └── doctrine.yaml    # Production Doctrine config
│       ├── test/
│       │   └── framework.yaml   # Test framework config
│       ├── doctrine.yaml        # Doctrine ORM configuration
│       ├── framework.yaml       # Symfony framework config
│       └── nelmio_cors.yaml     # CORS configuration
├── data/
│   └── knowledge/
│       ├── rules/
│       │   └── framework.json   # Framework rules (for future use)
│       └── technologies.json    # Technology database (20+ tech)
├── migrations/
│   └── Version20240326000001.php  # Initial database migration
├── public/
│   └── index.php                # Application entry point
├── src/
│   ├── Command/
│   │   └── InitializeCommand.php  # CLI initialization command
│   ├── Constants/
│   │   └── AppConstants.php     # Application constants
│   ├── Controller/
│   │   ├── AdviceRequestController.php  # Advice & Tech endpoints
│   │   └── RecommendationController.php # Recommendation endpoints
│   ├── DataFixtures/
│   │   └── SampleRecommendations.php  # Sample data for testing
│   ├── Entity/
│   │   ├── AdviceRequest.php    # AdviceRequest Doctrine entity
│   │   └── Recommendation.php   # Recommendation Doctrine entity
│   ├── EventListener/
│   │   └── ExceptionListener.php  # Global exception handler
│   ├── Exception/
│   │   ├── RecommendationException.php  # Recommendation errors
│   │   └── ValidationException.php      # Validation errors
│   ├── Repository/
│   │   ├── AdviceRequestRepository.php  # AdviceRequest queries
│   │   └── RecommendationRepository.php # Recommendation queries
│   ├── Service/
│   │   ├── MarkdownGenerator/
│   │   │   └── MarkdownGenerator.php  # Generates 5 markdown files
│   │   └── RuleEngine/
│   │       └── StackRecommender.php   # Core recommendation engine
│   ├── Util/
│   │   └── ValidationUtil.php   # Validation helper functions
│   └── Kernel.php               # Symfony application kernel
├── tests/
│   ├── bootstrap.php            # Test bootstrap file
│   └── Service/
│       └── RuleEngine/
│           └── StackRecommenderTest.php  # Unit tests
├── var/
│   ├── cache/                   # Application cache (git ignored)
│   ├── log/                     # Application logs (git ignored)
│   └── data.db                  # SQLite database (git ignored)
├── .env                         # Default environment variables
├── .env.example                 # Example environment template
├── .env.local                   # Local overrides (git ignored)
├── .env.test                    # Test environment variables
├── .gitignore                   # Git ignore rules
├── API.md                       # Complete API documentation
├── INSTALLATION.md              # Installation guide
├── Makefile                     # Convenient make commands
├── README.md                    # Main documentation
├── STRUCTURE.md                 # This file
├── composer.json                # PHP dependencies
├── docker-compose.yml           # Docker configuration
├── phpunit.xml.dist             # PHPUnit test configuration
├── symfony.lock                 # Dependency lock file
└── STRUCTURE.md                 # This documentation
```

## File Count Summary

- **PHP Files**: 24
- **Configuration Files**: 10
- **Documentation Files**: 4
- **Data Files**: 2
- **Configuration/Setup**: 4
- **Total**: 44 files

---

## Architecture Overview

### Layers

```
┌─────────────────────────────────────────┐
│         Controllers (HTTP Layer)         │
│  - RecommendationController             │
│  - AdviceRequestController              │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│         Services (Business Logic)        │
│  - StackRecommender (Rule Engine)       │
│  - MarkdownGenerator                    │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│    Repositories (Data Access Layer)     │
│  - RecommendationRepository             │
│  - AdviceRequestRepository              │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│     Entities (Domain Objects)            │
│  - Recommendation                       │
│  - AdviceRequest                        │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│   Database (SQLite)                     │
│  - recommendations table                │
│  - advice_requests table                │
└─────────────────────────────────────────┘
```

---

## Key Components

### 1. Controllers

**RecommendationController** (`src/Controller/RecommendationController.php`)
- `POST /api/recommendations` - Generate recommendation
- `GET /api/recommendations/{id}` - Get recommendation
- `GET /api/recommendations/{id}/files` - Get markdown files
- `GET /api/recommendations/{id}/download` - Download ZIP

**AdviceRequestController** (`src/Controller/AdviceRequestController.php`)
- `POST /api/advice-requests` - Create advice request
- `GET /api/advice-requests` - List advice requests
- `GET /api/advice-requests/{id}` - Get advice request
- `GET /api/technologies` - List technologies
- `GET /api/technologies/{id}` - Get technology
- `POST /api/compare` - Compare technologies
- `GET /api/health` - Health check

### 2. Services

**StackRecommender** (`src/Service/RuleEngine/StackRecommender.php`)
- Loads 20+ technologies from `data/knowledge/technologies.json`
- Calculates weighted scores based on:
  - Objective (learning, mvp, performance, balanced)
  - Profile (beginner, intermediate, advanced)
  - Use cases and features
  - Constraints
- Returns top 3 recommendations with justifications
- Provides comparison functionality

**MarkdownGenerator** (`src/Service/MarkdownGenerator/MarkdownGenerator.php`)
- Generates 5 dynamic markdown files:
  1. PROJECT.md - Project overview
  2. STACK.md - Technology details
  3. CONVENTIONS.md - Code conventions
  4. SETUP.md - Installation guide
  5. LIBRARIES.md - Recommended libraries

### 3. Entities

**Recommendation**
- `id` (UUID) - Primary key
- `answersJson` - Questionnaire responses
- `resultJson` - Recommendation result
- `createdAt` - Creation timestamp

**AdviceRequest**
- `id` (int) - Primary key
- `name`, `email`, `subject`, `message` - Request details
- `recommendationId` - Optional reference to recommendation
- `status` - pending/answered/archived
- `createdAt`, `answeredAt` - Timestamps

### 4. Data

**technologies.json** - Technology database with 20+ entries including:
- Frameworks: Next.js, Nuxt, Astro, React, Vue, Svelte
- Backends: Express, FastAPI, Django, NestJS, Laravel
- Databases: PostgreSQL, MongoDB, Redis
- Tools: TypeScript, Tailwind, Prisma, Stripe, GraphQL

---

## Configuration Files

### Framework Configuration
- `config/packages/framework.yaml` - Core Symfony settings
- `config/packages/doctrine.yaml` - ORM mapping and database
- `config/packages/nelmio_cors.yaml` - CORS headers

### Environment-Specific
- `config/packages/dev/debug.yaml` - Development debug settings
- `config/packages/prod/doctrine.yaml` - Production caching
- `config/packages/test/framework.yaml` - Test configuration

### Routing
- `config/routes.yaml` - Controller route discovery

### Services
- `config/services.yaml` - Service container configuration

---

## Database Schema

### recommendations table
```sql
CREATE TABLE recommendations (
    id VARCHAR(36) PRIMARY KEY,
    answers_json CLOB NOT NULL,
    result_json CLOB NOT NULL,
    created_at DATETIME NOT NULL
);
```

### advice_requests table
```sql
CREATE TABLE advice_requests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255),
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message CLOB NOT NULL,
    recommendation_id VARCHAR(36),
    questionnaire_snapshot CLOB,
    status VARCHAR(50) DEFAULT 'pending',
    created_at DATETIME NOT NULL,
    answered_at DATETIME
);
```

---

## Dependencies

### Core Symfony 8
- symfony/framework-bundle
- symfony/console
- symfony/serializer
- symfony/validator
- symfony/yaml

### Database
- doctrine/doctrine-bundle
- doctrine/orm
- doctrine/migrations-bundle

### CORS
- nelmio/cors-bundle

### Development
- symfony/maker-bundle
- phpunit/phpunit

---

## Testing

**Unit Tests**: `tests/Service/RuleEngine/StackRecommenderTest.php`
- Test technology loading
- Test recommendation generation with various objectives
- Test technology filtering and scoring
- Test comparison functionality

**Run Tests**:
```bash
make test
# or
php bin/phpunit
```

---

## Quick Commands

```bash
# Setup
make setup                  # Full initial setup
./bin/setup.sh             # Alternative setup script

# Development
make serve                 # Start development server
make cache-clear           # Clear cache
make lint                  # Lint YAML and container

# Database
make db-setup              # Create and migrate database
make db-fresh              # Drop and recreate database
make db-migrate            # Run migrations only

# Testing
make test                  # Run all tests

# Utilities
make help                  # Show all available commands
make routes                # List all routes
make env-check             # Check environment
```

---

## Security Considerations

1. **No Authentication Required** (Currently)
   - Suitable for public API
   - Can be secured with JWT/OAuth in future

2. **CORS Enabled**
   - Configured in `nelmio_cors.yaml`
   - Allow all methods for `/api/*` routes

3. **Input Validation**
   - Email validation on advice requests
   - JSON schema validation for recommendations

4. **Error Handling**
   - Global exception listener
   - Custom exceptions for specific errors
   - Safe error messages in production

---

## Performance Features

1. **Service Caching**
   - Technology data cached in memory
   - Database queries optimized with repositories

2. **Streaming Downloads**
   - ZIP generation uses streaming response
   - Efficient file handling

3. **Database Efficiency**
   - SQLite for simplicity and performance
   - Proper indexes on commonly queried fields

---

## Extensibility

### Adding New Technologies
1. Edit `data/knowledge/technologies.json`
2. Add new technology object
3. Run database seeds if needed

### Adding New Services
1. Create service in `src/Service/`
2. Configure in `config/services.yaml`
3. Inject into controller via constructor

### Adding New Endpoints
1. Create controller method
2. Use `#[Route]` attribute
3. Return JsonResponse

### Adding Database Tables
1. Create entity in `src/Entity/`
2. Run `php bin/console make:migration`
3. Review and execute migration

---

## Development Workflow

```
1. Create feature branch
2. Make code changes
3. Run tests: make test
4. Lint code: make lint
5. Commit changes
6. Create pull request
7. Merge to develop
8. Deploy to production
```

---

## Documentation Files

- **README.md** - Main project documentation
- **API.md** - Complete API reference with examples
- **INSTALLATION.md** - Setup guide with troubleshooting
- **STRUCTURE.md** - This file, project architecture

---

## Version Control

- **Git Repository**: Project tracked with Git
- **.gitignore**: Excludes `/var/`, `/vendor/`, environment files
- **Commit Strategy**: Feature branches with meaningful messages

---

## Deployment

### Development
```bash
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony serve
```

### Production
```bash
composer install --no-dev --optimize-autoloader
php bin/console cache:warmup --env=prod
php bin/console doctrine:migrations:migrate --env=prod
# Configure web server (Nginx/Apache with PHP-FPM)
```

### Docker
```bash
docker-compose up -d
```

---

## Support Resources

- **Symfony Documentation**: https://symfony.com/doc
- **Doctrine ORM**: https://www.doctrine-project.org/
- **Technologies Data**: `data/knowledge/technologies.json`
- **API Examples**: See API.md

---

## Next Steps

1. Install dependencies: `composer install`
2. Setup database: `make db-setup`
3. Start server: `make serve`
4. Test API: `curl http://localhost:8000/api/health`
5. Generate recommendation: See API.md examples
6. Run tests: `make test`

Enjoy building with ProjectAdvisor!
