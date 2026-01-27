# Security Guidelines

## File Upload

### Validation Rules
```php
'file' => [
    'required',
    'file',
    'mimes:pdf',
    'max:10240',  // 10MB
    File::types(['pdf'])->max(10 * 1024),
]
```

### Storage
- Store outside web root (`storage/app/contracts/`)
- Generate random filename: `Str::uuid() . '.pdf'`
- Calculate SHA-256 hash for integrity
- Never expose original filename in URL

### Serving Files
```php
// Use signed URLs with expiration
return Storage::temporaryUrl($path, now()->addMinutes(5));

// Or stream through controller with auth check
public function download(Contract $contract)
{
    $this->authorize('view', $contract);
    return Storage::download($contract->storage_path);
}
```

## Authentication

### Session Security
```php
// config/session.php
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'lax',
```

### Rate Limiting
```php
// routes/api.php
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/auth/login', LoginController::class);
    Route::post('/auth/register', RegisterController::class);
});

Route::middleware(['throttle:10,60'])->group(function () {
    Route::post('/contracts', UploadContractController::class);
});
```

## Authorization

### Policy Pattern
```php
// Always use policies for resource access
public function view(User $user, Contract $contract): bool
{
    return $user->id === $contract->user_id;
}

// In controller
$this->authorize('view', $contract);
```

### Query Scoping
```php
// Always scope queries to authenticated user
Contract::where('user_id', Auth::id())->get();

// Or use global scope
protected static function booted(): void
{
    static::addGlobalScope('user', function (Builder $builder) {
        $builder->where('user_id', Auth::id());
    });
}
```

## Data Protection

### Sensitive Data
- Never log file contents or contract text
- Never log API keys or tokens
- Use `$hidden` on models for sensitive fields

```php
protected $hidden = [
    'storage_path',
    'file_hash',
];
```

### Encryption
```php
// Encrypt sensitive fields
protected $casts = [
    'storage_path' => 'encrypted',
];
```

## API Security

### Input Validation
- Always use Form Requests
- Validate all input, even from authenticated users
- Sanitize filenames: `Str::slug(pathinfo($name, PATHINFO_FILENAME))`

### CSRF
- Sanctum handles CSRF for SPA
- Ensure `withCredentials: true` on Axios

### XSS Prevention
- Vue escapes by default
- Never use `v-html` with user content
- Sanitize AI-generated content before display

## Environment Variables

### Required Secrets
```env
APP_KEY=                    # Laravel encryption key
DB_PASSWORD=                # Database password
ANTHROPIC_API_KEY=          # Claude API key
MAIL_PASSWORD=              # Email service credentials
AWS_SECRET_ACCESS_KEY=      # S3 credentials (production)
```

### Never Commit
- `.env` files
- API keys
- Passwords
- Private keys

## Logging

### Safe Logging
```php
// Good - log IDs only
Log::info('Contract uploaded', ['contract_id' => $contract->id]);

// Bad - never log content
// Log::info('Contract content', ['text' => $contractText]);
```

### Error Handling
```php
// Don't expose internal errors to users
try {
    $result = $service->analyze($contract);
} catch (Exception $e) {
    Log::error('Analysis failed', [
        'contract_id' => $contract->id,
        'error' => $e->getMessage(),
    ]);

    throw new AnalysisFailedException('Analysis failed. Please try again.');
}
```

## Checklist

Before deploying, verify:
- [ ] All routes require authentication (except public endpoints)
- [ ] File uploads validate MIME type and size
- [ ] User can only access their own contracts
- [ ] Rate limiting configured
- [ ] HTTPS enforced in production
- [ ] Environment variables secured
- [ ] Error messages don't leak internals
- [ ] Logs don't contain sensitive data
