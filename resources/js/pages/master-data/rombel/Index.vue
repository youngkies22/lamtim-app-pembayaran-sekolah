<template>
  <Layout :active-menu="'Master Data'">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-purple-600 via-pink-600 to-rose-600 p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                  </path>
                </svg>
              </div>
              Data Rombel
            </h1>
            <p class="mt-2 text-purple-100">Kelola data rombongan belajar dengan mudah dan efisien</p>
          </div>
          <div class="flex items-center gap-3">
            <button v-if="canCreateData" @click="openImportModal"
              class="group relative inline-flex items-center gap-2 px-6 py-3 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-xl shadow-lg hover:bg-white/30 transform hover:-translate-y-0.5 transition-all duration-200">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
              </svg>
              Import
            </button>
            <button v-if="canCreateData" @click="openCreateModal"
              class="group relative inline-flex items-center gap-2 px-6 py-3 bg-white text-rose-600 font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
              <svg class="w-5 h-5 transition-transform group-hover:rotate-90 duration-300" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Tambah Rombel
            </button>
          </div>
        </div>
      </div>


      <!-- DataTable Section -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <!-- Search & Per Page Bar -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-4">
          <div class="flex-1">
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input type="text" v-model="searchQuery" @input="handleSearch(searchQuery)" placeholder="Cari rombel..."
                class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white" />
            </div>
          </div>
          <select v-model="params.length" @change="handlePerpageChange(params.length)"
            class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
              <tr>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">#</th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Kode
                </th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Nama
                  Rombel</th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Kode
                  Sekolah</th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">{{
                  labelJurusan }}</th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Kelas
                </th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Status
                </th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Aksi
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="loading" class="bg-white dark:bg-gray-800">
                <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                      viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                      </path>
                    </svg>
                    Memuat...
                  </div>
                </td>
              </tr>
              <tr v-else-if="error" class="bg-white dark:bg-gray-800">
                <td colspan="8" class="px-4 py-8 text-center text-red-600 dark:text-red-400">
                  Error: {{ error.message }}
                </td>
              </tr>
              <tr v-else-if="data.length === 0" class="bg-white dark:bg-gray-800">
                <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                  Tidak ada data
                </td>
              </tr>
              <tr v-else v-for="(item, index) in data" :key="item.id"
                class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <td class="px-4 py-2.5 text-gray-900 dark:text-gray-100">{{ params.start + index + 1 }}</td>
                <td class="px-4 py-2.5">
                  <span class="text-sm font-medium text-gray-900 dark:text-white">{{ item.kode }}</span>
                </td>
                <td class="px-4 py-2.5">
                  <div class="flex items-center gap-3">
                    <div
                      class="px-2 py-1 text-xs font-bold rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 text-white min-w-[3rem] text-center shadow-sm">
                      {{ item.kelas_kode || '??' }}
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.nama }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400">
                  {{ item.sekolah_kode || '-' }}
                </td>
                <td class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400">
                  {{ item.jurusan_kode || '-' }}
                </td>
                <td class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400">
                  {{ item.kelas_kode || '-' }}
                </td>
                <td class="px-4 py-2.5" v-html="item.isActive_badge"></td>
                <td class="px-4 py-2.5">
                  <div class="flex items-center gap-2" v-if="canEditData">
                    <button @click="handleEdit(item.id)"
                      class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                      title="Edit">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                      </svg>
                    </button>
                    <button v-if="canDeleteData" @click="handleDelete(item.id, item.nama)"
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

        <!-- Compact Pagination -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <div class="text-xs text-gray-700 dark:text-gray-300">
            Menampilkan <span class="font-medium">{{ params.start + 1 }}</span> -
            <span class="font-medium">{{ params.start + data.length }}</span> dari
            <span class="font-medium">{{ totalRecords }}</span>
          </div>
          <div class="flex items-center gap-2">
            <button @click="handlePageChange(currentPage - 1)" :disabled="currentPage === 1 || loading"
              class="px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
              Prev
            </button>
            <span class="px-2.5 py-1.5 text-xs text-gray-700 dark:text-gray-300">
              {{ currentPage }} / {{ totalPages }}
            </span>
            <button @click="handlePageChange(currentPage + 1)" :disabled="currentPage === totalPages || loading"
              class="px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <FormModal :show="showModal" :title="editingId ? 'Edit Rombel' : 'Tambah Rombel Baru'" :loading="submitLoading"
      :error="formError" @close="closeModal" @submit="handleSubmit">
      <template #form>
        <div class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Kode *</label>
            <input v-model="form.kode" type="text" required
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-purple-500 dark:text-white"
              placeholder="Kode rombel" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama Rombel *</label>
            <input v-model="form.nama" type="text" required
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-purple-500 dark:text-white"
              placeholder="Nama lengkap rombel" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Sekolah</label>
              <select v-model="form.idSekolah"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-purple-500 dark:text-white">
                <option value="">Pilih Sekolah</option>
                <option v-for="sekolah in sekolahList" :key="sekolah.id" :value="sekolah.id">{{ sekolah.nama }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ labelJurusan
              }}</label>
              <select v-model="form.idJurusan"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-purple-500 dark:text-white">
                <option value="">Pilih {{ labelJurusan }}</option>
                <option v-for="jurusan in jurusanList" :key="jurusan.id" :value="jurusan.id">{{ jurusan.nama }}</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Kelas</label>
            <select v-model="form.idKelas"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-purple-500 dark:text-white">
              <option value="">Pilih Kelas</option>
              <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">{{ kelas.nama }} ({{ kelas.kode }})
              </option>
            </select>
          </div>
          <div>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.isActive"
                class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500" />
              <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Aktif</span>
            </label>
          </div>
        </div>
      </template>
    </FormModal>

    <!-- Confirm Delete Modal -->
    <ConfirmModal :show="showDeleteModal" type="danger" title="Hapus Rombel"
      :message="`Apakah Anda yakin ingin menghapus rombel '${deletingItem?.nama || ''}'?`" confirm-text="Ya, Hapus"
      :loading="deleteLoading" @confirm="confirmDelete" @cancel="showDeleteModal = false" />

    <!-- Import Modal -->
    <ImportModal :show="showImportModal" type="rombel" @close="showImportModal = false"
      @success="handleImportSuccess" />

    <!-- Toast -->
    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import Layout from '../../../components/layout/Layout.vue';
