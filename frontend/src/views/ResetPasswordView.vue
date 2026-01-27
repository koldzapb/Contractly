<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { ExclamationCircleIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import * as authService from '@/services/auth'

const router = useRouter()
const route = useRoute()

const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const token = ref('')
const loading = ref(false)
const error = ref<string | null>(null)
const success = ref(false)

onMounted(() => {
  token.value = (route.query.token as string) || ''
  email.value = (route.query.email as string) || ''

  if (!token.value) {
    error.value = 'Invalid or missing reset token. Please request a new password reset link.'
  }
})

async function handleSubmit(): Promise<void> {
  loading.value = true
  error.value = null

  try {
    await authService.resetPassword({
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    success.value = true
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    error.value = err.response?.data?.message ?? 'Failed to reset password. Please try again.'
  } finally {
    loading.value = false
  }
}

function clearError(): void {
  error.value = null
}

function goToLogin(): void {
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h1 class="text-center text-2xl font-bold text-indigo-600">Contractly</h1>
        <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">Set new password</h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Enter your new password below.
        </p>
      </div>

      <form v-if="!success" class="mt-8 space-y-6" novalidate @submit.prevent="handleSubmit">
        <div
          v-if="error"
          class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg flex items-start gap-3"
        >
          <ExclamationCircleIcon class="h-5 w-5 flex-shrink-0 mt-0.5" />
          <span>{{ error }}</span>
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
              @focus="clearError"
            />
          </div>

          <div>
            <label for="password" class="label">New password</label>
            <input
              id="password"
              v-model="password"
              name="password"
              type="password"
              autocomplete="new-password"
              required
              class="input"
              placeholder="Enter new password"
              @focus="clearError"
            />
          </div>

          <div>
            <label for="password_confirmation" class="label">Confirm new password</label>
            <input
              id="password_confirmation"
              v-model="passwordConfirmation"
              name="password_confirmation"
              type="password"
              autocomplete="new-password"
              required
              class="input"
              placeholder="Confirm new password"
              @focus="clearError"
            />
          </div>
        </div>

        <button type="submit" :disabled="loading || !token" class="w-full btn-primary py-3">
          <span v-if="loading">Resetting password...</span>
          <span v-else>Reset password</span>
        </button>

        <div class="text-center">
          <RouterLink to="/login" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
            Back to sign in
          </RouterLink>
        </div>
      </form>

      <div v-else class="mt-8 space-y-6">
        <div
          class="bg-green-50 border border-green-200 text-green-700 px-4 py-4 rounded-lg flex items-start gap-3"
        >
          <CheckCircleIcon class="h-5 w-5 flex-shrink-0 mt-0.5" />
          <div>
            <p class="font-medium">Password reset successful</p>
            <p class="mt-1 text-sm">Your password has been updated. You can now sign in with your new password.</p>
          </div>
        </div>

        <button type="button" class="w-full btn-primary py-3" @click="goToLogin">
          Sign in
        </button>
      </div>
    </div>
  </div>
</template>
