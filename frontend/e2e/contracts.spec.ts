import { test, expect } from '@playwright/test'
import * as path from 'path'
import * as fs from 'fs'

const testEmail = `e2e-contracts-${Date.now()}@example.com`
const testPassword = 'TestPassword123!'
const testName = 'Contracts Test User'

test.describe('Contracts', () => {
  // Setup: Create user before tests
  test.beforeAll(async ({ browser }) => {
    const context = await browser.newContext()
    const page = await context.newPage()

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

    // Navigate to contracts page
    await page.goto('/contracts')
    await expect(page.locator('h1:has-text("Contracts")')).toBeVisible()
  })

  test('displays contracts page with upload area', async ({ page }) => {
    // Check upload area is visible
    await expect(page.locator('text=Drag and drop')).toBeVisible()
    await expect(page.locator('text=Your Contracts')).toBeVisible()
  })

  test('shows empty state when no contracts', async ({ page }) => {
    // New user should see empty state or contracts list
    const contractsList = page.locator('[class*="card"]')
    await expect(contractsList.first()).toBeVisible()
  })

  test('can upload a PDF contract', async ({ page }) => {
    // Create a test PDF file
    const testPdfPath = path.join(__dirname, 'test-contract.pdf')

    // Create a minimal valid PDF if it doesn't exist
    if (!fs.existsSync(testPdfPath)) {
      const minimalPdf = Buffer.from(
        '%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000052 00000 n \n0000000101 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF',
      )
      fs.writeFileSync(testPdfPath, minimalPdf)
    }

    // Find the file input and upload
    const fileInput = page.locator('input[type="file"]')
    await fileInput.setInputFiles(testPdfPath)

    // Wait for upload to complete
    await expect(page.locator('text=Uploading')).toBeVisible({ timeout: 5000 })

    // Wait for upload to finish (either success or processing)
    await expect(
      page
        .locator('text=Pending')
        .or(page.locator('text=Processing'))
        .or(page.locator('text=Analyzed')),
    ).toBeVisible({ timeout: 30000 })
  })

  test('uploaded contract appears in list', async ({ page }) => {
    // Create and upload a test PDF
    const testPdfPath = path.join(__dirname, 'test-contract-list.pdf')

    if (!fs.existsSync(testPdfPath)) {
      const minimalPdf = Buffer.from(
        '%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000052 00000 n \n0000000101 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF',
      )
      fs.writeFileSync(testPdfPath, minimalPdf)
    }

    const fileInput = page.locator('input[type="file"]')
    await fileInput.setInputFiles(testPdfPath)

    // Wait for the contract to appear in the list
    await expect(page.locator('text=test-contract-list')).toBeVisible({ timeout: 30000 })
  })

  test('can click on contract to view details', async ({ page }) => {
    // First check if there are any contracts in the list
    const contractLink = page.locator('a[href*="/contracts/"]').first()

    // If no contracts, upload one first
    const hasContracts = await contractLink.isVisible().catch(() => false)

    if (!hasContracts) {
      // Upload a contract first
      const testPdfPath = path.join(__dirname, 'test-contract-detail.pdf')

      if (!fs.existsSync(testPdfPath)) {
        const minimalPdf = Buffer.from(
          '%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000052 00000 n \n0000000101 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF',
        )
        fs.writeFileSync(testPdfPath, minimalPdf)
      }

      const fileInput = page.locator('input[type="file"]')
      await fileInput.setInputFiles(testPdfPath)

      // Wait for contract to appear
      await expect(page.locator('a[href*="/contracts/"]').first()).toBeVisible({ timeout: 30000 })
    }

    // Click on the contract
    await page.locator('a[href*="/contracts/"]').first().click()

    // Should navigate to contract detail page
    await expect(page).toHaveURL(/.*contracts\/[a-f0-9-]+/)

    // Should show contract detail content
    await expect(page.locator('nav[aria-label="Breadcrumb"]')).toBeVisible()
  })

  test('can delete a contract', async ({ page }) => {
    // First upload a contract to delete
    const testPdfPath = path.join(__dirname, 'test-contract-delete.pdf')

    if (!fs.existsSync(testPdfPath)) {
      const minimalPdf = Buffer.from(
        '%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000052 00000 n \n0000000101 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF',
      )
      fs.writeFileSync(testPdfPath, minimalPdf)
    }

    const fileInput = page.locator('input[type="file"]')
    await fileInput.setInputFiles(testPdfPath)

    // Wait for contract to appear
    await expect(page.locator('text=test-contract-delete')).toBeVisible({ timeout: 30000 })

    // Click delete button
    const deleteButton = page.locator('[aria-label="Delete contract"]').first()
    await deleteButton.click()

    // Confirm deletion in modal
    await expect(page.locator('text=Delete Contract')).toBeVisible()
    await page.click('button:has-text("Delete")')

    // Contract should be removed from list
    await expect(page.locator('text=test-contract-delete')).not.toBeVisible({ timeout: 5000 })
  })
})
