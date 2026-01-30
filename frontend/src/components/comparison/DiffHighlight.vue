<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  textA: string | null
  textB: string | null
  showFull?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showFull: false,
})

const MAX_LENGTH = 300

const displayTextA = computed(() => {
  if (!props.textA) return ''
  if (props.showFull || props.textA.length <= MAX_LENGTH) return props.textA
  return props.textA.slice(0, MAX_LENGTH) + '...'
})

const displayTextB = computed(() => {
  if (!props.textB) return ''
  if (props.showFull || props.textB.length <= MAX_LENGTH) return props.textB
  return props.textB.slice(0, MAX_LENGTH) + '...'
})

const isTruncated = computed(() => {
  return (
    (props.textA && props.textA.length > MAX_LENGTH) ||
    (props.textB && props.textB.length > MAX_LENGTH)
  )
})
</script>

<template>
  <div class="space-y-3">
    <!-- Text A -->
    <div v-if="textA" class="relative">
      <div class="absolute left-0 top-0 bottom-0 w-1 rounded bg-indigo-400" />
      <div class="pl-4">
        <span
          class="text-xs font-medium text-indigo-600 dark:text-indigo-400 uppercase tracking-wide"
        >
          Contract A
        </span>
        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 font-mono whitespace-pre-wrap">
          {{ displayTextA }}
        </p>
      </div>
    </div>

    <!-- Text B -->
    <div v-if="textB" class="relative">
      <div class="absolute left-0 top-0 bottom-0 w-1 rounded bg-purple-400" />
      <div class="pl-4">
        <span
          class="text-xs font-medium text-purple-600 dark:text-purple-400 uppercase tracking-wide"
        >
          Contract B
        </span>
        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 font-mono whitespace-pre-wrap">
          {{ displayTextB }}
        </p>
      </div>
    </div>

    <!-- Truncation notice -->
    <p v-if="isTruncated && !showFull" class="text-xs text-gray-400 italic">
      Text truncated for display
    </p>
  </div>
</template>
