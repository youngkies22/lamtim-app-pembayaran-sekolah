<template>
  <Layout :active-menu="'Trash'">
    <div class="space-y-6">
      <div class="bg-gradient-to-r from-gray-700 to-gray-900 p-8 rounded-2xl shadow-xl">
        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
          <TrashIcon class="w-8 h-8" />
          Tempat Sampah (Trash)
        </h1>
        <p class="mt-2 text-gray-300">Kelola data yang telah dihapus (soft delete). Anda dapat memulihkan atau menghapus
          permanen.</p>
      </div>

      <!-- Tabs -->
      <div class="flex gap-4 border-b border-gray-200 dark:border-gray-700">
        <button v-for="tab in tabs" :key="tab.value" @click="activeTab = tab.value"
          :class="['px-4 py-2 font-medium text-sm transition-colors relative',
            activeTab === tab.value ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400']">
          {{ tab.label }}
          <div v-if="activeTab === tab.value"
            class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 dark:bg-blue-400"></div>
        </button>
      </div>

      <!-- Action Bar -->
      <div class="flex justify-end">
        <button @click="emptyTrash" :disabled="trashItems.length === 0"
          class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 transition-colors">
          <TrashIcon class="w-4 h-4" />
          Kosongkan Sampah {{ activeTab === 'tagihan' ? 'Tagihan' : 'Pembayaran' }}
        </button>
      </div>

      <!-- Empty State -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 dark:border-white mx-auto"></div>
        <p class="mt-2 text-gray-500">Memuat data...</p>
      </div>

      <div v-else-if="trashItems.length === 0"
        class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <TrashIcon class="w-12 h-12 text-gray-300 mx-auto" />
        <p class="mt-2 text-gray-500">Tidak ada item di sampah</p>
      </div>

      <!-- List Items -->
      <div v-else
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-300">
              <tr>
                <th class="px-6 py-3">Identifier</th>
                <th class="px-6 py-3">Deskripsi</th>
                <th class="px-6 py-3">Dihapus Pada</th>
                <th class="px-6 py-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="item in trashItems" :key="item.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="px-6 py-4 font-mono font-medium text-gray-900 dark:text-white">{{ item.identifier }}</td>
                <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ item.description }}</td>
                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ formatDate(item.deleted_at) }}</td>
                <td class="px-6 py-4 text-right space-x-2">
                  <button @click="restoreItem(item)"
                    class="text-green-600 hover:text-green-800 font-medium text-xs px-2 py-1 bg-green-50 dark:bg-green-900/20 rounded hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors">
                    Restore
                  </button>
                  <button @click="forceDeleteItem(item)"
                    class="text-red-600 hover:text-red-800 font-medium text-xs px-2 py-1 bg-red-50 dark:bg-red-900/20 rounded hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">
                    Hapus Permanen
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import Layout from '../../components/layout/Layout.vue';
import Toast from '../../components/Toast.vue';
import { TrashIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const activeTab = ref('tagihan');
const tabs = [
  { label: 'Tagihan', value: 'tagihan' },
  { label: 'Pembayaran', value: 'pembayaran' },
];

const trashItems = ref([]);
const loading = ref(false);
const toastRef = ref(null);

const loadTrash = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/trash', {
      params: { type: activeTab.value },
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    });
    trashItems.value = response.data.data || [];
  } catch (err) {
    console.error(err);
    toastRef.value?.error('Gagal memuat data sampah');
  } finally {
    loading.value = false;
  }
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });
};

const restoreItem = async (item) => {
  if (!confirm(`Restore item ${item.identifier}?`)) return;

  try {
    await axios.post(`/api/trash/${item.id}/restore`, { type: activeTab.value }, {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    });
    toastRef.value?.success('Data berhasil dipulihkan');
    loadTrash();
  } catch (err) {
    console.error(err);
    toastRef.value?.error(err.response?.data?.message || 'Gagal restore item');
  }
};

const forceDeleteItem = async (item) => {
  if (!confirm(`HAPUS PERMANEN item ${item.identifier}? Data tidak bisa dikembalikan!`)) return;

  try {
    await axios.delete(`/api/trash/${item.id}/force-delete`, {
      data: { type: activeTab.value },
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    });
    toastRef.value?.success('Data berhasil dihapus selamanya');
    loadTrash();
  } catch (err) {
    console.error(err);
    toastRef.value?.error(err.response?.data?.message || 'Gagal menghapus item permanen');
  }
};

const emptyTrash = async () => {
  if (!confirm(`Kosongkan semua sampah ${activeTab.value}? Semua data di tab ini akan dihapus permanen dan TIDAK BISA DIKEMBALIKAN!`)) return;

  try {
    await axios.post('/api/trash/empty', { type: activeTab.value }, {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    });
    toastRef.value?.success('Sampah berhasil dibersihkan');
    loadTrash();
  } catch (err) {
    console.error(err);
    toastRef.value?.error(err.response?.data?.message || 'Gagal mengosongkan sampah');
  }
};

watch(activeTab, () => {
  loadTrash();
});

onMounted(() => {
  loadTrash();
});
</script>
