<template>
  <div class="relative" ref="dropdownRef">
    <!-- Trigger Button -->
    <button @click="toggleDropdown" type="button"
      class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400 transition-colors relative"
      aria-label="Cache Manager" :title="'Cache Manager (' + status.driver + ')'">
      <CircleStackIcon class="w-5 h-5" />
      <span v-if="hasOptimizeCache" class="absolute top-1.5 right-1.5 w-2 h-2 bg-emerald-500 border-2 border-white dark:border-gray-800 rounded-full"></span>
      <span v-else class="absolute top-1.5 right-1.5 w-2 h-2 bg-gray-300 dark:bg-gray-600 border-2 border-white dark:border-gray-800 rounded-full"></span>
    </button>

    <!-- Dropdown Menu -->
    <div v-if="isOpen"
      class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-3 z-50 overflow-hidden transform origin-top-right transition-all duration-200">
      
      <!-- Header -->
      <div class="px-4 pb-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
          Cache Manager
        </h3>
        <span class="flex items-center gap-1.5 text-[10px] uppercase font-bold text-emerald-600 dark:text-emerald-400">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          Aktif
        </span>
      </div>

      <!-- Stats Section -->
      <div class="px-4 py-3 bg-gray-50/50 dark:bg-gray-900/20 space-y-2">
        <div class="flex justify-between items-center text-xs">
          <span class="text-gray-500 dark:text-gray-400">Driver:</span>
          <span class="font-mono text-gray-900 dark:text-gray-100">{{ status.driver }}</span>
        </div>
        
        <div v-if="status.driver === 'redis'" class="flex justify-between items-center text-xs">
          <span class="text-gray-500 dark:text-gray-400">Redis:</span>
          <span v-if="status.redis_connected" class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
            <CheckIcon class="w-3.5 h-3.5 stroke-[3]" /> Connected
          </span>
          <span v-else class="flex items-center gap-1 text-red-600">
            <XMarkIcon class="w-3.5 h-3.5 stroke-[3]" /> Disconnected
          </span>
        </div>

        <div class="flex justify-between items-center text-xs">
          <span class="text-gray-500 dark:text-gray-400">Config Cache:</span>
          <CheckIcon v-if="status.config_cached" class="w-3.5 h-3.5 text-emerald-500 stroke-[3]" />
          <XMarkIcon v-else class="w-3.5 h-3.5 text-gray-400 stroke-[3]" />
        </div>

        <div class="flex justify-between items-center text-xs">
          <span class="text-gray-500 dark:text-gray-400">Routes Cache:</span>
          <CheckIcon v-if="status.routes_cached" class="w-3.5 h-3.5 text-emerald-500 stroke-[3]" />
          <XMarkIcon v-else class="w-3.5 h-3.5 text-gray-400 stroke-[3]" />
        </div>

        <div class="flex justify-between items-center text-xs">
          <span class="text-gray-500 dark:text-gray-400">Views Cache:</span>
          <CheckIcon v-if="status.views_cached" class="w-3.5 h-3.5 text-emerald-500 stroke-[3]" />
          <XMarkIcon v-else class="w-3.5 h-3.5 text-gray-400 stroke-[3]" />
        </div>
      </div>

      <!-- Actions -->
      <div class="p-2 space-y-1">
        <button @click="handleAction('clearLaravel')" :disabled="loading"
          class="w-full flex items-center gap-3 px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-md transition-all group disabled:opacity-50">
          <TrashIcon class="w-4 h-4 text-red-500 group-hover:scale-110 transition-transform" />
          <span class="font-medium">Clear Laravel Cache</span>
        </button>

        <button v-if="status.driver === 'redis'" @click="handleAction('clearRedis')" :disabled="loading"
          class="w-full flex items-center gap-3 px-3 py-2 text-sm text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/10 rounded-md transition-all group disabled:opacity-50">
          <CircleStackIcon class="w-4 h-4 text-orange-500 group-hover:scale-110 transition-transform" />
          <span class="font-medium">Clear Redis Cache</span>
        </button>

        <button @click="handleAction('optimize')" :disabled="loading"
          class="w-full flex items-center gap-3 px-3 py-2 text-sm text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/10 rounded-md transition-all group disabled:opacity-50">
          <BoltIcon class="w-4 h-4 text-blue-500 group-hover:scale-110 transition-transform" />
          <span class="font-medium">Optimize Cache</span>
        </button>

        <button @click="fetchStatus" :disabled="loading"
          class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 rounded-md transition-all group disabled:opacity-50">
          <ArrowPathIcon class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" :class="{ 'animate-spin': loading }" />
          <span class="font-medium">Refresh Status</span>
        </button>
      </div>

    </div>
    
    <!-- Toast Reference -->
    <Toast ref="toastRef" />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { 
  CircleStackIcon, 
  CheckIcon, 
  XMarkIcon, 
  TrashIcon, 
  BoltIcon, 
  ArrowPathIcon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/outline'
import { cacheAPI } from '../services/api'
import Toast from './Toast.vue'

const isOpen = ref(false)
const loading = ref(false)
const dropdownRef = ref(null)
const toastRef = ref(null)

const status = ref({
  driver: '...',
  redis_connected: false,
  config_cached: false,
  routes_cached: false,
  events_cached: false,
  views_cached: false
})

const hasOptimizeCache = computed(() => {
  return status.value.config_cached || status.value.routes_cached
})

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    fetchStatus()
  }
}

const closeDropdown = () => {
  isOpen.value = false
}

const fetchStatus = async () => {
  try {
    loading.value = true
    const response = await cacheAPI.getStatus()
    if (response.data.success) {
      status.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch cache status:', error)
  } finally {
    loading.value = false
  }
}

const handleAction = async (action) => {
  try {
    loading.value = true
    let response
    
    if (action === 'clearLaravel') response = await cacheAPI.clearLaravel()
    else if (action === 'clearRedis') response = await cacheAPI.clearRedis()
    else if (action === 'optimize') response = await cacheAPI.optimize()
    
    if (response.data.success) {
      toastRef.value?.success(response.data.message || 'Berhasil')
      await fetchStatus()
    } else {
      toastRef.value?.error(response.data.message || 'Gagal')
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Terjadi kesalahan sistem'
    toastRef.value?.error(msg)
  } finally {
    loading.value = false
  }
}

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  fetchStatus()
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
