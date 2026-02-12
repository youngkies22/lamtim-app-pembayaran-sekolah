<template>
  <Layout :active-menu="'Closing'">
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Closing</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage daily and monthly financial closings.</p>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 dark:border-gray-700">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
          <li class="mr-2" role="presentation">
            <button @click="activeTab = 'daily'" :class="[
              'inline-block p-4 border-b-2 rounded-t-lg',
              activeTab === 'daily'
                ? 'text-blue-600 border-blue-600 dark:text-blue-500 dark:border-blue-500'
                : 'hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300'
            ]">
              Daily Closing
            </button>
          </li>
          <li class="mr-2" role="presentation">
            <button @click="activeTab = 'monthly'" :class="[
              'inline-block p-4 border-b-2 rounded-t-lg',
              activeTab === 'monthly'
                ? 'text-blue-600 border-blue-600 dark:text-blue-500 dark:border-blue-500'
                : 'hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300'
            ]">
              Monthly Closing
            </button>
          </li>
        </ul>
      </div>

      <!-- Daily Tab Content -->
      <div v-if="activeTab === 'daily'" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex flex-col md:flex-row gap-4 mb-6 items-center">
          <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Month:</label>
            <select v-model="filters.month" @change="fetchData"
              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
              <option v-for="m in 12" :key="m" :value="m">{{ getMonthName(m) }}</option>
            </select>
          </div>
          <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Year:</label>
            <select v-model="filters.year" @change="fetchData"
              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
              <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
            </select>
          </div>
          <div class="ml-auto">
            <button @click="fetchData" class="text-blue-600 hover:underline text-sm">Refresh Data</button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
              <tr>
                <th scope="col" class="px-6 py-3">Date</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3 text-right">Total Income</th>
                <th scope="col" class="px-6 py-3">Closed By</th>
                <th scope="col" class="px-6 py-3">Closed At</th>
                <th scope="col" class="px-6 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td colspan="6" class="px-6 py-4 text-center">Loading...</td>
              </tr>
              <tr v-else-if="items.length === 0" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td colspan="6" class="px-6 py-4 text-center">No data available</td>
              </tr>
              <tr v-for="item in items" :key="item.date"
                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                :class="{ 'bg-blue-50 dark:bg-blue-900/20': item.is_today }">
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                  {{ formatDate(item.date) }}
                  <span v-if="item.is_today"
                    class="ml-2 text-xs font-semibold text-blue-600 bg-blue-100 rounded px-2 py-0.5">Today</span>
                </td>
                <td class="px-6 py-4">
                  <span :class="getStatusClass(item.status)">
                    {{ item.status === 'closed' ? 'Closed' : 'Open' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-medium">
                  {{ formatCurrency(item.total_income) }}
                </td>
                <td class="px-6 py-4">
                  {{ item.user?.name || '-' }}
                </td>
                <td class="px-6 py-4">
                  {{ item.closed_at ? formatDateTime(item.closed_at) : '-' }}
                </td>
                <td class="px-6 py-4 text-right">
                  <button v-if="item.status === 'open'" @click="confirmCloseDaily(item)"
                    class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-xs px-3 py-2 dark:bg-red-600 dark:hover:bg-red-700 focus:outline-none dark:focus:ring-red-800">
                    Close Day
                  </button>
                  <button v-if="item.status === 'closed' && isAdminUser" @click="confirmReopen(item)"
                    class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-xs px-3 py-2 dark:bg-green-600 dark:hover:bg-green-700 focus:outline-none dark:focus:ring-green-800">
                    Reopen
                  </button>
                  <span v-if="item.status === 'closed' && !isAdminUser" class="text-gray-400 text-xs italic">
                    Locked
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Monthly Tab Content -->
      <div v-if="activeTab === 'monthly'" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex flex-col md:flex-row gap-4 mb-6 items-center">
          <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Year:</label>
            <select v-model="filters.year" @change="fetchData"
              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
              <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
            </select>
          </div>
          <div class="ml-auto">
            <button @click="fetchData" class="text-blue-600 hover:underline text-sm">Refresh Data</button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
              <tr>
                <th scope="col" class="px-6 py-3">Month</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3 text-right">Total Income</th>
                <th scope="col" class="px-6 py-3">Closed By</th>
                <th scope="col" class="px-6 py-3">Closed At</th>
                <th scope="col" class="px-6 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td colspan="6" class="px-6 py-4 text-center">Loading...</td>
              </tr>
              <tr v-else-if="items.length === 0" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td colspan="6" class="px-6 py-4 text-center">No data available</td>
              </tr>
              <tr v-for="item in items" :key="item.month"
                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                :class="{ 'bg-blue-50 dark:bg-blue-900/20': item.is_current_month }">
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                  {{ item.month_name }}
                </td>
                <td class="px-6 py-4">
                  <span :class="getStatusClass(item.status)">
                    {{ item.status === 'closed' ? 'Closed' : 'Open' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-medium">
                  {{ formatCurrency(item.total_income) }}
                </td>
                <td class="px-6 py-4">
                  {{ item.user?.name || '-' }}
                </td>
                <td class="px-6 py-4">
                  {{ item.closed_at ? formatDateTime(item.closed_at) : '-' }}
                </td>
                <td class="px-6 py-4 text-right">
                  <button v-if="item.status === 'open' && isAdminUser" @click="confirmCloseMonthly(item)"
                    class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-xs px-3 py-2 dark:bg-red-600 dark:hover:bg-red-700 focus:outline-none dark:focus:ring-red-800">
                    Close Month
                  </button>
                  <span v-if="item.status === 'open' && !isAdminUser" class="text-gray-400 text-xs italic">
                    Admin Only
                  </span>
                  <button v-if="item.status === 'closed' && isAdminUser" @click="confirmReopen(item)"
                    class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-xs px-3 py-2 dark:bg-green-600 dark:hover:bg-green-700 focus:outline-none dark:focus:ring-green-800">
                    Reopen
                  </button>
                  <span v-if="item.status === 'closed' && !isAdminUser" class="text-gray-400 text-xs italic">
                    Locked
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modals -->
      <ConfirmModal :show="showConfirmModal" :title="confirmTitle" :message="confirmMessage" :loading="processing"
        @confirm="processAction" @close="showConfirmModal = false" />

      <Toast ref="toastRef" />
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import Layout from '../../components/layout/Layout.vue';
import ConfirmModal from '../../components/ConfirmModal.vue';
import Toast from '../../components/Toast.vue';
import { useAppSettings } from '../../composables/useAppSettings';
import { useRoleAccess } from '../../composables/useRoleAccess';

const { appSettings } = useAppSettings();
const { isAdminUser } = useRoleAccess();

const toastRef = ref(null);

const activeTab = ref('daily');
const loading = ref(false);
const processing = ref(false);
const items = ref([]);

const filters = reactive({
  month: new Date().getMonth() + 1,
  year: new Date().getFullYear(),
});

const years = computed(() => {
  const currentYear = new Date().getFullYear();
  return [currentYear - 1, currentYear, currentYear + 1];
});

// Modal State
const showConfirmModal = ref(false);
const confirmTitle = ref('');
const confirmMessage = ref('');
const actionType = ref(''); // 'daily', 'monthly', 'reopen'
const selectedItem = ref(null);

const getMonthName = (m) => {
  const date = new Date();
  date.setMonth(m - 1);
  return date.toLocaleString('id-ID', { month: 'long' });
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
};

const formatDateTime = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value);
};

const getStatusClass = (status) => {
  if (status === 'closed') {
    return 'bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300';
  }
  return 'bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300';
};

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/closing', {
      params: {
        type: activeTab.value,
        month: filters.month,
        year: filters.year
      }
    });
    items.value = response.data.data;
  } catch (error) {
    toastRef.value?.error('Failed to fetch closing data');
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const confirmCloseDaily = (item) => {
  selectedItem.value = item;
  actionType.value = 'daily';
  confirmTitle.value = 'Close Day?';
  confirmMessage.value = `Are you sure you want to close <b>${formatDate(item.date)}</b>? Any further transactions for this date will be blocked.`;
  showConfirmModal.value = true;
};

const confirmCloseMonthly = (item) => {
  selectedItem.value = item;
  actionType.value = 'monthly';
  confirmTitle.value = 'Close Month?';
  confirmMessage.value = `Are you sure you want to close <b>${item.month_name} ${item.year}</b>? This requires all daily transactions to be finalized.`;
  showConfirmModal.value = true;
};

const confirmReopen = (item) => {
  selectedItem.value = item;
  actionType.value = 'reopen';
  confirmTitle.value = 'Reopen Period?';
  confirmMessage.value = `Are you sure you want to reopen this period? This will allow transactions to be modified again.`;
  showConfirmModal.value = true;
};

const processAction = async () => {
  processing.value = true;
  try {
    if (actionType.value === 'daily') {
      await axios.post('/api/closing/daily', { date: selectedItem.value.date });
      toastRef.value?.success('Daily closing successful');
    } else if (actionType.value === 'monthly') {
      await axios.post('/api/closing/monthly', {
        month: selectedItem.value.month,
        year: selectedItem.value.year
      });
      toastRef.value?.success('Monthly closing successful');
    } else if (actionType.value === 'reopen') {
      await axios.post('/api/closing/reopen', { id: selectedItem.value.id });
      toastRef.value?.success('Period reopened successfully');
    }

    showConfirmModal.value = false;
    fetchData(); // Refresh list
  } catch (error) {
    console.error(error);
    const msg = error.response?.data?.message || 'Action failed';
    toastRef.value?.error(msg);
  } finally {
    processing.value = false;
  }
};

watch(activeTab, () => {
  fetchData();
});

onMounted(() => {
  fetchData();
});
</script>
