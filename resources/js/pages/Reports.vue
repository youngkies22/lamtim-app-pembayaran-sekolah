<template>
  <Layout :active-menu="'Reports'">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative">
          <h1 class="text-3xl font-bold text-white flex items-center gap-3">
            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
              <ChartBarIcon class="w-8 h-8 text-white" />
            </div>
            Laporan & Analitik
          </h1>
          <p class="mt-2 text-indigo-100">Analisis data pembayaran dan biaya sekolah</p>
        </div>
      </div>

      <!-- Filter Card -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-center gap-2 mb-4">
          <div class="p-2 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg">
            <FunnelIcon class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
          </div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Filter Laporan</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Periode Awal</label>
            <input v-model="filters.startDate" type="date"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Periode Akhir</label>
            <input v-model="filters.endDate" type="date"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Jenis Pembayaran</label>
            <select v-model="filters.jenisPembayaran"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white">
              <option value="">Semua Jenis</option>
              <option v-for="jenis in jenisPembayaranList" :key="jenis.id" :value="jenis.kode">
                {{ jenis.kode }} - {{ jenis.nama }}
              </option>
            </select>
          </div>
          <div class="flex items-end">
            <button @click="loadReport" :disabled="loading"
              class="w-full px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition-all flex items-center justify-center gap-2">
              <svg v-if="loading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              <MagnifyingGlassIcon v-else class="w-5 h-5" />
              {{ loading ? 'Memuat...' : 'Tampilkan' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="stat in summaryStats" :key="stat.name"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 hover:shadow-xl transition-all">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ stat.name }}</p>
              <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                {{ stat.value }}
              </p>
              <p v-if="stat.change" :class="[
                'mt-1 text-xs font-medium',
                stat.change > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
              ]">
                {{ stat.change > 0 ? '+' : '' }}{{ stat.change }}% dari periode sebelumnya
              </p>
            </div>
            <div :class="['p-3 rounded-xl', stat.color]">
              <component :is="stat.icon" class="w-6 h-6" />
            </div>
          </div>
        </div>
      </div>

      <!-- Report Table -->
      <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div
          class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Detail Laporan</h3>
          <button @click="exportReport" :disabled="loading"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50 rounded-lg transition-colors cursor-pointer disabled:opacity-50">
            <ArrowDownTrayIcon class="w-4 h-4" />
            Export Excel
          </button>
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
                  Tanggal</th>
                <th
                  class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Nama Siswa</th>
                <th
                  class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Jenis</th>
                <th
                  class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Nominal</th>
                <th
                  class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-if="loading">
                <td colspan="6" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center gap-3">
                    <svg class="animate-spin h-10 w-10 text-indigo-600" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                      </path>
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Memuat data...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="reportData.length === 0">
                <td colspan="6" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center gap-3">
                    <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full">
                      <ChartBarIcon class="w-10 h-10 text-gray-400" />
                    </div>
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Tidak ada data untuk periode yang
                      dipilih</span>
                  </div>
                </td>
              </tr>
              <tr v-else v-for="(item, index) in reportData" :key="item.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ index + 1 }}</td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ formatDate(item.tanggalBayar ||
                  item.tanggalTagihan || item.tanggal) }}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ item.siswa?.nama || item.nama
                  || '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ item.masterPembayaran?.nama ||
                  item.jenis || '-' }}</td>
                <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white text-right">{{
                  formatCurrency(item.nominalBayar || item.nominalTagihan || item.nominal || 0) }}</td>
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
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import Layout from '../components/layout/Layout.vue';
import {
  ChartBarIcon,
  FunnelIcon,
  MagnifyingGlassIcon,
  ArrowDownTrayIcon,
  CurrencyDollarIcon,
  DocumentTextIcon,
  UserGroupIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';
import { pembayaranAPI, tagihanAPI, masterDataAPI } from '../services/api';

const loading = ref(false);
const reportData = ref([]);
const jenisPembayaranList = ref([]);

const getLocalToday = () => {
    const today = new Date();
    const offset = today.getTimezoneOffset();
    return new Date(today.getTime() - (offset * 60 * 1000)).toISOString().split('T')[0];
};

const filters = reactive({
  startDate: getLocalToday(), // Hari ini (local)
  endDate: getLocalToday(),   // Hari ini (local)
  jenisPembayaran: '',
});

const summaryStats = ref([
  {
    name: 'Total Pembayaran',
    value: '-',
    change: null,
    icon: CurrencyDollarIcon,
    color: 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
  },
  {
    name: 'Total Tagihan',
    value: '-',
    change: null,
    icon: DocumentTextIcon,
    color: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
  },
  {
    name: 'Jumlah Transaksi',
    value: '-',
    change: null,
    icon: UserGroupIcon,
    color: 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
  },
  {
    name: 'Belum Lunas',
    value: '-',
    change: null,
    icon: ExclamationTriangleIcon,
    color: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400',
  },
]);

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
    default:
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
  }
};

