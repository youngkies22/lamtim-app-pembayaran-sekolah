<template>
  <Layout active-menu="Reset Data">
    <div class="space-y-6">
      <!-- Header -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-600 via-red-600 to-orange-600 p-6 md:p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative flex flex-col md:flex-row items-center justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                <ExclamationTriangleIcon class="w-6 h-6 md:w-8 md:h-8 text-white" />
              </div>
              Reset Data
              <button @click="showInfoModal = true" class="p-1.5 rounded-lg hover:bg-white/20 transition-colors group"
                title="Informasi Reset Data">
                <InformationCircleIcon class="w-5 h-5 text-white group-hover:scale-110 transition-transform" />
              </button>
            </h1>
            <p class="mt-2 text-rose-100 text-sm md:text-base">
              Kosongkan data transaksi, siswa, &amp; master secara permanen agar sistem bisa dipakai dari awal (mis. untuk pelanggan baru). Pilih kategori dengan hati-hati.
            </p>
          </div>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-rose-600"></div>
      </div>

      <template v-else>
        <!-- Data Transaksi -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Data Transaksi</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label v-for="cat in transactionCategories" :key="cat.key"
              class="flex items-center justify-between gap-3 p-4 rounded-xl border cursor-pointer transition-colors"
              :class="selectedKeys.includes(cat.key)
                ? 'border-rose-400 bg-rose-50 dark:bg-rose-900/20 dark:border-rose-700'
                : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
              <span class="flex items-center gap-3">
                <input type="checkbox" :checked="selectedKeys.includes(cat.key)" @change="toggleCategory(cat.key)"
                  class="rounded border-gray-300 text-rose-600 focus:ring-rose-500" />
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ cat.label }}</span>
              </span>
              <span class="text-xs font-semibold px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                {{ cat.count }} baris
              </span>
            </label>
          </div>
          <p v-if="showCascadeNote" class="mt-4 text-sm text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3 flex items-start gap-2">
            <InformationCircleIcon class="w-5 h-5 flex-shrink-0 mt-0.5" />
            Tagihan dan Master Pembayaran memiliki relasi database ke Invoice &amp; Pembayaran di bawahnya — jika salah satu dicentang, Invoice &amp; Pembayaran terkait akan ikut terhapus otomatis walau tidak dicentang.
          </p>
        </div>

        <!-- Data Siswa -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Data Siswa</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label v-for="cat in studentCategories" :key="cat.key"
              class="flex items-center justify-between gap-3 p-4 rounded-xl border cursor-pointer transition-colors"
              :class="selectedKeys.includes(cat.key)
                ? 'border-rose-400 bg-rose-50 dark:bg-rose-900/20 dark:border-rose-700'
                : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
              <span class="flex items-center gap-3">
                <input type="checkbox" :checked="selectedKeys.includes(cat.key)" @change="toggleCategory(cat.key)"
                  class="rounded border-gray-300 text-rose-600 focus:ring-rose-500" />
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ cat.label }}</span>
              </span>
              <span class="text-xs font-semibold px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                {{ cat.count }} baris
              </span>
            </label>
          </div>
          <p v-if="selectedKeys.includes('siswa')" class="mt-4 text-sm text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 flex items-start gap-2">
            <ExclamationTriangleIcon class="w-5 h-5 flex-shrink-0 mt-0.5" />
            <strong>PERINGATAN:</strong>&nbsp;Menghapus Siswa akan menghapus SEMUA data siswa (aktif &amp; alumni) beserta akun login, profil, riwayat rombel, Tagihan, Invoice, dan Pembayaran mereka secara otomatis. Gunakan hanya untuk reset total sebelum aplikasi dipakai pelanggan/pengguna baru.
          </p>
        </div>

        <!-- Data Master & Organisasi -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Data Master &amp; Organisasi</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label v-for="cat in masterCategories" :key="cat.key"
              class="flex items-center justify-between gap-3 p-4 rounded-xl border cursor-pointer transition-colors"
              :class="selectedKeys.includes(cat.key)
                ? 'border-rose-400 bg-rose-50 dark:bg-rose-900/20 dark:border-rose-700'
                : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
              <span class="flex items-center gap-3">
                <input type="checkbox" :checked="selectedKeys.includes(cat.key)" @change="toggleCategory(cat.key)"
                  class="rounded border-gray-300 text-rose-600 focus:ring-rose-500" />
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ cat.label }}</span>
              </span>
              <span class="text-xs font-semibold px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                {{ cat.count }} baris
              </span>
            </label>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
          <div class="text-sm text-gray-600 dark:text-gray-400">
            <span v-if="selectedKeys.length > 0" class="font-semibold text-gray-900 dark:text-white">{{ selectedKeys.length }} kategori dipilih</span>
            <span v-else>Belum ada kategori yang dipilih</span>
          </div>
          <div class="flex items-center gap-3">
            <button @click="toggleSelectAll" type="button"
              class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
              {{ selectedKeys.length === allCategories.length ? 'Batal Semua' : 'Pilih Semua' }}
            </button>
            <button @click="openConfirm" type="button" :disabled="selectedKeys.length === 0"
              class="px-5 py-2.5 bg-rose-600 text-white font-semibold rounded-xl shadow-lg hover:bg-rose-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
              <TrashIcon class="w-5 h-5" />
              Reset Data Terpilih
            </button>
          </div>
        </div>
      </template>
    </div>

    <!-- Confirm Modal -->
    <ConfirmModal :show="showConfirmModal" title="Konfirmasi Reset Data" :message="confirmMessage" type="danger"
      confirm-text="Reset Sekarang" :loading="resetLoading" @confirm="handleReset" @cancel="showConfirmModal = false"
      @close="showConfirmModal = false" />

    <!-- Info Modal -->
    <InfoModal :show="showInfoModal" title="Informasi Reset Data" subtitle="Pelajari fungsi halaman reset data"
      :sections="infoSections" @close="showInfoModal = false" />

    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted, markRaw } from 'vue'
