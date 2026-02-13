<template>
  <Layout :active-menu="'Reports'">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative">
          <h1 class="text-3xl font-bold text-white flex items-center gap-3">
            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
              <UserGroupIcon class="w-8 h-8 text-white" />
            </div>
            Laporan per Siswa
          </h1>
          <p class="mt-2 text-emerald-100">Lihat rincian tagihan dan pembayaran per siswa</p>
        </div>
      </div>

      <!-- Filter Card - Pilih Siswa -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-center gap-2 mb-4">
          <div class="p-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg">
            <MagnifyingGlassIcon class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
          </div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Pilih Siswa</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-2 relative" ref="searchContainer">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Cari Siswa</label>
            <div class="relative">
              <input type="text" v-model="searchQuery" @focus="showDropdown = true"
                placeholder="Ketikan Nama atau NIS Siswa..."
                class="w-full pl-4 pr-10 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-emerald-500 dark:text-white transition-all" />
              <div v-if="selectedSiswaId" @click="clearSelection"
                class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path times-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
                </svg>
              </div>
            </div>

            <!-- Custom Dropdown -->
            <div v-if="showDropdown"
              class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-60 overflow-y-auto">
              <div v-if="filteredSiswaList.length === 0" class="p-4 text-center text-gray-500 dark:text-gray-400">
                Tidak ditemukan siswa "{{ searchQuery }}"
              </div>
              <div v-else>
                <div v-for="siswa in filteredSiswaList" :key="siswa.id" @click="selectSiswa(siswa)"
                  class="px-4 py-3 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-0 transition-colors">
                  <div class="font-medium text-gray-800 dark:text-gray-200">{{ siswa.nama }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400 flex justify-between mt-1">
                    <span>NIS: {{ siswa.nis }}</span>
                    <span>{{ siswa.rombel?.nama || siswa.rombel || '-' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="flex items-end">
            <button @click="loadSiswaReport" :disabled="!selectedSiswaId || loading"
              class="w-full px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-700 hover:to-teal-700 disabled:opacity-50 transition-all flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
              <svg v-if="loading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              <MagnifyingGlassIcon v-else class="w-5 h-5" />
              {{ loading ? 'Memuat...' : 'Tampilkan Laporan' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Report Content Wrapper -->
      <div v-if="siswaData" id="report-content" class="space-y-6">
        <!-- Siswa Info Card -->
        <div
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 print-card">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-4">
              <div
                class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-md print-no-shadow">
                {{ siswaData.nama?.charAt(0)?.toUpperCase() || '?' }}
              </div>
              <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white print-text-black">{{ siswaData.nama }}</h2>
                <p class="text-gray-500 dark:text-gray-400 print-text-gray">NIS: {{ siswaData.nis }} | NISN: {{
                  siswaData.nisn || '-' }}</p>
                <p class="text-emerald-600 dark:text-emerald-400 font-medium print-text-black">{{ siswaData.rombel?.nama
                  || siswaData.rombel || '-' }}</p>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 no-print">
              <button @click="printDetail"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors">
                <PrinterIcon class="w-4 h-4" />
                Cetak Lengkap
              </button>
              <button @click="exportExcel"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300 dark:focus:ring-green-800 transition-colors">
                <ArrowDownTrayIcon class="w-4 h-4" />
                Excel
              </button>
            </div>
          </div>

          <!-- Summary Stats -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div
              class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
              <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Total Tagihan</p>
              <p class="text-xl font-bold text-blue-800 dark:text-blue-300 mt-1">{{ formatCurrency(summary.totalTagihan)
              }}</p>
            </div>
            <div
              class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 rounded-xl p-4 border border-green-200 dark:border-green-800">
              <p class="text-sm text-green-600 dark:text-green-400 font-medium">Total Dibayar</p>
              <p class="text-xl font-bold text-green-800 dark:text-green-300 mt-1">{{
                formatCurrency(summary.totalDibayar)
              }}</p>
            </div>
            <div
              class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/30 dark:to-yellow-800/30 rounded-xl p-4 border border-yellow-200 dark:border-yellow-800">
              <p class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">Sisa Tunggakan</p>
              <p class="text-xl font-bold text-yellow-800 dark:text-yellow-300 mt-1">{{
                formatCurrency(summary.sisaTunggakan) }}</p>
            </div>
            <div
              class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30 rounded-xl p-4 border border-purple-200 dark:border-purple-800">
              <p class="text-sm text-purple-600 dark:text-purple-400 font-medium">Persentase Lunas</p>
              <p class="text-xl font-bold text-purple-800 dark:text-purple-300 mt-1">{{ summary.persentaseLunas }}%</p>
            </div>
          </div>
        </div>

        <!-- Tagihan Table -->
        <div
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
              <DocumentTextIcon class="w-5 h-5 text-blue-600" />
              Daftar Tagihan
            </h3>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/50">
                  <th
                    class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    No</th>
                  <th
                    class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Jenis Pembayaran</th>
                  <!-- Periode Removed -->
                  <th
                    class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Nominal</th>
                  <th
                    class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Terbayar</th>
                  <th
                    class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Sisa</th>
                  <th
                    class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-if="tagihanList.length === 0">
                  <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    Tidak ada data tagihan
                  </td>
                </tr>
                <tr v-else v-for="(item, index) in tagihanList" :key="item.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                  <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ index + 1 }}</td>
                  <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ item.masterPembayaran?.nama
                    || '-' }}</td>
                  <!-- Periode Removed -->
                  <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white text-right">{{
                    formatCurrency(item.nominalTagihan) }}</td>
                  <td class="px-6 py-4 text-sm font-semibold text-green-600 dark:text-green-400 text-right">{{
                    formatCurrency(item.totalSudahBayar || item.totalBayar || 0) }}</td>
                  <td class="px-6 py-4 text-sm font-semibold text-yellow-600 dark:text-yellow-400 text-right">{{
                    formatCurrency(item.totalSisa || ((item.nominalTagihan || 0) - (item.totalSudahBayar ||
                      item.totalBayar || 0))) }}</td>
                  <td class="px-6 py-4 text-center">
                    <span :class="[
                      'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold',
                      getStatusClass(item.status)
                    ]">
                      {{ getStatusText(item.status) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pembayaran Table -->
        <div
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
              <BanknotesIcon class="w-5 h-5 text-green-600" />
              Riwayat Pembayaran
            </h3>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/50">
                  <th
                    class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    No</th>
                  <th
                    class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Kode</th>
                  <th
                    class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Tanggal</th>
                  <th
                    class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Jenis</th>
                  <th
                    class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Nominal</th>
                  <th
                    class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Metode</th>
                  <th
                    class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-if="pembayaranList.length === 0">
                  <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    Tidak ada data pembayaran
                  </td>
                </tr>
                <tr v-else v-for="(item, index) in pembayaranList" :key="item.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                  <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ index + 1 }}</td>
                  <td class="px-6 py-4 text-sm font-mono text-gray-600 dark:text-gray-400">{{ item.kodePembayaran ||
                    item.kodeBayar || '-' }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ formatDate(item.tanggalBayar) }}
                  </td>
                  <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ item.masterPembayaran?.nama
                    || item.tagihan?.masterPembayaran?.nama || '-' }}</td>
                  <td class="px-6 py-4 text-sm font-semibold text-green-600 dark:text-green-400 text-right">{{
                    formatCurrency(item.nominalBayar) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ item.metodeBayar || '-' }}</td>
                  <td class="px-6 py-4 text-center">
                    <span :class="[
                      'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold',
                      getPembayaranStatusClass(item.status)
                    ]">
                      {{ getPembayaranStatusText(item.status) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!siswaData && !loading"
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-12 text-center">
        <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full inline-flex mb-4">
          <UserGroupIcon class="w-12 h-12 text-gray-400" />
        </div>
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Pilih Siswa</h3>
        <p class="text-gray-500 dark:text-gray-400">Silakan pilih siswa terlebih dahulu untuk melihat laporan</p>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import Layout from '../../components/layout/Layout.vue';
import {
  UserGroupIcon,
  MagnifyingGlassIcon,
  DocumentTextIcon,
  BanknotesIcon,
  PrinterIcon,
  ArrowDownTrayIcon,
} from '@heroicons/vue/24/outline';
import { siswaAPI, tagihanAPI, pembayaranAPI, reportAPI } from '../../services/api';

// ... (refs and data)

// ... (computed and helpers)

// This part is handled by the second chunk (methods)
// We need multi_replace or sequential replace. 
// I will use multi_replace.


const router = useRouter();
const loading = ref(false);
const selectedSiswaId = ref('');
const searchQuery = ref('');
const showDropdown = ref(false);
const searchContainer = ref(null);

const siswaList = ref([]);
const siswaData = ref(null);
const tagihanList = ref([]);
const pembayaranList = ref([]);

const summary = reactive({
  totalTagihan: 0,
  totalDibayar: 0,
  sisaTunggakan: 0,
  persentaseLunas: 0,
});

// Computed property untuk filter siswa list berdasarkan search query
const filteredSiswaList = computed(() => {
  let list = siswaList.value || [];
  if (!Array.isArray(list)) list = [];

  if (!searchQuery.value) {
    // Jika sudah ada yang dipilih, jangan tampilkan list default jika ingin clean
    // Tapi biasanya dropdown menampilkan semua jika kosong.
    // Namun jika list sangat panjang, mungkin batasi?
    // Untuk select API yang return semua, kita return semua.
    return list;
  }

  const query = searchQuery.value.toLowerCase();
  return list.filter(siswa => {
    const nama = siswa.nama?.toLowerCase() || '';
    const nis = siswa.nis?.toString().toLowerCase() || '';
    const rombel = siswa.rombel?.toLowerCase() || ''; // rombel string dari select API

    return nama.includes(query) || nis.includes(query) || rombel.includes(query);
  });
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount || 0);
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
};

const getStatusClass = (status) => {
  switch (status) {
    case 1:
      return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
    case 2:
      return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
    case 3:
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400';
  }
};

const getStatusText = (status) => {
  switch (status) {
    case 1:
      return 'Lunas';
    case 2:
      return 'Dibatalkan';
    case 3:
      return 'Sebagian';
    default:
      return 'Belum Bayar';
  }
};

const getPembayaranStatusClass = (status) => {
  switch (status) {
    case 1:
      return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
    case 2:
      return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
    default:
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
  }
};

const getPembayaranStatusText = (status) => {
  switch (status) {
    case 1:
      return 'Berhasil';
    case 2:
      return 'Dibatalkan';
    default:
      return 'Pending';
  }
};

const selectSiswa = (siswa) => {
  selectedSiswaId.value = siswa.id;
  searchQuery.value = `${siswa.nis} - ${siswa.nama} (${siswa.rombel || '-'})`;
  showDropdown.value = false;
  loadSiswaReport();
};

const clearSelection = () => {
  selectedSiswaId.value = '';
  searchQuery.value = '';
  siswaData.value = null;
  tagihanList.value = [];
  pembayaranList.value = [];

  // Reset summary
  summary.totalTagihan = 0;
  summary.totalDibayar = 0;
  summary.sisaTunggakan = 0;
  summary.persentaseLunas = 0;
};

// Click outside handler
onMounted(() => {
  loadSiswaList();
  document.addEventListener('click', handleClickOutside);
});

import { onUnmounted } from 'vue';
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

const printDetail = () => {
  if (!selectedSiswaId.value) return;
  const url = router.resolve({
    name: 'reports-siswa-print',
    params: { id: selectedSiswaId.value }
  }).href;
  window.open(url, '_blank');
};

const exportExcel = async () => {
  if (!selectedSiswaId.value) return;

  try {
    const response = await reportAPI.exportSiswa({
      idSiswa: selectedSiswaId.value
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    
    // Attempt to extract filename from Content-Disposition header
    const contentDisposition = response.headers['content-disposition'];
    let fileName = `Laporan_Siswa_${new Date().getTime()}.xlsx`;
    if (contentDisposition) {
      const fileNameMatch = contentDisposition.match(/filename="?(.+)"?/);
      if (fileNameMatch?.[1]) fileName = fileNameMatch[1];
    }
    
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (err) {
    console.error('Export error:', err);
    alert('Gagal mengunduh laporan Excel');
  }
};

const handleClickOutside = (event) => {
  if (searchContainer.value && !searchContainer.value.contains(event.target)) {
    showDropdown.value = false;
  }
};

const loadSiswaList = async () => {
  try {
    const response = await siswaAPI.select({ isActive: 1 });
    const data = response.data?.data || response.data?.items || response.data || [];
    siswaList.value = Array.isArray(data) ? data : [];
  } catch (err) {
    console.error('Error loading siswa list:', err);
    siswaList.value = [];
  }
};

const loadSiswaReport = async () => {
  if (!selectedSiswaId.value) return;

  try {
    loading.value = true;

    // Load siswa detail
    const siswaRes = await siswaAPI.get(selectedSiswaId.value);
    siswaData.value = siswaRes.data?.data || siswaRes.data;

    // Load tagihan for this siswa
    const tagihanRes = await tagihanAPI.list({ idSiswa: selectedSiswaId.value });
    tagihanList.value = tagihanRes.data?.data || tagihanRes.data || [];

    // Load pembayaran for this siswa
    const pembayaranRes = await pembayaranAPI.list({ idSiswa: selectedSiswaId.value });
    pembayaranList.value = pembayaranRes.data?.data || pembayaranRes.data || [];

    // Calculate summary
    const totalTagihan = tagihanList.value.reduce((sum, item) => sum + (item.nominalTagihan || 0), 0);
    const totalDibayar = pembayaranList.value
      .filter(item => item.status === 1)
      .reduce((sum, item) => sum + (item.nominalBayar || 0), 0);
    const sisaTunggakan = totalTagihan - totalDibayar;
    const persentaseLunas = totalTagihan > 0 ? Math.round((totalDibayar / totalTagihan) * 100) : 0;

    summary.totalTagihan = totalTagihan;
    summary.totalDibayar = totalDibayar;
    summary.sisaTunggakan = sisaTunggakan > 0 ? sisaTunggakan : 0;
    summary.persentaseLunas = persentaseLunas > 100 ? 100 : persentaseLunas;

  } catch (err) {
    console.error('Error loading siswa report:', err);
  } finally {
    loading.value = false;
  }
};


</script>

<style>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}

@media print {
  @page {
    size: A4 portrait;
    margin: 1cm;
  }

  body * {
    visibility: hidden;
  }

  #report-content,
  #report-content * {
    visibility: visible;
  }

  #report-content {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    margin: 0;
    padding: 0;
    background: white;
  }

  .no-print {
    display: none !important;
  }

  /* Reset some styles for print */
  .shadow-lg,
  .shadow-xl,
  .shadow-md {
    box-shadow: none !important;
  }

  .bg-white,
  .dark\:bg-gray-800 {
    background: white !important;
    color: black !important;
    border: none !important;
  }

  /* Tables */
  table {
    width: 100% !important;
    border-collapse: collapse !important;
  }

  th,
  td {
    border: 1px solid #ddd !important;
    padding: 8px !important;
    color: black !important;
  }

  th {
    background-color: #f3f4f6 !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
}
</style>
