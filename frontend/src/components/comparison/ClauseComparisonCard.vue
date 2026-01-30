<script setup lang="ts">
import { ref, computed } from 'vue'
import type { ClauseComparison } from '@/types'
import RiskBadge from '@/components/RiskBadge.vue'
import DiffHighlight from './DiffHighlight.vue'

interface Props {
  comparison: ClauseComparison
}

const props = defineProps<Props>()

const expanded = ref(false)

const clauseType = computed(() => {
  return (
    props.comparison.clause_a?.clause_type_label ??
    props.comparison.clause_b?.clause_type_label ??
    ''
  )
})

const similarityPercent = computed(() => {
  if (props.comparison.similarity === null) return null
  return Math.round(props.comparison.similarity * 100)
})

const borderClass = computed(() => {
  if (props.comparison.match_type === 'matched') {
    if (props.comparison.has_risk_difference) {
      return 'border-l-yellow-500'
    }
    return 'border-l-green-500'
  }
  if (props.comparison.match_type === 'only_in_a') {
    return 'border-l-indigo-500'
  }
  return 'border-l-purple-500'
})

const matchBadgeClass = computed(() => {
  if (props.comparison.match_type === 'matched') {
    return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
  }
  if (props.comparison.match_type === 'only_in_a') {
    return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400'
  }
  return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400'
})

const matchLabel = computed(() => {
  if (props.comparison.match_type === 'matched') return 'Matched'
  if (props.comparison.match_type === 'only_in_a') return 'Only in A'
  return 'Only in B'
})

const hasOriginalText = computed(() => {
  return props.comparison.clause_a?.original_text || props.comparison.clause_b?.original_text
})
</script>

<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 border-l-4 overflow-hidden"
    :class="borderClass"
  >
    <div class="p-4">
      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <span
              class="text-xs font-medium px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400"
            >
              {{ clauseType }}
            </span>
            <span :class="matchBadgeClass" class="text-xs font-medium px-2 py-0.5 rounded">
              {{ matchLabel }}
            </span>
            <span
              v-if="similarityPercent !== null"
              class="text-xs text-gray-500 dark:text-gray-400"
            >
              {{ similarityPercent }}% similar
            </span>
            <span
              v-if="comparison.has_risk_difference"
              class="text-xs font-medium px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400"
            >
              Risk Changed
            </span>
          </div>
        </div>
        <button
          v-if="hasOriginalText"
          type="button"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1"
          @click="expanded = !expanded"
        >
          <svg
            class="h-5 w-5 transition-transform"
            :class="{ 'rotate-180': expanded }"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 9l-7 7-7-7"
            />
          </svg>
        </button>
      </div>

      <!-- Risk levels comparison for matched -->
      <div v-if="comparison.match_type === 'matched'" class="mt-3 flex items-center gap-4">
        <div class="flex items-center gap-2">
          <span class="text-xs text-gray-500 dark:text-gray-400">A:</span>
          <RiskBadge
            v-if="comparison.clause_a"
            :level="comparison.clause_a.risk_level"
            size="sm"
            :show-label="false"
          />
        </div>
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M14 5l7 7m0 0l-7 7m7-7H3"
          />
        </svg>
        <div class="flex items-center gap-2">
          <span class="text-xs text-gray-500 dark:text-gray-400">B:</span>
          <RiskBadge
            v-if="comparison.clause_b"
            :level="comparison.clause_b.risk_level"
            size="sm"
            :show-label="false"
          />
        </div>
      </div>

      <!-- Single clause risk for only_in_a or only_in_b -->
      <div v-else class="mt-3 flex items-center gap-2">
        <RiskBadge v-if="comparison.clause_a" :level="comparison.clause_a.risk_level" size="sm" />
        <RiskBadge v-if="comparison.clause_b" :level="comparison.clause_b.risk_level" size="sm" />
      </div>

      <!-- Plain explanations -->
      <div class="mt-3 space-y-2">
        <p v-if="comparison.clause_a" class="text-sm text-gray-700 dark:text-gray-300">
          <span class="font-medium text-indigo-600 dark:text-indigo-400">A:</span>
          {{ comparison.clause_a.plain_explanation }}
        </p>
        <p v-if="comparison.clause_b" class="text-sm text-gray-700 dark:text-gray-300">
          <span class="font-medium text-purple-600 dark:text-purple-400">B:</span>
          {{ comparison.clause_b.plain_explanation }}
        </p>
      </div>
    </div>

    <!-- Expandable Original Text -->
    <div
      v-if="hasOriginalText && expanded"
      class="px-4 pb-4 pt-2 border-t border-gray-100 dark:border-gray-700"
    >
      <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">
        Original Text
      </p>
      <DiffHighlight
        :text-a="comparison.clause_a?.original_text ?? null"
        :text-b="comparison.clause_b?.original_text ?? null"
      />
    </div>
  </div>
</template>
