<template>
  <Layout active-menu="Sync">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 p-6 md:p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative flex flex-col md:flex-row items-center justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                <ArrowPathIcon class="w-6 h-6 md:w-8 md:h-8 text-white" />
              </div>
              Sinkronisasi Data
            </h1>
            <p class="mt-2 text-emerald-100 text-sm md:text-base">
              Sinkronisasi data dari API eksternal ke database lokal
            </p>
          </div>
          <div class="flex items-center gap-3">
            <button @click="handleTestConnection" :disabled="testLoading || isSyncing"
              class="px-4 py-3 bg-white/20 text-white font-medium rounded-xl backdrop-blur-sm hover:bg-white/30 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 text-sm">
              <svg v-if="testLoading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              <SignalIcon v-else class="w-4 h-4" />
              Tes Koneksi
            </button>
            <button @click="runSyncAll" :disabled="isSyncing"
              class="px-6 py-3 bg-white text-emerald-600 font-semibold rounded-xl shadow-lg hover:shadow-xl hover:bg-emerald-50 transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2">
              <svg v-if="syncingEntity === 'all'" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              <ArrowPathIcon v-else class="w-5 h-5" />
              {{ syncingEntity === 'all' ? 'Menyinkronkan...' : 'Sync Semua' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Last Sync Info -->
      <div v-if="lastSync"
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5">
        <div class="flex items-center gap-3">
          <ClockIcon class="w-5 h-5 text-gray-400" />
          <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Sinkronisasi Terakhir:</span>
          <span class="text-sm font-bold text-gray-900 dark:text-white">{{ formatDate(lastSync.timestamp) }}</span>
        </div>
      </div>

      <!-- Entity Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div v-for="entityName in entities" :key="entityName"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-5 hover:shadow-xl transition-all duration-200">
          <!-- Header -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-xl" :class="entityColor(entityName)">
                <component :is="entityIcon(entityName)" class="w-5 h-5 text-white" />
              </div>
              <div>
                <h3 class="font-bold text-gray-900 dark:text-white text-sm">{{ entityLabel(entityName) }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ stats[entityName]?.total || 0 }} total · {{ stats[entityName]?.synced || 0 }} synced
                </p>
              </div>
            </div>
          </div>

          <!-- Siswa Progress Bar (chunked sync) -->
          <div v-if="entityName === 'siswa' && siswaProgress" class="mb-4">
            <div class="flex justify-between items-center mb-1.5 text-[10px] font-bold uppercase">
              <span class="text-emerald-600 dark:text-emerald-400">
                {{ siswaProgress.stage === 'downloading' ? 'Mendownload data...' : `Memproses:
                ${siswaProgress.processed} / ${siswaProgress.total}` }}
              </span>
              <span class="text-emerald-600 dark:text-emerald-400">{{ siswaProgress.percentage }}%</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
              <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-full transition-all duration-500 ease-out"
                :style="`width: ${siswaProgress.percentage}%`"
                :class="{ 'animate-pulse': siswaProgress.stage === 'downloading' }"></div>
            </div>
            <!-- Detail stats during processing -->
            <div v-if="siswaProgress.stage === 'processing'" class="mt-2 grid grid-cols-3 gap-1 text-[10px]">
              <div class="text-center p-1 bg-emerald-50 dark:bg-emerald-900/20 rounded">
                <div class="font-bold text-emerald-600">+{{ siswaProgress.inserted }}</div>
                <div class="text-gray-500">Tambah</div>
              </div>
              <div class="text-center p-1 bg-blue-50 dark:bg-blue-900/20 rounded">
                <div class="font-bold text-blue-600">{{ siswaProgress.updated }}</div>
                <div class="text-gray-500">Update</div>
              </div>
              <div class="text-center p-1 bg-red-50 dark:bg-red-900/20 rounded">
                <div class="font-bold text-red-600">{{ siswaProgress.failed }}</div>
                <div class="text-gray-500">Gagal</div>
              </div>
            </div>
          </div>

          <!-- Last Result -->
          <div v-if="syncResults && syncResults[entityName]" class="mb-4 space-y-1.5">
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 text-xs font-semibold rounded-full"
                :class="statusBadge(syncResults[entityName].status)">
                {{ statusText(syncResults[entityName].status) }}
              </span>
            </div>
            <div class="grid grid-cols-3 gap-1 text-xs">
              <div class="text-center p-1.5 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                <div class="font-bold text-emerald-600 dark:text-emerald-400">+{{ syncResults[entityName].inserted || 0
                }}</div>
                <div class="text-gray-500 dark:text-gray-400">Tambah</div>
              </div>
              <div class="text-center p-1.5 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <div class="font-bold text-blue-600 dark:text-blue-400">{{ syncResults[entityName].updated || 0 }}</div>
                <div class="text-gray-500 dark:text-gray-400">Update</div>
              </div>
              <div class="text-center p-1.5 bg-red-50 dark:bg-red-900/20 rounded-lg">
                <div class="font-bold text-red-600 dark:text-red-400">{{ syncResults[entityName].failed || 0 }}</div>
                <div class="text-gray-500 dark:text-gray-400">Gagal</div>
              </div>
            </div>
            <!-- Error detail -->
            <button v-if="syncResults[entityName].errors?.length > 0"
              @click="showErrors(entityName, syncResults[entityName].errors)"
              class="text-xs text-red-500 hover:text-red-700 underline mt-1">
              Lihat {{ syncResults[entityName].errors.length }} error
            </button>
          </div>

          <!-- Sync Button -->
          <button @click="runSync(entityName)" :disabled="isSyncing"
            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200"
            :class="syncingEntity === entityName
              ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300'
              : 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 hover:text-emerald-700 dark:hover:text-emerald-300 disabled:opacity-50 disabled:cursor-not-allowed'">
            <svg v-if="syncingEntity === entityName" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg"
              fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg>
            <ArrowPathIcon v-else class="w-4 h-4" />
            {{ syncingEntity === entityName ? 'Menyinkronkan...' : 'Sinkronisasi' }}
          </button>
        </div>
      </div>

      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-emerald-600"></div>
      </div>
    </div>

    <!-- Error Modal -->
    <div v-if="showErrorModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
      @click.self="showErrorModal = false">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg p-6 animate-modalIn">
        <h3 class="text-lg font-bold text-red-600 dark:text-red-400 mb-4 flex items-center gap-2">
          <ExclamationTriangleIcon class="w-5 h-5" />
          Error — {{ errorModalTitle }}
        </h3>
        <div class="max-h-64 overflow-y-auto space-y-2">
          <div v-for="(err, idx) in errorModalErrors" :key="idx"
            class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg text-sm text-red-700 dark:text-red-300 break-words">
            {{ err }}
          </div>
        </div>
        <button @click="showErrorModal = false"
          class="mt-4 w-full py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors font-medium">
          Tutup
        </button>
      </div>
    </div>

    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Layout from '../../components/layout/Layout.vue'
