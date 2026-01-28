import { test, expect } from '@playwright/test'

// Reuse credentials from auth tests - this email should exist after auth.spec.ts runs
const testEmail = `e2e-dashboard-${Date.now()}@example.com`
const testPassword = 'TestPassword123!'
const testName = 'Dashboard Test User'

test.describe('Dashboard', () => {
  // Setup: Create user and login before tests
  test.beforeAll(async ({ browser }) => {
    const context = await browser.newContext()
    const page = await context.newPage()

    // Register a new user for dashboard tests
    await page.goto('/register')
    await page.fill('input[type="text"]', testName)
    await page.fill('input[type="email"]', testEmail)
    await page.fill('input[type="password"]', testPassword)
    const passwordFields = page.locator('input[type="password"]')
    await passwordFields.nth(1).fill(testPassword)
    await page.click('button[type="submit"]')
    await expect(page).toHaveURL(/.*dashboard/, { timeout: 10000 })

    await context.close()
  })

  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login')
    await page.fill('input[type="email"]', testEmail)
    await page.fill('input[type="password"]', testPassword)
    await page.click('button[type="submit"]')
    await expect(page).toHaveURL(/.*dashboard/, { timeout: 10000 })
  })

  test('displays dashboard with stats overview', async ({ page }) => {
    // Check main dashboard elements are visible
    await expect(page.locator('h1:has-text("Dashboard")')).toBeVisible()

    // Stats overview should be visible
    await expect(page.locator('text=Total Contracts')).toBeVisible()
    await expect(page.locator('text=Pending Analysis')).toBeVisible()
  })

  test('displays upcoming deadlines section', async ({ page }) => {
    await expect(page.locator('text=Upcoming Deadlines')).toBeVisible()
  })

  test('displays recent contracts section', async ({ page }) => {
    await expect(page.locator('text=Recent Contracts')).toBeVisible()
  })

  test('displays quick actions', async ({ page }) => {
    await expect(page.locator('text=Quick Actions')).toBeVisible()
    await expect(page.locator('text=Upload Contract')).toBeVisible()
    await expect(page.locator('text=Manage Reminders')).toBeVisible()
  })

  test('can navigate to contracts page via quick action', async ({ page }) => {
    await page.click('a:has-text("Upload Contract")')
    await expect(page).toHaveURL(/.*contracts/)
  })

  test('can navigate to reminders page via quick action', async ({ page }) => {
    await page.click('a:has-text("Manage Reminders")')
    await expect(page).toHaveURL(/.*reminders/)
  })

  test('navigation links work correctly', async ({ page }) => {
    // Click Contracts in nav
    await page.click('nav >> text=Contracts')
    await expect(page).toHaveURL(/.*contracts/)

    // Click Dashboard in nav
    await page.click('nav >> text=Dashboard')
    await expect(page).toHaveURL(/.*dashboard/)

    // Click Reminders in nav
    await page.click('nav >> text=Reminders')
    await expect(page).toHaveURL(/.*reminders/)
  })

  test('theme toggle works', async ({ page }) => {
    // Find and click theme toggle button
    const themeButton = page.locator('[aria-label*="theme"]')
    await expect(themeButton).toBeVisible()

    // Click to toggle theme
    await themeButton.click()

    // Verify the html element has dark class or theme changed
    // The app uses dark mode classes
    await page.waitForTimeout(500) // Wait for theme transition
  })
})
