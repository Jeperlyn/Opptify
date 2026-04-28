# Complete Setup Guide for MicroApp

This guide covers setting up the backend, frontend, and Docker with n8n integration.

---

## Part 1: Backend Setup

### Step 1.1: Create Backend Environment File

Create `backend/.env` with the following configuration:

```env
# Application
APP_NAME=MicroApp
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=microapp_db
DB_USERNAME=postgres
DB_PASSWORD=postgres

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache & Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=cookie
QUEUE_CONNECTION=redis

# Mail (Mailpit for local testing)
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=admin@microapp.local
MAIL_FROM_NAME="MicroApp"

# Admin Portal
ADMIN_PORTAL_EMAIL=admin@microapp.local
ADMIN_PORTAL_PASSWORD=P3$0!

# n8n Webhook Configuration
N8N_WEBHOOK_URL=http://localhost:5678/webhook/peso-event-email-alert
N8N_WEBHOOK_SECRET=your_shared_secret_key_here

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173

# Broadcasting
BROADCAST_DRIVER=redis

# Logging
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
```

### Step 1.2: Install Backend Dependencies

**Option A: Using Docker (Recommended for Docker setup)**
```bash
cd backend
# Dependencies will be installed when Docker container starts
```

**Option B: Local Setup (for standalone backend)**
```bash
cd backend
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

### Step 1.3: Backend Directory Structure

Ensure these key directories exist:
- `backend/app` - Application code
- `backend/database` - Migrations and seeders
- `backend/routes` - API routes
- `backend/storage` - Logs, cache, uploads

---

## Part 2: Frontend Setup

### Step 2.1: Create Frontend Environment File

Create `frontend/.env` with the following:

```env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_APP_NAME=MicroApp
```

### Step 2.2: Install Frontend Dependencies

```bash
cd frontend
npm install
```

### Step 2.3: Start Frontend Development Server

```bash
npm run dev
```

This will start the Vite dev server at `http://localhost:5173`

### Step 2.4: Build for Production

```bash
npm run build
```

---

## Part 3: Docker Setup with n8n Integration

### Step 3.1: Update docker-compose.yml

Your `docker-compose.yml` needs to include n8n service. See the updated file section below for complete configuration.

The key addition is:

```yaml
  n8n:
    image: n8n:latest
    container_name: microapp_n8n
    ports:
      - "5678:5678"
    environment:
      - N8N_HOST=localhost
      - N8N_PORT=5678
      - N8N_PROTOCOL=http
      - WEBHOOK_TUNNEL_URL=http://localhost:5678/
      - DB_TYPE=postgres
      - DB_POSTGRESDB_HOST=postgres
      - DB_POSTGRESDB_PORT=5432
      - DB_POSTGRESDB_DATABASE=n8n_db
      - DB_POSTGRESDB_USER=postgres
      - DB_POSTGRESDB_PASSWORD=postgres
    volumes:
      - microapp:/home/node/.n8n
    depends_on:
      - postgres
```

### Step 3.2: Create Docker Named Volume

Before running Docker, create the named volume for n8n:

```bash
docker volume create microapp
```

### Step 3.3: Start All Services

```bash
# From project root
docker-compose up -d

# Verify all services are running
docker-compose ps
```

### Step 3.4: Verify Services

Expected services:
- `microapp_postgres` - Database
- `microapp_redis` - Cache
- `microapp_memcached` - Session store
- `microapp_mailpit` - Email testing (http://localhost:8025)
- `microapp_backend` - Laravel API (http://localhost:8000)
- `microapp_queue` - Job queue worker
- `microapp_frontend` - React frontend (http://localhost:5173)
- `microapp_n8n` - n8n workflow engine (http://localhost:5678)

### Step 3.5: Access Your Application

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000
- **n8n**: http://localhost:5678
- **Mailpit**: http://localhost:8025
- **Database**: localhost:5432 (user: postgres, password: postgres)
- **Redis**: localhost:6379

---

## n8n Webhook Configuration

### Step 4.1: Access n8n Dashboard

1. Open http://localhost:5678
2. Create your first user account
3. Create a new workflow

### Step 4.2: Create Webhook Listener

1. In n8n, add a **Webhook** node
2. Set the method to `POST`
3. Set the webhook URL path to: `/peso-event-email-alert`
4. The full URL will be: `http://localhost:5678/webhook/peso-event-email-alert`

### Step 4.3: Connect to Laravel Backend

Add this to your workflow:
1. Add a **PostgreSQL** node
2. Configure with:
   - Host: `postgres`
   - Database: `microapp_db`
   - User: `postgres`
   - Password: `postgres`
3. Query subscribed emails from Laravel

### Step 4.4: Send Email Notifications

1. Add **Send Email** node
2. Use SMTP (Mailpit) for testing:
   - Host: `mailpit`
   - Port: `1025`
3. Send to fetched email addresses

---

## Common Commands

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f backend
docker-compose logs -f frontend
docker-compose logs -f n8n
docker-compose logs -f postgres
```

### Execute Commands

```bash
# Run Laravel artisan command
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan db:seed

# Access database
docker-compose exec postgres psql -U postgres -d microapp_db

# Access Redis
docker-compose exec redis redis-cli

# View n8n logs
docker-compose logs -f n8n
```

### Database Management

```bash
# Fresh migration with seeds
docker-compose exec backend php artisan migrate:refresh --seed

# Clear Laravel cache
docker-compose exec backend php artisan cache:clear
docker-compose exec backend php artisan config:clear
```

### Stop and Clean Up

```bash
# Stop all containers (keeps data)
docker-compose stop

# Remove containers (keeps volumes)
docker-compose down

# Remove everything including volumes
docker-compose down -v

# Remove specific volume
docker volume rm microapp
```

---

## Troubleshooting

### Port Already in Use
If a port is already in use, either:
1. Stop the process using that port
2. Change the port mapping in docker-compose.yml

### Database Connection Failed
- Verify PostgreSQL is running: `docker-compose ps | grep postgres`
- Check credentials in .env match docker-compose.yml

### Frontend Can't Connect to Backend
- Verify backend is running on port 8000
- Check `VITE_API_BASE_URL` in frontend/.env
- Check CORS settings in backend

### n8n Can't Reach Backend
- Ensure containers are on same Docker network
- Use service names instead of localhost (e.g., `backend:8000`)
- Check firewall rules

---

## Admin Credentials (Pre-seeded)

- Email: `admin@microapp.local`
- Password: `P3$0!`

Change these in production!

---

## Next Steps

1. ✅ Create `.env` files for backend and frontend
2. ✅ Run `docker volume create microapp`
3. ✅ Update `docker-compose.yml` with n8n service
4. ✅ Run `docker-compose up -d`
5. ✅ Access frontend at http://localhost:5173
6. ✅ Configure n8n workflows at http://localhost:5678