import Toast from '../../components/Toast.vue'
import { syncAPI } from '../../services/api'
import { useAppSettings } from '../../composables/useAppSettings'
import { useMasterDataCache } from '../../composables/useMasterDataCache'
import {
  ArrowPathIcon,
  ClockIcon,
  SignalIcon,
  ExclamationTriangleIcon,
  BuildingOffice2Icon,
  AcademicCapIcon,
  UserGroupIcon,
  CalendarDaysIcon,
  RectangleStackIcon,
  BookOpenIcon,
  TableCellsIcon,
  GlobeAltIcon,
} from '@heroicons/vue/24/outline'

const { appSettings, loadAppSettings } = useAppSettings()
const { getCached, loadAll } = useMasterDataCache()

const cached = getCached()
const labelJurusan = ref(cached.labelJurusan || 'Jurusan')
const toastRef = ref(null)
const loading = ref(true)
const testLoading = ref(false)
const syncingEntity = ref(null)
const showErrorModal = ref(false)
const errorModalTitle = ref('')
const errorModalErrors = ref([])

const lastSync = ref(null)
const stats = ref({})
const entities = ref([])
const syncResults = ref(null)

// Siswa chunked progress
const siswaProgress = ref(null)

const CHUNK_SIZE = 50

const isSyncing = computed(() => syncingEntity.value !== null)

