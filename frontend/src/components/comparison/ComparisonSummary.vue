<script setup lang="ts">
import { computed } from 'vue'
import type { ContractComparisonResult } from '@/types'
import SimilarityScore from './SimilarityScore.vue'
import RiskLevelComparison from './RiskLevelComparison.vue'

interface Props {
  result: ContractComparisonResult
}

const props = defineProps<Props>()

const clauseStats = computed(() => ({
  matched: props.result.stats.matched_clauses,
  onlyInA: props.result.stats.clauses_only_in_a,
  onlyInB: props.result.stats.clauses_only_in_b,
  total:
    props.result.stats.total_clauses_a +
    props.result.stats.total_clauses_b -
    props.result.stats.matched_clauses,
}))

const deadlineStats = computed(() => ({
  matched: props.result.stats.matched_deadlines,
  onlyInA: props.result.stats.deadlines_only_in_a,
  onlyInB: props.result.stats.deadlines_only_in_b,
  total:
    props.result.stats.total_deadlines_a +
    props.result.stats.total_deadlines_b -
    props.result.stats.matched_deadlines,
}))
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Similarity Score -->
      <div class="flex justify-center">
        <SimilarityScore :score="result.similarity_score" size="md" />
      </div>

      <!-- Risk Comparison -->
      <div class="flex flex-col items-center justify-center">
        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">
          Overall Risk Level
        </h4>
        <RiskLevelComparison
          :risk-a="result.risk_comparison.contract_a"
          :risk-b="result.risk_comparison.contract_b"
          :changed="result.risk_comparison.changed"
        />
      </div>

      <!-- Stats Summary -->
      <div class="space-y-4">
        <!-- Clauses -->
        <div>
          <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Clauses</h4>
          <div class="grid grid-cols-3 gap-2 text-center">
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-2">
              <span class="text-lg font-bold text-green-600 dark:text-green-400">
                {{ clauseStats.matched }}
              </span>
              <p class="text-xs text-green-700 dark:text-green-500">Matched</p>
            </div>
            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-2">
              <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                {{ clauseStats.onlyInA }}
              </span>
              <p class="text-xs text-indigo-700 dark:text-indigo-500">Only A</p>
            </div>
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-2">
              <span class="text-lg font-bold text-purple-600 dark:text-purple-400">
                {{ clauseStats.onlyInB }}
              </span>
              <p class="text-xs text-purple-700 dark:text-purple-500">Only B</p>
            </div>
          </div>
        </div>

        <!-- Deadlines -->
        <div>
          <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Deadlines</h4>
          <div class="grid grid-cols-3 gap-2 text-center">
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-2">
              <span class="text-lg font-bold text-green-600 dark:text-green-400">
                {{ deadlineStats.matched }}
              </span>
              <p class="text-xs text-green-700 dark:text-green-500">Matched</p>
            </div>
            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-2">
              <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                {{ deadlineStats.onlyInA }}
              </span>
              <p class="text-xs text-indigo-700 dark:text-indigo-500">Only A</p>
            </div>
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-2">
              <span class="text-lg font-bold text-purple-600 dark:text-purple-400">
                {{ deadlineStats.onlyInB }}
              </span>
              <p class="text-xs text-purple-700 dark:text-purple-500">Only B</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contract titles -->
    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
      <div class="grid grid-cols-2 gap-4">
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-full bg-indigo-500 flex-shrink-0" />
          <div class="min-w-0">
            <span class="text-xs text-gray-500 dark:text-gray-400 block">Contract A</span>
            <span class="text-sm font-medium text-gray-900 dark:text-white truncate block">
              {{ result.contract_a.title }}
            </span>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-full bg-purple-500 flex-shrink-0" />
          <div class="min-w-0">
            <span class="text-xs text-gray-500 dark:text-gray-400 block">Contract B</span>
            <span class="text-sm font-medium text-gray-900 dark:text-white truncate block">
              {{ result.contract_b.title }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
