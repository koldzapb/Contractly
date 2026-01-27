# Contractly Documentation

## Quick Links

| Document | Description |
|----------|-------------|
| [Features](./features.md) | Feature list with acceptance criteria |
| [Architecture](./architecture.md) | System design, data models, request flows |
| [API Reference](./api-reference.md) | Endpoints, TypeScript types, examples |
| [Design System](./design-system.md) | UI components, colors, patterns |
| [Testing](./testing.md) | Test strategy, patterns, coverage |
| [Setup](./setup.md) | Development environment setup |
| [Tasks](./tasks.md) | Development progress tracking |

## AI Guidelines

Located in `/.ai/guidelines/` (used by Laravel Boost):

| File | Purpose |
|------|---------|
| `project-context.md` | Project overview, tech stack |
| `coding-standards.md` | PHP/Laravel and Vue/TypeScript standards |
| `api-conventions.md` | JSON response format contract |
| `ui-design.md` | UI component guidelines |
| `security.md` | Security best practices |

## IDE-Specific

| Location | Tool |
|----------|------|
| `/.claude/skills/` | Claude Code skills |
| `/.cursor/rules/` | Cursor IDE rules |

## Project Structure

```
contractly/
├── .ai/guidelines/       # AI guidelines (Boost)
├── .claude/skills/       # Claude Code
├── .cursor/rules/        # Cursor IDE
├── docs/                 # This folder
├── backend/              # Laravel API
├── frontend/             # Vue SPA
└── docker/               # Docker config
```

## Getting Started

1. Read [Setup](./setup.md) to configure your environment
2. Review [Architecture](./architecture.md) for system overview
3. Check [Tasks](./tasks.md) for current development status
4. Follow coding standards in `/.ai/guidelines/`