const entityLabels = computed(() => ({
  tahun_ajaran: 'Tahun Ajaran',
  semester: 'Semester',
  sekolah: 'Sekolah',
  kelas: 'Kelas',
  jurusan: labelJurusan.value || 'Jurusan',
  rombel: 'Rombel',
  siswa: 'Siswa',
}))

const entityLabel = (key) => entityLabels.value[key] || key

const entityIcon = (key) => {
  const icons = {
    tahun_ajaran: CalendarDaysIcon,
    semester: BookOpenIcon,
    sekolah: BuildingOffice2Icon,
    kelas: TableCellsIcon,
    jurusan: AcademicCapIcon,
    rombel: RectangleStackIcon,
    siswa: UserGroupIcon,
  }
  return icons[key] || GlobeAltIcon
}

const entityColor = (key) => {
  const colors = {
    tahun_ajaran: 'bg-violet-500',
    semester: 'bg-blue-500',
    sekolah: 'bg-emerald-500',
    kelas: 'bg-amber-500',
    jurusan: 'bg-rose-500',
    rombel: 'bg-cyan-500',
    siswa: 'bg-indigo-500',
  }
  return colors[key] || 'bg-gray-500'
}

const statusBadge = (status) => {
  const badges = {
    completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    completed_with_errors: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    failed: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    skipped: 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
  }
  return badges[status] || badges.skipped
}

