<script setup lang="ts">
import { computed } from 'vue'
import RiskBadge from './RiskBadge.vue'
import type { QuickAnalysisResult } from '@/types/quickAnalysis'

interface Props {
  result: QuickAnalysisResult
}

const props = defineProps<Props>()

const emit = defineEmits<{
  back: []
}>()

const formattedProcessingTime = computed(() => {
  const ms = props.result.processing_time_ms
  if (ms < 1000) return `${ms}ms`
  return `${(ms / 1000).toFixed(1)}s`
})

const clausesByRisk = computed(() => {
  const high = props.result.clauses.filter((c) => c.risk_level === 'high')
  const medium = props.result.clauses.filter((c) => c.risk_level === 'medium')
  const low = props.result.clauses.filter((c) => c.risk_level === 'low')
  return { high, medium, low }
})

function getRiskColorClass(level: string): string {
  switch (level) {
    case 'high':
      return 'border-l-red-500'
    case 'medium':
      return 'border-l-yellow-500'
    default:
      return 'border-l-green-500'
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Back Button & Title -->
    <div class="flex items-center justify-between">
      <button
        type="button"
        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
        @click="emit('back')"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 19l-7-7 7-7"
          />
        </svg>
        Analyze another
      </button>
      <div class="text-xs text-gray-500 dark:text-gray-400">
        {{ formattedProcessingTime }} | {{ result.tokens_used.toLocaleString() }} tokens
      </div>
    </div>

    <!-- Ephemeral Warning Banner -->
    <div
      class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3"
    >
      <div class="flex items-start gap-2">
        <svg
          class="h-5 w-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
          />
        </svg>
        <p class="text-sm text-amber-800 dark:text-amber-200">
          This analysis is not saved. Upload a file for permanent storage with chat, reminders, and
          history.
        </p>
      </div>
    </div>

    <!-- PII Warning (if detected) -->
    <div
      v-if="result.pii_warning"
      class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3"
    >
      <div class="flex items-start gap-2">
        <svg
          class="h-5 w-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
          />
        </svg>
        <p class="text-sm text-red-800 dark:text-red-200">
          Personal information (PII) was detected in the text. Since this is a quick analysis, the
          data was not stored.
        </p>
      </div>
    </div>

    <!-- Summary Section -->
    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
          {{ result.title }}
        </h3>
        <RiskBadge :level="result.overall_risk_level" size="md" />
      </div>
      <p class="text-gray-700 dark:text-gray-300">{{ result.summary }}</p>
    </div>

    <!-- Key Findings -->
    <div v-if="result.key_findings.length > 0">
      <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Key Findings</h4>
      <ul class="space-y-2">
        <li
          v-for="(finding, index) in result.key_findings"
          :key="index"
          class="flex items-start gap-2"
        >
          <svg
            class="h-5 w-5 text-indigo-500 flex-shrink-0 mt-0.5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <span class="text-sm text-gray-700 dark:text-gray-300">{{ finding }}</span>
        </li>
      </ul>
    </div>

    <!-- Clauses Section -->
    <div v-if="result.clauses.length > 0">
      <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
          Clauses ({{ result.clauses.length }})
        </h4>
        <div class="flex gap-2 text-xs">
          <span v-if="clausesByRisk.high.length > 0" class="text-red-600 dark:text-red-400">
            {{ clausesByRisk.high.length }} high risk
          </span>
          <span v-if="clausesByRisk.medium.length > 0" class="text-yellow-600 dark:text-yellow-400">
            {{ clausesByRisk.medium.length }} medium
          </span>
          <span v-if="clausesByRisk.low.length > 0" class="text-green-600 dark:text-green-400">
            {{ clausesByRisk.low.length }} low
          </span>
        </div>
      </div>

      <div class="space-y-3">
        <div
          v-for="clause in result.clauses"
          :key="clause.id"
          class="border-l-4 bg-white dark:bg-gray-800 rounded-r-lg p-3 shadow-sm"
          :class="getRiskColorClass(clause.risk_level)"
        >
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
              {{ clause.clause_type_label }}
            </span>
            <RiskBadge :level="clause.risk_level" size="sm" :show-label="false" />
          </div>
          <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
            {{ clause.plain_explanation }}
          </p>
          <div
            v-if="clause.risk_reason"
            class="text-xs text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded px-2 py-1"
          >
            {{ clause.risk_reason }}
          </div>
          <details class="mt-2">
            <summary
              class="text-xs text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300"
            >
              View original text
            </summary>
            <blockquote
              class="mt-2 text-xs text-gray-600 dark:text-gray-400 border-l-2 border-gray-200 dark:border-gray-600 pl-2 italic"
            >
              {{ clause.original_text }}
            </blockquote>
          </details>
        </div>
      </div>
    </div>

    <!-- Deadlines Section -->
    <div v-if="result.deadlines.length > 0">
      <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Deadlines ({{ result.deadlines.length }})
      </h4>

      <div class="space-y-3">
        <div
          v-for="deadline in result.deadlines"
          :key="deadline.id"
          class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-200 dark:border-gray-700"
        >
          <div class="flex items-start justify-between mb-1">
            <div>
              <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ deadline.title }}
              </span>
              <span
                class="ml-2 text-xs px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded"
              >
                {{ deadline.deadline_type_label }}
              </span>
            </div>
            <span
              v-if="deadline.deadline_date"
              class="text-sm font-medium text-indigo-600 dark:text-indigo-400"
            >
              {{ deadline.deadline_date }}
            </span>
          </div>
          <p v-if="deadline.description" class="text-sm text-gray-700 dark:text-gray-300 mt-1">
            {{ deadline.description }}
          </p>
          <div
            v-if="deadline.is_recurring"
            class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1"
          >
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              />
            </svg>
            Recurring: {{ deadline.recurrence_pattern }}
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-if="result.clauses.length === 0 && result.deadlines.length === 0"
      class="text-center py-8 text-gray-500 dark:text-gray-400"
    >
      <svg class="mx-auto h-12 w-12 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
        />
      </svg>
      <p class="text-sm">No specific clauses or deadlines were extracted from this text.</p>
    </div>
  </div>
</template>
