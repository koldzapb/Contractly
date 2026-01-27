import { test, expect } from '@playwright/test'

test('has title', async ({ page }) => {
  await page.goto('/')
  await expect(page).toHaveTitle(/Contractly/)
})

test('can navigate to login', async ({ page }) => {
  await page.goto('/')
  await page.click('text=Sign in')
  await expect(page).toHaveURL(/.*login/)
})

test('can navigate to register', async ({ page }) => {
  await page.goto('/')
  await page.click('text=Get started')
  await expect(page).toHaveURL(/.*register/)
})
