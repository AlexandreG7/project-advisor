# ProjectAdvisor Backend - Installation Guide

## System Requirements

- **PHP**: 8.5 or higher
- **Composer**: Latest version
- **SQLite**: 3.0+
- **Node.js**: (Optional, for frontend development)

## Quick Start

### 1. Clone the Repository

```bash
cd project-advisor/backend
```

### 2. Automated Setup (Recommended)

```bash
./bin/setup.sh
```

This will:
- Install PHP dependencies with Composer
- Create `.env.local` with a generated secret
- Create the database
- Run migrations
- Set proper permissions

### 3. Start the Development Server

```bash
symfony serve
# Or without Symfony CLI:
php -S localhost:8000 -t public/
```

The API will be available at `http://localhost:8000/api`

---

## Manual Setup (Alternative)

If you prefer to set up manually:

### 1. Install Dependencies

```bash
composer install
```

### 2. Configure Environment

```bash
cp .env.example .env.local
```

Edit `.env.local` and set your configuration:
```env
APP_ENV=dev
APP_SECRET=your_random_32_character_secret
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

### 3. Create Database and Run Migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 4. Set Permissions (if needed)

```bash
chmod -R 777 var/
```

### 5. Start Server

```bash
symfony serve
# Or:
php -S localhost:8000 -t public/
```

---

## Using Make Commands

If `make` is installed, use these convenient commands:

```bash
make setup           # Full setup
make install         # Install dependencies
make serve           # Start development server
make test            # Run tests
make db-fresh        # Reset database
make cache-clear     # Clear cache
```

See `Makefile` for all available commands.

---

## Docker Setup (Optional)

### Build and Run with Docker

```bash
docker-compose up -d
```

Access the API at `http://localhost:8000/api`

---

## Verification

### Check API Health

```bash
curl http://localhost:8000/api/health
```

Expected response:
```json
{
  "status": "ok",
  "timestamp": "2024-03-26T12:00:00+00:00",
  "service": "ProjectAdvisor API"
}
```

### List Available Technologies

```bash
curl http://localhost:8000/api/technologies
```

### Test Recommendation Generation

```bash
curl -X POST http://localhost:8000/api/recommendations \
  -H "Content-Type: application/json" \
  -d '{
    "objective": "mvp",
    "profile": "intermediate",
    "useCases": ["web-app"],
    "features": [],
    "constraints": [],
    "preferences": []
  }'
```

---

## Database Management

### Create Database

```bash
php bin/console doctrine:database:create
```

### Run Migrations

```bash
php bin/console doctrine:migrations:migrate
```

### Create New Migration

```bash
php bin/console make:migration
```

### Drop Database (Destructive!)

```bash
php bin/console doctrine:database:drop --force
```

---

## Development Tools

### Clear Cache

```bash
php bin/console cache:clear
php bin/console cache:warmup
```

### Check Routes

```bash
php bin/console debug:router
```

### Lint YAML Configuration

```bash
php bin/console lint:yaml config/
```

### Check Container Configuration

```bash
php bin/console debug:container
```

---

## Testing

### Run All Tests

```bash
php bin/phpunit
```

### Run Specific Test File

```bash
php bin/phpunit tests/Service/RuleEngine/StackRecommenderTest.php
```

### Run with Code Coverage

```bash
php bin/phpunit --coverage-html coverage/
```

---

## Troubleshooting

### Issue: "Composer not found"

**Solution**: Install Composer from https://getcomposer.org

### Issue: "PHP version too old"

**Solution**: Install PHP 8.5+ from https://www.php.net/

### Issue: "Database file permission denied"

**Solution**:
```bash
chmod -R 777 var/
```

### Issue: "Migrations not running"

**Solution**:
```bash
php bin/console doctrine:migrations:migrate --force
```

### Issue: "Port 8000 already in use"

**Solution**: Use a different port:
```bash
php -S localhost:9000 -t public/
```

Or with Symfony CLI:
```bash
symfony serve --port=9000
```

### Issue: Cache issues

**Solution**: Clear cache:
```bash
php bin/console cache:clear
rm -rf var/cache/*
```

---

## Environment Variables

### Required

- `APP_ENV`: `dev` or `prod`
- `APP_SECRET`: Random 32+ character string
- `DATABASE_URL`: Database connection string

### Optional

- `CORS_ALLOW_ORIGIN`: CORS origin pattern (default: `^https?://localhost(:[0-9]+)?$`)
- `DATABASE_TIMEOUT`: Connection timeout in seconds

### Example `.env.local`

```env
APP_ENV=dev
APP_SECRET=abc123def456ghi789jkl012mno345pqr
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
CORS_ALLOW_ORIGIN=^https?://localhost(:[0-9]+)?$
```

---

## Production Deployment

### 1. Install Production Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 2. Set Environment to Production

```env
APP_ENV=prod
APP_DEBUG=0
```

### 3. Warm Up Cache

```bash
php bin/console cache:warmup --env=prod
```

### 4. Run Migrations

```bash
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

### 5. Set Proper Permissions

```bash
chmod -R 755 var/
chown -R www-data:www-data var/
```

### 6. Configure Web Server

Use a reverse proxy (Nginx/Apache) to forward requests to PHP-FPM.

---

## Support

For issues or questions:
1. Check the README.md file
2. Review the API documentation in README.md
3. Check logs in `var/log/`
4. Run tests to verify installation

---

## Next Steps

1. Read the [API Documentation](./README.md)
2. Create your first recommendation via the API
3. Explore the [Controller documentation](./src/Controller/)
4. Check out [Service implementations](./src/Service/)

Enjoy building with ProjectAdvisor!