const getStatusText = (status) => {
  switch (status) {
    case 1:
      return 'Lunas';
    case 2:
      return 'Dibatalkan';
    default:
      return 'Belum Lunas';
  }
};

const loadReport = async () => {
  try {
    loading.value = true;
    const params = {
      start_date: filters.startDate,
      end_date: filters.endDate,
    };

    // Add filter by jenis pembayaran if selected
    if (filters.jenisPembayaran) {
      params.jenisPembayaran = filters.jenisPembayaran;
    }

    // Load pembayaran data for table display
    const response = await pembayaranAPI.list(params);
    reportData.value = response.data.data || response.data || [];

    // Load both pembayaran and tagihan data for summary
    const [pembayaranRes, tagihanRes] = await Promise.all([
      pembayaranAPI.list(params),
      tagihanAPI.list(params),
    ]);

    const pembayaranData = pembayaranRes.data.data || pembayaranRes.data || [];
    const tagihanData = tagihanRes.data.data || tagihanRes.data || [];

    // Calculate summary stats
    // Total Pembayaran: sum of all nominalBayar from pembayaran with status 1 (sukses)
    const totalPembayaran = pembayaranData
      .filter(item => item.status === 1)
      .reduce((sum, item) => sum + (item.nominalBayar || 0), 0);

    // Total Tagihan: sum of all nominalTagihan from tagihan
    const totalTagihan = tagihanData
      .reduce((sum, item) => sum + (item.nominalTagihan || 0), 0);

    // Jumlah Transaksi: count of completed payments
    const jumlahTransaksi = pembayaranData.filter(item => item.status === 1).length;

    // Belum Lunas: count of tagihan with status 0 (belum bayar) or 3 (sebagian)
    const belumLunas = tagihanData.filter(item => item.status === 0 || item.status === 3).length;

    summaryStats.value[0].value = formatCurrency(totalPembayaran);
    summaryStats.value[1].value = formatCurrency(totalTagihan);
    summaryStats.value[2].value = jumlahTransaksi.toLocaleString('id-ID');
    summaryStats.value[3].value = belumLunas.toLocaleString('id-ID');
  } catch (err) {
    console.error('Error loading report:', err);
    reportData.value = [];
  } finally {
    loading.value = false;
  }
};

const exportReport = async () => {
  try {
    loading.value = true;
    // Map filters to backend expectations (startDate/endDate camelCase)
    // Note: The PembayaranController expects startDate, endDate, jenisPembayaran
    const params = {
      startDate: filters.startDate,
      endDate: filters.endDate,
      jenisPembayaran: filters.jenisPembayaran,
    };

    const response = await pembayaranAPI.exportExcel(params);
    
    // Create download link from blob
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    
    // Generate filename with date
    const dateStr = new Date().toISOString().split('T')[0];
    link.setAttribute('download', `Laporan_Pembayaran_${dateStr}.xlsx`);
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (err) {
    console.error('Error exporting report:', err);
    alert('Gagal mengexport laporan');
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  // Load jenis pembayaran for filter dropdown
  try {
    const response = await masterDataAPI.jenisPembayaran.list();
    jenisPembayaranList.value = response.data.data || response.data || [];
  } catch (err) {
    console.error('Error loading jenis pembayaran:', err);
  }
  loadReport();
});
</script>

<style>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
