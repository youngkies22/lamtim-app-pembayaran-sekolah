<template>
  <Layout :active-menu="'Reports'">
    <div class="space-y-6">
      <!-- Header & Filters -->
      <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <ChartBarIcon class="w-8 h-8 text-blue-600" />
            Laporan Alumni
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Silakan pilih sekolah &amp; angkatan untuk menampilkan analisa data.
          </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3">
          <!-- School Filter -->
          <div class="relative w-full sm:w-64">
            <select v-model="filterSekolah" @change="handleSchoolChange"
              class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 appearance-none font-medium">
              <option :value="null" disabled>-- Pilih Sekolah --</option>
              <option value="all">Semua Sekolah</option>
              <option v-for="school in schools" :key="school.id" :value="school.id">
                {{ school.nama }}
              </option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
              <ChevronDownIcon class="w-4 h-4" />
            </div>
          </div>

          <!-- Year Filter -->
          <div class="relative w-full sm:w-56">
            <select v-model="filterYear" @change="handleFilterChange"
              class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 appearance-none font-medium">
              <option :value="null" disabled>-- Pilih Angkatan --</option>
              <option value="all">Semua Angkatan</option>
              <option v-for="year in availableYears" :key="year" :value="year">
                Angkatan {{ year }}
              </option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
              <ChevronDownIcon class="w-4 h-4" />
            </div>
          </div>

          <button v-if="hasData" @click="exportData"
            class="flex items-center gap-2 px-4 py-2.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-xl transition-all font-semibold text-sm whitespace-nowrap"
            :disabled="loading">
            <DocumentArrowDownIcon class="w-5 h-5" />
            Export Excel
          </button>

          <!-- Refresh Button -->
          <button v-if="hasData" @click="refreshData"
            class="flex items-center gap-2 px-4 py-2.5 bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 rounded-xl transition-all font-semibold text-sm whitespace-nowrap"
            :disabled="loading">
            <ArrowPathIcon :class="['w-5 h-5', loading ? 'animate-spin' : '']" />
            {{ loading ? 'Memproses...' : 'Refresh Data' }}
          </button>
        </div>
      </div>

      <!-- Initial Placeholder State -->
      <div v-if="!hasData && !loading"
        class="bg-white dark:bg-gray-800 p-12 rounded-2xl shadow-sm border border-dashed border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center text-center">
        <div class="w-20 h-20 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center mb-4">
          <AdjustmentsHorizontalIcon class="w-10 h-10 text-blue-500" />
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Siap Menganalisa Data</h3>
        <p class="text-gray-500 dark:text-gray-400 mt-2 max-w-sm">
          Pilih <b>Sekolah</b> dan <b>Tahun Angkatan</b> pada opsi di atas untuk memulai kalkulasi laporan keuangan
          alumni.
        </p>
      </div>

      <!-- Content (Only after selection) -->
      <template v-if="hasData">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div
            class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform text-blue-600">
              <BanknotesIcon class="w-16 h-16" />
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tagihan Alumni</p>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white m-0 mt-1">
              {{ formatCurrency(summary.totalTagihan) }}
            </h3>
            <p class="text-xs text-gray-400 mt-2">
              {{ filterYear === 'all' ? 'Akumulasi seluruh angkatan' : `Angkatan ${filterYear}` }}
            </p>
          </div>

          <div
            class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
            <div
              class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform text-green-600">
              <CheckBadgeIcon class="w-16 h-16" />
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Terbayar</p>
            <h3 class="text-2xl font-bold text-green-600 m-0 mt-1">
              {{ formatCurrency(summary.totalTerbayar) }}
            </h3>
            <p class="text-xs text-emerald-500 mt-2 font-medium">
              {{ calculatePercentage(summary.totalTerbayar, summary.totalTagihan) }}% Tertagih
            </p>
          </div>

          <div
            class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform text-red-600">
              <ExclamationTriangleIcon class="w-16 h-16" />
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Sisa Tunggakan</p>
            <h3 class="text-2xl font-bold text-red-600 m-0 mt-1">
              {{ formatCurrency(summary.totalTunggakan) }}
            </h3>
            <p class="text-xs text-red-500 mt-2 font-medium">Piutang belum tertagih</p>
          </div>
        </div>

        <!-- Year Breakdown Table -->
        <div
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 dark:text-white">{{ tableTitle }}</h3>
            <span
              class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs rounded-full font-medium">
              {{ byYear.length }} Baris Data
            </span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/50">
                  <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Angkatan</th>
                  <th
                    class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">
                    Jml Siswa</th>
                  <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Total
                    Tagihan</th>
                  <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Terbayar</th>
                  <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Tunggakan</th>
                  <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Rasio
                    (%)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-if="loading" v-for="i in 3" :key="'loader-' + i" class="animate-pulse">
                  <td class="px-6 py-4">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-16"></div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-10 mx-auto"></div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
                  </td>
                  <td class="px-6 py-4 min-w-[150px]">
                    <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full w-full"></div>
                  </td>
                </tr>

                <tr v-else-if="byYear.length === 0">
                  <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                    Tidak ada data alumni yang ditemukan{{ filterYear && filterYear !== 'all' ? ' untuk Angkatan ' +
                      filterYear : '' }}.
                  </td>
                </tr>

                <tr v-else v-for="year in byYear" :key="year.tahunAngkatan"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                  <td class="px-6 py-4">
                    <span class="font-bold text-gray-900 dark:text-white">{{ year.tahunAngkatan || 'N/A' }}</span>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <span
                      class="px-2 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded text-xs font-bold">
                      {{ year.total_siswa }} Siswa
                    </span>
                  </td>
                  <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ formatCurrency(year.total_tagihan) }}</td>
                  <td class="px-6 py-4 text-emerald-600 dark:text-emerald-400 font-medium">{{
                    formatCurrency(year.total_terbayar) }}</td>
                  <td class="px-6 py-4 text-red-600 dark:text-red-400 font-bold">{{ formatCurrency(year.total_tunggakan)
                  }}</td>
                  <td class="px-6 py-4 min-w-[150px]">
                    <div class="flex items-center gap-3">
                      <div class="flex-1 h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 transition-all duration-1000"
                          :style="{ width: `${calculatePercentage(year.total_terbayar, year.total_tagihan)}%` }"></div>
                      </div>
                      <span class="text-xs font-bold text-gray-600 dark:text-gray-400">
                        {{ calculatePercentage(year.total_terbayar, year.total_tagihan) }}%
                      </span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- Loading State (Large) -->
      <div v-if="loading && !hasData" class="flex flex-col items-center justify-center p-20">
        <ArrowPathIcon class="w-12 h-12 text-blue-500 animate-spin mb-4" />
        <p class="text-gray-500 font-medium">Memuat pilihan angkatan...</p>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import Layout from '../../components/layout/Layout.vue';
