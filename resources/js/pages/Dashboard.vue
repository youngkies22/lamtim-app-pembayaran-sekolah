<template>
  <Layout :active-menu="'Dashboard'">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative">
          <h1 class="text-3xl font-bold text-white flex items-center gap-3">
            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                </path>
              </svg>
            </div>
            Dashboard
          </h1>
          <div class="mt-2 flex items-center justify-between">
            <p class="text-blue-100">Selamat datang! Berikut ringkasan sistem SPP.</p>
            <button @click="togglePrivacy"
              class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-sm transition-colors backdrop-blur-sm"
              :title="showValues ? 'Sembunyikan Nilai' : 'Tampilkan Nilai'">
              <component :is="showValues ? EyeSlashIcon : EyeIcon" class="w-4 h-4" />
              <span>{{ showValues ? 'Hide Values' : 'Show Values' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="stat in stats" :key="stat.name"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-100 dark:border-gray-700 hover:shadow-xl transition-all">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                {{ stat.name }}
              </p>
              <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white truncate">
                <span v-if="loadingStats"
                  class="inline-block w-20 h-8 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></span>
                <span v-else>{{ formatValue(stat.value, stat.isCurrency) }}</span>
              </p>
              <p v-if="stat.subtitle || stat.isCurrencySubtitle" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                <span v-if="stat.isCurrencySubtitle">
                  {{ formatValue(stat.rawValue, true) }}
                </span>
                <span v-else>{{ stat.subtitle }}</span>
              </p>
            </div>
            <div :class="[
              'p-3 rounded-lg',
              stat.color
            ]">
              <component :is="stat.icon" class="w-6 h-6" />
            </div>
          </div>
        </div>
      </div>



      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="action in quickActions" :key="action.name"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-100 dark:border-gray-700 hover:shadow-xl transition-all cursor-pointer transform hover:-translate-y-1 group"
          @click="handleAction(action)">
          <div class="flex items-center gap-4">
            <div :class="[
              'p-3 rounded-xl transition-all group-hover:scale-110',
              action.color
            ]">
              <component :is="action.icon" class="w-6 h-6" />
            </div>
            <div>
              <h4
                class="font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                {{ action.name }}
              </h4>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                {{ action.description }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Layout from '../components/layout/Layout.vue';
import { dashboardAPI } from '../services/api';
import {
  UserGroupIcon,
  CreditCardIcon,
  ChartBarIcon,
  ExclamationTriangleIcon,
  DocumentTextIcon,
  BanknotesIcon,
  DocumentDuplicateIcon,
  ChartPieIcon,
  UserIcon,
  ClipboardDocumentCheckIcon,
  EyeIcon,
  EyeSlashIcon,
  AcademicCapIcon
} from '@heroicons/vue/24/outline';

const router = useRouter();

// Loading states
const loadingStats = ref(true);
const showValues = ref(localStorage.getItem('dashboard_show_values') === 'true'); // Default hidden (false) unless explicitly 'true'

const togglePrivacy = () => {
  showValues.value = !showValues.value;
  localStorage.setItem('dashboard_show_values', showValues.value);
};

const formatValue = (value, isCurrency = false) => {
  if (isCurrency) {
    if (!showValues.value) {
      return 'Rp *****';
    }
    return formatCurrency(value);
  }
  return value;
};


// Stats data
const stats = ref([
  {
    name: 'Siswa Aktif',
    value: '-',
    subtitle: null,
    icon: UserGroupIcon,
    color: 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300'
  },
  {
    name: 'Total Pembayaran',
    value: '-',
    isCurrency: true,
    subtitle: null,
    icon: CreditCardIcon,
    color: 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300'
  },
  {
    name: 'Tagihan Belum Lunas',
    value: '-',
    subtitle: null,
    icon: ExclamationTriangleIcon,
    color: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-300'
  },
  {
    name: 'Bulan Ini',
    value: '-',
    isCurrency: true,
    subtitle: null,
    icon: ChartBarIcon,
    color: 'bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-300'
  }
]);



const quickActions = ref([
  {
    name: 'Billing',
    description: 'Kelola penagihan',
    icon: BanknotesIcon,
    color: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
    route: '/billing'
  },
  {
    name: 'Tagihan',
    description: 'Daftar tagihan',
    icon: DocumentTextIcon,
    color: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
    route: '/tagihan'
  },
  {
    name: 'Invoice',
    description: 'Cetak invoice',
    icon: DocumentDuplicateIcon,
    color: 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
    route: '/invoice'
  },
  {
    name: 'Pembayaran',
    description: 'Entri pembayaran',
    icon: CreditCardIcon,
    color: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400',
    route: '/pembayaran'
  },
  {
    name: 'Report Harian',
    description: 'Rekap harian',
    icon: ChartPieIcon,
    color: 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400',
    route: '/reports'
  },
  {
    name: 'Report Per Siswa',
    description: 'Detail per siswa',
    icon: UserIcon,
    color: 'bg-cyan-100 text-cyan-600 dark:bg-cyan-900/30 dark:text-cyan-400',
    route: '/reports/siswa'
  },
  {
    name: 'Report Per Rombel',
    description: 'Rekap per kelas',
    icon: ClipboardDocumentCheckIcon,
    color: 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400',
    route: '/reports/rombel'
  },
  {
    name: 'Laporan Alumni',
    description: 'Keuangan alumni',
    icon: AcademicCapIcon,
    color: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400',
    route: '/reports/alumni-analysis'
  }
]);

// Helper functions
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount || 0);
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
};

const getStatusClass = (status) => {
  switch (status) {
    case 1:
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    case 2:
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
    default:
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
  }
};

const getStatusText = (status) => {
  switch (status) {
    case 1:
      return 'Berhasil';
    case 2:
      return 'Dibatalkan';
    default:
      return 'Pending';
  }
};

const handleAction = (action) => {
  if (action.route) {
    router.push(action.route);
  }
};

// Load dashboard stats
const loadStats = async () => {
  try {
    loadingStats.value = true;
    const response = await dashboardAPI.stats();
    const data = response.data.data || response.data;

    // Update stats with real data
    if (data) {
      stats.value[0].value = (data.totalSiswa || 0).toLocaleString('id-ID');
      stats.value[0].subtitle = 'Semua aktif';

      stats.value[1].value = data.totalPembayaran || 0;
      stats.value[1].subtitle = data.jumlahTransaksi ? `${data.jumlahTransaksi} transaksi` : null;

      stats.value[2].value = (data.tagihanBelumLunas || 0).toLocaleString('id-ID');
      stats.value[2].rawValue = data.nominalBelumLunas; // Store raw value
      stats.value[2].isCurrencySubtitle = true; // Flag for template

      stats.value[3].value = data.pembayaranBulanIni || 0;
      stats.value[3].subtitle = data.transaksiBulanIni ? `${data.transaksiBulanIni} transaksi` : null;
    }
  } catch (err) {
    // Fallback to static data if API fails
    stats.value[0].value = '-';
    stats.value[1].value = '-';
    stats.value[2].value = '-';
    stats.value[3].value = '-';
  } finally {
    loadingStats.value = false;
  }
};



onMounted(() => {
  loadStats();
});
</script>

<style>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
