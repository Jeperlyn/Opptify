# MicroApp - Docker Setup Complete! 🎉

## ✅ Files Created

1. **Docker Configuration**
   - `docker-compose.yml` - Orchestrates all services
   - `backend/Dockerfile` - PHP 8.3 + Laravel environment
   - `frontend/Dockerfile` - Node 20 + React environment
   - `nginx.conf` - Production reverse proxy configuration
   - `.dockerignore` - Excludes unnecessary files from Docker builds

2. **Environment Files**
   - `backend/.env` - Updated for Docker (points to Docker services)
   - `frontend/.env` - React Vite configuration

3. **Setup Guides**
   - `DOCKER_SETUP.md` - Comprehensive Docker guide
   - `setup.sh` - Linux/Mac quick start script
   - `setup.bat` - Windows quick start script

## 🚀 Quick Start (Choose One)

### Windows
```bash
# Run the setup script
setup.bat
```

### Mac/Linux
```bash
# Run the setup script
bash setup.sh
```

### Manual (All Platforms)
```bash
# Generate APP_KEY for Laravel (if not already done)
cd backend
php artisan key:generate
cd ..

# Start all services
docker-compose up -d

# Check status
docker-compose ps
```

## 📍 Access Your Application

Once running, access:
- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000
- **Email Testing (Mailpit)**: http://localhost:8025
- **Database**: localhost:5432 (use psql or DB client)
- **Redis**: localhost:6379

## 🧠 What's Running

- **PostgreSQL 16** - Database (port 5432)
- **Redis 7** - Cache (port 6379)
- **Memcached 1.6** - Session cache (port 11211)
- **Mailpit** - Email testing (ports 1025, 8025)
- **Laravel Backend** - API server (port 8000)
- **Queue Worker** - Background job processor
- **React Frontend** - Vite dev server (port 5173)

## 📝 Next Steps

1. **View Logs** (troubleshooting):
   ```bash
   docker-compose logs -f
   ```

2. **Run Migrations** (if not auto-run):
   ```bash
   docker-compose exec backend php artisan migrate
   ```

3. **Add Dependencies**:
   ```bash
   # Backend (PHP)
   docker-compose exec backend composer require vendor/package
   
   # Frontend (JavaScript)
   docker-compose exec frontend npm install package-name
   ```

4. **Monitor Queue Jobs**:
   ```bash
   docker-compose logs -f queue
   ```

5. **Check Emails** (sent from Laravel):
   Open http://localhost:8025 to see emails

## 🛑 Stopping

```bash
# Stop all services (keeps data)
docker-compose stop

# Stop and remove containers (keeps database)
docker-compose down

# Full cleanup (WARNING: deletes database)
docker-compose down -v
```

## 📚 Full Documentation

See **DOCKER_SETUP.md** for:
- Detailed troubleshooting
- Database management
- Testing workflows
- Production setup
- All available commands

## 💡 Tips

- **Hot Reload**: Frontend and Laravel files hot-reload automatically
- **Logs**: Run `docker-compose logs backend` to debug
- **Database Access**: `docker-compose exec postgres psql -U postgres -d MicroApp_db`
- **Clear Cache**: `docker-compose exec backend php artisan cache:clear`

## ⚠️ Important Notes

- **First Run**: Database migrations run automatically on container startup
- **Credentials**: Default DB user is `postgres` with password `postgres` (dev only)
- **Port Conflicts**: If ports 5173, 8000, 5432 are in use, modify `docker-compose.yml`
- **Windows**: Use WSL 2 backend for best performance

---

**Questions?** Check DOCKER_SETUP.md or run: `docker-compose logs -f`
