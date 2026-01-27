# Laravel Backend Skill

## Context
Working in `backend/` directory. See `/.ai/guidelines/coding-standards.md` for full standards.

## Quick Reference

### File Locations
- Controllers: `app/Http/Controllers/`
- Services: `app/Services/`
- Models: `app/Models/`
- Enums: `app/Enums/`
- Requests: `app/Http/Requests/`
- Resources: `app/Http/Resources/`
- Jobs: `app/Jobs/`
- Tests: `tests/`

### Key Rules
1. `declare(strict_types=1)` at file top
2. Facades (`Auth::id()`) not helpers (`auth()->id()`)
3. Slim controllers → Services for logic
4. Form Requests for validation
5. API Resources for responses
6. Enums for fixed values
7. Pest for testing

### New Feature Checklist
1. [ ] Migration (if DB changes)
2. [ ] Enum (if new status/type)
3. [ ] Model (relationships, casts, fillable)
4. [ ] Service (business logic)
5. [ ] Form Request (validation)
6. [ ] API Resource (JSON output)
7. [ ] Controller (slim, delegates to service)
8. [ ] Route in `routes/api.php`
9. [ ] Feature test

### Commands
```bash
make migrate          # Run migrations
make test             # Run tests
make pint             # Format code
make tinker           # Laravel REPL
```
