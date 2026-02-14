<template>
  <Layout :active-menu="'Students'">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-6 md:p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                <ArrowsRightLeftIcon class="w-6 h-6 md:w-8 md:h-8 text-white" />
              </div>
              Mapping Rombel Siswa
            </h1>
            <p class="mt-2 text-indigo-100 text-sm md:text-base">Pilih siswa yang belum memiliki rombel dan mapping ke
              rombel yang dipilih</p>
          </div>
          <button @click="$router.push('/siswa')"
            class="group relative inline-flex items-center gap-2 px-4 md:px-6 py-2.5 md:py-3 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-xl shadow-lg hover:bg-white/30 transform hover:-translate-y-0.5 transition-all duration-200 text-sm md:text-base">
            <ArrowLeftIcon class="w-4 h-4 md:w-5 md:h-5" />
            Kembali ke Data Siswa
          </button>
        </div>
      </div>

      <!-- Selection Section -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Pilih Rombel</h2>
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-600 dark:text-gray-400">
                Terpilih: <strong class="text-indigo-600 dark:text-indigo-400">{{ selectedSiswaIds.length }}</strong>
                siswa
              </span>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Rombel *</label>
            <select v-model="selectedRombelId"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white">
              <option value="">Pilih Rombel</option>
              <option v-for="rombel in rombelList" :key="rombel.id" :value="rombel.id">
                {{ rombel.kelas ? rombel.kelas.kode + ' ' : '' }}{{ rombel.nama }} ({{ rombel.kode || '-' }})
              </option>
            </select>
          </div>

          <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
              <button @click="selectAll"
                class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors">
                Pilih Semua
              </button>
              <button @click="deselectAll"
                class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                Batal Pilih Semua
              </button>
            </div>
            <button @click="handleBatchMapping" :disabled="!canMap || mappingLoading"
              class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-2">
              <svg v-if="mappingLoading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              <CheckIcon v-else class="w-5 h-5" />
              {{ mappingLoading ? 'Memproses...' : `Mapping ${selectedSiswaIds.length} Siswa` }}
            </button>
          </div>
        </div>
      </div>

      <!-- Students List -->
      <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Siswa yang Belum Memiliki Rombel</h2>
            <div class="flex items-center gap-2">
              <MagnifyingGlassIcon class="w-5 h-5 text-gray-400" />
              <input type="text" v-model="searchQuery" @input="handleSearch" placeholder="Cari siswa..."
                class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white" />
            </div>
          </div>
        </div>

        <div v-if="loading" class="p-12 text-center">
          <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
          </svg>
          <p class="mt-4 text-gray-600 dark:text-gray-400">Memuat data siswa...</p>
        </div>

        <div v-else-if="siswaList.length === 0" class="p-12 text-center">
          <InboxStackIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tidak ada siswa</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ searchQuery ? 'Tidak ada siswa yang sesuai dengan pencarian' : 'Semua siswa sudah memiliki rombel' }}
          </p>
        </div>

        <div v-else>
          <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <div v-for="siswa in siswaList" :key="siswa.id"
              class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
              <div class="flex items-center gap-4">
                <input type="checkbox" :value="siswa.id" v-model="selectedSiswaIds"
                  class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" />
                <div class="flex-1">
                  <h3 class="font-semibold text-gray-900 dark:text-white">{{ siswa.nama }}</h3>
                  <div class="flex items-center gap-4 mt-1 text-sm text-gray-600 dark:text-gray-400">
                    <span>NIS: {{ siswa.nis || '-' }}</span>
                    <span>NISN: {{ siswa.nisn || '-' }}</span>
                    <span v-if="siswa.username">Username: {{ siswa.username }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="totalRecords > 0"
            class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="text-sm text-gray-600 dark:text-gray-400">
              Menampilkan {{ (currentPage - 1) * perPage + 1 }} - {{ Math.min(currentPage * perPage, totalRecords) }}
              dari {{ totalRecords }} siswa
            </div>
            <div v-if="totalPages > 1" class="flex items-center gap-2">
              <button @click="currentPage = Math.max(1, currentPage - 1); loadUnmappedStudents()"
                :disabled="currentPage === 1"
                class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Sebelumnya
              </button>
              <span class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                Halaman {{ currentPage }} dari {{ totalPages }}
              </span>
              <button @click="currentPage = Math.min(totalPages, currentPage + 1); loadUnmappedStudents()"
                :disabled="currentPage === totalPages"
                class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Selanjutnya
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <Toast ref="toastRef" />

    <ConfirmModal :show="showConfirm" title="Konfirmasi Mapping Rombel" :message="confirmMessage" type="warning"
      confirm-text="Ya, Map Sekarang" :loading="mappingLoading" @confirm="executeBatchMapping"
      @cancel="showConfirm = false" />
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Layout from '../../components/layout/Layout.vue';
import Toast from '../../components/Toast.vue';
import ConfirmModal from '../../components/ConfirmModal.vue';
import { siswaRombelAPI, masterDataAPI } from '../../services/api';
import { useMasterDataCache } from '../../composables/useMasterDataCache';
import { useRoleAccess } from '../../composables/useRoleAccess';
import { debounce } from '../../composables/useDebounce';
import {
  ArrowsRightLeftIcon,
  ArrowLeftIcon,
  CheckIcon,
  MagnifyingGlassIcon,
  InboxStackIcon,
} from '@heroicons/vue/24/outline';

const router = useRouter();
const toastRef = ref(null);
const { canCreateData } = useRoleAccess();
const { loadAll, getCached } = useMasterDataCache();

// Data
const siswaList = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const selectedSiswaIds = ref([]);
const selectedRombelId = ref('');
const mappingLoading = ref(false);
const currentPage = ref(1);
const perPage = ref(20);
const totalRecords = ref(0);

// Confirmation
const showConfirm = ref(false);
const confirmMessage = computed(() => {
  const rombel = rombelList.value.find(r => r.id === selectedRombelId.value);
  const rombelName = rombel ? `${rombel.kelas?.kode || ''} ${rombel.nama}`.trim() : 'rombel terpilih';
  return `Apakah Anda yakin ingin memindahkan <strong>${selectedSiswaIds.value.length} siswa</strong> ke rombel <strong>${rombelName}</strong>?`;
});

// Master data
const cached = getCached();
const rombelList = ref(Array.isArray(cached.rombel) ? [...cached.rombel] : []);

// Computed
const totalPages = computed(() => {
  return Math.ceil(totalRecords.value / perPage.value);
});

const canMap = computed(() => {
  return selectedRombelId.value && selectedSiswaIds.value.length > 0 && canCreateData;
});

// Methods
const loadUnmappedStudents = async () => {
  loading.value = true;
  try {
    const response = await siswaRombelAPI.getUnmapped({
      search: searchQuery.value || '',
      page: currentPage.value,
      per_page: perPage.value,
    });
    if (response.data?.success) {
      const result = response.data.data;
      if (result && typeof result === 'object' && 'data' in result) {
        // Paginated response
        siswaList.value = result.data || [];
        totalRecords.value = result.total || 0;
        // Update current page if needed
        if (result.current_page) {
          currentPage.value = result.current_page;
        }
      } else if (Array.isArray(result)) {
        // Fallback: if result is array, treat as non-paginated
        siswaList.value = result;
        totalRecords.value = result.length;
      } else {
        siswaList.value = [];
        totalRecords.value = 0;
      }
    } else {
      // If response is not successful, log and show error
      console.error('Failed to load unmapped students:', response.data);
      siswaList.value = [];
      totalRecords.value = 0;
    }
  } catch (err) {
    console.error('Error loading unmapped students:', err);
    const errorMessage = err.response?.data?.message || err.message || 'Gagal memuat data siswa';
    toastRef.value?.error(errorMessage);
    siswaList.value = [];
    totalRecords.value = 0;
  } finally {
    loading.value = false;
  }
};

const loadMasterData = async () => {
  try {
    const masterData = await loadAll();
    rombelList.value = masterData.rombel || [];
  } catch (err) {
    console.error('Error loading master data:', err);
  }
};

const handleSearch = debounce(() => {
  currentPage.value = 1; // Reset to first page on search
  loadUnmappedStudents();
}, 300);

const selectAll = () => {
  selectedSiswaIds.value = siswaList.value.map(siswa => siswa.id);
};

const deselectAll = () => {
  selectedSiswaIds.value = [];
};

const handleBatchMapping = () => {
  if (!canMap.value) {
    toastRef.value?.error('Pilih rombel dan minimal satu siswa');
    return;
  }
  showConfirm.value = true;
};

const executeBatchMapping = async () => {
  mappingLoading.value = true;
  try {
    const response = await siswaRombelAPI.batchCreate({
      siswa_ids: selectedSiswaIds.value,
      idRombel: selectedRombelId.value,
    });

    if (response.data?.success) {
      const result = response.data.data;
      const successCount = result.created?.length || 0;
      const errorCount = result.errors?.length || 0;

      if (errorCount > 0) {
        toastRef.value?.warning(`Mapping berhasil untuk ${successCount} siswa. ${errorCount} siswa gagal.`);
      } else {
        toastRef.value?.success(`Mapping berhasil untuk ${successCount} siswa`);
      }

      // Reload unmapped students
      await loadUnmappedStudents();
      selectedSiswaIds.value = [];
      selectedRombelId.value = '';
      showConfirm.value = false;
    } else {
      toastRef.value?.error(response.data?.message || 'Gagal melakukan mapping');
    }
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal melakukan mapping');
  } finally {
    mappingLoading.value = false;
  }
};

onMounted(async () => {
  await Promise.all([
    loadUnmappedStudents(),
    loadMasterData(),
  ]);
});
</script>

<style scoped>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
