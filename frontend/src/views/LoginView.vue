<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { ExclamationCircleIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const remember = ref(false)

async function handleSubmit(): Promise<void> {
  try {
    await authStore.login({
      email: email.value,
      password: password.value,
      remember: remember.value,
    })
    // Redirect to the intended page or dashboard
    const redirect = route.query.redirect as string
    await router.push(redirect || { name: 'dashboard' })
  } catch {
    // Error is handled by the store
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h1 class="text-center text-2xl font-bold text-indigo-600">Contractly</h1>
        <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">Sign in to your account</h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Or
          <RouterLink to="/register" class="font-medium text-indigo-600 hover:text-indigo-500">
            create a new account
          </RouterLink>
        </p>
      </div>

      <form class="mt-8 space-y-6" novalidate @submit.prevent="handleSubmit">
        <div
          v-if="authStore.error"
          class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg flex items-start gap-3"
        >
          <ExclamationCircleIcon class="h-5 w-5 flex-shrink-0 mt-0.5" />
          <span>{{ authStore.error }}</span>
        </div>

        <div class="space-y-4">
          <div>
            <label for="email" class="label">Email address</label>
            <input
              id="email"
              v-model="email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="input"
              placeholder="you@example.com"
              @focus="authStore.clearError"
            />
          </div>

          <div>
            <label for="password" class="label">Password</label>
            <input
              id="password"
              v-model="password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
              class="input"
              placeholder="Enter your password"
              @focus="authStore.clearError"
            />
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember"
              v-model="remember"
              name="remember"
              type="checkbox"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <label for="remember" class="ml-2 block text-sm text-gray-900">Remember me</label>
          </div>

          <RouterLink
            to="/forgot-password"
            class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
          >
            Forgot your password?
          </RouterLink>
        </div>

        <button type="submit" :disabled="authStore.loading" class="w-full btn-primary py-3">
          <span v-if="authStore.loading">Signing in...</span>
          <span v-else>Sign in</span>
        </button>
      </form>
    </div>
  </div>
</template>
