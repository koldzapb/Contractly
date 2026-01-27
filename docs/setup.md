# Development Setup

## Prerequisites

- Docker & Docker Compose
- Git
- Node.js 20+ (for local frontend development)
- PHP 8.3+ (optional, for running commands locally)
- Composer (optional, for local PHP commands)

## Quick Start

```bash
# Clone repository
git clone <repository-url>
cd contractly

# Copy environment files
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env

# Start Docker containers
make up

# Install dependencies (runs inside containers)
make install

# Run database migrations
make migrate

# Seed database (optional)
make seed
```

Access the application:
- **Frontend:** http://localhost
- **API:** http://localhost/api
- **Mailhog:** http://localhost:8025

## Environment Variables

### Backend (`backend/.env`)

```env
# Application
APP_NAME=Contractly
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=contractly
DB_USERNAME=contractly
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Queue & Cache
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis

# Mail (Mailhog for local)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_FROM_ADDRESS=noreply@contractly.local
MAIL_FROM_NAME="${APP_NAME}"

# Storage
FILESYSTEM_DISK=local

# Anthropic (Claude API)
ANTHROPIC_API_KEY=your-api-key-here
ANTHROPIC_MODEL=claude-sonnet-4-20250514

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost
SESSION_DOMAIN=localhost
```

### Frontend (`frontend/.env`)

```env
VITE_API_URL=http://localhost/api
VITE_APP_NAME=Contractly
```

## Docker Services

| Service | Port | Description |
|---------|------|-------------|
| nginx | 80 | Web server, reverse proxy |
| app | 9000 | PHP-FPM (Laravel) |
| queue | - | Queue worker |
| scheduler | - | Task scheduler |
| node | 5173 | Vite dev server |
| postgres | 5432 | Database |
| redis | 6379 | Cache, queue, sessions |
| mailhog | 1025, 8025 | Email testing |

## Make Commands

```bash
# Docker
make up              # Start containers
make down            # Stop containers
make restart         # Restart containers
make logs            # View logs
make logs-app        # View app logs only

# Shell Access
make shell           # PHP container shell
make shell-node      # Node container shell
make tinker          # Laravel tinker

# Backend
make install         # Install all dependencies
make migrate         # Run migrations
make migrate-fresh   # Fresh migrate (drops tables)
make seed            # Run seeders
make test            # Run PHP tests
make test-coverage   # Tests with coverage
make pint            # Format PHP code

# Frontend
make npm-install     # Install npm packages
make npm-dev         # Start Vite dev server
make npm-build       # Production build
make npm-test        # Run Vitest
make npm-lint        # Lint TypeScript/Vue

# Database
make db-reset        # Drop, migrate, seed
make db-backup       # Backup database
make db-restore      # Restore from backup

# Queue
make queue-restart   # Restart queue worker
make queue-failed    # List failed jobs
make queue-retry     # Retry failed jobs
```

## Laravel Boost Setup

After initializing the Laravel project:

```bash
# Install Boost
composer require laravel/boost --dev

# Run installer (generates CLAUDE.md, .mcp.json)
php artisan boost:install

# Choose your editor when prompted:
# - Claude Code
# - Cursor
# - GitHub Copilot
# - Other
```

Boost will:
- Generate `CLAUDE.md` with Laravel-specific guidelines
- Include our custom guidelines from `.ai/guidelines/`
- Configure MCP servers if applicable

## IDE Setup

### VS Code / Cursor

Recommended extensions:
- PHP Intelephense
- Laravel Blade Snippets
- Vue Language Features (Volar)
- TypeScript Vue Plugin
- Tailwind CSS IntelliSense
- ESLint
- Prettier

### PHPStorm

- Enable Laravel plugin
- Configure PHP interpreter from Docker
- Set up PHPUnit with Docker

## Troubleshooting

### Container won't start
```bash
# Check logs
docker compose logs <service-name>

# Rebuild containers
make build
make up
```

### Database connection refused
```bash
# Ensure postgres is running
docker compose ps

# Check connection from app container
make shell
php artisan db:show
```

### Queue jobs not processing
```bash
# Check queue worker status
docker compose logs queue

# Restart queue worker
make queue-restart
```

### Frontend HMR not working
```bash
# Check node container
docker compose logs node

# Restart Vite
make npm-dev
```

### Permission issues
```bash
# Fix storage permissions
make shell
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Production Notes

For production deployment:

1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Use proper database credentials
3. Configure S3 for file storage
4. Use Resend/SendGrid for email
5. Set up SSL/HTTPS
6. Configure proper session domain
7. Set up error tracking (Sentry)
8. Enable OPcache
9. Run `npm run build` for frontend
10. Configure supervisor for queue workers
