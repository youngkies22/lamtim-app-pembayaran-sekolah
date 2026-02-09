<template>
  <Layout :active-menu="'Master Data'">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                <AcademicCapIcon class="w-8 h-8 text-white" />
              </div>
              Data Semester
            </h1>
            <p class="mt-2 text-indigo-100">Kelola data semester dengan mudah dan efisien</p>
          </div>
          <button
            v-if="canCreateData"
            @click="openCreateModal"
            class="group relative inline-flex items-center gap-2 px-6 py-3 bg-white text-indigo-600 font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
          >
            <PlusIcon class="w-5 h-5 transition-transform group-hover:rotate-90 duration-300" />
            Tambah Semester
          </button>
        </div>
      </div>

      <!-- Modern Table Card -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Menampilkan {{ tableData.length }} data
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gray-50 dark:bg-gray-900/50">
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Singkat</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-if="loading">
                <td colspan="6" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center gap-3">
                    <svg class="animate-spin h-10 w-10 text-indigo-600" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Memuat data...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="tableData.length === 0">
                <td colspan="6" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center gap-3">
                    <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full">
                      <AcademicCapIcon class="w-10 h-10 text-gray-400" />
                    </div>
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Tidak ada data ditemukan</span>
                  </div>
                </td>
              </tr>
              <tr v-else v-for="(item, index) in tableData" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ index + 1 }}</td>
                <td class="px-6 py-4">
                  <span class="text-sm font-medium text-gray-900 dark:text-white">{{ item.kode }}</span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
                      {{ getInitials(item.nama) }}
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.nama }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-600 dark:text-gray-400">{{ item.namaSingkat || '-' }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span
                    :class="[
                      'px-3 py-1 text-xs font-semibold rounded-full',
                      item.isActive
                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400'
                    ]"
                  >
                    {{ item.isActive ? 'Aktif' : 'Tidak Aktif' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <button
                      v-if="canEditData"
                      @click="handleEdit(item)"
                      class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors cursor-pointer"
                      title="Edit"
                    >
                      <PencilIcon class="w-4 h-4" />
                    </button>
                    <button
                      v-if="!item.isActive && canEditData"
                      @click="handleSetActive(item)"
                      class="p-2 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded transition-colors cursor-pointer"
                      title="Set Aktif"
                    >
                      <CheckCircleIcon class="w-4 h-4" />
                    </button>
                    <button
                      v-if="canDeleteData"
                      @click="handleDelete(item)"
                      class="p-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors cursor-pointer"
                      title="Hapus"
                    >
                      <TrashIcon class="w-4 h-4" />
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
    <FormModal
      :show="showModal"
      :title="editingId ? 'Edit Semester' : 'Tambah Semester Baru'"
      :loading="submitLoading"
      :error="formError"
      @close="closeModal"
      @submit="handleSubmit"
    >
      <template #form>
        <div class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Kode *</label>
            <input
              v-model="form.kode"
              type="text"
              required
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white"
              placeholder="Contoh: Ganjil atau Genap"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama *</label>
            <input
              v-model="form.nama"
              type="text"
              required
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white"
              placeholder="Contoh: Semester Ganjil"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama Singkat</label>
            <input
              v-model="form.namaSingkat"
              type="text"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white"
              placeholder="Contoh: Ganjil"
            />
          </div>
        </div>
      </template>
    </FormModal>

    <!-- Confirm Delete Modal -->
    <ConfirmModal
      :show="showDeleteModal"
      type="danger"
      title="Hapus Semester"
      :message="`Apakah Anda yakin ingin menghapus semester '${deletingItem?.nama || ''}'?`"
      confirm-text="Ya, Hapus"
      :loading="deleteLoading"
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />

    <!-- Toast -->
    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import Layout from '../../../components/layout/Layout.vue';
import FormModal from '../../../components/FormModal.vue';
import ConfirmModal from '../../../components/ConfirmModal.vue';
import Toast from '../../../components/Toast.vue';
import { masterDataAPI } from '../../../services/api';
import { useRoleAccess } from '../../../composables/useRoleAccess';
import {
  AcademicCapIcon,
  PlusIcon,
  PencilIcon,
  TrashIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline';

const { canCreateData, canEditData, canDeleteData } = useRoleAccess();

// Data
const tableData = ref([]);
const loading = ref(true);
const showModal = ref(false);
const editingId = ref(null);
const submitLoading = ref(false);
const formError = ref('');
const showDeleteModal = ref(false);
const deletingItem = ref(null);
const deleteLoading = ref(false);
const toastRef = ref(null);

// Form
const initialForm = {
  kode: '',
  nama: '',
  namaSingkat: '',
};

const form = reactive({ ...initialForm });

// Methods
const getInitials = (text) => {
  if (!text) return 'SM';
  return text
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .substring(0, 2);
};

const loadData = async () => {
  loading.value = true;
  try {
    const response = await masterDataAPI.semester.list({
      _t: Date.now(),
    });
    if (response.data?.success) {
      tableData.value = response.data.data || [];
    }
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal memuat data semester');
    tableData.value = [];
  } finally {
    loading.value = false;
  }
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
    const response = await masterDataAPI.semester.get(item.id);
    const semester = response.data.data;
    
    editingId.value = item.id;
    Object.assign(form, {
      kode: semester.kode || '',
      nama: semester.nama || '',
      namaSingkat: semester.namaSingkat || '',
    });
    formError.value = '';
    showModal.value = true;
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal memuat data semester');
  } finally {
    submitLoading.value = false;
  }
};

const handleSubmit = async () => {
  formError.value = '';
  submitLoading.value = true;

  try {
    if (editingId.value) {
      await masterDataAPI.semester.update(editingId.value, form);
      toastRef.value?.success('Semester berhasil diupdate');
    } else {
      await masterDataAPI.semester.create(form);
      toastRef.value?.success('Semester berhasil dibuat');
    }

    closeModal();
    await loadData();
  } catch (err) {
    formError.value = err.response?.data?.message || 'Terjadi kesalahan saat menyimpan data';
    toastRef.value?.error(formError.value);
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

const handleDelete = (item) => {
  deletingItem.value = item;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!deletingItem.value) return;

  deleteLoading.value = true;
  try {
    await masterDataAPI.semester.delete(deletingItem.value.id);
    toastRef.value?.success('Semester berhasil dihapus');
    showDeleteModal.value = false;
    deletingItem.value = null;
    await loadData();
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal menghapus semester');
  } finally {
    deleteLoading.value = false;
  }
};

const handleSetActive = async (item) => {
  try {
    await masterDataAPI.semester.setActive(item.id);
    toastRef.value?.success('Semester berhasil diaktifkan');
    await loadData();
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal mengaktifkan semester');
  }
};

onMounted(() => {
  loadData();
});
</script>

<style scoped>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