import ActionButton from '../../../components/ActionButton.vue';
import FormModal from '../../../components/FormModal.vue';
import ConfirmModal from '../../../components/ConfirmModal.vue';
import ImportModal from '../../../components/ImportModal.vue';
import Toast from '../../../components/Toast.vue';
import { masterDataAPI } from '../../../services/api';
import { useDataTable } from '../../../composables/useDataTable';
import { useMasterDataCache } from '../../../composables/useMasterDataCache';
import { useRoleAccess } from '../../../composables/useRoleAccess';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const { canEditData, canDeleteData, canCreateData } = useRoleAccess();

const editingId = ref(null);
const deletingItem = ref(null);
const showModal = ref(false);
const showDeleteModal = ref(false);
const showImportModal = ref(false);
const submitLoading = ref(false);
const deleteLoading = ref(false);
const formError = ref('');
const toastRef = ref(null);

const { getCached, loadAll, clearCache } = useMasterDataCache();

// Get cached config immediately (synchronous)
const cached = getCached();
const labelJurusan = ref(cached.labelJurusan || 'Jurusan');

const sekolahList = ref([]);
const jurusanList = ref([]);
const kelasList = ref([]);

const initialForm = {
  kode: '',
  nama: '',
  idSekolah: '',
  idJurusan: '',
  idKelas: '',
  isActive: true,
};

const form = reactive({ ...initialForm });


// DataTable
const {
  data,
  totalRecords,
  loading,
  error,
  params,
  loadData,
  handlePageChange,
  handlePerpageChange,
  handleSearch,
} = useDataTable(masterDataAPI.rombel.datatable, {
  autoLoad: false,
  columns: [
    { data: 'id', name: 'id', searchable: false, orderable: false },
    { data: 'kode', name: 'kode', searchable: true, orderable: true },
    { data: 'nama', name: 'nama', searchable: true, orderable: true },
    { data: 'sekolah_kode', name: 'sekolah_kode', searchable: false, orderable: false },
    { data: 'jurusan_kode', name: 'jurusan_kode', searchable: false, orderable: false },
    { data: 'kelas_kode', name: 'kelas_kode', searchable: false, orderable: false },
    { data: 'isActive_badge', name: 'isActive', searchable: false, orderable: true },
    { data: 'action', name: 'action', searchable: false, orderable: false },
  ],
});

