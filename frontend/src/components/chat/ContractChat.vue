<script setup lang="ts">
import { ref, onMounted, watch, nextTick } from 'vue'
import { useContractChat } from '@/composables/useContractChat'
import ChatMessage from './ChatMessage.vue'
import ChatInput from './ChatInput.vue'
import ChatTypingIndicator from './ChatTypingIndicator.vue'
import ChatEmptyState from './ChatEmptyState.vue'
import ChatDisclaimer from './ChatDisclaimer.vue'

interface Props {
  contractId: string
  contractCompleted: boolean
}

const props = defineProps<Props>()

const {
  messages,
  loading,
  sending,
  error,
  hasMessages,
  fetchHistory,
  sendMessage,
  clearHistory,
  clearError,
} = useContractChat(props.contractId)

const messagesContainer = ref<HTMLElement | null>(null)

async function handleSend(message: string): Promise<void> {
  clearError()
  await sendMessage(message)
  scrollToBottom()
}

function handleSelectQuestion(question: string): void {
  handleSend(question)
}

async function handleClearHistory(): Promise<void> {
  if (confirm('Are you sure you want to clear the chat history?')) {
    await clearHistory()
  }
}

function scrollToBottom(): void {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

// Watch for new messages and scroll
watch(messages, () => {
  scrollToBottom()
}, { deep: true })

onMounted(async () => {
  if (props.contractCompleted) {
    await fetchHistory()
    scrollToBottom()
  }
})
</script>

<template>
  <div class="flex flex-col h-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-2">
        <svg
          class="h-5 w-5 text-indigo-600 dark:text-indigo-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
          />
        </svg>
        <h3 class="font-medium text-gray-900 dark:text-white">
          Ask about this contract
        </h3>
      </div>
      <button
        v-if="hasMessages"
        type="button"
        class="text-xs text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"
        @click="handleClearHistory"
      >
        Clear chat
      </button>
    </div>

    <!-- Not completed notice -->
    <div
      v-if="!contractCompleted"
      class="flex-1 flex items-center justify-center p-8"
    >
      <div class="text-center">
        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
          <svg
            class="h-8 w-8 text-gray-400 dark:text-gray-500"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          Analysis in progress
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Chat will be available once the contract analysis is complete.
        </p>
      </div>
    </div>

    <!-- Chat content -->
    <template v-else>
      <!-- Messages area -->
      <div
        ref="messagesContainer"
        class="flex-1 overflow-y-auto p-4 space-y-4"
      >
        <!-- Loading state -->
        <div v-if="loading && !hasMessages" class="flex items-center justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600" />
        </div>

        <!-- Empty state -->
        <ChatEmptyState
          v-else-if="!hasMessages && !loading"
          @select-question="handleSelectQuestion"
        />

        <!-- Messages -->
        <template v-else>
          <ChatMessage
            v-for="message in messages"
            :key="message.id"
            :message="message"
          />

          <!-- Typing indicator -->
          <ChatTypingIndicator v-if="sending" />
        </template>
      </div>

      <!-- Error message -->
      <div
        v-if="error"
        class="mx-4 mb-2 px-3 py-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"
      >
        <div class="flex items-center justify-between gap-2">
          <p class="text-sm text-red-600 dark:text-red-400">
            {{ error }}
          </p>
          <button
            type="button"
            class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
            @click="clearError"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Disclaimer -->
      <div class="px-4 pb-2">
        <ChatDisclaimer />
      </div>

      <!-- Input area -->
      <div class="px-4 pb-4">
        <ChatInput
          :disabled="sending"
          :placeholder="sending ? 'Waiting for response...' : 'Ask about this contract...'"
          @send="handleSend"
        />
      </div>
    </template>
  </div>
</template>
