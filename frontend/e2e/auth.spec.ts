import { test, expect, type Page } from '@playwright/test'

// Generate unique email for each test run to avoid conflicts
const testEmail = `e2e-test-${Date.now()}@example.com`
const testPassword = 'TestPassword123!'
const testName = 'E2E Test User'

test.describe('Authentication Flow', () => {
  test.describe('Registration', () => {
    test('can register a new account', async ({ page }) => {
      await page.goto('/register')

      // Fill registration form
      await page.fill('input[type="text"]', testName)
      await page.fill('input[type="email"]', testEmail)
      await page.fill('input[type="password"]', testPassword)

      // Find and fill password confirmation (second password field)
      const passwordFields = page.locator('input[type="password"]')
      await passwordFields.nth(1).fill(testPassword)

      // Submit form
      await page.click('button[type="submit"]')

      // Should redirect to dashboard after successful registration
      await expect(page).toHaveURL(/.*dashboard/, { timeout: 10000 })
      await expect(page.locator('text=Dashboard')).toBeVisible()
    })

    test('shows validation errors for invalid registration', async ({ page }) => {
      await page.goto('/register')

      // Submit empty form
      await page.click('button[type="submit"]')

      // Should show validation errors (stay on register page)
      await expect(page).toHaveURL(/.*register/)
    })
  })

  test.describe('Login', () => {
    test('can login with valid credentials', async ({ page }) => {
      await page.goto('/login')

      // Fill login form with credentials from registration
      await page.fill('input[type="email"]', testEmail)
      await page.fill('input[type="password"]', testPassword)

      // Submit form
      await page.click('button[type="submit"]')

      // Should redirect to dashboard
      await expect(page).toHaveURL(/.*dashboard/, { timeout: 10000 })
      await expect(page.locator('text=Dashboard')).toBeVisible()
    })

    test('shows error for invalid credentials', async ({ page }) => {
      await page.goto('/login')

      // Fill login form with wrong password
      await page.fill('input[type="email"]', testEmail)
      await page.fill('input[type="password"]', 'WrongPassword123!')

      // Submit form
      await page.click('button[type="submit"]')

      // Should stay on login page and show error
      await expect(page).toHaveURL(/.*login/)
      await expect(page.locator('text=credentials')).toBeVisible({ timeout: 5000 })
    })

    test('redirects to login when accessing protected route unauthenticated', async ({ page }) => {
      // Clear any existing auth state
      await page.context().clearCookies()

      // Try to access dashboard directly
      await page.goto('/dashboard')

      // Should redirect to login
      await expect(page).toHaveURL(/.*login/)
    })
  })

  test.describe('Logout', () => {
    test('can logout successfully', async ({ page }) => {
      // First login
      await page.goto('/login')
      await page.fill('input[type="email"]', testEmail)
      await page.fill('input[type="password"]', testPassword)
      await page.click('button[type="submit"]')
      await expect(page).toHaveURL(/.*dashboard/, { timeout: 10000 })

      // Click logout button
      await page.click('button:has-text("Logout"), [aria-label="Logout"]')

      // Should redirect to login
      await expect(page).toHaveURL(/.*login/, { timeout: 5000 })
    })
  })
})
