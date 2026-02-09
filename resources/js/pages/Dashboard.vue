<template>
  <Layout :active-menu="'Dashboard'">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative">
          <h1 class="text-3xl font-bold text-white flex items-center gap-3">
            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
              </svg>
            </div>
            Dashboard
          </h1>
          <p class="mt-2 text-blue-100">Selamat datang! Berikut ringkasan sistem SPP.</p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div
          v-for="stat in stats"
          :key="stat.name"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-100 dark:border-gray-700 hover:shadow-xl transition-all"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                {{ stat.name }}
              </p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                <span v-if="loadingStats" class="inline-block w-20 h-8 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></span>
                <span v-else>{{ stat.value }}</span>
              </p>
              <p v-if="stat.subtitle" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ stat.subtitle }}
              </p>
            </div>
            <div
              :class="[
                'p-3 rounded-lg',
                stat.color
              ]"
            >
              <component :is="stat.icon" class="w-6 h-6" />
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Payments Table -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Pembayaran Terbaru
          </h3>
          <router-link 
            to="/pembayaran" 
            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
          >
            Lihat Semua →
          </router-link>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
              <tr>
                <th scope="col" class="px-6 py-3">Nama Siswa</th>
                <th scope="col" class="px-6 py-3">Nominal</th>
                <th scope="col" class="px-6 py-3">Jenis</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Tanggal</th>
              </tr>
            </thead>
            <tbody>
              <!-- Loading state -->
              <tr v-if="loadingPayments" v-for="n in 5" :key="n" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-32 animate-pulse"></div></td>
                <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24 animate-pulse"></div></td>
                <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-20 animate-pulse"></div></td>
                <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-16 animate-pulse"></div></td>
                <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24 animate-pulse"></div></td>
              </tr>
              
              <!-- Empty state -->
              <tr v-else-if="recentPayments.length === 0">
                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                  Belum ada pembayaran terbaru
                </td>
              </tr>
              
              <!-- Data -->
              <tr
                v-else
                v-for="payment in recentPayments"
                :key="payment.id"
                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
              >
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                  {{ payment.siswa?.nama || '-' }}
                </td>
                <td class="px-6 py-4">
                  {{ formatCurrency(payment.nominalBayar) }}
                </td>
                <td class="px-6 py-4">{{ payment.metodeBayar || '-' }}</td>
                <td class="px-6 py-4">
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded',
                      getStatusClass(payment.status)
                    ]"
                  >
                    {{ getStatusText(payment.status) }}
                  </span>
                </td>
                <td class="px-6 py-4">{{ formatDate(payment.tanggalBayar) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="action in quickActions"
          :key="action.name"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-100 dark:border-gray-700 hover:shadow-xl transition-all cursor-pointer transform hover:-translate-y-1"
          @click="handleAction(action)"
        >
          <div class="flex items-center gap-4">
            <div
              :class="[
                'p-3 rounded-lg',
                action.color
              ]"
            >
              <component :is="action.icon" class="w-6 h-6" />
            </div>
            <div>
              <h4 class="font-semibold text-gray-900 dark:text-white">
                {{ action.name }}
              </h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">
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
import { dashboardAPI, pembayaranAPI } from '../services/api';
import {
  UserGroupIcon,
  CreditCardIcon,
  ChartBarIcon,
  ExclamationTriangleIcon,
  PlusIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline';

const router = useRouter();

// Loading states
const loadingStats = ref(true);
const loadingPayments = ref(true);

// Stats data
const stats = ref([
  {
    name: 'Total Siswa',
    value: '-',
    subtitle: null,
    icon: UserGroupIcon,
    color: 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300'
  },
  {
    name: 'Total Pembayaran',
    value: '-',
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
    subtitle: null,
    icon: ChartBarIcon,
    color: 'bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-300'
  }
]);

const recentPayments = ref([]);

const quickActions = ref([
  {
    name: 'Tambah Siswa',
    description: 'Daftarkan siswa baru',
    icon: PlusIcon,
    color: 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300',
    route: '/siswa'
  },
  {
    name: 'Proses Pembayaran',
    description: 'Catat pembayaran baru',
    icon: CreditCardIcon,
    color: 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300',
    route: '/pembayaran'
  },
  {
    name: 'Generate Tagihan',
    description: 'Buat tagihan batch',
    icon: DocumentTextIcon,
    color: 'bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-300',
    route: '/tagihan'
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
      stats.value[0].subtitle = data.siswaAktif ? `${data.siswaAktif} aktif` : null;
      
      stats.value[1].value = formatCurrency(data.totalPembayaran || 0);
      stats.value[1].subtitle = data.jumlahTransaksi ? `${data.jumlahTransaksi} transaksi` : null;
      
      stats.value[2].value = (data.tagihanBelumLunas || 0).toLocaleString('id-ID');
      stats.value[2].subtitle = data.nominalBelumLunas ? formatCurrency(data.nominalBelumLunas) : null;
      
      stats.value[3].value = formatCurrency(data.pembayaranBulanIni || 0);
      stats.value[3].subtitle = data.transaksibulanIni ? `${data.transaksibulanIni} transaksi` : null;
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

// Load recent payments
const loadRecentPayments = async () => {
  try {
    loadingPayments.value = true;
    const response = await pembayaranAPI.list({ per_page: 5 });
    recentPayments.value = response.data.data || [];
  } catch (err) {
    recentPayments.value = [];
  } finally {
    loadingPayments.value = false;
  }
};

onMounted(() => {
  loadStats();
  loadRecentPayments();
});
</script>

<style>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
