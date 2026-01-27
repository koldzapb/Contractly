# Contractly - Testing Strategy

## Overview

This document outlines the testing approach for Contractly, covering both backend (Laravel/PHP) and frontend (Vue/TypeScript) testing.

---

## Testing Philosophy

1. **Test behavior, not implementation** - Tests should verify what the code does, not how it does it
2. **Arrange-Act-Assert** - Structure tests clearly with setup, action, and verification
3. **One assertion per concept** - Each test should verify one logical concept (may include multiple related assertions)
4. **Fast feedback** - Unit tests should run in milliseconds, integration tests in seconds
5. **Isolated tests** - Tests should not depend on each other or external state

---

## Backend Testing (Laravel + Pest)

### Test Types

| Type | Location | Purpose | Speed |
|------|----------|---------|-------|
| Unit | `tests/Unit/` | Test individual classes/methods in isolation | Fast |
| Feature | `tests/Feature/` | Test HTTP endpoints and request/response cycles | Medium |
| Integration | `tests/Feature/` | Test multiple components working together | Medium |

### Directory Structure

```
backend/tests/
├── Feature/
│   ├── Auth/
│   │   ├── RegistrationTest.php
│   │   ├── LoginTest.php
│   │   ├── LogoutTest.php
│   │   └── PasswordResetTest.php
│   ├── Contract/
│   │   ├── UploadContractTest.php
│   │   ├── ListContractsTest.php
│   │   ├── ViewContractTest.php
│   │   └── DeleteContractTest.php
│   ├── Analysis/
│   │   └── ContractAnalysisTest.php
│   ├── Reminder/
│   │   ├── CreateReminderTest.php
│   │   ├── UpdateReminderTest.php
│   │   ├── DeleteReminderTest.php
│   │   └── SendReminderTest.php
│   └── Dashboard/
│       └── DashboardTest.php
├── Unit/
│   ├── Models/
│   │   ├── ContractTest.php
│   │   ├── UserTest.php
│   │   └── ReminderTest.php
│   └── Services/
│       ├── PdfParserServiceTest.php
│       ├── ClaudeAiServiceTest.php
│       ├── ContractAnalysisServiceTest.php
│       └── ReminderServiceTest.php
├── Pest.php
└── TestCase.php
```

### Pest Configuration

```php
// tests/Pest.php
<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class)->in('Unit');
```

### Example Tests

**Unit Test - Service**
```php
// tests/Unit/Services/PdfParserServiceTest.php
<?php

use App\Services\PdfParserService;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->service = new PdfParserService();
});

it('extracts text from a valid PDF', function () {
    // Arrange
    $pdf = UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf');

    // Act
    $result = $this->service->extractText($pdf->path());

    // Assert
    expect($result)->toBeString();
    expect($result)->not->toBeEmpty();
});

it('throws exception for invalid file', function () {
    // Arrange
    $invalidPath = '/nonexistent/file.pdf';

    // Act & Assert
    expect(fn () => $this->service->extractText($invalidPath))
        ->toThrow(InvalidArgumentException::class);
});

it('handles multi-page documents', function () {
    // Arrange
    $pdf = createMultiPagePdf(5); // Test helper

    // Act
    $result = $this->service->extractText($pdf);

    // Assert
    expect($result)->toContain('Page 1');
    expect($result)->toContain('Page 5');
});
```

