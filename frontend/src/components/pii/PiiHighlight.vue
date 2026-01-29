<script setup lang="ts">
import { computed } from 'vue'
import type { PiiType } from '@/types'

interface Props {
  type: PiiType
  value: string
  redactedValue: string
  selected: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  toggle: []
}>()

const colorClasses = computed(() => {
  const colors: Record<PiiType, { selected: string; unselected: string }> = {
    ssn: {
      selected: 'bg-red-200 dark:bg-red-800/50 text-red-900 dark:text-red-200',
      unselected: 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400',
    },
    email: {
      selected: 'bg-blue-200 dark:bg-blue-800/50 text-blue-900 dark:text-blue-200',
      unselected: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400',
    },
    phone: {
      selected: 'bg-green-200 dark:bg-green-800/50 text-green-900 dark:text-green-200',
      unselected: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400',
    },
    credit_card: {
      selected: 'bg-purple-200 dark:bg-purple-800/50 text-purple-900 dark:text-purple-200',
      unselected: 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400',
    },
    bank_routing: {
      selected: 'bg-orange-200 dark:bg-orange-800/50 text-orange-900 dark:text-orange-200',
      unselected: 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400',
    },
    bank_account: {
      selected: 'bg-yellow-200 dark:bg-yellow-800/50 text-yellow-900 dark:text-yellow-200',
      unselected: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400',
    },
  }

  return (
    colors[props.type] || {
      selected: 'bg-gray-200 dark:bg-gray-800/50 text-gray-900 dark:text-gray-200',
      unselected: 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400',
    }
  )
})

const displayValue = computed(() => {
  return props.selected ? props.redactedValue : props.value
})

function handleClick(): void {
  emit('toggle')
}

function handleKeydown(event: KeyboardEvent): void {
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault()
    emit('toggle')
  }
}
</script>

<template>
  <span
    :class="[selected ? colorClasses.selected : colorClasses.unselected]"
    class="inline-block px-1.5 py-0.5 rounded cursor-pointer font-mono text-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-indigo-500"
    :title="selected ? `Will be redacted to: ${redactedValue}` : `Click to redact: ${value}`"
    role="button"
    :aria-pressed="selected"
    tabindex="0"
    @click="handleClick"
    @keydown="handleKeydown"
  >
    <span v-if="selected" class="line-through opacity-50">{{ value }}</span>
    <span v-else>{{ displayValue }}</span>
    <svg
      v-if="selected"
      class="inline-block ml-1 h-3 w-3"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      aria-hidden="true"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
    </svg>
  </span>
</template>
