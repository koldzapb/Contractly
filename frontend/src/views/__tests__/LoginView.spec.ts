import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../LoginView.vue'
import { useAuthStore } from '@/stores/auth'

// Mock the auth service
vi.mock('@/services/auth', () => ({
  login: vi.fn(),
  register: vi.fn(),
  logout: vi.fn(),
  checkAuth: vi.fn(),
}))

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
    { path: '/login', name: 'login', component: LoginView },
    { path: '/dashboard', name: 'dashboard', component: { template: '<div>Dashboard</div>' } },
    { path: '/register', name: 'register', component: { template: '<div>Register</div>' } },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: { template: '<div>Forgot</div>' },
    },
  ],
})

describe('LoginView', () => {
  beforeEach(async () => {
    setActivePinia(createPinia())
    router.push('/login')
    await router.isReady()
    vi.clearAllMocks()
  })

  const mountComponent = () => {
    return mount(LoginView, {
      global: {
        plugins: [router],
        stubs: {
          ThemeToggle: true,
        },
      },
    })
  }

  it('should render login form', () => {
    const wrapper = mountComponent()

    expect(wrapper.find('h2').text()).toContain('Sign in to your account')
    expect(wrapper.find('input[type="email"]').exists()).toBe(true)
    expect(wrapper.find('input[type="password"]').exists()).toBe(true)
    expect(wrapper.find('button[type="submit"]').exists()).toBe(true)
  })

  it('should have link to register page', () => {
    const wrapper = mountComponent()

    const registerLink = wrapper.find('a[href="/register"]')
    expect(registerLink.exists()).toBe(true)
    expect(registerLink.text()).toContain('create a new account')
  })

  it('should have link to forgot password page', () => {
    const wrapper = mountComponent()

    const forgotLink = wrapper.find('a[href="/forgot-password"]')
    expect(forgotLink.exists()).toBe(true)
    expect(forgotLink.text()).toContain('Forgot your password?')
  })

  it('should update email input on change', async () => {
    const wrapper = mountComponent()

    const emailInput = wrapper.find('input[type="email"]')
    await emailInput.setValue('test@example.com')

    expect((emailInput.element as HTMLInputElement).value).toBe('test@example.com')
  })

  it('should update password input on change', async () => {
    const wrapper = mountComponent()

    const passwordInput = wrapper.find('input[type="password"]')
    await passwordInput.setValue('password123')

    expect((passwordInput.element as HTMLInputElement).value).toBe('password123')
  })

  it('should display error message when login fails', async () => {
    const wrapper = mountComponent()
    const authStore = useAuthStore()

    authStore.error = 'Invalid credentials'
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Invalid credentials')
  })

  it('should show loading state when submitting', async () => {
    const wrapper = mountComponent()
    const authStore = useAuthStore()

    authStore.loading = true
    await wrapper.vm.$nextTick()

    expect(wrapper.find('button[type="submit"]').text()).toContain('Signing in...')
  })

  it('should disable submit button when loading', async () => {
    const wrapper = mountComponent()
    const authStore = useAuthStore()

    authStore.loading = true
    await wrapper.vm.$nextTick()

    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined()
  })

  it('should clear error on input focus', async () => {
    const wrapper = mountComponent()
    const authStore = useAuthStore()

    authStore.error = 'Some error'
    await wrapper.vm.$nextTick()

    const emailInput = wrapper.find('input[type="email"]')
    await emailInput.trigger('focus')

    expect(authStore.error).toBeNull()
  })
})
