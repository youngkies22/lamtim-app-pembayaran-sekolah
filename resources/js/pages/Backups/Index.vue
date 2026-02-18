<template>
  <Layout active-menu="Backups">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-6 md:p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative flex flex-col md:flex-row items-center justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                <CircleStackIcon class="w-6 h-6 md:w-8 md:h-8 text-white" />
              </div>
              Database Backups
            </h1>
            <p class="mt-2 text-blue-100 text-sm md:text-base">
              Kelola backup database aplikasi untuk keamanan data
            </p>
          </div>
          <div class="flex gap-2">
            <!-- Spatie Backup Button -->
            <button @click="handleCreateBackup('spatie')" :disabled="createLoading"
              class="px-5 py-3 bg-white text-blue-600 font-semibold rounded-xl shadow-lg hover:shadow-xl hover:bg-blue-50 transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2 text-sm">
              <svg v-if="createLoading === 'spatie'" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              <PlusIcon v-else class="w-5 h-5" />
              {{ createLoading === 'spatie' ? 'Processing...' : 'Backup Spatie' }}
            </button>

            <!-- Laravel Backup Button -->
            <button @click="handleCreateBackup('laravel')" :disabled="createLoading"
              class="px-5 py-3 bg-emerald-500 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:bg-emerald-600 transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2 text-sm">
              <svg v-if="createLoading === 'laravel'" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              <CommandLineIcon v-else class="w-5 h-5" />
              {{ createLoading === 'laravel' ? 'Processing...' : 'Backup Laravel' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Tab Switcher -->
      <div class="flex gap-1 bg-gray-100 dark:bg-gray-700 rounded-xl p-1">
        <button @click="activeTab = 'spatie'"
          :class="[
            'flex-1 px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200',
            activeTab === 'spatie'
              ? 'bg-white dark:bg-gray-800 text-blue-600 shadow-sm'
              : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
          ]">
          Spatie Backup
          <span v-if="spatieBackups.length" class="ml-1.5 px-2 py-0.5 text-xs rounded-full"
            :class="activeTab === 'spatie' ? 'bg-blue-100 text-blue-600' : 'bg-gray-200 text-gray-500'">
            {{ spatieBackups.length }}
          </span>
        </button>
        <button @click="activeTab = 'laravel'"
          :class="[
            'flex-1 px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200',
            activeTab === 'laravel'
              ? 'bg-white dark:bg-gray-800 text-emerald-600 shadow-sm'
              : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
          ]">
          Laravel Backup
          <span v-if="laravelBackups.length" class="ml-1.5 px-2 py-0.5 text-xs rounded-full"
            :class="activeTab === 'laravel' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-200 text-gray-500'">
            {{ laravelBackups.length }}
          </span>
        </button>
      </div>

      <!-- Backup List -->
      <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
          <div v-if="loading" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
          </div>

          <div v-else-if="currentBackups.length === 0" class="text-center py-12">
            <div
              class="bg-gray-50 dark:bg-gray-700/50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <CircleStackIcon class="w-8 h-8 text-gray-400" />
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Belum ada backup</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Silakan buat backup pertama Anda</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                  <th scope="col" class="px-6 py-4 rounded-tl-xl ml-4">File Name</th>
                  <th scope="col" class="px-6 py-4">Size</th>
                  <th scope="col" class="px-6 py-4">Created At</th>
                  <th scope="col" class="px-6 py-4 text-center rounded-tr-xl mr-4">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-for="backup in currentBackups" :key="backup.filename"
                  class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                  <td class="px-6 py-4 font-medium text-gray-900 dark:text-white flex items-center gap-3">
                    <DocumentTextIcon class="w-5 h-5 text-gray-400 flex-shrink-0" />
                    <span class="truncate">{{ backup.filename }}</span>
                  </td>
                  <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                    {{ backup.size }}
                  </td>
                  <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                    {{ formatDate(backup.created_at) }}
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center gap-2">
                      <a :href="getDownloadUrl(backup)" target="_blank"
                        class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                        title="Download">
                        <ArrowDownTrayIcon class="w-5 h-5" />
                      </a>
                      <button @click="confirmDelete(backup)"
                        class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                        title="Delete">
                        <TrashIcon class="w-5 h-5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal :show="showDeleteModal" title="Konfirmasi Hapus Backup"
      :message="`Apakah Anda yakin ingin menghapus backup <strong>${backupToDelete?.filename}</strong>? Tindakan ini tidak dapat dibatalkan.`"
      type="danger" confirm-text="Hapus" :loading="deleteLoading" @confirm="handleDelete" @cancel="closeDeleteModal"
      @close="closeDeleteModal" />

    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Layout from '../../components/layout/Layout.vue'
