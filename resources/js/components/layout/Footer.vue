<template>
  <footer class="fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 transition-colors duration-200">
    <div class="px-4 py-3 sm:px-6 lg:px-8">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          © {{ currentYear }} SPP Management System. All rights reserved.
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-500">
          Version 1.0.0
        </p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>
