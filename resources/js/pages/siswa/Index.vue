<template>
  <Layout :active-menu="'Students'">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-6 md:p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                <UserGroupIcon class="w-6 h-6 md:w-8 md:h-8 text-white" />
              </div>
              Data Siswa
            </h1>
            <p class="mt-2 text-blue-100 text-sm md:text-base">Kelola data siswa dan mapping rombel dengan mudah dan efisien</p>
          </div>
          <div class="flex items-center gap-3 flex-wrap">
            <button
              @click="$router.push('/siswa-rombel/mapping')"
              class="group relative inline-flex items-center gap-2 px-4 md:px-6 py-2.5 md:py-3 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-xl shadow-lg hover:bg-white/30 transform hover:-translate-y-0.5 transition-all duration-200 text-sm md:text-base"
            >
              <ArrowsRightLeftIcon class="w-4 h-4 md:w-5 md:h-5 transition-transform group-hover:rotate-90 duration-300" />
              Mapping Rombel
            </button>
            <button
              v-if="canCreateData"
              @click="handleAdd"
              class="group relative inline-flex items-center gap-2 px-4 md:px-6 py-2.5 md:py-3 bg-white text-blue-600 font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 text-sm md:text-base"
            >
              <PlusIcon class="w-4 h-4 md:w-5 md:h-5 transition-transform group-hover:rotate-90 duration-300" />
              Tambah Siswa
            </button>
          </div>
        </div>
      </div>

      <!-- Data Siswa Content -->
      <div class="space-y-4">

      <!-- Filter Section -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
          <!-- Search -->
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              type="text"
              v-model="searchQuery"
              @input="handleSearch(searchQuery)"
              placeholder="Cari siswa..."
              class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            />
          </div>

          <!-- Filter Kelas -->
          <select
            v-model="filters.idKelas"
            @change="applyFilters"
            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
          >
            <option value="">Semua Kelas</option>
            <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">{{ kelas.nama || kelas.kode }}</option>
          </select>

          <!-- Filter Group (Jurusan) -->
          <select
            v-model="filters.idJurusan"
            @change="applyFilters"
            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
          >
            <option value="">Semua {{ labelJurusan }}</option>
            <option v-for="jurusan in jurusanList" :key="jurusan.id" :value="jurusan.id">{{ jurusan.nama }}</option>
          </select>

          <!-- Filter Status -->
          <select
            v-model="filters.isActive"
            @change="applyFilters"
            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
          >
            <option :value="null">Semua Status</option>
            <option :value="1">Aktif</option>
            <option :value="0">Tidak Aktif</option>
            <option :value="2">Off</option>
          </select>
        </div>
      </div>

      <!-- DataTable Section -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <!-- Search & Per Page Bar -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-4">
          <div class="flex-1">
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                type="text"
                v-model="searchQuery"
                @input="handleSearch(searchQuery)"
                placeholder="Cari siswa..."
                class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              />
            </div>
          </div>
          <select
            v-model="params.length"
            @change="handlePerpageChange(params.length)"
            class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
          >
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
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Nama</th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">NISN</th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">JK</th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Rombel</th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Status</th>
                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="loading" class="bg-white dark:bg-gray-800">
                <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memuat...
                  </div>
                </td>
              </tr>
              <tr v-else-if="error" class="bg-white dark:bg-gray-800">
                <td colspan="7" class="px-4 py-8 text-center text-red-600 dark:text-red-400">
                  Error: {{ error.message }}
                </td>
              </tr>
              <tr v-else-if="data.length === 0" class="bg-white dark:bg-gray-800">
                <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                  Tidak ada data
                </td>
              </tr>
              <tr
                v-else
                v-for="(student, index) in data"
                :key="student.id"
                class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
              >
                <td class="px-4 py-2.5 text-gray-900 dark:text-gray-100">{{ params.start + index + 1 }}</td>
                <td class="px-4 py-2.5">
                  <div class="font-medium text-gray-900 dark:text-white">{{ student.nama }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ student.nis || '-' }}</div>
                </td>
                <td class="px-4 py-2.5 text-gray-900 dark:text-gray-100">{{ student.nisn || '-' }}</td>
                <td class="px-4 py-2.5 text-gray-900 dark:text-gray-100">{{ student.jsk_label }}</td>
                <td class="px-4 py-2.5 text-gray-900 dark:text-gray-100">{{ student.rombel_info || '-' }}</td>
                <td class="px-4 py-2.5" v-html="student.isActive_badge"></td>
                <td class="px-4 py-2.5">
                  <div class="flex items-center gap-2">
                    <button
                      @click="handleEdit(student.id)"
                      class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors cursor-pointer"
                      title="Edit"
                    >
                      <PencilIcon class="w-4 h-4" />
                    </button>
                    <button
                      @click="handleDelete(student.id, student.nama)"
                      class="p-1.5 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors cursor-pointer"
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

        <!-- Compact Pagination -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <div class="text-xs text-gray-700 dark:text-gray-300">
            Menampilkan <span class="font-medium">{{ params.start + 1 }}</span> -
            <span class="font-medium">{{ params.start + data.length }}</span> dari
            <span class="font-medium">{{ totalRecords }}</span>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="handlePageChange(currentPage - 1)"
              :disabled="currentPage === 1 || loading"
              class="px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Prev
            </button>
            <span class="px-2.5 py-1.5 text-xs text-gray-700 dark:text-gray-300">
              {{ currentPage }} / {{ totalPages }}
            </span>
            <button
              @click="handlePageChange(currentPage + 1)"
              :disabled="currentPage === totalPages || loading"
              class="px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Next
            </button>
          </div>
        </div>
        </div>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <ConfirmModal
      :show="showDeleteModal"
      type="danger"
      title="Hapus Siswa"
      :message="`Apakah Anda yakin ingin menghapus siswa '${deletingItem?.nama || ''}'?`"
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
import { useRouter } from 'vue-router';
import Layout from '../../components/layout/Layout.vue';
import ConfirmModal from '../../components/ConfirmModal.vue';
import Toast from '../../components/Toast.vue';
import { siswaAPI } from '../../services/api';
import { useDataTable } from '../../composables/useDataTable';
import { useMasterDataCache } from '../../composables/useMasterDataCache';
import { useRoleAccess } from '../../composables/useRoleAccess';
import { PlusIcon, MagnifyingGlassIcon, PencilIcon, TrashIcon, UserGroupIcon, ArrowsRightLeftIcon } from '@heroicons/vue/24/outline';

