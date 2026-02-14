<template>
  <Layout :active-menu="'Students'">
    <div class="space-y-6">
      <!-- Header -->
      <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-600 via-orange-600 to-red-600 p-6 md:p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                <AcademicCapIcon class="w-6 h-6 md:w-8 md:h-8 text-white" />
              </div>
              Data Alumni
            </h1>
            <p class="mt-2 text-amber-100 text-sm md:text-base">Lihat dan kelola data siswa yang telah lulus (Alumni)
            </p>
          </div>
        </div>
      </div>

      <!-- Filter Section -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Tahun Angkatan (Mandatory) -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              Tahun Angkatan / Lulus <span class="text-red-500">*</span>
            </label>
            <input type="text" v-model="filters.tahunAngkatan" placeholder="Contoh: 2024 atau 24"
              class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white"
              @input="handleFilterChange" />
            <p class="mt-1 text-[10px] text-gray-500 italic">Bisa ketik 2024 atau 24</p>
          </div>

          <!-- Jurusan (Mandatory) -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              {{ labelJurusan }} <span class="text-red-500">*</span>
            </label>
            <select v-model="filters.idJurusan" @change="handleFilterChange"
              class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white">
              <option value="">Pilih {{ labelJurusan }}...</option>
              <option v-for="j in jurusanList" :key="j.id" :value="j.id">{{ j.nama }}</option>
            </select>
          </div>

          <!-- Search (Optional) -->
          <div v-if="isFilterSelected">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Cari
              Nama/NIS/Username</label>
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input type="text" v-model="searchQuery" @input="handleLocalSearch" placeholder="Cari..."
                class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white" />
            </div>
          </div>
        </div>
      </div>

      <!-- Data Section -->
      <div v-if="isFilterSelected"
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase italic">#
                </th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase">Nama / NIS
                </th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase">Username
                </th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase">Tahun</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase">Jurusan &
                  Rombel</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase">JK</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="loading" class="animate-pulse">
                <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                  <div class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-amber-500" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                      </path>
                    </svg>
                    Memuat data alumni...
                  </div>
                </td>
              </tr>
              <tr v-else-if="data.length === 0">
                <td colspan="8" class="px-4 py-12 text-center">
                  <InboxIcon class="w-12 h-12 text-gray-300 mx-auto mb-2" />
                  <p class="text-gray-500 dark:text-gray-400">Tidak ada data alumni ditemukan untuk filter ini.</p>
                </td>
              </tr>
              <tr v-else v-for="(item, index) in data" :key="item.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="px-4 py-3 text-gray-500 italic">{{ params.start + index + 1 }}</td>
                <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">
                  <div>{{ item.nama }}</div>
                  <div class="text-[10px] text-gray-400 font-mono">{{ item.nis || '-' }}</div>
                </td>
                <td class="px-4 py-3 text-amber-600 dark:text-amber-400 font-semibold text-xs">{{ item.username || '-'
                }}</td>
                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-mono font-bold">{{ item.tahunAngkatan || '-'
                }}</td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                  <div class="font-medium">{{ item.jurusan_info || '-' }}</div>
                  <div class="text-[10px] text-gray-400 font-bold uppercase">{{ item.rombel_info || '-' }}</div>
                </td>
                <td class="px-4 py-3">
                  <div v-html="item.isActive_badge"></div>
                </td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ item.jsk_label }}</td>
                <td class="px-4 py-3 text-blue-600 dark:text-blue-400 hover:underline cursor-pointer font-medium"
                  @click="handleEdit(item.id)">
                  Detail
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalRecords > 0"
          class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <div class="text-xs text-gray-500">
            Menampilkan {{ params.start + 1 }} - {{ params.start + data.length }} dari {{ totalRecords }}
          </div>
          <div class="flex gap-2">
            <button @click="handlePageChange(currentPage - 1)" :disabled="currentPage === 1"
              class="px-3 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-xs disabled:opacity-50">Prev</button>
            <button @click="handlePageChange(currentPage + 1)" :disabled="currentPage === totalPages"
              class="px-3 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-xs disabled:opacity-50">Next</button>
          </div>
        </div>
      </div>

      <!-- Empty State / Warning -->
      <div v-else
        class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl p-12 text-center border-2 border-dashed border-amber-200 dark:border-amber-800">
        <ExclamationCircleIcon class="w-16 h-16 text-amber-500 mx-auto mb-4 opacity-50" />
        <h3 class="text-lg font-bold text-amber-800 dark:text-amber-200 mb-2">Filter Diperlukan</h3>
        <p class="text-amber-700 dark:text-amber-300 max-w-md mx-auto">
          Silakan isi <strong>Tahun Angkatan</strong> dan pilih <strong>{{ labelJurusan }}</strong> untuk menampilkan
          data alumni.
        </p>
      </div>
    </div>

    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Layout from '../../components/layout/Layout.vue';
import Toast from '../../components/Toast.vue';
import { siswaAPI, masterDataAPI } from '../../services/api';
import { useDataTable } from '../../composables/useDataTable';
import { useMasterDataCache } from '../../composables/useMasterDataCache';
import { debounce } from '../../composables/useDebounce';
import {
  AcademicCapIcon,
  MagnifyingGlassIcon,
  InboxIcon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/outline';

const router = useRouter();
const toastRef = ref(null);
const { loadAll, getCached } = useMasterDataCache();

// Filters
const filters = reactive({
  tahunAngkatan: '',
  idJurusan: '',
});

// Master Data
const cached = getCached();
const jurusanList = ref(Array.isArray(cached.jurusan) ? [...cached.jurusan] : []);
const labelJurusan = ref(cached.labelJurusan || 'Jurusan');

const isFilterSelected = computed(() => {
  return filters.tahunAngkatan.trim() !== '' && filters.idJurusan !== '';
});

// DataTable
const {
  data,
  totalRecords,
  loading,
  params,
  loadData,
  handlePageChange,
  handleSearch,
} = useDataTable(siswaAPI.datatable, {
  autoLoad: false,
  columns: [
    { data: 'nama', name: 'nama', searchable: true, orderable: true },
    { data: 'username', name: 'username', searchable: true, orderable: true },
    { data: 'tahunAngkatan', name: 'tahunAngkatan', searchable: true, orderable: true },
    { data: 'idJurusan', name: 'idJurusan', searchable: false, orderable: false },
    { data: 'jsk_label', name: 'jsk', searchable: false, orderable: true },
    { data: 'action', name: 'action', searchable: false, orderable: false },
  ],
});

const searchQuery = ref('');
const currentPage = computed(() => Math.floor(params.start / params.length) + 1);
const totalPages = computed(() => Math.ceil(totalRecords.value / params.length));

const handleFilterChange = debounce(() => {
  if (isFilterSelected.value) {
    applyFilters();
  }
}, 300);

const applyFilters = () => {
  params.start = 0;
  Object.assign(params, {
    tahunAngkatan: filters.tahunAngkatan,
    idJurusan: filters.idJurusan,
    isActive: null, // Don't filter by isActive status, use isAlumni instead
    isAlumni: 1,
  });
  loadData();
};

const handleLocalSearch = debounce((val) => {
  handleSearch(val);
}, 300);

const handleEdit = (id) => {
  router.push(`/siswa/${id}/edit`);
};

onMounted(async () => {
  const result = await loadAll();
  jurusanList.value = result.jurusan;
  labelJurusan.value = result.labelJurusan || 'Jurusan';
});
</script>

<style scoped>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