**Feature Test - API Endpoint**
```php
// tests/Feature/Contract/UploadContractTest.php
<?php

use App\Models\User;
use App\Models\Contract;
use App\Jobs\AnalyzeContractJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('contracts');
    Queue::fake();
});

it('allows authenticated users to upload contracts', function () {
    // Arrange
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('contract.pdf', 1000, 'application/pdf');

    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/contracts', [
            'file' => $file,
            'title' => 'My Contract'
        ]);

    // Assert
    $response->assertCreated();
    $response->assertJsonStructure([
        'data' => ['id', 'title', 'status', 'created_at']
    ]);

    $this->assertDatabaseHas('contracts', [
        'user_id' => $user->id,
        'title' => 'My Contract',
        'status' => 'pending'
    ]);

    Queue::assertPushed(AnalyzeContractJob::class);
});

it('rejects files larger than 10MB', function () {
    // Arrange
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('large.pdf', 11000, 'application/pdf'); // 11MB

    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/contracts', ['file' => $file]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['file']);
});

it('rejects non-PDF files', function () {
    // Arrange
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('document.docx', 100, 'application/msword');

    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/contracts', ['file' => $file]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['file']);
});

it('requires authentication', function () {
    // Arrange
    $file = UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf');

    // Act
    $response = $this->postJson('/api/contracts', ['file' => $file]);

    // Assert
    $response->assertUnauthorized();
});

it('prevents users from accessing other users contracts', function () {
    // Arrange
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $contract = Contract::factory()->for($user1)->create();

    // Act
    $response = $this->actingAs($user2)
        ->getJson("/api/contracts/{$contract->id}");

    // Assert
    $response->assertNotFound();
});
```

**Feature Test - Authentication**
```php
// tests/Feature/Auth/RegistrationTest.php
<?php

use App\Models\User;

it('registers a new user', function () {
    // Act
    $response = $this->postJson('/api/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    // Assert
    $response->assertCreated();
    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com'
    ]);
});

it('requires valid email format', function () {
    // Act
    $response = $this->postJson('/api/auth/register', [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
});

it('requires password confirmation to match', function () {
    // Act
    $response = $this->postJson('/api/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different'
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['password']);
});

it('prevents duplicate email registration', function () {
    // Arrange
    User::factory()->create(['email' => 'existing@example.com']);

    // Act
    $response = $this->postJson('/api/auth/register', [
        'name' => 'John Doe',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
});
```

### Mocking External Services

```php
// tests/Unit/Services/ClaudeAiServiceTest.php
<?php

use App\Services\ClaudeAiService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->service = new ClaudeAiService();
});

it('analyzes contract text and returns structured response', function () {
    // Arrange
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [
                ['text' => json_encode([
                    'summary' => 'This is a test contract summary.',
                    'risk_level' => 'low',
                    'clauses' => []
                ])]
            ]
        ], 200)
    ]);

    // Act
    $result = $this->service->analyzeContract('Sample contract text...');

    // Assert
    expect($result)->toHaveKey('summary');
    expect($result)->toHaveKey('risk_level');
    expect($result['risk_level'])->toBe('low');
});

it('handles API errors gracefully', function () {
    // Arrange
    Http::fake([
        'api.anthropic.com/*' => Http::response(null, 500)
    ]);

    // Act & Assert
    expect(fn () => $this->service->analyzeContract('text'))
        ->toThrow(Exception::class);
});

it('handles rate limiting with retry', function () {
    // Arrange
    Http::fake([
        'api.anthropic.com/*' => Http::sequence()
            ->push(null, 429) // First call: rate limited
            ->push(['content' => [['text' => '{"summary":"ok"}']]], 200) // Retry: success
    ]);

    // Act
    $result = $this->service->analyzeContract('text');

    // Assert
    expect($result)->toHaveKey('summary');
    Http::assertSentCount(2);
});
```

### Running Backend Tests

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/Contract/UploadContractTest.php

# Run specific test
php artisan test --filter="allows authenticated users to upload contracts"

# Run in parallel
php artisan test --parallel
```

---

## Frontend Testing (Vue + Vitest + Playwright)

### Test Types

| Type | Tool | Location | Purpose |
|------|------|----------|---------|
| Unit | Vitest | `tests/unit/` | Test components, composables, utilities in isolation |
| E2E | Playwright | `tests/e2e/` | Test full user flows in browser |

### Directory Structure

```
frontend/tests/
├── unit/
│   ├── components/
│   │   ├── common/
│   │   │   ├── LoadingSpinner.spec.ts
│   │   │   └── RiskBadge.spec.ts
│   │   ├── contracts/
│   │   │   ├── ContractUploader.spec.ts
│   │   │   └── ContractCard.spec.ts
│   │   └── analysis/
│   │       ├── ClauseCard.spec.ts
│   │       └── AnalysisSummary.spec.ts
│   ├── composables/
│   │   └── useContracts.spec.ts
│   ├── stores/
│   │   ├── auth.spec.ts
│   │   └── contracts.spec.ts
│   └── utils/
│       └── formatters.spec.ts
├── e2e/
│   ├── auth.spec.ts
│   ├── upload-contract.spec.ts
│   ├── view-analysis.spec.ts
│   └── reminders.spec.ts
└── setup.ts
```

### Vitest Configuration

```typescript
// vitest.config.ts
import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath } from 'url'

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./tests/setup.ts'],
    include: ['tests/unit/**/*.spec.ts'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'html'],
      exclude: ['node_modules/', 'tests/']
    }
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  }
})
```

### Test Setup

```typescript
// tests/setup.ts
import { config } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { vi } from 'vitest'

