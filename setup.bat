@echo off
setlocal enabledelayedexpansion

echo 🚀 Starting MicroApp Docker Setup...
echo.

:: Check if Docker is installed
docker --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker is not installed. Please install Docker Desktop first.
    exit /b 1
)

docker-compose --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker Compose is not installed. Please install Docker Desktop first.
    exit /b 1
)

echo ✅ Docker is installed
echo.

:: Check if .env files exist
if not exist "backend\.env" (
    echo ❌ backend\.env file not found
    exit /b 1
)

if not exist "frontend\.env" (
    echo ❌ frontend\.env file not found
    exit /b 1
)

echo ✅ Environment files found
echo.

echo 📦 Creating Docker volume for n8n...
docker volume create microapp
if errorlevel 1 (
    echo ⚠️  Volume already exists ^(this is OK^)
)

echo.
echo 📦 Building Docker images...
docker-compose build

echo.
echo 🚀 Starting services...
docker-compose up -d

echo.
echo ⏳ Waiting for services to be ready...
timeout /t 30 /nobreak

echo.
echo ✅ Services started!
echo.
echo 📋 Service Status:
docker-compose ps
echo.
echo 📍 Access your application at:
echo    Frontend: http://localhost:5173
echo    Backend API: http://localhost:8000
echo    n8n Dashboard: http://localhost:5678
echo    Mailpit ^(Email Testing^): http://localhost:8025
echo.
echo 👤 Admin Credentials:
echo    Email: admin@microapp.local
echo    Password: P3$0!
echo.
echo 📖 Documentation:
echo    COMPLETE_SETUP_GUIDE.md - Full setup documentation
echo    DOCKER_SETUP.md - Docker-specific commands
echo.
pause
echo 📍 Access your application at:
echo    Frontend: http://localhost:5173
echo    Backend: http://localhost:8000
echo    Mailpit (Email Testing): http://localhost:8025
echo.
echo 📋 Useful commands:
echo    View logs: docker-compose logs -f
echo    Stop services: docker-compose stop
echo    Restart services: docker-compose restart
echo    Remove everything: docker-compose down -v
echo.
echo 📖 Full setup guide: See DOCKER_SETUP.md
echo.
pause
