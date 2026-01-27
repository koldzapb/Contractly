# Contractly - AI Agent Instructions

Universal instructions for AI coding agents (Claude Code, Cursor, Copilot, etc.)

## Project

**Contractly** - AI-powered contract analysis platform

| Component | Technology |
|-----------|------------|
| Backend | Laravel 11+ (PHP 8.3+) |
| Frontend | Vue 3 + TypeScript + Vite |
| Database | PostgreSQL 16 |
| Cache/Queue | Redis 7 |
| AI | Anthropic Claude API |

## Documentation

```
/.ai/guidelines/          # AI guidelines (Boost reads this)
  ├── project-context.md  # Overview, tech stack
  ├── coding-standards.md # PHP + Vue standards
  ├── api-conventions.md  # JSON response format
  ├── ui-design.md        # UI components
  └── security.md         # Security practices

/docs/                    # Human documentation
  ├── README.md           # Index
  ├── features.md         # Feature specs
  ├── architecture.md     # System design
  ├── api-reference.md    # API docs
  ├── design-system.md    # Full UI specs
  ├── testing.md          # Test strategy
  ├── setup.md            # Dev environment
  └── tasks.md            # Progress tracking

/.claude/skills/          # Claude Code skills
/.cursor/rules/           # Cursor IDE rules
```

## Critical Rules

### PHP/Laravel
```php
// ALWAYS
declare(strict_types=1);

// Use Facades
use Illuminate\Support\Facades\Auth;
$userId = Auth::id();

// NOT helpers
// $userId = auth()->id();  ← WRONG
```

### Vue/TypeScript
```vue
<!-- ALWAYS Composition API -->
<script setup lang="ts">
// NEVER use 'any'
// ALWAYS define interfaces
interface Props {
  contract: Contract
}
</script>
```

### API Responses
```json
{
  "data": { ... }
}
```

## Before Coding

1. Read relevant `/.ai/guidelines/` file
2. Check `/docs/` for specifications
3. Follow existing patterns in codebase

## Laravel Boost

This project uses Laravel Boost. After setup:
```bash
php artisan boost:install
```

Boost generates `CLAUDE.md` automatically, incorporating our `/.ai/guidelines/`.
