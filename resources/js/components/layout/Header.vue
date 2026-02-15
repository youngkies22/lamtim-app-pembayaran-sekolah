<template>
  <header
    class="fixed top-0 left-0 right-0 z-50 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 transition-colors duration-200">
    <div class="px-4 py-3 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between">
        <!-- Left: Logo and Mobile Menu Button -->
        <div class="flex items-center gap-4">
          <button @click="$emit('toggle-sidebar')" type="button"
            class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400 transition-colors"
            aria-label="Toggle sidebar">
            <Bars3Icon class="w-6 h-6" />
          </button>

          <div class="flex items-center gap-3">
            <div v-if="appSettings.logo_aplikasi"
              class="w-10 h-10 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0 bg-white dark:bg-gray-700 p-1 border border-gray-200 dark:border-gray-600">
              <img :src="getLogoUrl(appSettings.logo_aplikasi)" :alt="appSettings.nama_aplikasi || 'Logo'"
                class="w-full h-full object-contain" @error="handleLogoError" />
            </div>
            <div v-else
              class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center shadow-md">
              <span class="text-white font-bold text-sm">SPP</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white hidden sm:block">
              {{ appSettings.nama_aplikasi || 'SPP Management' }}
            </h1>
          </div>
        </div>

        <!-- Right: Dark Mode Toggle and User Menu -->
        <div class="flex items-center gap-3">
          <!-- Dark Mode Toggle -->
          <button @click="toggleDarkMode"
            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400 transition-colors"
            aria-label="Toggle dark mode">
            <SunIcon v-if="isDark" class="w-5 h-5" />
            <MoonIcon v-else class="w-5 h-5" />
          </button>

          <!-- Cache Manager (Admin Only) -->
          <CacheManager v-if="isAdminUser" />

          <!-- User Dropdown -->
          <div class="relative" ref="dropdownRef">
            <button @click="toggleDropdown" type="button"
              class="flex items-center gap-2 p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400 transition-colors">
              <div v-if="currentUser?.avatar"
                class="w-8 h-8 rounded-full overflow-hidden border-2 border-indigo-500 flex-shrink-0">
                <img :src="getAvatarUrl(currentUser.avatar)" :alt="currentUser?.name || 'Avatar'"
                  class="w-full h-full object-cover" />
              </div>
              <div v-else
                class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                <span v-if="currentUser?.name || currentUser?.username">
                  {{ getInitials(currentUser?.name || currentUser?.username) }}
                </span>
                <UserIcon v-else class="w-5 h-5" />
              </div>
              <span class="hidden sm:block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ currentUser?.name || currentUser?.username || 'User' }}
              </span>
              <ChevronDownIcon class="w-4 h-4 hidden sm:block" />
            </button>

            <!-- Dropdown Menu -->
            <div v-if="dropdownOpen"
              class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50">
              <!-- User Info -->
              <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                  {{ currentUser?.name || currentUser?.username || 'User' }}
                </p>
                <p v-if="currentUser?.email" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                  {{ currentUser.email }}
                </p>
              </div>

              <!-- Menu Items -->
              <div class="py-1">
                <button @click="handleProfile"
                  class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-left">
                  <UserIcon class="w-4 h-4" />
                  Profile
                </button>
                <button v-if="isAdminUser" @click="handleSettings"
                  class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-left">
                  <Cog6ToothIcon class="w-4 h-4" />
                  Settings
                </button>
                <button v-if="isAdminUser" @click="handleFailedJobs"
                  class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-left">
                  <ExclamationTriangleIcon class="w-4 h-4" />
                  Failed Jobs
                </button>
              </div>

              <hr class="my-1 border-gray-200 dark:border-gray-700" />

              <button @click="handleLogout" :disabled="logoutLoading"
                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-left">
                <svg v-if="logoutLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                <ArrowRightOnRectangleIcon v-else class="w-4 h-4" />
                {{ logoutLoading ? 'Logging out...' : 'Logout' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useDarkMode } from '../../composables/useDarkMode'
import { useAppSettings } from '../../composables/useAppSettings'
import { useRoleAccess } from '../../composables/useRoleAccess'
import { authAPI } from '../../services/api'
import CacheManager from '../CacheManager.vue'
import {
  Bars3Icon,
  SunIcon,
  MoonIcon,
  UserIcon,
  ChevronDownIcon,
  Cog6ToothIcon,
  ExclamationTriangleIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const { appSettings, getLogoUrl, loadAppSettings } = useAppSettings()
const { isAdminUser } = useRoleAccess()

defineEmits(['toggle-sidebar'])

const { isDark, toggleDarkMode } = useDarkMode()
const dropdownOpen = ref(false)
const dropdownRef = ref(null)
const logoutLoading = ref(false)

// Get current user
const currentUser = computed(() => {
  return authAPI.getCurrentUser()
})

// Get user initials
const getInitials = (name) => {
  if (!name) return 'U'
  const parts = name.trim().split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

const closeDropdown = () => {
  dropdownOpen.value = false
}



// Handle profile click
const handleProfile = () => {
  closeDropdown()
  router.push('/profile')
}

// Handle settings click
const handleSettings = () => {
  closeDropdown()
  router.push('/settings')
}

// Handle failed jobs click
const handleFailedJobs = () => {
  closeDropdown()
  router.push('/failed-jobs')
}

// Handle logout
const handleLogout = async () => {
  try {
    logoutLoading.value = true
    await authAPI.logout()
    closeDropdown()
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
    // Even if API call fails, clear local storage and redirect
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
    router.push('/login')
  } finally {
    logoutLoading.value = false
  }
}

// Get avatar URL
const getAvatarUrl = (avatarPath) => {
  if (!avatarPath) return null
  if (avatarPath.startsWith('http://') || avatarPath.startsWith('https://')) {
    return avatarPath
  }
  return `/storage/${avatarPath}`
}

// Handle logo error
const handleLogoError = (event) => {
  console.warn('Logo image failed to load:', event.target.src)
  // Hide the broken image
  event.target.style.display = 'none'
}

// Click outside handler
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

onMounted(async () => {
  document.addEventListener('click', handleClickOutside)
  // Load app settings to get logo
  await loadAppSettings()
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