import ConfirmModal from '../../components/ConfirmModal.vue'
import Toast from '../../components/Toast.vue'
import axios from 'axios'
import {
  CircleStackIcon,
  PlusIcon,
  TrashIcon,
  ArrowDownTrayIcon,
  DocumentTextIcon,
  CommandLineIcon
} from '@heroicons/vue/24/outline'

const spatieBackups = ref([])
const laravelBackups = ref([])
const activeTab = ref('laravel')
const loading = ref(true)
const createLoading = ref(false) // false | 'spatie' | 'laravel'
const deleteLoading = ref(false)
const showDeleteModal = ref(false)
const backupToDelete = ref(null)
const toastRef = ref(null)

const currentBackups = computed(() => {
  return activeTab.value === 'spatie' ? spatieBackups.value : laravelBackups.value
})

const getDownloadUrl = (backup) => {
  if (activeTab.value === 'laravel' || backup.type === 'laravel') {
    return `/api/backups/laravel/${backup.filename}/download`
  }
  return `/api/backups/${backup.filename}/download`
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const fetchBackups = async () => {
  try {
    loading.value = true
    const [spatieRes, laravelRes] = await Promise.all([
      axios.get('/api/backups'),
      axios.get('/api/backups/laravel/list'),
    ])
    if (spatieRes.data.success) spatieBackups.value = spatieRes.data.data
    if (laravelRes.data.success) laravelBackups.value = laravelRes.data.data
  } catch (error) {
    console.error('Error fetching backups:', error)
    toastRef.value?.error('Gagal memuat daftar backup')
  } finally {
    loading.value = false
  }
}

const handleCreateBackup = async (mode) => {
  try {
    createLoading.value = mode
    const url = mode === 'laravel' ? '/api/backups/laravel' : '/api/backups'
    const response = await axios.post(url)
    if (response.data.success) {
      toastRef.value?.success(response.data.message || 'Backup berhasil dibuat')
      activeTab.value = mode
      await fetchBackups()
    }
  } catch (error) {
    console.error('Error creating backup:', error)
    toastRef.value?.error(error.response?.data?.message || 'Gagal membuat backup')
  } finally {
    createLoading.value = false
  }
}

const confirmDelete = (backup) => {
  backupToDelete.value = { ...backup, tab: activeTab.value }
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  backupToDelete.value = null
}

const handleDelete = async () => {
  if (!backupToDelete.value) return

  try {
    deleteLoading.value = true
    const isLaravel = backupToDelete.value.tab === 'laravel'
    const url = isLaravel
      ? `/api/backups/laravel/${backupToDelete.value.filename}`
      : `/api/backups/${backupToDelete.value.filename}`
    const response = await axios.delete(url)
    if (response.data.success) {
      toastRef.value?.success('Backup berhasil dihapus')
      await fetchBackups()
      closeDeleteModal()
    }
  } catch (error) {
    console.error('Error deleting backup:', error)
    toastRef.value?.error('Gagal menghapus backup')
  } finally {
    deleteLoading.value = false
  }
}

onMounted(() => {
  fetchBackups()
})
</script>

<style scoped>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