import { useRouter } from 'vue-router'
import Layout from '../../components/layout/Layout.vue'
import ConfirmModal from '../../components/ConfirmModal.vue'
import InfoModal from '../../components/InfoModal.vue'
import Toast from '../../components/Toast.vue'
import { resetDataAPI } from '../../services/api'
import { useRoleAccess } from '../../composables/useRoleAccess'
import {
  ExclamationTriangleIcon,
  InformationCircleIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'

const TRANSACTION_KEYS = ['tagihan', 'invoice', 'pembayaran', 'master_pembayaran']
const STUDENT_KEYS = ['siswa', 'siswa_rombel']
const CASCADE_TRIGGER_KEYS = ['tagihan', 'master_pembayaran', 'siswa']

const router = useRouter()
const { isAdminUser } = useRoleAccess()

const loading = ref(true)
const resetLoading = ref(false)
const showInfoModal = ref(false)
const showConfirmModal = ref(false)
const toastRef = ref(null)
const allCategories = ref([])
const selectedKeys = ref([])

const transactionCategories = computed(() => allCategories.value.filter(c => TRANSACTION_KEYS.includes(c.key)))
const studentCategories = computed(() => allCategories.value.filter(c => STUDENT_KEYS.includes(c.key)))
const masterCategories = computed(() => allCategories.value.filter(c => !TRANSACTION_KEYS.includes(c.key) && !STUDENT_KEYS.includes(c.key)))

const showCascadeNote = computed(() => selectedKeys.value.some(key => CASCADE_TRIGGER_KEYS.includes(key)))

const confirmMessage = computed(() => {
  const labels = allCategories.value
    .filter(c => selectedKeys.value.includes(c.key))
    .map(c => c.label)
  const totalRows = allCategories.value
    .filter(c => selectedKeys.value.includes(c.key))
    .reduce((sum, c) => sum + c.count, 0)
  const includesSiswa = selectedKeys.value.includes('siswa')
  const siswaWarning = includesSiswa
    ? ' Termasuk SEMUA data Siswa (aktif & alumni) beserta akun login, profil, riwayat rombel, dan transaksi mereka.'
    : ' Data Siswa tidak ikut terhapus.'
  return `Anda akan menghapus permanen kategori: <strong>${labels.join(', ')}</strong> (sekitar ${totalRows} baris). Tindakan ini <strong>tidak dapat dibatalkan</strong>.${siswaWarning} Lanjutkan?`
})

const infoSections = [
  {
    title: 'Fungsi Halaman Ini',
    description: 'Halaman ini menghapus permanen data transaksi, data siswa, dan data master yang dipilih, agar aplikasi bisa dipakai dari awal (misalnya untuk pelanggan/pengguna baru, tahun ajaran baru, atau membersihkan data uji coba). Hanya kategori yang dicentang yang akan diproses.',
    icon: markRaw(ExclamationTriangleIcon),
    items: [
      { name: 'Hard Delete', description: 'Data dihapus permanen, tidak masuk ke halaman Trash dan tidak bisa dipulihkan' },
      { name: 'Relasi Cascade', description: 'Menghapus Siswa, Tagihan, atau Master Pembayaran otomatis ikut menghapus data turunannya (profil, riwayat rombel, Invoice, Pembayaran)' },
      { name: 'Backup Dulu', description: 'Disarankan membuat backup lewat halaman Backups sebelum melakukan reset di data produksi' },
    ],
  },
]

const fetchCategories = async () => {
  try {
    loading.value = true
    const response = await resetDataAPI.getCategories()
    if (response.data.success) {
      allCategories.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching reset categories:', error)
    toastRef.value?.error('Gagal memuat kategori data')
  } finally {
    loading.value = false
  }
}

const toggleCategory = (key) => {
  const idx = selectedKeys.value.indexOf(key)
  if (idx === -1) selectedKeys.value.push(key)
  else selectedKeys.value.splice(idx, 1)
}

const toggleSelectAll = () => {
  if (selectedKeys.value.length === allCategories.value.length) {
    selectedKeys.value = []
  } else {
    selectedKeys.value = allCategories.value.map(c => c.key)
  }
}

const openConfirm = () => {
  if (selectedKeys.value.length === 0) return
  showConfirmModal.value = true
}

const handleReset = async () => {
  try {
    resetLoading.value = true
    const response = await resetDataAPI.reset(selectedKeys.value)
    if (response.data.success) {
      toastRef.value?.success(response.data.message || 'Data berhasil direset')
      selectedKeys.value = []
      showConfirmModal.value = false
      await fetchCategories()
    }
  } catch (error) {
    console.error('Error resetting data:', error)
    toastRef.value?.error(error.response?.data?.message || 'Gagal mereset data')
  } finally {
    resetLoading.value = false
  }
}

onMounted(() => {
  if (!isAdminUser.value) {
    router.replace('/dashboard')
    return
  }
  fetchCategories()
})
</script>

<style scoped>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
