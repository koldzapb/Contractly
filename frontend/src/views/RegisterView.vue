<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { ExclamationCircleIcon } from '@heroicons/vue/24/outline'
import ThemeToggle from '@/components/ThemeToggle.vue'

const router = useRouter()
const authStore = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')

async function handleSubmit(): Promise<void> {
  try {
    await authStore.register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    await router.push({ name: 'dashboard' })
  } catch {
    // Error is handled by the store
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="absolute top-4 right-4">
      <ThemeToggle />
    </div>
    <div class="max-w-md w-full space-y-8">
      <div>
        <h1 class="text-center text-2xl font-bold text-indigo-600 dark:text-indigo-400">Contractly</h1>
        <h2 class="mt-6 text-center text-3xl font-bold text-gray-900 dark:text-white">Create your account</h2>
        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
          Already have an account?
          <RouterLink to="/login" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
            Sign in
          </RouterLink>
        </p>
      </div>

      <form class="mt-8 space-y-6" novalidate @submit.prevent="handleSubmit">
        <div
          v-if="authStore.error"
          class="bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg flex items-start gap-3"
        >
          <ExclamationCircleIcon class="h-5 w-5 flex-shrink-0 mt-0.5" />
          <span>{{ authStore.error }}</span>
        </div>

        <div class="space-y-4">
          <div>
            <label for="name" class="label">Full name</label>
            <input
              id="name"
              v-model="name"
              name="name"
              type="text"
              autocomplete="name"
              required
              class="input"
              placeholder="John Doe"
              @focus="authStore.clearError"
            />
          </div>

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
              autocomplete="new-password"
              required
              class="input"
              placeholder="Create a password"
              @focus="authStore.clearError"
            />
          </div>

          <div>
            <label for="password_confirmation" class="label">Confirm password</label>
            <input
              id="password_confirmation"
              v-model="passwordConfirmation"
              name="password_confirmation"
              type="password"
              autocomplete="new-password"
              required
              class="input"
              placeholder="Confirm your password"
              @focus="authStore.clearError"
            />
          </div>
        </div>

        <button type="submit" :disabled="authStore.loading" class="w-full btn-primary py-3">
          <span v-if="authStore.loading">Creating account...</span>
          <span v-else>Create account</span>
        </button>
      </form>
    </div>
  </div>
</template>
