<template>
  <footer
    class="fixed bottom-0 left-0 right-0 z-40 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md border-t border-gray-200 dark:border-gray-700 transition-all duration-300">
    <div class="px-4 py-3 sm:px-6 lg:px-8">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 text-center sm:text-left">
          © {{ currentYear }} <span class="text-blue-600 dark:text-blue-400 font-bold">{{ appSettings.nama_aplikasi ||
            'SPP Management' }}</span>. All rights reserved.
        </p>
        <div class="flex items-center gap-4">
          <p
            class="text-xs font-medium px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-gray-500 dark:text-gray-400">
            v1.0.0
          </p>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'
import { useAppSettings } from '../../composables/useAppSettings'

const { appSettings } = useAppSettings()
const currentYear = computed(() => new Date().getFullYear())
</script>
