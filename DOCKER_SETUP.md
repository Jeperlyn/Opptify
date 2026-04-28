# Docker Setup Guide for MicroApp

This guide walks you through setting up and running the complete MicroApp environment using Docker.

## Prerequisites

- Docker Desktop installed (includes Docker Engine and Docker Compose)
- Git (optional, but recommended)
- 4GB+ RAM available for Docker

## Quick Start

### 1. Generate Laravel Application Key

Before starting the containers, you need to generate the APP_KEY for Laravel:

```bash
cd backend
php artisan key:generate
```

This will update your `.env` file with an encrypted key.

### 2. Start All Services

From the project root directory, run:

```bash
docker-compose up -d
```

This command will:
- Build the Docker images
- Pull required base images
- Start all services in the background

### 3. Verify Services Are Running

Check if all containers are running:

```bash
docker-compose ps
```

You should see:
- `microapp_postgres` - Database
- `microapp_redis` - Cache
- `microapp_memcached` - Session/Cache
- `microapp_mailpit` - Email testing
- `microapp_backend` - Laravel API
- `microapp_queue` - Job queue worker
- `microapp_frontend` - React frontend

### 4. Access Your Application

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000
- **Mailpit (Email Testing)**: http://localhost:8025
- **API Documentation**: http://localhost:8000/api (if available)

## Database Setup

The database migrations and seeders run automatically when the backend container starts. If you need to run them manually:

```bash
# Run migrations
docker-compose exec backend php artisan migrate

# Run seeders
docker-compose exec backend php artisan db:seed

# Both together
docker-compose exec backend php artisan migrate:refresh --seed
```

## Common Commands

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f backend
docker-compose logs -f frontend
docker-compose logs -f postgres
```

### Execute Commands in Containers

```bash
# Run artisan command
docker-compose exec backend php artisan <command>

# Run npm command in frontend
docker-compose exec frontend npm <command>

# Access database
docker-compose exec postgres psql -U postgres -d MicroApp_db

# View Redis
docker-compose exec redis redis-cli
```

### Restart Services

```bash
# Restart specific service
docker-compose restart backend

# Restart all services
docker-compose restart

# Stop and start
docker-compose stop
docker-compose start
```

### Clear Laravel Cache

```bash
docker-compose exec backend php artisan cache:clear
docker-compose exec backend php artisan config:clear
docker-compose exec backend php artisan view:clear
```

### Queue Jobs Monitoring

Queue jobs are processed by the `microapp_queue` service. To monitor:

```bash
# View queue jobs table
docker-compose exec backend php artisan queue:work --verbose

# Or check database
docker-compose exec postgres psql -U postgres -d MicroApp_db -c "SELECT * FROM jobs;"
```

## Stopping Services

```bash
# Stop all running containers (keeps data)
docker-compose stop

# Stop and remove containers (keeps volumes)
docker-compose down

# Remove everything including volumes (CAUTION: deletes database)
docker-compose down -v
```

## Troubleshooting

### Containers Keep Restarting

Check logs:
```bash
docker-compose logs backend
```

Common issues:
- Database not ready: Wait a moment and check if `postgres` container is healthy
- Missing `.env` file: Ensure `.env` files exist in `backend/` and `frontend/`
- Port conflicts: Another service is using ports 5173, 8000, 5432, etc.

### Database Connection Error

```bash
# Verify database is running
docker-compose ps postgres

# Check database connectivity
docker-compose exec backend php artisan tinker
>>> DB::connection()->getPdo()
```

### Frontend Can't Connect to Backend API

Ensure `VITE_API_BASE_URL` in `frontend/.env` matches your setup:
```
VITE_API_BASE_URL=http://localhost:8000/api
```

And in `backend/.env`, `APP_URL` is set correctly:
```
APP_URL=http://localhost:8000
```

### Redis Connection Issues

```bash
# Check Redis
docker-compose exec redis redis-cli ping
# Should return: PONG
```

### Permission Issues (Linux/Mac)

If you encounter permission errors:

```bash
# Fix ownership
sudo chown -R $USER:$USER backend/storage backend/bootstrap/cache

# Or use Docker to fix
docker-compose exec backend chown -R www-data:www-data storage bootstrap/cache
```

## Production Setup

For production, use the Nginx reverse proxy:

```bash
docker-compose --profile production up -d
```

This adds an Nginx container that routes traffic to both backend and frontend.

Access through: http://localhost (port 80)

## Development Workflows

### Adding New Dependencies

**Backend (PHP/Composer):**
```bash
docker-compose exec backend composer require vendor/package
```

**Frontend (JavaScript/npm):**
```bash
docker-compose exec frontend npm install package-name
```

### Running Tests

```bash
# Laravel tests
docker-compose exec backend php artisan test

# Frontend linting
docker-compose exec frontend npm run lint
```

### Building Frontend for Production

```bash
docker-compose exec frontend npm run build
```

Output will be in `frontend/dist/`

## Useful Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Docker Documentation](https://laravel.com/docs/deployment)
- [PostgreSQL Docker Documentation](https://hub.docker.com/_/postgres)

## Support

If you encounter issues:
1. Check the logs: `docker-compose logs -f <service>`
2. Verify all services are healthy: `docker-compose ps`
3. Try rebuilding: `docker-compose build --no-cache`
4. Clear everything and start fresh: `docker-compose down -v && docker-compose up -d`