// Global mocks
vi.mock('@/services/api', () => ({
  api: {
    contracts: {
      list: vi.fn(),
      get: vi.fn(),
      upload: vi.fn(),
      delete: vi.fn()
    },
    auth: {
      login: vi.fn(),
      logout: vi.fn(),
      register: vi.fn()
    }
  }
}))

// Default plugins for all tests
config.global.plugins = [createTestingPinia()]
```

### Example Unit Tests

**Component Test**
```typescript
// tests/unit/components/common/RiskBadge.spec.ts
import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import RiskBadge from '@/components/common/RiskBadge.vue'

describe('RiskBadge', () => {
  it('renders with correct color for high risk', () => {
    const wrapper = mount(RiskBadge, {
      props: { level: 'high' }
    })

    expect(wrapper.classes()).toContain('bg-red-500')
    expect(wrapper.text()).toBe('High')
  })

  it('renders with correct color for medium risk', () => {
    const wrapper = mount(RiskBadge, {
      props: { level: 'medium' }
    })

    expect(wrapper.classes()).toContain('bg-yellow-500')
    expect(wrapper.text()).toBe('Medium')
  })

  it('renders with correct color for low risk', () => {
    const wrapper = mount(RiskBadge, {
      props: { level: 'low' }
    })

    expect(wrapper.classes()).toContain('bg-green-500')
    expect(wrapper.text()).toBe('Low')
  })

  it('renders nothing for none risk level', () => {
    const wrapper = mount(RiskBadge, {
      props: { level: 'none' }
    })

    expect(wrapper.html()).toBe('')
  })
})
```

**Component Test with User Interaction**
```typescript
// tests/unit/components/contracts/ContractUploader.spec.ts
import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import ContractUploader from '@/components/contracts/ContractUploader.vue'

describe('ContractUploader', () => {
  it('emits file when valid PDF is selected', async () => {
    const wrapper = mount(ContractUploader)

    const file = new File(['content'], 'contract.pdf', { type: 'application/pdf' })
    const input = wrapper.find('input[type="file"]')

    // Simulate file selection
    Object.defineProperty(input.element, 'files', {
      value: [file]
    })
    await input.trigger('change')

    expect(wrapper.emitted('file-selected')).toBeTruthy()
    expect(wrapper.emitted('file-selected')![0]).toEqual([file])
  })

  it('shows error for non-PDF files', async () => {
    const wrapper = mount(ContractUploader)

    const file = new File(['content'], 'document.docx', {
      type: 'application/msword'
    })
    const input = wrapper.find('input[type="file"]')

    Object.defineProperty(input.element, 'files', {
      value: [file]
    })
    await input.trigger('change')

    expect(wrapper.text()).toContain('Only PDF files are accepted')
    expect(wrapper.emitted('file-selected')).toBeFalsy()
  })

  it('shows error for files over 10MB', async () => {
    const wrapper = mount(ContractUploader)

    // Create mock file with size > 10MB
    const file = new File([''], 'large.pdf', { type: 'application/pdf' })
    Object.defineProperty(file, 'size', { value: 11 * 1024 * 1024 })

    const input = wrapper.find('input[type="file"]')
    Object.defineProperty(input.element, 'files', {
      value: [file]
    })
    await input.trigger('change')

    expect(wrapper.text()).toContain('File must be less than 10MB')
  })

  it('supports drag and drop', async () => {
    const wrapper = mount(ContractUploader)

    const file = new File(['content'], 'contract.pdf', { type: 'application/pdf' })
    const dropZone = wrapper.find('[data-testid="drop-zone"]')

    await dropZone.trigger('drop', {
      dataTransfer: { files: [file] }
    })

    expect(wrapper.emitted('file-selected')).toBeTruthy()
  })
})
```

**Store Test**
```typescript
// tests/unit/stores/contracts.spec.ts
import { setActivePinia, createPinia } from 'pinia'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { useContractStore } from '@/stores/contracts'
import { api } from '@/services/api'

