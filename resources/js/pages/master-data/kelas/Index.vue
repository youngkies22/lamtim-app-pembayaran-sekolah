<template>
  <Layout :active-menu="'Master Data'">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                  </path>
                </svg>
              </div>
              Data Kelas
            </h1>
            <p class="mt-2 text-green-100">Kelola data kelas dengan mudah dan efisien</p>
          </div>
          <div class="flex items-center gap-3">
            <button v-if="isAdminUser" @click="openImportModal"
              class="group relative inline-flex items-center gap-2 px-6 py-3 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-xl shadow-lg hover:bg-white/30 transform hover:-translate-y-0.5 transition-all duration-200">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
              </svg>
              Import
            </button>
            <button v-if="isAdminUser" @click="openCreateModal"
              class="group relative inline-flex items-center gap-2 px-6 py-3 bg-white text-indigo-600 font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
              <svg class="w-5 h-5 transition-transform group-hover:rotate-90 duration-300" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Tambah Kelas
            </button>
          </div>
        </div>
      </div>

      <!-- Modern Filter Card -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-center gap-2 mb-4">
          <div class="p-2 bg-green-50 dark:bg-green-900/30 rounded-lg">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
              </path>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Pencarian</h3>
        </div>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <input type="text" v-model="searchQuery" @input="debouncedSearch" placeholder="Cari kelas..."
            class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:text-white transition-all duration-200" />
        </div>
      </div>

      <!-- Modern Table Card -->
      <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Menampilkan {{ tableData.length }} data
          </div>
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
                  Nama Kelas</th>
                <th
                  class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-if="loading">
                <td colspan="4" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center gap-3">
                    <svg class="animate-spin h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                      </path>
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Memuat data...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="tableData.length === 0">
                <td colspan="4" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center gap-3">
                    <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full">
                      <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                        </path>
                      </svg>
                    </div>
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Tidak ada data ditemukan</span>
                  </div>
                </td>
              </tr>
              <tr v-else v-for="(item, index) in tableData" :key="item.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ index + 1 }}</td>
                <td class="px-6 py-4">
                  <span class="text-sm font-medium text-gray-900 dark:text-white">{{ item.kode }}</span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div
                      class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center text-white font-semibold text-sm">
                      {{ getInitials(item.nama) }}
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.nama }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-2.5">
                  <div class="flex items-center gap-2" v-if="isAdminUser">
                    <button @click="handleEdit(item.id)"
                      class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                      title="Edit">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                      </svg>
                    </button>
                    <button @click="handleDelete(item.id, item.nama)"
                      class="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                      title="Hapus">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <FormModal :show="showModal" :title="editingId ? 'Edit Kelas' : 'Tambah Kelas Baru'" :loading="submitLoading"
      :error="formError" @close="closeModal" @submit="handleSubmit">
      <template #form>
        <div class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Kode *</label>
            <input v-model="form.kode" type="text" required
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-green-500 dark:text-white"
              placeholder="Kode kelas" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama Kelas *</label>
            <input v-model="form.nama" type="text" required
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-green-500 dark:text-white"
              placeholder="Nama lengkap kelas" />
          </div>
        </div>
      </template>
    </FormModal>

    <!-- Confirm Delete Modal -->
    <ConfirmModal :show="showDeleteModal" type="danger" title="Hapus Kelas"
      :message="`Apakah Anda yakin ingin menghapus kelas '${deletingItem?.nama || ''}'?`" confirm-text="Ya, Hapus"
      :loading="deleteLoading" @confirm="confirmDelete" @cancel="showDeleteModal = false" />

    <!-- Import Modal -->
    <ImportModal :show="showImportModal" type="kelas" @close="showImportModal = false" @success="handleImportSuccess" />

    <!-- Toast -->
    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import Layout from '../../../components/layout/Layout.vue';
import ActionButton from '../../../components/ActionButton.vue';
import FormModal from '../../../components/FormModal.vue';
import ConfirmModal from '../../../components/ConfirmModal.vue';
import ImportModal from '../../../components/ImportModal.vue';
import Toast from '../../../components/Toast.vue';
import { masterDataAPI } from '../../../services/api';
import { useMasterDataCache } from '../../../composables/useMasterDataCache';
import { useRoleAccess } from '../../../composables/useRoleAccess';

