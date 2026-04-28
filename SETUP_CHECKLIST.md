# 🚀 MicroApp Setup Checklist

## ✅ Already Configured

### Backend
- [x] `backend/.env` - All configuration files created with proper database, Redis, n8n, and admin settings
- [x] Database settings configured for PostgreSQL container
- [x] Queue connection set to Redis
- [x] Sanctum CORS configured for frontend
- [x] n8n webhook URL configured

### Frontend
- [x] `frontend/.env` - Vite configuration created
- [x] API base URL pointing to backend

### Docker
- [x] `docker-compose.yml` updated with n8n service
- [x] n8n configured with PostgreSQL persistence
- [x] Named volume `microapp` configured for n8n data
- [x] All services configured with proper networking

## 🔄 What You Need to Do Now

### Step 1: Create Docker Volume
```bash
docker volume create microapp
```

### Step 2: Start All Services

**Option A - Automatic (Recommended)**
- Windows: Double-click `setup.bat`
- Mac/Linux: Run `bash setup.sh`

**Option B - Manual**
```bash
cd c:\MicroApp
docker-compose up -d
```

### Step 3: Verify Services Are Running
```bash
docker-compose ps
```

You should see 8 containers running:
- ✅ microapp_postgres
- ✅ microapp_redis
- ✅ microapp_memcached
- ✅ microapp_mailpit
- ✅ microapp_backend
- ✅ microapp_queue
- ✅ microapp_frontend
- ✅ microapp_n8n

### Step 4: Access Your Application

| Service | URL | Purpose |
|---------|-----|---------|
| Frontend | http://localhost:5173 | React app - citizen & admin portal |
| Backend API | http://localhost:8000 | Laravel API |
| n8n Dashboard | http://localhost:5678 | Workflow automation |
| Mailpit | http://localhost:8025 | Email testing interface |
| Database | localhost:5432 | PostgreSQL (user: postgres) |
| Redis | localhost:6379 | Cache & session store |

### Step 5: Set Up n8n Workflows

1. Go to http://localhost:5678
2. Create your first user account
3. Create a new workflow:
   - Add **Webhook** node → POST to `/peso-event-email-alert`
   - Add **PostgreSQL** node → Connect to microapp_db
   - Add **Send Email** node → Use Mailpit SMTP
4. Save and activate the workflow

## 🔐 Admin Access

- Email: `admin@microapp.local`
- Password: `P3$0!`
- Access: http://localhost:5173 → Click "Admin" → Enter password

## 📚 Useful Commands

### View Logs
```bash
docker-compose logs -f              # All services
docker-compose logs -f backend       # Specific service
docker-compose logs -f n8n          # n8n logs
```

### Database Operations
```bash
# Run migrations manually
docker-compose exec backend php artisan migrate

# Seed data
docker-compose exec backend php artisan db:seed

# Reset database
docker-compose exec backend php artisan migrate:refresh --seed

# Access database directly
docker-compose exec postgres psql -U postgres -d microapp_db
```

### Stop/Restart Services
```bash
docker-compose stop                 # Stop (keeps data)
docker-compose restart              # Restart
docker-compose down                 # Remove containers (keeps volumes)
docker-compose down -v              # Remove everything (⚠️ deletes data)
```

### View Service Status
```bash
docker-compose ps                   # All services
docker-compose exec backend php artisan tinker  # Laravel shell
docker-compose exec redis redis-cli  # Redis shell
```

## 🔧 Environment Variables

### Backend (backend/.env)
```env
DB_HOST=postgres              # Container name (not localhost in Docker)
REDIS_HOST=redis              # Container name
N8N_WEBHOOK_URL=http://localhost:5678/webhook/peso-event-email-alert
ADMIN_PORTAL_PASSWORD=P3$0!
```

### Frontend (frontend/.env)
```env
VITE_API_BASE_URL=http://localhost:8000/api
```

## 📦 Named Volume Details

The `microapp` named volume stores n8n data:
```bash
docker volume ls                    # List volumes
docker volume inspect microapp      # View volume details
docker volume rm microapp           # Delete volume (⚠️ deletes all n8n data)
```

## 🐛 Troubleshooting

### Services won't start
```bash
# Check Docker is running
docker ps

# View service logs
docker-compose logs -f

# Rebuild images
docker-compose build --no-cache
```

### Port already in use
- Frontend port 5173: Change in docker-compose.yml
- Backend port 8000: Change in docker-compose.yml
- n8n port 5678: Change in docker-compose.yml

### Database connection failed
```bash
# Restart database
docker-compose restart postgres

# Check database is healthy
docker-compose ps postgres
```

### Frontend can't connect to backend
- Check backend is running: http://localhost:8000/api
- Check CORS settings in backend
- Check firewall settings

## 📖 Full Documentation

- **[COMPLETE_SETUP_GUIDE.md](COMPLETE_SETUP_GUIDE.md)** - Detailed setup for all components
- **[DOCKER_SETUP.md](DOCKER_SETUP.md)** - Docker-specific commands and troubleshooting
- **[README.md](README.md)** - Project overview

---

## 🎯 Quick Start Command

```bash
# Windows
setup.bat

# Mac/Linux
bash setup.sh
```

That's it! Your full MicroApp stack should be running in ~30 seconds.