vi.mock('@/services/api')

describe('contracts store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('fetches contracts and updates state', async () => {
    const mockContracts = [
      { id: '1', title: 'Contract 1', status: 'completed' },
      { id: '2', title: 'Contract 2', status: 'pending' }
    ]
    vi.mocked(api.contracts.list).mockResolvedValue(mockContracts)

    const store = useContractStore()

    await store.fetchContracts()

    expect(store.contracts).toEqual(mockContracts)
    expect(store.isLoading).toBe(false)
  })

  it('handles fetch errors', async () => {
    vi.mocked(api.contracts.list).mockRejectedValue(new Error('Network error'))

    const store = useContractStore()

    await expect(store.fetchContracts()).rejects.toThrow('Network error')
    expect(store.contracts).toEqual([])
  })

  it('getById returns correct contract', async () => {
    const store = useContractStore()
    store.contracts = [
      { id: '1', title: 'Contract 1' },
      { id: '2', title: 'Contract 2' }
    ]

    const contract = store.getById('2')

    expect(contract?.title).toBe('Contract 2')
  })

  it('filters contracts by status', () => {
    const store = useContractStore()
    store.contracts = [
      { id: '1', status: 'completed' },
      { id: '2', status: 'pending' },
      { id: '3', status: 'completed' }
    ]

    const completed = store.byStatus('completed')

    expect(completed).toHaveLength(2)
  })
})
```

### Playwright E2E Tests

```typescript
// tests/e2e/auth.spec.ts
import { test, expect } from '@playwright/test'

test.describe('Authentication', () => {
  test('user can register', async ({ page }) => {
    await page.goto('/register')

    await page.fill('[data-testid="name-input"]', 'John Doe')
    await page.fill('[data-testid="email-input"]', 'john@example.com')
    await page.fill('[data-testid="password-input"]', 'password123')
    await page.fill('[data-testid="password-confirm-input"]', 'password123')

    await page.click('[data-testid="register-button"]')

    await expect(page).toHaveURL('/dashboard')
    await expect(page.locator('[data-testid="user-name"]')).toContainText('John Doe')
  })

  test('user can login', async ({ page }) => {
    // Seed user first via API or fixture
    await page.goto('/login')

    await page.fill('[data-testid="email-input"]', 'existing@example.com')
    await page.fill('[data-testid="password-input"]', 'password123')

    await page.click('[data-testid="login-button"]')

    await expect(page).toHaveURL('/dashboard')
  })

  test('shows error for invalid credentials', async ({ page }) => {
    await page.goto('/login')

    await page.fill('[data-testid="email-input"]', 'wrong@example.com')
    await page.fill('[data-testid="password-input"]', 'wrongpassword')

    await page.click('[data-testid="login-button"]')

    await expect(page.locator('[data-testid="error-message"]')).toBeVisible()
    await expect(page).toHaveURL('/login')
  })
})
```

```typescript
// tests/e2e/upload-contract.spec.ts
import { test, expect } from '@playwright/test'
import path from 'path'

