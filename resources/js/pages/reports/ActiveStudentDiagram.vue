<template>
  <Layout :active-menu="'Diagrams'">
    <div class="space-y-6">
      <!-- Header & Filter -->
      <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <PresentationChartBarIcon class="w-8 h-8 text-teal-600" />
            Diagram Siswa Aktif
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Visualisasi data keuangan siswa aktif per bulan.
          </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3">
          <!-- School Filter -->
          <div class="relative w-full sm:w-64">
            <select v-model="filterSekolah" @change="handleSchoolChange"
              class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-teal-500 focus:border-teal-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white appearance-none font-medium">
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

          <!-- Refresh Button -->
          <button v-if="hasData" @click="refreshData"
            class="flex items-center gap-2 px-4 py-2.5 bg-teal-50 text-teal-600 hover:bg-teal-100 dark:bg-teal-900/30 dark:text-teal-400 rounded-xl transition-all font-semibold text-sm whitespace-nowrap"
            :disabled="loading">
            <ArrowPathIcon :class="['w-5 h-5', loading ? 'animate-spin' : '']" />
            {{ loading ? 'Memproses...' : 'Refresh Data' }}
          </button>
        </div>
      </div>

      <!-- Initial Placeholder State -->
      <div v-if="!hasData && !loading"
        class="bg-white dark:bg-gray-800 p-12 rounded-2xl shadow-sm border border-dashed border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center text-center">
        <div class="w-20 h-20 bg-teal-50 dark:bg-teal-900/20 rounded-full flex items-center justify-center mb-4">
          <AdjustmentsHorizontalIcon class="w-10 h-10 text-teal-500" />
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Siap Menampilkan Diagram</h3>
        <p class="text-gray-500 dark:text-gray-400 mt-2 max-w-sm">
          Pilih <b>Sekolah</b> pada opsi di atas untuk menampilkan diagram keuangan siswa aktif per bulan.
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading && !hasData" class="flex flex-col items-center justify-center p-20">
        <ArrowPathIcon class="w-12 h-12 text-teal-500 animate-spin mb-4" />
        <p class="text-gray-500 font-medium">Memuat data diagram...</p>
      </div>

      <!-- Charts Content -->
      <template v-if="hasData">
        <!-- Summary Cards (Compact) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div
            class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-3">
            <div class="w-10 h-10 bg-teal-100 dark:bg-teal-900/30 rounded-xl flex items-center justify-center">
              <UserGroupIcon class="w-5 h-5 text-teal-600 dark:text-teal-400" />
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Total Siswa Aktif</p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ summary.totalSiswa?.toLocaleString('id-ID') ?? 0 }}</p>
            </div>
          </div>
          <div
            class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-3">
            <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center">
              <BanknotesIcon class="w-5 h-5 text-gray-600 dark:text-gray-400" />
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Total Tagihan</p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(summary.totalTagihan) }}</p>
            </div>
          </div>
          <div
            class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
              <CheckBadgeIcon class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Terbayar</p>
              <p class="text-lg font-bold text-emerald-600">{{ formatCurrency(summary.totalTerbayar) }}</p>
            </div>
          </div>
          <div
            class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-3">
            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
              <ExclamationTriangleIcon class="w-5 h-5 text-red-600 dark:text-red-400" />
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Tunggakan</p>
              <p class="text-lg font-bold text-red-600">{{ formatCurrency(summary.totalTunggakan) }}</p>
            </div>
          </div>
        </div>

        <!-- Charts Grid -->
        <div v-if="byMonth.length > 0" class="space-y-6">
          <!-- Row 1: Stacked Bar (2/3) + Donut (1/3) -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
              <StudentStackedBarChart :data="byMonth" :loading="loading" :is-dark="isDark" />
            </div>
            <div class="lg:col-span-1">
              <StudentDonutChart :summary="summary" :loading="loading" :is-dark="isDark" />
            </div>
          </div>

          <!-- Row 2: Horizontal Bar (1/2) + Line Chart (1/2) -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <StudentHorizontalBarChart :data="byMonth" :loading="loading" :is-dark="isDark" />
            <StudentTrendLineChart :data="byMonth" :loading="loading" :is-dark="isDark" />
          </div>
        </div>

        <!-- No data state -->
        <div v-else
          class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 text-center">
          <p class="text-gray-500 dark:text-gray-400">Tidak ada data tagihan siswa aktif yang ditemukan.</p>
        </div>
      </template>
    </div>
  </Layout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Layout from '../../components/layout/Layout.vue';
import { reportAPI } from '../../services/api';
import { useDarkMode } from '../../composables/useDarkMode';
import StudentStackedBarChart from '../../components/charts/StudentStackedBarChart.vue';
import StudentDonutChart from '../../components/charts/StudentDonutChart.vue';
import StudentHorizontalBarChart from '../../components/charts/StudentHorizontalBarChart.vue';
import StudentTrendLineChart from '../../components/charts/StudentTrendLineChart.vue';
import {
  PresentationChartBarIcon,
  ArrowPathIcon,
  BanknotesIcon,
  CheckBadgeIcon,
  ExclamationTriangleIcon,
  ChevronDownIcon,
  AdjustmentsHorizontalIcon,
  UserGroupIcon,
} from '@heroicons/vue/24/outline';

const { isDark } = useDarkMode();

const loading = ref(true);
const hasData = ref(false);
const filterSekolah = ref(null);
const schools = ref([]);
const summary = ref({
  totalTagihan: 0,
  totalTerbayar: 0,
  totalTunggakan: 0,
  totalSiswa: 0
});
const byMonth = ref([]);

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount || 0);
};

const handleSchoolChange = () => {
  if (filterSekolah.value) {
    loadData();
  }
};

const loadData = async (options = {}) => {
  try {
    loading.value = true;
    const params = {
      idSekolah: filterSekolah.value,
      ...options
    };

    const response = await reportAPI.activeStudentAnalysis(params);
    if (response.data.success) {
      const { data } = response.data;
      summary.value = data.summary;
      byMonth.value = data.byMonth || [];
      hasData.value = true;
    }
  } catch (error) {
    console.error('Failed to load student diagram data:', error);
  } finally {
    loading.value = false;
  }
};

const refreshData = () => {
  loadData({ refresh: true });
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
