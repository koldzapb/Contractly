<script setup lang="ts">
import { computed } from 'vue'
import type { ChatMessage } from '@/types'

interface Props {
  message: ChatMessage
}

const props = defineProps<Props>()

const isUser = computed(() => props.message.is_user_message)
const isAssistant = computed(() => props.message.is_assistant_message)

const formattedTime = computed(() => {
  const date = new Date(props.message.created_at)
  return date.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
  })
})

const bubbleClasses = computed(() => {
  if (isUser.value) {
    return 'bg-indigo-600 text-white ml-auto'
  }
  if (props.message.is_off_topic) {
    return 'bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-200 border border-amber-200 dark:border-amber-800'
  }
  return 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100'
})
</script>

<template>
  <div
    class="flex flex-col gap-1"
    :class="[isUser ? 'items-end' : 'items-start']"
  >
    <!-- Role label -->
    <span class="text-xs text-gray-500 dark:text-gray-400 px-1">
      {{ message.role_label }}
    </span>

    <!-- Message bubble -->
    <div
      class="max-w-[85%] rounded-2xl px-4 py-2.5 text-sm leading-relaxed whitespace-pre-wrap"
      :class="bubbleClasses"
    >
      {{ message.content }}
    </div>

    <!-- Timestamp and metadata -->
    <div class="flex items-center gap-2 px-1">
      <span class="text-xs text-gray-400 dark:text-gray-500">
        {{ formattedTime }}
      </span>
      <span
        v-if="isAssistant && message.tokens_used"
        class="text-xs text-gray-400 dark:text-gray-500"
        title="Tokens used"
      >
        {{ message.tokens_used }} tokens
      </span>
    </div>
  </div>
</template>