const router = useRouter();
const toastRef = ref(null);
const showDeleteModal = ref(false);
const deleteLoading = ref(false);
const deletingItem = ref(null);


// Use master data cache
const { loadAll, getCached } = useMasterDataCache();
const { canCreateData, canEditData, canDeleteData } = useRoleAccess();

// Master data - get from cache immediately (synchronous)
// This ensures filters are populated instantly if data is already cached
const cached = getCached();
const kelasList = ref(Array.isArray(cached.kelas) ? [...cached.kelas] : []);
const jurusanList = ref(Array.isArray(cached.jurusan) ? [...cached.jurusan] : []);
const rombelList = ref(Array.isArray(cached.rombel) ? [...cached.rombel] : []);
const labelJurusan = ref(cached.labelJurusan || 'Group');

// Filters
const filters = reactive({
  idKelas: '',
  idJurusan: '',
  idRombel: '',
  isActive: null,
});

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
} = useDataTable(siswaAPI.datatable, {
  autoLoad: false, // Disable auto-load, we'll load after master data is ready
  columns: [
    { data: 'id', name: 'id', searchable: false, orderable: false },
    { data: 'nama', name: 'nama', searchable: true, orderable: true },
    { data: 'nisn', name: 'nisn', searchable: true, orderable: true },
    { data: 'jsk_label', name: 'jsk', searchable: false, orderable: true },
    { data: 'rombel_info', name: 'rombel_info', searchable: false, orderable: false },
    { data: 'isActive_badge', name: 'isActive', searchable: false, orderable: true },
    { data: 'action', name: 'action', searchable: false, orderable: false },
  ],
});

const searchQuery = ref(params.search.value);

const currentPage = computed(() => Math.floor(params.start / params.length) + 1);
const totalPages = computed(() => Math.ceil(totalRecords.value / params.length));

// Load master data (will use cache if already loaded)
const loadMasterData = async () => {
  try {
    const result = await loadAll();

    // Update refs with cached/loaded data
    kelasList.value = result.kelas;
    jurusanList.value = result.jurusan;
    rombelList.value = result.rombel;
    labelJurusan.value = result.labelJurusan;

    console.log('Master data loaded (from cache or API):', {
      kelas: kelasList.value.length,
      jurusan: jurusanList.value.length,
      rombel: rombelList.value.length,
      labelJurusan: labelJurusan.value
    });
  } catch (err) {
    console.error('Error loading master data:', err);
    // Keep existing cached data or empty arrays
    if (kelasList.value.length === 0) kelasList.value = [];
    if (jurusanList.value.length === 0) jurusanList.value = [];
    if (rombelList.value.length === 0) rombelList.value = [];
  }
};

// Apply filters
const applyFilters = () => {
  params.start = 0; // Reset to first page
  // Add filters to params
  Object.assign(params, {
    idKelas: filters.idKelas || null,
    idJurusan: filters.idJurusan || null,
    idRombel: filters.idRombel || null,
    isActive: filters.isActive,
  });
  loadData();
};

const handleAdd = () => {
  router.push('/siswa/create');
};

const handleEdit = (id) => {
  router.push(`/siswa/${id}/edit`);
};

const handleDelete = (id, nama) => {
  deletingItem.value = { id, nama };
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!deletingItem.value) return;
  
  try {
    deleteLoading.value = true;
    await siswaAPI.delete(deletingItem.value.id);
    showDeleteModal.value = false;
    deletingItem.value = null;
    toastRef.value?.success('Siswa berhasil dihapus');
    loadData();
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal menghapus siswa');
  } finally {
    deleteLoading.value = false;
  }
};


onMounted(async () => {
  // Load master data first (will use cache if already loaded)
  // This ensures filters are populated immediately without waiting for siswa data
  // Master data loading is fast because it uses cache
  await loadMasterData();

  // Now load siswa data after master data is ready
  // This ensures filters are already populated when siswa data loads
  loadData();
});
</script>

<style scoped>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
