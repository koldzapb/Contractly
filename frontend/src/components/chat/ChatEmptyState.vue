<script setup lang="ts">
const emit = defineEmits<{
  selectQuestion: [question: string]
}>()

const suggestedQuestions = [
  'What are the key obligations I need to fulfill?',
  'What happens if I want to terminate early?',
  'Are there any automatic renewal clauses?',
  'What are the payment terms?',
  'What are the highest risk clauses?',
]

function handleSelectQuestion(question: string): void {
  emit('selectQuestion', question)
}
</script>

<template>
  <div class="flex flex-col items-center justify-center py-8 text-center">
    <div class="w-16 h-16 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mb-4">
      <svg
        class="h-8 w-8 text-indigo-600 dark:text-indigo-400"
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
    </div>

    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
      Ask about your contract
    </h3>

    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-sm">
      I can help you understand this contract. Ask me about clauses, deadlines, risks, or anything else.
    </p>

    <div class="space-y-2 w-full max-w-md">
      <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
        Suggested questions
      </p>
      <button
        v-for="question in suggestedQuestions"
        :key="question"
        type="button"
        class="w-full text-left px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-300 hover:border-indigo-500 dark:hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
        @click="handleSelectQuestion(question)"
      >
        {{ question }}
      </button>
    </div>
  </div>
</template>