const { isAdminUser } = useRoleAccess();

const { clearCache } = useMasterDataCache();

const toastRef = ref(null);
const showModal = ref(false);
const showDeleteModal = ref(false);
const showImportModal = ref(false);
const editingId = ref(null);
const deletingItem = ref(null);
const loading = ref(false);
const submitLoading = ref(false);
const deleteLoading = ref(false);
const formError = ref('');

const tableData = ref([]);
const searchQuery = ref('');

const initialForm = {
  kode: '',
  nama: '',
};

const form = reactive({ ...initialForm });

let searchTimer = null;

const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const loadData = async (force = false) => {
  try {
    loading.value = true;

    // Clear cache if force reload
    if (force) {
      clearCache('kelas');
    }

    // Add timestamp to prevent browser cache
    const params = {
      search: searchQuery.value,
      _t: Date.now() // Force fresh request
    };

    const response = await masterDataAPI.kelas.list(params);

    // Handle different response structures
    let data = [];
    if (response.data?.success && response.data?.data) {
      data = response.data.data;
    } else if (response.data?.data) {
      data = response.data.data;
    } else if (Array.isArray(response.data)) {
      data = response.data;
    }

    tableData.value = Array.isArray(data) ? data : [];
  } catch (err) {
    console.error('Error loading data:', err);
    toastRef.value?.error('Gagal memuat data kelas');
    tableData.value = [];
  } finally {
    loading.value = false;
  }
};

const debouncedSearch = () => {
  if (searchTimer) clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    loadData();
  }, 500);
};

const openCreateModal = () => {
  editingId.value = null;
  Object.assign(form, initialForm);
  formError.value = '';
  showModal.value = true;
};

const handleEdit = async (item) => {
  try {
    submitLoading.value = true;
    const response = await masterDataAPI.kelas.get(item.id);
    const kelas = response.data.data;

    editingId.value = item.id;
    Object.assign(form, {
      kode: kelas.kode || '',
      nama: kelas.nama || '',
    });
    formError.value = '';
    showModal.value = true;
  } catch (err) {
    toastRef.value?.error('Gagal memuat data kelas');
  } finally {
    submitLoading.value = false;
  }
};

const handleDelete = (item) => {
  deletingItem.value = item;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!deletingItem.value) return;

  try {
    deleteLoading.value = true;
    await masterDataAPI.kelas.delete(deletingItem.value.id);
    showDeleteModal.value = false;
    deletingItem.value = null;
    toastRef.value?.success('Kelas berhasil dihapus');

    // Clear cache to ensure fresh data
    clearCache('kelas');

    // Force reload to get fresh data
    await loadData(true);
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal menghapus kelas');
  } finally {
    deleteLoading.value = false;
  }
};

const handleSubmit = async () => {
  try {
    submitLoading.value = true;
    formError.value = '';

    const payload = { ...form };

    if (editingId.value) {
      await masterDataAPI.kelas.update(editingId.value, payload);
      toastRef.value?.success('Kelas berhasil diperbarui');
    } else {
      await masterDataAPI.kelas.create(payload);
      toastRef.value?.success('Kelas berhasil ditambahkan');
    }

    // Clear cache to ensure fresh data
    clearCache('kelas');

    closeModal();
    // Force reload to get fresh data
    await loadData(true);
  } catch (err) {
    formError.value = err.response?.data?.message || 'Terjadi kesalahan';
  } finally {
    submitLoading.value = false;
  }
};

const closeModal = () => {
  showModal.value = false;
  editingId.value = null;
  Object.assign(form, initialForm);
  formError.value = '';
};

const openImportModal = () => {
  showImportModal.value = true;
};

const handleImportSuccess = async () => {
  // Clear cache to ensure fresh data
  clearCache('kelas');
  // Force reload to get fresh data
  await loadData(true);
  toastRef.value?.success('Import berhasil');
};

onMounted(() => {
  loadData();
});
</script>

<style>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
