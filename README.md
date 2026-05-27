# Meridian

> A Team Project Intelligence Platform — issue tracking, analytics, and automation in one workspace.

Built with **Laravel 13** · **Vue 3** · **TypeScript** · **Tailwind CSS v4**

---

## What's inside

- **Issue tracking** — Kanban boards, priority management, custom statuses and labels
- **Project analytics** — Velocity charts, cycle time, member workload
- **Team management** — Invite members, RBAC (owner / admin / member / viewer)
- **Automation** — Trigger-based rules that act on issues automatically
- **Webhooks** — Push events to external services
- **Real-time** — WebSocket broadcasting via Laravel Reverb
- **Dark / light theme** — System preference + manual toggle
- **Admin panel** — Feature flags with rollout percentages

---

## Requirements

| Tool | Version |
|------|---------|
| PHP  | 8.3+    |
| Composer | 2+ |
| Node | 20+ or 22+ |
| npm  | 10+     |
| SQLite | (bundled with PHP) |

> No Docker required for local development. Everything runs with built-in PHP and SQLite.

---

## Quick start

### 1. Clone

```bash
git clone <repo-url> meridian
cd meridian
```

### 2. Backend setup

```bash
cd backend

# Install PHP dependencies
composer install

# Create the environment file
cp .env.example .env
```

Open `backend/.env` and set these values for local development:

```env
APP_KEY=          # leave blank — next step fills this
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/meridian/backend/database/database.sqlite
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
MAIL_MAILER=array
```

Then:

```bash
# Generate the app key
php artisan key:generate

# Create the SQLite database file
touch database/database.sqlite

# Run all migrations
php artisan migrate

# (Optional) Seed with demo data
php artisan db:seed
```

The seeder creates:
- **Admin account** — `admin@meridian.test` / `password`
- **Demo account** — `demo@meridian.test` / `password`
- 3 teams with projects, issues, comments, and automation rules

### 3. Start the backend

```bash
php artisan serve
# → http://localhost:8000
```

### 4. Frontend setup

```bash
cd ../frontend

# Install dependencies
npm install

# Start the dev server
npm run dev
# → http://localhost:5173
```

Open [http://localhost:5173](http://localhost:5173) in your browser.

---

## Demo login

After seeding, use these credentials on the login page:

| Role  | Email | Password |
|-------|-------|----------|
| Admin | `admin@meridian.test` | `password` |
| Demo  | `demo@meridian.test`  | `password` |

The login page also has a **"Use demo credentials"** button that fills them in automatically.

---

## Project structure

```
meridian/
├── backend/                  # Laravel 13 API
│   ├── app/
│   │   ├── Actions/          # Single-responsibility use cases
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/
│   │   │   ├── Middleware/   # EnsureTeamMember
│   │   │   ├── Requests/     # Form request validation
│   │   │   └── Resources/    # JSON API resources
│   │   ├── Models/           # Eloquent models
│   │   ├── Observers/        # Auto activity log on mutations
│   │   └── Services/         # AnalyticsService, AutomationEngine
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/
│       ├── api.php           # All REST endpoints under /api/v1/
│       └── channels.php      # WebSocket private channels
│
└── frontend/                 # Vue 3 + TypeScript SPA
    └── src/
        ├── components/
        │   ├── features/     # IssueCard, CreateIssueModal
        │   ├── layouts/      # AppLayout (sidebar + topbar)
        │   └── ui/           # Design system components
        ├── pages/            # Route-level page components
        ├── stores/           # Pinia stores (auth, team, projects, issues, ui)
        ├── lib/              # API client, utilities
        └── types/            # TypeScript interfaces
```

---

## API overview

All endpoints are under `/api/v1/`. Authentication uses **Sanctum bearer tokens** — login returns a token, include it as `Authorization: Bearer <token>`.

```
POST   /api/v1/auth/register
POST   /api/v1/auth/login
GET    /api/v1/auth/me

GET    /api/v1/teams
POST   /api/v1/teams

GET    /api/v1/teams/{team}/projects
POST   /api/v1/teams/{team}/projects
GET    /api/v1/teams/{team}/projects/{project}/issues
POST   /api/v1/teams/{team}/projects/{project}/issues
PATCH  /api/v1/teams/{team}/projects/{project}/issues/{issue}

GET    /api/v1/teams/{team}/analytics
GET    /api/v1/teams/{team}/members
POST   /api/v1/teams/{team}/invitations
GET    /api/v1/teams/{team}/webhooks
```

`{team}` is the team **slug** (e.g. `acme-engineering`).

---

## Running tests

```bash
cd backend
php artisan test
```

All 18 feature tests cover auth, team management, projects, and issues using an in-memory SQLite database.

---

## Tech stack

### Backend
| | |
|--|--|
| Framework | Laravel 13 |
| Auth | Laravel Sanctum (bearer tokens) |
| Database | SQLite (local) · PostgreSQL (production) |
| Queue | Sync (local) · Redis + Horizon (production) |
| Broadcasting | Log (local) · Laravel Reverb (production) |
| Testing | Pest |

### Frontend
| | |
|--|--|
| Framework | Vue 3 (Composition API) |
| Language | TypeScript |
| Build | Vite 8 |
| State | Pinia |
| Routing | Vue Router 5 |
| Styling | Tailwind CSS v4 |
| HTTP | Fetch API (custom wrapper) |

---

## Docker (optional)

A full Docker Compose setup is included for production-like environments with PostgreSQL, Redis, Reverb, MinIO, Mailpit, and Laravel Horizon.

```bash
# Copy the Docker-ready env
cp backend/.env.example backend/.env

# Edit backend/.env — fill in APP_KEY, then:
docker compose up -d

docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Services exposed:

| Service | URL |
|---------|-----|
| App (nginx) | http://localhost:8000 |
| Frontend (Vite) | http://localhost:5173 |
| Mailpit | http://localhost:8025 |
| MinIO | http://localhost:9001 |

---

## Password rules

Registration requires a password that is:
- At least 8 characters
- Contains uppercase and lowercase letters
- Contains at least one number

Example: `Password1`