import { reportAPI } from '../../services/api';
import {
  ChartBarIcon,
  ArrowPathIcon,
  BanknotesIcon,
  CheckBadgeIcon,
  ExclamationTriangleIcon,
  ChevronDownIcon,
  AdjustmentsHorizontalIcon,
  DocumentArrowDownIcon
} from '@heroicons/vue/24/outline';

const loading = ref(true);
const hasData = ref(false);
const filterSekolah = ref(null);
const filterYear = ref(null);
const schools = ref([]);
const availableYears = ref([]);
const summary = ref({
  totalTagihan: 0,
  totalTerbayar: 0,
  totalTunggakan: 0,
  totalSiswa: 0
});
const byYear = ref([]);

const tableTitle = computed(() => {
  return filterYear.value && filterYear.value !== 'all'
    ? `Detail Angkatan ${filterYear.value}`
    : 'Detail Seluruh Angkatan';
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount || 0);
};

const calculatePercentage = (part, total) => {
  if (!total || total <= 0) return 0;
  return Math.round((part / total) * 100);
};

const handleSchoolChange = () => {
  filterYear.value = null;
  hasData.value = false;
  loadData({ only_years: true });
};

const handleFilterChange = () => {
  if (filterSekolah.value && filterYear.value) {
    loadData();
  }
};

const loadData = async (options = {}) => {
  try {
    loading.value = true;

    const isInitialLoad = options.only_years;

    const params = {
      idSekolah: filterSekolah.value,
      tahunAngkatan: filterYear.value === 'all' ? '' : filterYear.value,
      ...options
    };

    const response = await reportAPI.alumniAnalysis(params);
    if (response.data.success) {
      const { data } = response.data;

      availableYears.value = data.availableYears || [];

      if (!isInitialLoad) {
        summary.value = data.summary;
        byYear.value = data.byYear || [];
        hasData.value = true;
      }
    }
  } catch (error) {
    console.error('Failed to load alumni analysis:', error);
  } finally {
    loading.value = false;
  }
};

const refreshData = () => {
  loadData({ refresh: true });
};

const exportData = async () => {
  try {
    loading.value = true;
    const params = {
      idSekolah: filterSekolah.value,
      tahunAngkatan: filterYear.value === 'all' ? '' : filterYear.value
    };

    const response = await reportAPI.exportAlumniAnalysis(params);

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;

    const fileName = `Laporan_Alumni_${filterYear.value || 'Semua'}_${new Date().getTime()}.xlsx`;
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    console.error('Failed to export alumni report:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  try {
    const schoolResponse = await reportAPI.getSekolahSelect();
    if (schoolResponse.data.success) {
      schools.value = schoolResponse.data.data;
    }

    loading.value = false;
  } catch (error) {
    console.error('Failed to fetch schools:', error);
    loading.value = false;
  }
});
</script>
