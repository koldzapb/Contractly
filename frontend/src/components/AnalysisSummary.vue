<script setup lang="ts">
import type { ContractAnalysis } from '@/types'
import RiskBadge from './RiskBadge.vue'

interface Props {
  analysis: ContractAnalysis
}

defineProps<Props>()
</script>

<template>
  <div class="card">
    <div class="flex items-start justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Analysis Summary</h2>
      <RiskBadge :level="analysis.overall_risk_level" size="md" />
    </div>

    <!-- Summary -->
    <div class="mb-6">
      <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
        {{ analysis.summary }}
      </p>
    </div>

    <!-- Key Findings -->
    <div v-if="analysis.key_findings && analysis.key_findings.length > 0" class="mb-6">
      <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide mb-3">
        Key Findings
      </h3>
      <ul class="space-y-2">
        <li
          v-for="(finding, index) in analysis.key_findings"
          :key="index"
          class="flex items-start gap-2"
        >
          <svg
            class="h-5 w-5 text-indigo-500 dark:text-indigo-400 flex-shrink-0 mt-0.5"
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
          <span class="text-gray-700 dark:text-gray-300">{{ finding }}</span>
        </li>
      </ul>
    </div>

    <!-- Analysis Metadata -->
    <div
      class="pt-4 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500 dark:text-gray-400"
    >
      <div class="flex items-center gap-1.5">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
          />
        </svg>
        <span>{{ analysis.ai_model }}</span>
      </div>
      <div v-if="analysis.tokens_used" class="flex items-center gap-1.5">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
          />
        </svg>
        <span>{{ analysis.tokens_used.toLocaleString() }} tokens</span>
      </div>
      <div v-if="analysis.processing_time_ms" class="flex items-center gap-1.5">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <span>{{ (analysis.processing_time_ms / 1000).toFixed(1) }}s</span>
      </div>
    </div>
  </div>
</template>
