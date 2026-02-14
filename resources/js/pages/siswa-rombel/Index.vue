<template>
  <Layout :active-menu="'Mapping'">
    <div class="space-y-4">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mapping Rombel Siswa</h1>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">Kelola mapping siswa ke rombel dengan mudah</p>
        </div>
        <button @click="openCreateModal"
          class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-700 dark:hover:bg-blue-600 transition-colors flex items-center gap-2">
          <PlusIcon class="w-4 h-4" />
          Tambah Mapping
        </button>
      </div>

      <!-- Filter Section -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <!-- Filter Siswa -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Siswa</label>
            <select v-model="filters.idSiswa" @change="applyFilters"
              class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
              <option value="">Semua Siswa</option>
              <option v-for="siswa in siswaList" :key="siswa.id" :value="siswa.id">
                {{ siswa.nis || '-' }} - {{ siswa.nama || '-' }} - {{ siswa.rombel || '-' }}
              </option>
            </select>
          </div>

          <!-- Filter Rombel -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rombel</label>
            <select v-model="filters.idRombel" @change="applyFilters"
              class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
              <option value="">Semua Rombel</option>
              <option v-for="rombel in rombelList" :key="rombel.id" :value="rombel.id">
                {{ rombel.kelas ? rombel.kelas.kode + ' ' : '' }}{{ rombel.nama }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- DataTable Section -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
              <tr>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">#</th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Siswa
                </th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">NIS
                </th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Rombel
                </th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Sekolah
                </th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Jurusan
                </th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Kelas
                </th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Aksi
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="loading" class="bg-white dark:bg-gray-800">
                <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
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
              <tr v-else v-for="(mapping, index) in data" :key="mapping.id"
                class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <td class="px-4 py-2.5 text-gray-900 dark:text-gray-100">{{ params.start + index + 1 }}</td>
                <td class="px-4 py-2.5 text-gray-900 dark:text-gray-100">{{ mapping.siswa_nama || '-' }}</td>
                <td class="px-4 py-2.5 text-gray-900 dark:text-gray-100">{{ mapping.siswa_nis || '-' }}</td>
                <td class="px-4 py-2.5 text-gray-900 dark:text-gray-100">{{ mapping.rombel_nama || '-' }}</td>
                <td class="px-4 py-2.5 text-gray-900 dark:text-gray-100">{{ mapping.sekolah_nama || '-' }}</td>
                <td class="px-4 py-2.5 text-gray-900 dark:text-gray-100">{{ mapping.jurusan_nama || '-' }}</td>
                <td class="px-4 py-2.5 text-gray-900 dark:text-gray-100">{{ mapping.kelas_kode || '-' }}</td>
                <td class="px-4 py-2.5">
                  <div class="flex items-center gap-2">
                    <button @click="handleEdit(mapping.id)"
                      class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors cursor-pointer"
                      title="Edit">
                      <PencilIcon class="w-4 h-4" />
                    </button>
                    <button @click="handleDelete(mapping.id, mapping.siswa_nama)"
                      class="p-1.5 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors cursor-pointer"
                      title="Hapus">
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
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

    <!-- Create/Edit Modal -->
    <div v-if="showModal"
      class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
      @click.self="closeModal">
      <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          {{ editingId ? 'Edit Mapping' : 'Tambah Mapping' }}
        </h3>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Error Message -->
          <div v-if="formError"
            class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-sm text-red-800 dark:text-red-200">{{ formError }}</p>
          </div>

          <!-- Siswa -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Siswa <span class="text-red-500">*</span>
            </label>
            <select v-model="form.idSiswa" :disabled="editingId" required
              class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white disabled:opacity-50">
              <option value="">Pilih Siswa</option>
              <option v-for="siswa in siswaList" :key="siswa.id" :value="siswa.id">
                {{ siswa.nis || '-' }} - {{ siswa.nama || '-' }} - {{ siswa.rombel || '-' }}
              </option>
            </select>
          </div>

          <!-- Rombel -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Rombel <span class="text-red-500">*</span>
            </label>
            <select v-model="form.idRombel" @change="onRombelChange" required
              class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
              <option value="">Pilih Rombel</option>
              <option v-for="rombel in rombelList" :key="rombel.id" :value="rombel.id">
                {{ rombel.kelas ? rombel.kelas.kode + ' ' : '' }}{{ rombel.nama }}
              </option>
            </select>
            <p v-if="selectedRombel" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
              Sekolah: {{ selectedRombel.sekolah?.nama || '-' }} | Jurusan: {{ selectedRombel.jurusan?.nama || '-' }}
            </p>
          </div>

          <!-- Kelas -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Kelas
            </label>
            <select v-model="form.idKelas"
              class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
              <option value="">Pilih Kelas</option>
              <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">
                {{ kelas.nama || kelas.kode }}
              </option>
            </select>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" @click="closeModal"
              class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
              Batal
            </button>
            <button type="submit" :disabled="submitLoading"
              class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-700 dark:hover:bg-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
              <svg v-if="submitLoading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              {{ submitLoading ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <ConfirmModal :show="showDeleteModal" type="danger" title="Hapus Mapping"
      :message="`Apakah Anda yakin ingin menghapus mapping siswa '${deletingItem?.nama || ''}'?`"
      confirm-text="Ya, Hapus" :loading="deleteLoading" @confirm="confirmDelete" @cancel="showDeleteModal = false" />

    <!-- Toast -->
    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import Layout from '../../components/layout/Layout.vue';
import ConfirmModal from '../../components/ConfirmModal.vue';
import Toast from '../../components/Toast.vue';
import { siswaRombelAPI, siswaAPI, masterDataAPI } from '../../services/api';
import { useDataTable } from '../../composables/useDataTable';
import { useMasterDataCache } from '../../composables/useMasterDataCache';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

const toastRef = ref(null);
const showModal = ref(false);
const showDeleteModal = ref(false);
const deleteLoading = ref(false);
const deletingItem = ref(null);
const editingId = ref(null);
const submitLoading = ref(false);
const formError = ref('');

const { loadAll, getCached } = useMasterDataCache();

// Master data
const cached = getCached();
const siswaList = ref([]);
const rombelList = ref(Array.isArray(cached.rombel) ? [...cached.rombel] : []);
const kelasList = ref(Array.isArray(cached.kelas) ? [...cached.kelas] : []);

// Filters
const filters = reactive({
  idSiswa: '',
  idRombel: '',
});

// Form data
const form = reactive({
  idSiswa: '',
  idRombel: '',
  idKelas: '',
});

const selectedRombel = ref(null);

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
} = useDataTable(siswaRombelAPI.datatable, {
  autoLoad: false,
  columns: [
    { data: 'id', name: 'id', searchable: false, orderable: false },
    { data: 'siswa_nama', name: 'siswa_nama', searchable: true, orderable: true },
    { data: 'rombel_nama', name: 'rombel_nama', searchable: true, orderable: true },
  ],
});

