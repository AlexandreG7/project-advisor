#!/bin/bash

set -e

echo "🚀 Setting up ProjectAdvisor Backend..."

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer first."
    exit 1
fi

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.5+ first."
    exit 1
fi

# Install dependencies
echo "📦 Installing PHP dependencies..."
composer install

# Create .env.local if it doesn't exist
if [ ! -f .env.local ]; then
    echo "🔧 Creating .env.local..."
    cp .env.example .env.local

    # Generate APP_SECRET
    SECRET=$(php -r 'echo bin2hex(random_bytes(16));')
    sed -i.bak "s/your_32_character_secret_key_here/$SECRET/" .env.local
    rm -f .env.local.bak
    echo "✅ .env.local created with generated secret"
else
    echo "⚠️  .env.local already exists, skipping..."
fi

# Create database directory
echo "📁 Creating database directory..."
mkdir -p var

# Create and migrate database
echo "🗄️  Creating database..."
php bin/console doctrine:database:create --if-not-exists

echo "🔄 Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Clear cache
echo "🧹 Clearing cache..."
php bin/console cache:clear

# Set permissions
echo "🔐 Setting directory permissions..."
chmod -R 777 var/ || true

echo ""
echo "✅ Setup complete!"
echo ""
echo "📖 Next steps:"
echo "   1. Start the server: symfony serve"
echo "   2. Test the API: curl http://localhost:8000/api/health"
echo "   3. Check README.md for API documentation"
echo ""
