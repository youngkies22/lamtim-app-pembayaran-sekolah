<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 overflow-y-auto"
    @click.self="$emit('close')"
  >
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
      <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="$emit('close')"></div>
      <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-600 to-indigo-600">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                <InformationCircleIcon class="w-6 h-6 text-white" />
              </div>
              <div>
                <h3 class="text-xl font-bold text-white">{{ title }}</h3>
                <p class="text-sm text-blue-100 mt-0.5">{{ subtitle }}</p>
              </div>
            </div>
            <button
              @click="$emit('close')"
              class="p-2 rounded-lg hover:bg-white/20 text-white transition-colors"
            >
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6">
          <div class="space-y-6">
            <div v-for="(section, index) in sections" :key="index" class="space-y-3">
              <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <component v-if="section.icon" :is="section.icon" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                {{ section.title }}
              </h4>
              <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                {{ section.description }}
              </p>
              <div v-if="section.items && section.items.length > 0" class="space-y-2">
                <div
                  v-for="(item, itemIndex) in section.items"
                  :key="itemIndex"
                  class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600"
                >
                  <div class="flex-shrink-0 mt-0.5">
                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                  </div>
                  <div class="flex-1">
                    <p class="font-medium text-gray-900 dark:text-white text-sm">{{ item.name }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ item.description }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex justify-end">
          <button
            @click="$emit('close')"
            class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
          >
            Mengerti
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { InformationCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline';

defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Informasi'
  },
  subtitle: {
    type: String,
    default: ''
  },
  sections: {
    type: Array,
    default: () => []
  }
});

defineEmits(['close']);
</script>