const currentPage = computed(() => Math.floor(params.start / params.length) + 1);
const totalPages = computed(() => Math.ceil(totalRecords.value / params.length));

// Load master data
const loadMasterData = async () => {
  try {
    // Load siswa
    const siswaResponse = await siswaAPI.list({ per_page: 1000 });
    if (siswaResponse.data?.success) {
      siswaList.value = siswaResponse.data.data?.data || [];
    }

    // Load rombel from cache
    const masterData = await loadAll();
    rombelList.value = masterData.rombel || [];
    kelasList.value = masterData.kelas || [];
  } catch (err) {
    console.error('Error loading master data:', err);
  }
};

// Apply filters
const applyFilters = () => {
  params.start = 0;
  Object.assign(params, {
    idSiswa: filters.idSiswa || null,
    idRombel: filters.idRombel || null,
  });
  loadData();
};

// Watch rombel change
const onRombelChange = async () => {
  if (form.idRombel) {
    try {
      const response = await masterDataAPI.rombel.get(form.idRombel);
      if (response.data?.success) {
        selectedRombel.value = response.data.data;
      }
    } catch (err) {
      console.error('Error loading rombel:', err);
    }
  } else {
    selectedRombel.value = null;
  }
};

// Open create modal
const openCreateModal = () => {
  editingId.value = null;
  Object.assign(form, {
    idSiswa: '',
    idRombel: '',
    idKelas: '',
  });
  selectedRombel.value = null;
  formError.value = '';
  showModal.value = true;
};

// Handle edit
const handleEdit = async (id) => {
  try {
    editingId.value = id;
    const response = await siswaRombelAPI.get(id);
    if (response.data?.success) {
      const mapping = response.data.data;
      form.idSiswa = mapping.idSiswa;
      form.idRombel = mapping.idRombel;
      form.idKelas = mapping.idKelas || '';
      await onRombelChange();
      formError.value = '';
      showModal.value = true;
    }
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal memuat data mapping');
  }
};

// Handle delete
const handleDelete = (id, nama) => {
  deletingItem.value = { id, nama };
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!deletingItem.value) return;

  try {
    deleteLoading.value = true;
    await siswaRombelAPI.delete(deletingItem.value.id);
    showDeleteModal.value = false;
    deletingItem.value = null;
    toastRef.value?.success('Mapping berhasil dihapus');
    loadData();
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal menghapus mapping');
  } finally {
    deleteLoading.value = false;
  }
};

// Handle submit
const handleSubmit = async () => {
  try {
    submitLoading.value = true;
    formError.value = '';

    const payload = {
      idSiswa: form.idSiswa,
      idRombel: form.idRombel,
      idKelas: form.idKelas || null,
    };

    if (editingId.value) {
      await siswaRombelAPI.update(editingId.value, payload);
      toastRef.value?.success('Mapping berhasil diperbarui');
    } else {
      await siswaRombelAPI.create(payload);
      toastRef.value?.success('Mapping berhasil ditambahkan');
    }

    closeModal();
    loadData();
  } catch (err) {
    formError.value = err.response?.data?.message || 'Terjadi kesalahan';
    toastRef.value?.error(formError.value);
  } finally {
    submitLoading.value = false;
  }
};

// Close modal
const closeModal = () => {
  showModal.value = false;
  editingId.value = null;
  Object.assign(form, {
    idSiswa: '',
    idRombel: '',
    idKelas: '',
  });
  selectedRombel.value = null;
  formError.value = '';
};

onMounted(async () => {
  await loadMasterData();
  loadData();
});
</script>
