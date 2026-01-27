# Testing Skill

## Context
See `/docs/testing.md` for full testing strategy.

## Backend (Pest)

### Location
- Feature tests: `backend/tests/Feature/`
- Unit tests: `backend/tests/Unit/`

### Pattern
```php
it('does something', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/endpoint', $data);

    // Assert
    $response->assertCreated();
    $this->assertDatabaseHas('table', [...]);
});
```

### Commands
```bash
make test              # All tests
make test-coverage     # With coverage
php artisan test --filter="test name"
```

## Frontend (Vitest)

### Location
- Unit tests: `frontend/tests/unit/`
- E2E tests: `frontend/tests/e2e/`

### Pattern
```typescript
describe('Component', () => {
  it('does something', async () => {
    const wrapper = mount(Component, {
      props: { ... }
    })

    await wrapper.trigger('click')

    expect(wrapper.emitted('event')).toBeTruthy()
  })
})
```

### Commands
```bash
make npm-test          # Run Vitest
npm run test:e2e       # Run Playwright
```

## Checklist
- [ ] Arrange-Act-Assert pattern
- [ ] Mock external services
- [ ] Test happy path
- [ ] Test error cases
- [ ] Test edge cases
