.PHONY: help setup install serve test db-setup db-migrate db-fresh db-seed clean cache-clear

help:
	@echo "ProjectAdvisor Backend Commands"
	@echo "==============================="
	@echo "make setup          - Initial setup (install + database setup)"
	@echo "make install        - Install dependencies"
	@echo "make serve          - Start development server"
	@echo "make test           - Run tests"
	@echo "make db-setup       - Create database"
	@echo "make db-migrate     - Run migrations"
	@echo "make db-fresh       - Drop and recreate database"
	@echo "make db-seed        - Seed database with sample data"
	@echo "make cache-clear    - Clear application cache"
	@echo "make clean          - Clean cache and logs"

setup: install db-setup
	@echo "✅ Setup complete!"

install:
	@echo "📦 Installing dependencies..."
	composer install
	@echo "✅ Dependencies installed"

serve:
	@echo "🚀 Starting development server..."
	symfony serve

test:
	@echo "🧪 Running tests..."
	php bin/phpunit

db-setup:
	@echo "🗄️  Setting up database..."
	php bin/console doctrine:database:create --if-not-exists
	php bin/console doctrine:migrations:migrate --no-interaction
	@echo "✅ Database ready"

db-migrate:
	@echo "🔄 Running migrations..."
	php bin/console doctrine:migrations:migrate --no-interaction

db-fresh:
	@echo "🔄 Dropping and recreating database..."
	php bin/console doctrine:database:drop --force --if-exists
	php bin/console doctrine:database:create
	php bin/console doctrine:migrations:migrate --no-interaction
	@echo "✅ Database reset"

db-seed:
	@echo "🌱 Seeding database..."
	@echo "No seeders defined yet"

cache-clear:
	@echo "🧹 Clearing cache..."
	php bin/console cache:clear
	@echo "✅ Cache cleared"

clean: cache-clear
	@echo "🧹 Cleaning up..."
	rm -rf var/cache/* var/log/*
	@echo "✅ Cleanup complete"

lint:
	@echo "🔍 Linting code..."
	php bin/console lint:yaml config/
	php bin/console lint:container
	@echo "✅ Linting complete"

routes:
	@echo "📋 Available routes:"
	php bin/console debug:router

env-check:
	@echo "🔍 Checking environment..."
	@php -v
	@composer --version
	@echo "✅ Environment check complete"
