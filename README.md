# MicroApp

MicroApp is a full-stack PESO operations app with:

- Laravel backend API
- React and Vite frontend
- n8n workflow integration for event email notifications

This guide reflects the current project state.

## Project Structure

- backend: Laravel 10 API, queue jobs, migrations, seeders
- frontend: React SPA for citizen view and admin command center
- backend/n8n/workflows: n8n workflow exports

## Core Features

### Citizen Side

- Program directory with searchable cards
- Public email signup for updates about:
  - Job fairs
  - Employer of the day

### Admin Side

- Password-gated admin access from the frontend
- Event creation endpoint protected by Sanctum plus admin middleware
- Event publish flow triggers queued background notification job

### n8n Integration

- Laravel posts event payloads to n8n webhook
- n8n fetches subscribed emails from Laravel
- n8n sends email notifications

## Backend Setup

1. Open terminal in backend.
2. Install dependencies if needed:
   - composer install
3. Configure backend environment in backend/.env.
4. Run migrations:
   - php artisan migrate
5. Seed data:
   - php artisan db:seed
6. Clear config cache after env changes:
   - php artisan config:clear
7. Start backend:
   - php artisan serve

## Frontend Setup

1. Open terminal in frontend.
2. Install dependencies:
   - npm install
3. Start dev server:
   - npm run dev

Optional checks:

- npm run lint
- npm run build

## Important Environment Variables

In backend/.env:

- PostgreSQL is the expected database engine for this project.
- DB_CONNECTION=pgsql
- DB_HOST=127.0.0.1
- DB_PORT=5432
- DB_DATABASE=microapp_db
- DB_USERNAME=postgres
- DB_PASSWORD=your_password

For n8n:

- N8N_WEBHOOK_URL=http://localhost:5678/webhook/peso-event-email-alert
- N8N_WEBHOOK_SECRET=your_shared_secret

For admin login target account:

- ADMIN_PORTAL_EMAIL=admin@microapp.local

## Admin Access Flow

1. Frontend user clicks Admin.
2. Password prompt appears.
3. Frontend calls POST api/admin/session.
4. Backend validates password against the user with ADMIN_PORTAL_EMAIL and is_admin=true.
5. Backend returns Sanctum token for admin actions.
6. Frontend uses that token for POST api/admin/events.

Seeded local admin defaults:

- Email: admin@microapp.local
- Password: P3$0!

Seeder file:

- backend/database/seeders/AdminUserSeeder.php

## n8n Workflow

Workflow file included in repo:

- backend/n8n/workflows/peso-event-email-alert.json

### Import Steps

1. Open n8n at http://localhost:5678.
2. Import workflow JSON from backend/n8n/workflows/peso-event-email-alert.json.
3. Save workflow.
4. Activate workflow.

### Required n8n Runtime Variables

If your nodes use environment expressions, configure these in n8n runtime:

- BACKEND_API_BASE_URL=http://host.docker.internal:8000
- N8N_WEBHOOK_SECRET=same value as backend .env
- MAIL_FROM_ADDRESS=your_sender_email

In the repo's `docker-compose.yml`, the n8n service now sets `BACKEND_API_BASE_URL` to `http://host.docker.internal:8000` so the workflow can fetch backend event data from the host machine.

If you see access to env vars denied inside n8n expression fields:

- Either set literal values in nodes
- Or run n8n with N8N_BLOCK_ENV_ACCESS_IN_NODE=false

### Webhook URL Pattern

For this project, valid production pattern is:

- http://localhost:5678/webhook/peso-event-email-alert

## API Summary

Public:

- GET api/programs
- GET api/programs/{pesoProgram}
- GET api/events
- POST api/email-subscriptions
- GET api/email-subscriptions (protected by X-N8N-Secret header)
- POST api/admin/session

Authenticated:

- GET api/user

Admin only:

- POST api/admin/events

## Queue and Notification Flow

When admin creates an event with send_email_alert true:

1. Event is saved to database.
2. SendEmailBroadcast job is dispatched.
3. Queue worker processes job.
4. Job posts event ID and type to N8N_WEBHOOK_URL with X-N8N-Secret.
5. n8n fetches the event from the backend API, loads email subscribers, and sends the emails.

Run one job manually:

- php artisan queue:work --once --tries=1

## Quick Smoke Test

1. Ensure backend is running at http://127.0.0.1:8000.
2. Ensure n8n workflow is active.
3. Create admin event via frontend or API.
4. Run one queue cycle:
   - php artisan queue:work --once --tries=1
5. Check backend logs:
   - backend/storage/logs/laravel.log
6. Check n8n executions list for delivery.

## Common Issues

### 404 webhook not registered

Cause:

- Workflow not active or wrong URL

Fix:

- Activate workflow in n8n
- Use production URL with path peso-event-email-alert

### access to env vars denied in n8n node

Cause:

- n8n blocks env access in expressions

Fix:

- Use literal field value in node
- Or allow env access in n8n runtime and restart container

### Admin cannot publish or access workflow

Cause:

- n8n UI permission mismatch on workflow owner/project

Fix options:

- Publish and activate workflow from owner account
- Or publish via n8n CLI inside container and restart n8n

### Laravel event create works but no email sent

Checklist:

- Queue worker running
- N8N_WEBHOOK_URL set
- N8N_WEBHOOK_SECRET matches both sides
- n8n workflow active
- n8n email node credentials valid

## Useful Files

- backend/routes/api.php
- backend/app/Http/Controllers/AdminEventController.php
- backend/app/Http/Controllers/Api/AdminSessionController.php
- backend/app/Http/Controllers/Api/EmailSubscriptionController.php
- backend/app/Jobs/SendEmailBroadcast.php
- backend/config/services.php
- backend/database/seeders/AdminUserSeeder.php
- backend/n8n/workflows/peso-event-email-alert.json
- frontend/src/App.jsx
- frontend/src/api.js
