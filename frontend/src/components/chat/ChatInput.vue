<script setup lang="ts">
import { ref, computed } from 'vue'

interface Props {
  disabled?: boolean
  placeholder?: string
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  placeholder: 'Type your question...',
})

const emit = defineEmits<{
  send: [message: string]
}>()

const message = ref('')

const canSend = computed(() => message.value.trim().length > 0 && !props.disabled)

function handleSubmit(): void {
  if (!canSend.value) return

  emit('send', message.value.trim())
  message.value = ''
}

function handleKeydown(event: KeyboardEvent): void {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault()
    handleSubmit()
  }
}
</script>

<template>
  <form @submit.prevent="handleSubmit" class="flex items-end gap-2">
    <div class="flex-1 relative">
      <textarea
        v-model="message"
        :disabled="disabled"
        :placeholder="placeholder"
        rows="1"
        class="w-full resize-none rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-3 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"
        @keydown="handleKeydown"
      />
    </div>
    <button
      type="submit"
      :disabled="!canSend"
      class="flex-shrink-0 rounded-xl bg-indigo-600 px-4 py-3 text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-indigo-600"
      title="Send message"
    >
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
        />
      </svg>
    </button>
  </form>
</template>