test.describe('Contract Upload', () => {
  test.beforeEach(async ({ page }) => {
    // Login first
    await page.goto('/login')
    await page.fill('[data-testid="email-input"]', 'test@example.com')
    await page.fill('[data-testid="password-input"]', 'password123')
    await page.click('[data-testid="login-button"]')
    await expect(page).toHaveURL('/dashboard')
  })

  test('user can upload a contract', async ({ page }) => {
    await page.goto('/contracts/upload')

    // Upload file
    const filePath = path.join(__dirname, 'fixtures', 'sample-contract.pdf')
    await page.setInputFiles('[data-testid="file-input"]', filePath)

    // Fill title
    await page.fill('[data-testid="title-input"]', 'Employment Contract')

    // Submit
    await page.click('[data-testid="upload-button"]')

    // Should show processing state
    await expect(page.locator('[data-testid="processing-indicator"]')).toBeVisible()

    // Wait for analysis (with timeout)
    await expect(page.locator('[data-testid="analysis-summary"]')).toBeVisible({
      timeout: 60000
    })
  })

  test('shows analysis results after processing', async ({ page }) => {
    // Navigate to existing analyzed contract
    await page.goto('/contracts/test-contract-id')

    await expect(page.locator('[data-testid="analysis-summary"]')).toBeVisible()
    await expect(page.locator('[data-testid="clause-list"]')).toBeVisible()
    await expect(page.locator('[data-testid="risk-badge"]')).toBeVisible()
  })
})
```

### Running Frontend Tests

```bash
# Unit tests
npm run test

# Unit tests with coverage
npm run test:coverage

# Unit tests in watch mode
npm run test:watch

# E2E tests
npm run test:e2e

# E2E tests with UI
npm run test:e2e:ui

# E2E tests for specific browser
npx playwright test --project=chromium
```

---

## Test Data

### Factories (Laravel)

```php
// database/factories/ContractFactory.php
<?php

namespace Database\Factories;

use App\Enums\ContractStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContractFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'original_filename' => fake()->word() . '.pdf',
            'storage_path' => 'contracts/' . Str::uuid() . '.pdf',
            'file_size_bytes' => fake()->numberBetween(10000, 5000000),
            'file_hash' => Str::random(64),
            'page_count' => fake()->numberBetween(1, 50),
            'status' => ContractStatus::COMPLETED,
            'language_detected' => 'en',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContractStatus::PENDING,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContractStatus::PROCESSING,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContractStatus::FAILED,
            'error_message' => 'Analysis failed due to invalid PDF format',
        ]);
    }
}
```

### Fixtures (Frontend)

```typescript
// tests/fixtures/contracts.ts
export const mockContract = {
  id: '550e8400-e29b-41d4-a716-446655440000',
  title: 'Employment Contract',
  original_filename: 'employment-contract.pdf',
  file_size_bytes: 245780,
  page_count: 12,
  status: 'completed',
  overall_risk_level: 'medium',
  created_at: '2025-01-27T10:00:00Z'
}

export const mockAnalysis = {
  summary: 'This is a standard employment contract...',
  overall_risk_level: 'medium',
  key_findings: [
    'Non-compete clause restricts employment for 12 months',
    'All IP belongs to employer'
  ],
  clauses: [
    {
      id: 1,
      clause_type: 'non_compete',
      risk_level: 'medium',
      plain_explanation: 'You cannot work for competitors for 1 year'
    }
  ]
}
```

---

## CI/CD Integration

### GitHub Actions

```yaml
# .github/workflows/test.yml
name: Tests

on: [push, pull_request]

jobs:
  backend:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_DB: testing
          POSTGRES_USER: testing
          POSTGRES_PASSWORD: testing
        ports:
          - 5432:5432
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          coverage: xdebug
      - run: composer install
        working-directory: backend
      - run: php artisan test --coverage
        working-directory: backend
        env:
          DB_CONNECTION: pgsql
          DB_HOST: localhost
          DB_DATABASE: testing
          DB_USERNAME: testing
          DB_PASSWORD: testing

  frontend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
      - run: npm ci
        working-directory: frontend
      - run: npm run test:coverage
        working-directory: frontend

  e2e:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
      - run: npm ci
        working-directory: frontend
      - run: npx playwright install
        working-directory: frontend
      - run: npm run test:e2e
        working-directory: frontend
```

---

## Coverage Requirements

| Area | Minimum Coverage |
|------|-----------------|
| Backend Services | 80% |
| Backend Controllers | 70% |
| Frontend Stores | 80% |
| Frontend Components | 60% |
| Overall | 70% |
