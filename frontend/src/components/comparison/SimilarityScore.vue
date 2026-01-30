<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  score: number
  size?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
})

const percent = computed(() => Math.round(props.score * 100))

const circumference = computed(() => {
  const radius = sizeConfig.value.radius
  return 2 * Math.PI * radius
})

const dashOffset = computed(() => {
  return circumference.value - (percent.value / 100) * circumference.value
})

const sizeConfig = computed(() => {
  const configs = {
    sm: { width: 80, radius: 32, strokeWidth: 6, fontSize: 'text-lg' },
    md: { width: 120, radius: 48, strokeWidth: 8, fontSize: 'text-2xl' },
    lg: { width: 160, radius: 64, strokeWidth: 10, fontSize: 'text-3xl' },
  }
  return configs[props.size]
})

const colorClass = computed(() => {
  if (percent.value >= 80) return 'text-green-500'
  if (percent.value >= 50) return 'text-yellow-500'
  return 'text-red-500'
})

const strokeColor = computed(() => {
  if (percent.value >= 80) return '#22c55e'
  if (percent.value >= 50) return '#eab308'
  return '#ef4444'
})
</script>

<template>
  <div class="flex flex-col items-center">
    <div
      class="relative"
      :style="{ width: sizeConfig.width + 'px', height: sizeConfig.width + 'px' }"
    >
      <svg class="transform -rotate-90" :width="sizeConfig.width" :height="sizeConfig.width">
        <!-- Background circle -->
        <circle
          class="text-gray-200 dark:text-gray-700"
          stroke="currentColor"
          fill="transparent"
          :stroke-width="sizeConfig.strokeWidth"
          :r="sizeConfig.radius"
          :cx="sizeConfig.width / 2"
          :cy="sizeConfig.width / 2"
        />
        <!-- Progress circle -->
        <circle
          :stroke="strokeColor"
          fill="transparent"
          :stroke-width="sizeConfig.strokeWidth"
          :stroke-dasharray="circumference"
          :stroke-dashoffset="dashOffset"
          stroke-linecap="round"
          :r="sizeConfig.radius"
          :cx="sizeConfig.width / 2"
          :cy="sizeConfig.width / 2"
          class="transition-all duration-500 ease-out"
        />
      </svg>
      <!-- Percentage text -->
      <div class="absolute inset-0 flex items-center justify-center">
        <span :class="[sizeConfig.fontSize, colorClass]" class="font-bold"> {{ percent }}% </span>
      </div>
    </div>
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 text-center">Similarity Score</p>
  </div>
</template>
