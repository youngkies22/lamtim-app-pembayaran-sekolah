<template>
  <Layout :active-menu="'Payments'">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                  </path>
                </svg>
              </div>
              Tagihan
            </h1>
            <p class="mt-2 text-emerald-100">Generate dan kelola tagihan siswa dengan mudah</p>
          </div>
          <div class="flex items-center gap-3">
            <button @click="showFilter = !showFilter"
              class="relative inline-flex items-center gap-2.5 px-5 py-3 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 overflow-hidden group cursor-pointer"
              :class="showFilter ? 'bg-white text-emerald-600' : 'bg-white/10 text-white hover:bg-white/20'">
              <span class="relative z-10 flex items-center gap-2.5">
                <svg class="w-5 h-5 transition-transform duration-500 group-hover:scale-110"
                  :class="{ 'rotate-180': showFilter }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                  </path>
                </svg>
                <span class="font-bold tracking-wide uppercase text-xs">{{ showFilter ? 'Hide Filters' : 'Show Filters'
                }}</span>
              </span>
            </button>
            <button @click="openGenerateModal"
              class="group relative inline-flex items-center gap-2 px-6 py-3 bg-white text-emerald-600 font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300">
              <svg class="w-5 h-5 transition-transform group-hover:rotate-90 duration-300" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              <span>Generate</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Filters Section -->
      <Transition name="slide-fade">
        <div v-show="showFilter"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
          <div
            class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/50">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                  </path>
                </svg>
              </div>
              <div>
                <h3 class="font-bold text-gray-900 dark:text-white">Pencarian & Filter</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Gunakan kolom di bawah untuk mempersempit hasil data
                </p>
              </div>
            </div>
            <button @click="resetFilters"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl transition-all cursor-pointer">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                </path>
              </svg>
              Reset Filter
            </button>
          </div>

          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-6">
              <!-- Search Input -->
              <div class="lg:col-span-1 xl:col-span-1">
                <label
                  class="block text-xs font-bold text-gray-700 dark:text-emerald-400 uppercase tracking-wider mb-2">Cari
                  Data</label>
                <div class="relative group">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors" fill="none"
                      stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                  </div>
                  <input type="text" v-model="searchQuery" @input="handleSearch"
                    placeholder="Kode tagihan, nama siswa..."
                    class="block w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border-2 border-gray-100 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all text-sm font-medium" />
                </div>
              </div>

              <!-- Rombel Filter -->
              <div>
                <label
                  class="block text-xs font-bold text-gray-700 dark:text-emerald-400 uppercase tracking-wider mb-2">Rombel</label>
                <select v-model="filters.idRombel" @change="loadData"
                  class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-2 border-gray-100 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all text-sm font-bold appearance-none cursor-pointer">
                  <option value="">Semua Rombel</option>
                  <option v-for="r in rombelList" :key="r.id" :value="r.id">
                    {{ r.kelas ? r.kelas.kode + ' ' : '' }}{{ r.nama }}
                  </option>
                </select>
              </div>

              <!-- Status Filter -->
              <div>
                <label
                  class="block text-xs font-bold text-gray-700 dark:text-emerald-400 uppercase tracking-wider mb-2">Status</label>
                <select v-model="filters.status" @change="loadData"
                  class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-2 border-gray-100 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all text-sm font-bold appearance-none cursor-pointer">
                  <option value="">Semua Status</option>
                  <option value="0">Belum Bayar</option>
                  <option value="3">Sebagian</option>
                  <option value="1">Lunas</option>
                  <option value="2">Terlambat</option>
                </select>
              </div>

              <!-- Master Pembayaran Filter -->
              <div>
                <label
                  class="block text-xs font-bold text-gray-700 dark:text-emerald-400 uppercase tracking-wider mb-2">Pembayaran</label>
                <select v-model="filters.idMasterPembayaran" @change="loadData"
                  class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-2 border-gray-100 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all text-sm font-bold appearance-none cursor-pointer">
                  <option value="">Semua Master</option>
                  <option v-for="mp in masterPembayaranList" :key="mp.id" :value="mp.id">
                    {{ mp.nama }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- DataTable Section -->
      <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table ref="tableRef" class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
              <tr>
                <th
                  class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                  #</th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                  Aksi</th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                  Kode Tagihan</th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                  Siswa</th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                  Rombel</th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                  Pembayaran</th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                  Tanggal</th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                  Nominal</th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                  Terbayar</th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                  Sisa</th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                  Status</th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                  Angkatan</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Generate Tagihan -->
    <Teleport to="body">
      <div v-if="showGenerateModal"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
        @click.self="closeGenerateModal">
        <div class="relative mx-auto w-full max-w-2xl shadow-2xl rounded-2xl bg-white dark:bg-gray-800">
          <!-- Header Modal -->
          <div
            class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-5 rounded-t-2xl flex items-center justify-between">
            <div>
              <h3 class="text-xl font-bold text-white">Generate Tagihan</h3>
              <p class="text-sm text-emerald-100 mt-1">Buat tagihan untuk siswa berdasarkan master pembayaran</p>
            </div>
            <button @click="closeGenerateModal" class="text-white hover:text-emerald-100 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div class="p-6">
            <!-- Error message -->
            <div v-if="formError"
              class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
              <p class="text-sm text-red-600 dark:text-red-400">{{ formError }}</p>
            </div>

            <!-- Success message -->
            <div v-if="generateResult"
              class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg">
              <p class="text-sm text-green-600 dark:text-green-400">
                Berhasil generate {{ generateResult.generated?.length || 0 }} tagihan baru,
                {{ generateResult.skipped?.length || 0 }} sudah ada,
                total {{ generateResult.total || 0 }} siswa
              </p>
            </div>

            <form @submit.prevent="handleGenerate" class="space-y-5">
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                  Master Pembayaran <span class="text-red-500">*</span>
                </label>
                <select v-model="generateForm.idMasterPembayaran" required
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white transition-all">
                  <option value="">Pilih Master Pembayaran</option>
                  <option v-for="mp in masterPembayaranList" :key="mp.id" :value="mp.id">
                    {{ mp.kode }} - {{ mp.nama }} (Rp {{ formatNumber(mp.nominal) }})
                  </option>
                </select>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Pilih master pembayaran yang akan di-generate
                  tagihannya</p>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                  Target Kelas
                  <span class="text-xs text-gray-500 font-normal">(opsional)</span>
                </label>
                <select v-model="generateForm.idKelas"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:text-white transition-all">
                  <option value="">Semua Kelas</option>
                  <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">
                    {{ kelas.nama }} ({{ kelas.kode }})
                  </option>
                </select>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Kosongkan untuk generate ke semua siswa aktif,
                  atau pilih kelas tertentu</p>
              </div>

              <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" @click="closeGenerateModal"
                  class="flex-1 px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">
                  Batal
                </button>
                <button type="submit" :disabled="loading"
                  class="flex-1 px-6 py-3 text-sm font-semibold bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-lg hover:from-emerald-700 hover:to-teal-700 disabled:opacity-50 transition-all flex items-center justify-center shadow-lg hover:shadow-xl">
                  <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  {{ loading ? 'Generating...' : 'Generate Tagihan' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal Detail Tagihan -->
    <Teleport to="body">
      <div v-if="showDetailModal"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
        @click.self="showDetailModal = false">
        <div
          class="relative mx-auto w-full max-w-3xl shadow-2xl rounded-2xl bg-white dark:bg-gray-800 max-h-[90vh] overflow-y-auto">
          <div
            class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-5 rounded-t-2xl flex items-center justify-between sticky top-0 z-10">
            <div>
              <h3 class="text-xl font-bold text-white">Detail Tagihan</h3>
              <p class="text-sm text-emerald-100 mt-1">Informasi lengkap tagihan siswa</p>
            </div>
            <button @click="showDetailModal = false" class="text-white hover:text-emerald-100 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div class="p-6">
            <div v-if="detailData" class="space-y-6">
              <!-- Tagihan Info -->
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Kode Tagihan</p>
                    <p class="font-semibold text-gray-900 dark:text-white text-lg">{{ detailData.kodeTagihan }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Tanggal Tagihan</p>
                    <p class="font-semibold text-gray-900 dark:text-white text-lg">{{
                      formatDate(detailData.tanggalTagihan) }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Status</p>
                    <p class="font-semibold" v-html="getStatusBadge(detailData.status)"></p>
                  </div>
                </div>
              </div>

              <!-- Siswa Info -->
              <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Informasi Siswa</h4>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                  <p class="font-medium text-gray-900 dark:text-white text-lg">{{ detailData.siswa?.nama || '-' }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">NIS: {{ detailData.siswa?.nis || '-' }}</p>
                </div>
              </div>

              <!-- Master Pembayaran Info -->
              <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jenis Pembayaran</h4>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                  <p class="font-medium text-gray-900 dark:text-white">{{ detailData.masterPembayaran?.nama || '-' }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ detailData.masterPembayaran?.kode || '-'
                    }}</p>
                </div>
              </div>

              <!-- Nominal Info -->
              <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Informasi Nominal</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Nominal Tagihan</p>
                    <p class="font-bold text-xl text-gray-900 dark:text-white">Rp {{
                      formatNumber(detailData.nominalTagihan) }}</p>
                  </div>
                  <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Terbayar</p>
                    <p class="font-bold text-xl text-emerald-600 dark:text-emerald-400">Rp {{
                      formatNumber(detailData.totalSudahBayar || 0) }}</p>
                  </div>
                  <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Sisa</p>
                    <p class="font-bold text-xl text-orange-600 dark:text-orange-400">Rp {{
                      formatNumber(detailData.totalSisa || 0) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Confirm Delete Modal -->
    <ConfirmModal :show="showDeleteModal" type="danger" title="Hapus Tagihan"
      :message="`Apakah Anda yakin ingin menghapus tagihan '${deletingItem?.kodeTagihan || ''}'?`"
      confirm-text="Ya, Hapus" :loading="deleteLoading" @confirm="confirmDelete" @cancel="showDeleteModal = false" />

    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, nextTick } from 'vue';
import Layout from '../../components/layout/Layout.vue';
import ConfirmModal from '../../components/ConfirmModal.vue';
import Toast from '../../components/Toast.vue';
import { tagihanAPI, masterPembayaranAPI, masterDataAPI } from '../../services/api';

// Refs
const tableRef = ref(null);
const dataTable = ref(null);
const toastRef = ref(null);
const showGenerateModal = ref(false);
const showDetailModal = ref(false);
const showDeleteModal = ref(false);
const loading = ref(false);
const deleteLoading = ref(false);
const formError = ref('');
const detailData = ref(null);
const generateResult = ref(null);
const searchQuery = ref('');
const showFilter = ref(false);
const deletingItem = ref(null);

// Master data
const masterPembayaranList = ref([]);
const kelasList = ref([]);
const rombelList = ref([]);

// Filters
const filters = reactive({
  status: '',
  idMasterPembayaran: '',
  idRombel: '',
});

const resetFilters = () => {
  filters.status = '';
  filters.idMasterPembayaran = '';
  filters.idRombel = '';
  searchQuery.value = '';
  loadData();
};

// Generate Form
const initialGenerateForm = {
  idMasterPembayaran: '',
  idKelas: '',
};

const generateForm = reactive({ ...initialGenerateForm });

// Helper functions
const formatNumber = (num) => {
  return new Intl.NumberFormat('id-ID').format(num || 0);
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const getStatusBadge = (status) => {
  const badges = {
    0: '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Belum Bayar</span>',
    1: '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Lunas</span>',
    2: '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Terlambat</span>',
    3: '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Sebagian</span>',
  };
  return badges[status] || badges[0];
};

// Load master data
const loadMasterData = async () => {
  try {
    const [mpRes, kelasRes, rombelRes] = await Promise.all([
      masterPembayaranAPI.list({ isActive: 1 }),
      masterDataAPI.kelas.list(),
      masterDataAPI.rombel.select(),
    ]);
    masterPembayaranList.value = mpRes.data.data || [];
    kelasList.value = kelasRes.data.data || [];
    rombelList.value = rombelRes.data.data || [];
  } catch (err) {
    toastRef.value?.error('Gagal memuat data master');
  }
};

// Search handler with debounce
let searchTimeout = null;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadData();
  }, 500);
};

// Dropdown handler helper
const setupDropdownHandler = () => {
  document.addEventListener('click', (e) => {
    const dropdown = e.target.closest('[data-dropdown]');
    const allDropdownMenus = document.querySelectorAll('[data-dropdown-menu]');

    // Auto-hide when selecting an option inside the menu
    if (e.target.closest('[data-dropdown-menu]')) {
      allDropdownMenus.forEach(menu => menu.classList.add('hidden', 'scale-95', 'opacity-0'));
      return;
    }

    if (!dropdown) {
      allDropdownMenus.forEach(menu => menu.classList.add('hidden', 'scale-95', 'opacity-0'));
      return;
    }

    const currentMenu = dropdown.querySelector('[data-dropdown-menu]');

    if (e.target.closest('button') && !e.target.closest('[data-dropdown-menu]')) {
      const isHidden = currentMenu.classList.contains('hidden');

      // Close all first
      allDropdownMenus.forEach(menu => menu.classList.add('hidden', 'scale-95', 'opacity-0'));

      if (isHidden) {
        currentMenu.classList.remove('hidden');
        // Small timeout for animation trigger
        setTimeout(() => {
          currentMenu.classList.remove('scale-95', 'opacity-0');
          currentMenu.classList.add('scale-100', 'opacity-100');
        }, 10);
      }
    }
  });
};

// Initialize DataTable
const initDataTable = () => {
  if (!tableRef.value || !window.$ || !window.$.fn.DataTable) {
    return;
  }

  setupDropdownHandler();

  dataTable.value = window.$(tableRef.value).DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '/api/tagihan/datatable',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      },
      data: function (d) {
        d.status = filters.status;
        d.idMasterPembayaran = filters.idMasterPembayaran;
        d.idRombel = filters.idRombel;
        d.search = { value: searchQuery.value };
      },
      error: function (xhr) {
        if (xhr.status === 401) {
          window.location.href = '/login';
        }
      }
    },
    columns: [
      { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
      {
        data: 'action',
        name: 'action',
        orderable: false,
        searchable: false,
        className: 'text-center'
      },
      { data: 'kodeTagihan', name: 'kodeTagihan' },
      { data: 'siswa_nama', name: 'siswa.nama' },
      { data: 'rombel_nama', name: 'rombel.nama' },
      { data: 'master_nama', name: 'masterPembayaran.nama' },
      { data: 'tanggal_formatted', name: 'tanggalTagihan' },
      { data: 'nominal_formatted', name: 'nominalTagihan', orderable: false },
      { data: 'terbayar_formatted', name: 'totalSudahBayar', orderable: false },
      { data: 'sisa_formatted', name: 'totalSisa', orderable: false },
      { data: 'status_badge', name: 'status', orderable: false },
      { data: 'siswa_angkatan', name: 'siswa.tahunAngkatan' },
    ],
    order: [[4, 'desc']],
    pageLength: 10,
    language: {
      processing: 'Memproses...',
      search: 'Cari:',
      lengthMenu: 'Tampilkan _MENU_ data',
      info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
      infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
      infoFiltered: '(disaring dari _MAX_ total data)',
      loadingRecords: 'Memuat data...',
      zeroRecords: 'Tidak ada data yang ditemukan',
      emptyTable: 'Tidak ada data dalam tabel',
      paginate: {
        first: 'Pertama',
        previous: 'Sebelumnya',
        next: 'Selanjutnya',
        last: 'Terakhir'
      },
      aria: {
        sortAscending: ': aktifkan untuk mengurutkan kolom naik',
        sortDescending: ': aktifkan untuk mengurutkan kolom turun'
      }
    },
    drawCallback: function () {
      const table = this.api().table().node();
      window.$(table).off('click', '[data-action]').on('click', '[data-action]', function () {
        const action = this.dataset.action;
        const id = this.dataset.id;

        if (action === 'detail') {
          handleDetail(id);
        } else if (action === 'delete') {
          handleDelete(id, this.dataset.kode);
        }
      });
    }
  });
};

const loadData = () => {
  if (dataTable.value) {
    dataTable.value.ajax.reload();
  }
};

// Modal handlers
const openGenerateModal = () => {
  Object.assign(generateForm, initialGenerateForm);
  formError.value = '';
  generateResult.value = null;
  showGenerateModal.value = true;
};

const closeGenerateModal = () => {
  showGenerateModal.value = false;
  Object.assign(generateForm, initialGenerateForm);
  formError.value = '';
  generateResult.value = null;
  loadData();
};

const handleGenerate = async () => {
  try {
    loading.value = true;
    formError.value = '';
    generateResult.value = null;

    const payload = {
      idMasterPembayaran: generateForm.idMasterPembayaran,
    };

    if (generateForm.idKelas) {
      payload.idKelas = generateForm.idKelas;
    }

    const response = await tagihanAPI.generateBatch(payload);
    generateResult.value = response.data.data;

    const generated = generateResult.value.generated?.length || 0;
    const skipped = generateResult.value.skipped?.length || 0;
    const total = generateResult.value.total || 0;

    toastRef.value?.success(`Tagihan berhasil di-generate: ${generated} tagihan baru, ${skipped} sudah ada, total ${total} siswa`);

    if (dataTable.value) {
      dataTable.value.ajax.reload();
    }

    setTimeout(() => {
      closeGenerateModal();
    }, 2000);
  } catch (err) {
    formError.value = err.response?.data?.message || 'Terjadi kesalahan saat generate tagihan';
  } finally {
    loading.value = false;
  }
};

const handleDetail = async (id) => {
  try {
    const response = await tagihanAPI.get(id);
    detailData.value = response.data.data;
    showDetailModal.value = true;
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal memuat detail tagihan');
  }
};

const handleDelete = (id, kodeTagihan) => {
  deletingItem.value = { id, kodeTagihan };
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!deletingItem.value) return;

  try {
    deleteLoading.value = true;
    await tagihanAPI.delete(deletingItem.value.id);
    showDeleteModal.value = false;
    deletingItem.value = null;
    toastRef.value?.success('Tagihan berhasil dihapus');
    loadData();
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal menghapus tagihan');
  } finally {
    deleteLoading.value = false;
  }
};

onUnmounted(() => {
  if (dataTable.value) {
    dataTable.value.destroy();
    dataTable.value = null;
  }
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
});

onMounted(async () => {
  await nextTick();
  initDataTable();
  loadMasterData();
});
</script>

<style scoped>
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}

.bg-grid-pattern {
  background-image: radial-gradient(circle, #fff 1px, transparent 1px);
  background-size: 20px 20px;
}
</style>
