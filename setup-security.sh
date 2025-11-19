#!/bin/bash
# Quick Security Setup Script for Band Cafe

echo "🔒 Band Cafe Security Setup"
echo "=============================="
echo ""

# Create .env file from example
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    echo "✓ .env file created"
    echo ""
    echo "⚠️  IMPORTANT: Edit .env and change the default passwords!"
    echo "   - DB_PASSWORD"
    echo "   - DB_ROOT_PASSWORD"
    echo ""
else
    echo "ℹ️  .env file already exists"
fi

# Create logs directory
if [ ! -d logs ]; then
    echo "📁 Creating logs directory..."
    mkdir -p logs
    chmod 750 logs
    echo "✓ Logs directory created with proper permissions"
else
    echo "ℹ️  Logs directory already exists"
    chmod 750 logs
fi

# Set proper permissions
echo ""
echo "🔐 Setting proper permissions..."
chmod 600 .env 2>/dev/null || echo "⚠️  Could not set .env permissions (file may not exist yet)"
chmod 750 includes 2>/dev/null

echo ""
echo "✅ Security setup complete!"
echo ""
echo "Next steps:"
echo "1. Edit .env and set strong passwords"
echo "2. Run: docker-compose up -d"
echo "3. Test the security features"
echo ""
echo "For more details, see SECURITY_IMPLEMENTATION.md"
