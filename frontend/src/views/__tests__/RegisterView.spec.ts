import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import RegisterView from '../RegisterView.vue'
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
    { path: '/login', name: 'login', component: { template: '<div>Login</div>' } },
    { path: '/dashboard', name: 'dashboard', component: { template: '<div>Dashboard</div>' } },
    { path: '/register', name: 'register', component: RegisterView },
  ],
})

describe('RegisterView', () => {
  beforeEach(async () => {
    setActivePinia(createPinia())
    router.push('/register')
    await router.isReady()
    vi.clearAllMocks()
  })

  const mountComponent = () => {
    return mount(RegisterView, {
      global: {
        plugins: [router],
        stubs: {
          ThemeToggle: true,
        },
      },
    })
  }

  it('should render registration form', () => {
    const wrapper = mountComponent()

    expect(wrapper.find('h2').text()).toContain('Create your account')
    expect(wrapper.find('input[name="name"]').exists()).toBe(true)
    expect(wrapper.find('input[name="email"]').exists()).toBe(true)
    expect(wrapper.find('input[name="password"]').exists()).toBe(true)
    expect(wrapper.find('input[name="password_confirmation"]').exists()).toBe(true)
    expect(wrapper.find('button[type="submit"]').exists()).toBe(true)
  })

  it('should have link to login page', () => {
    const wrapper = mountComponent()

    const loginLink = wrapper.find('a[href="/login"]')
    expect(loginLink.exists()).toBe(true)
    expect(loginLink.text()).toContain('Sign in')
  })

  it('should update name input on change', async () => {
    const wrapper = mountComponent()

    const nameInput = wrapper.find('input[name="name"]')
    await nameInput.setValue('John Doe')

    expect((nameInput.element as HTMLInputElement).value).toBe('John Doe')
  })

  it('should update email input on change', async () => {
    const wrapper = mountComponent()

    const emailInput = wrapper.find('input[name="email"]')
    await emailInput.setValue('john@example.com')

    expect((emailInput.element as HTMLInputElement).value).toBe('john@example.com')
  })

  it('should update password inputs on change', async () => {
    const wrapper = mountComponent()

    const passwordInput = wrapper.find('input[name="password"]')
    const confirmInput = wrapper.find('input[name="password_confirmation"]')

    await passwordInput.setValue('password123')
    await confirmInput.setValue('password123')

    expect((passwordInput.element as HTMLInputElement).value).toBe('password123')
    expect((confirmInput.element as HTMLInputElement).value).toBe('password123')
  })

  it('should display error message when registration fails', async () => {
    const wrapper = mountComponent()
    const authStore = useAuthStore()

    authStore.error = 'Email already taken'
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Email already taken')
  })

  it('should show loading state when submitting', async () => {
    const wrapper = mountComponent()
    const authStore = useAuthStore()

    authStore.loading = true
    await wrapper.vm.$nextTick()

    expect(wrapper.find('button[type="submit"]').text()).toContain('Creating account...')
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

    const nameInput = wrapper.find('input[name="name"]')
    await nameInput.trigger('focus')

    expect(authStore.error).toBeNull()
  })
})
