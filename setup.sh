#!/bin/bash
set -e

echo "🚀 Starting MicroApp Docker Setup..."
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker Desktop first."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Desktop first."
    exit 1
fi

echo "✅ Docker is installed"
echo ""

# Check if .env files exist
if [ ! -f "backend/.env" ]; then
    echo "❌ backend/.env file not found"
    exit 1
fi

if [ ! -f "frontend/.env" ]; then
    echo "❌ frontend/.env file not found"
    exit 1
fi

echo "✅ Environment files found"
echo ""

# Create Docker volume for n8n
echo "📦 Creating Docker volume for n8n..."
docker volume create microapp || echo "⚠️  Volume already exists (this is OK)"

echo ""
echo "📦 Building Docker images..."
docker-compose build

echo ""
echo "🚀 Starting services..."
docker-compose up -d

echo ""
echo "⏳ Waiting for services to be ready..."
sleep 30

echo ""
echo "✅ Services started!"
echo ""
echo "📍 Access your application at:"
echo "   Frontend: http://localhost:5173"
echo "   Backend: http://localhost:8000"
echo "   n8n: http://localhost:5678"
echo "   Mailpit (Email Testing): http://localhost:8025"
echo ""
echo "👤 Admin Credentials:"
echo "   Email: admin@microapp.local"
echo "   Password: P3\$0!"
echo ""
echo "📋 Useful commands:"
echo "   View logs: docker-compose logs -f"
echo "   Stop services: docker-compose stop"
echo "   Restart services: docker-compose restart"
echo "   Remove everything: docker-compose down -v"
echo ""
echo "📖 Full setup guide: See COMPLETE_SETUP_GUIDE.md"