const statusText = (status) => {
  const texts = {
    completed: 'Selesai',
    completed_with_errors: 'Sebagian Gagal',
    failed: 'Gagal',
    skipped: 'Dilewati',
  }
  return texts[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const fetchStatus = async () => {
  try {
    loading.value = true
    const response = await syncAPI.status()
    if (response.data.success) {
      lastSync.value = response.data.data.last_sync
      stats.value = response.data.data.stats || {}
      entities.value = response.data.data.entities || []

      if (lastSync.value?.results) {
        syncResults.value = { ...syncResults.value, ...lastSync.value.results }
      }
    }
  } catch (error) {
    console.error('Error fetching sync status:', error)
  } finally {
    loading.value = false
  }
}

const handleTestConnection = async () => {
  try {
    testLoading.value = true
    const response = await syncAPI.testConnection()
    if (response.data.success) {
      toastRef.value?.success(response.data.message || 'Koneksi berhasil')
    } else {
      toastRef.value?.error(response.data.message || 'Koneksi gagal')
    }
  } catch (error) {
    toastRef.value?.error(error.response?.data?.message || 'Gagal menguji koneksi API')
  } finally {
    testLoading.value = false
  }
}

/**
 * Sync a single NON-SISWA entity (synchronous HTTP call).
 */
const runSyncNonSiswa = async (entity) => {
  syncingEntity.value = entity
  try {
    const response = await syncAPI.run({ entity })
    if (response.data.success) {
      const result = response.data.data.result
      if (!syncResults.value) syncResults.value = {}
      syncResults.value[entity] = result
      toastRef.value?.success(`${entityLabel(entity)}: Selesai`)
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Gagal sinkronisasi'
    toastRef.value?.error(`${entityLabel(entity)}: ${msg}`)
    if (!syncResults.value) syncResults.value = {}
    syncResults.value[entity] = { status: 'failed', message: msg, inserted: 0, updated: 0, failed: 1, errors: [msg] }
  } finally {
    syncingEntity.value = null
    await fetchStatus()
  }
}

/**
 * Sync SISWA via frontend-driven chunked processing.
 */
const runSyncSiswa = async () => {
  syncingEntity.value = 'siswa'
  siswaProgress.value = { stage: 'downloading', processed: 0, total: 0, percentage: 0, inserted: 0, updated: 0, failed: 0 }

  const allErrors = []
  let totalInserted = 0, totalUpdated = 0, totalFailed = 0

  try {
    // Stage 1: Download
    const dlResponse = await syncAPI.siswaDownload()
    if (!dlResponse.data.success) {
      throw new Error(dlResponse.data.message || 'Gagal download')
    }

    const total = dlResponse.data.data.total
    if (total === 0) {
      siswaProgress.value = null
      toastRef.value?.info('Tidak ada data siswa dari API')
      syncingEntity.value = null
      return
    }

    siswaProgress.value = { stage: 'processing', processed: 0, total, percentage: 0, inserted: 0, updated: 0, failed: 0 }

    // Stage 2: Process in chunks
    let offset = 0
    while (offset < total) {
      const chunkResponse = await syncAPI.siswaProcessChunk({ offset, limit: CHUNK_SIZE })

      if (chunkResponse.data.success) {
        const result = chunkResponse.data.data
        totalInserted += result.inserted || 0
        totalUpdated += result.updated || 0
        totalFailed += result.failed || 0
        if (result.errors?.length) allErrors.push(...result.errors)

        const processed = Math.min(offset + CHUNK_SIZE, total)
        const percentage = Math.round((processed / total) * 100)

        siswaProgress.value = {
          stage: 'processing',
          processed,
          total,
          percentage,
          inserted: totalInserted,
          updated: totalUpdated,
          failed: totalFailed,
        }
      } else {
        totalFailed += CHUNK_SIZE
        allErrors.push(chunkResponse.data.message || 'Chunk gagal')
      }

      offset += CHUNK_SIZE
    }

    // Stage 3: Cleanup
    await syncAPI.siswaCleanup()

    // Save result
    if (!syncResults.value) syncResults.value = {}
    const status = totalFailed > 0 ? 'completed_with_errors' : 'completed'
    syncResults.value['siswa'] = {
      status,
      message: `Sync selesai: ${totalInserted} tambah, ${totalUpdated} update, ${totalFailed} gagal`,
      inserted: totalInserted,
      updated: totalUpdated,
      failed: totalFailed,
      errors: allErrors,
    }

    toastRef.value?.success(`Siswa: Selesai — ${totalInserted} tambah, ${totalUpdated} update`)

  } catch (error) {
    const msg = error.response?.data?.message || error.message || 'Gagal sinkronisasi siswa'
    toastRef.value?.error(msg)
    if (!syncResults.value) syncResults.value = {}
    syncResults.value['siswa'] = { status: 'failed', message: msg, inserted: totalInserted, updated: totalUpdated, failed: totalFailed + 1, errors: [...allErrors, msg] }
    // Attempt cleanup on error
    try { await syncAPI.siswaCleanup() } catch (_) { }
  } finally {
    siswaProgress.value = null
    syncingEntity.value = null
    await fetchStatus()
  }
}

/**
 * Main entry: decide sync strategy based on entity type.
 */
const runSync = (entity) => {
  if (isSyncing.value) return
  if (entity === 'siswa') {
    runSyncSiswa()
  } else {
    runSyncNonSiswa(entity)
  }
}

/**
 * Sync all entities sequentially.
 */
const runSyncAll = async () => {
  if (isSyncing.value) return
  syncingEntity.value = 'all'

  // Sync non-siswa first (sequentially)
  const nonSiswa = entities.value.filter(e => e !== 'siswa')
  for (const entity of nonSiswa) {
    syncingEntity.value = entity
    try {
      const response = await syncAPI.run({ entity })
      if (response.data.success) {
        if (!syncResults.value) syncResults.value = {}
        syncResults.value[entity] = response.data.data.result
      }
    } catch (error) {
      if (!syncResults.value) syncResults.value = {}
      syncResults.value[entity] = { status: 'failed', inserted: 0, updated: 0, failed: 1, errors: [error.message] }
    }
  }

  // Then sync siswa (chunked)
  await runSyncSiswa()

  syncingEntity.value = null
  toastRef.value?.success('Sinkronisasi semua entitas selesai!')
  await fetchStatus()
}

const showErrors = (entityName, errors) => {
  errorModalTitle.value = entityLabel(entityName)
  errorModalErrors.value = errors || []
  showErrorModal.value = true
}

onMounted(async () => {
  await loadAppSettings()
  const result = await loadAll()
  labelJurusan.value = result.labelJurusan || 'Jurusan'
  fetchStatus()
})
</script>

<style scoped>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}

@keyframes modalIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
  }

  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-modalIn {
  animation: modalIn 0.2s ease-out;
}
</style>