const searchQuery = ref(params.search.value);
const currentPage = computed(() => Math.floor(params.start / params.length) + 1);
const totalPages = computed(() => Math.ceil(totalRecords.value / params.length));

const loadSekolah = async () => {
  try {
    const response = await masterDataAPI.sekolah.select();
    sekolahList.value = response.data.data || response.data || [];
  } catch (err) {
    console.error('Error loading sekolah:', err);
  }
};

const loadJurusan = async () => {
  try {
    const response = await masterDataAPI.jurusan.select();
    jurusanList.value = response.data.data || response.data || [];
  } catch (err) {
    console.error('Error loading jurusan:', err);
  }
};

const loadKelas = async () => {
  try {
    const response = await masterDataAPI.kelas.select();
    kelasList.value = response.data.data || response.data || [];
  } catch (err) {
    console.error('Error loading kelas:', err);
  }
};

const loadConfig = async () => {
  try {
    const result = await loadAll();
    labelJurusan.value = result.labelJurusan || 'Jurusan';
  } catch (err) {
    console.error('Error loading config:', err);
    // Keep cached value or default
    if (!labelJurusan.value) {
      labelJurusan.value = 'Jurusan';
    }
  }
};


const openCreateModal = () => {
  editingId.value = null;
  Object.assign(form, initialForm);
  formError.value = '';
  showModal.value = true;
};

const handleEdit = async (id) => {
  try {
    submitLoading.value = true;
    const response = await masterDataAPI.rombel.get(id);
    const rombel = response.data.data;

    editingId.value = id;
    Object.assign(form, {
      kode: rombel.kode || '',
      nama: rombel.nama || '',
      idSekolah: rombel.idSekolah || '',
      idJurusan: rombel.idJurusan || '',
      idKelas: rombel.idKelas || '',
      isActive: rombel.isActive ?? true,
    });
    formError.value = '';
    showModal.value = true;
  } catch (err) {
    toastRef.value?.error('Gagal memuat data rombel');
  } finally {
    submitLoading.value = false;
  }
};

const handleDelete = (id, nama) => {
  deletingItem.value = { id, nama };
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!deletingItem.value) return;

  try {
    deleteLoading.value = true;
    await masterDataAPI.rombel.delete(deletingItem.value.id);
    showDeleteModal.value = false;
    deletingItem.value = null;
    toastRef.value?.success('Rombel berhasil dihapus');

    // Clear cache to ensure fresh data
    clearCache('rombel');

    loadData();
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal menghapus rombel');
  } finally {
    deleteLoading.value = false;
  }
};

const handleSubmit = async () => {
  try {
    submitLoading.value = true;
    formError.value = '';

    const payload = { ...form };
    if (!payload.idSekolah) delete payload.idSekolah;
    if (!payload.idJurusan) delete payload.idJurusan;
    if (!payload.idKelas) delete payload.idKelas;
    payload.isActive = payload.isActive ? 1 : 0;

    if (editingId.value) {
      await masterDataAPI.rombel.update(editingId.value, payload);
      toastRef.value?.success('Rombel berhasil diperbarui');
    } else {
      await masterDataAPI.rombel.create(payload);
      toastRef.value?.success('Rombel berhasil ditambahkan');
    }

    // Clear cache to ensure fresh data
    clearCache('rombel');

    closeModal();
    loadData();
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

const handleImportSuccess = () => {
  // Clear cache to ensure fresh data
  clearCache('rombel');
  loadData();
  toastRef.value?.success('Import berhasil');
};

// Filter jurusan berdasarkan sekolah yang dipilih
watch(() => form.idSekolah, async (newSekolahId) => {
  if (newSekolahId) {
    try {
      const response = await masterDataAPI.jurusan.list({ idSekolah: newSekolahId });
      jurusanList.value = response.data.data || response.data || [];
    } catch (err) {
      console.error('Error loading jurusan by sekolah:', err);
    }
  } else {
    await loadJurusan();
  }
});

onMounted(async () => {
  await loadConfig();
  await loadSekolah();
  await loadJurusan();
  await loadKelas();
  loadData();
});
</script>

<style>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
