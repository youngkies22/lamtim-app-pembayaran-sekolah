<template>
  <Layout :active-menu="'Students'">
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl shadow-lg">
              <UserMinusIcon class="w-8 h-8 text-white" />
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Jadikan Alumni</h1>
              <p class="text-sm text-gray-500 dark:text-gray-400">Pilih siswa (Aktif & Keluar) tingkat akhir untuk
                dijadikan alumni</p>
            </div>
          </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
          <!-- Filter: Rombel (only final kelas) -->
          <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              Pilih Rombel Tingkat Akhir <span class="text-red-500">*</span>
            </label>

            <div v-if="filteredRombelList.length === 0 && rombelList.length > 0"
              class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl border border-yellow-200 dark:border-yellow-800 text-sm text-yellow-700 dark:text-yellow-300 text-center">
              Tidak ada rombel tingkat akhir yang aktif (6, 9, 12, XII)
            </div>

            <SearchableSelect v-else v-model="selectedRombelId" :options="filteredRombelList"
              placeholder="Cari dan pilih rombel kelas akhir..." search-placeholder="Ketik nama rombel atau kelas..."
              label-key="displayLabel" value-key="id" @change="onRombelChange" />
          </div>

          <!-- Student List -->
          <div v-if="selectedRombelId">
            <div class="flex items-center justify-between mb-3">
              <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                Siswa (Belum Lulus)
                <span v-if="students.length" class="text-gray-500 font-normal">({{ selectedSiswaIds.length }}/{{
                  students.length }} dipilih)</span>
              </label>
              <button v-if="students.length > 0" type="button"
                class="text-xs font-medium text-amber-600 dark:text-amber-400 hover:underline" @click="toggleSelectAll">
                {{ selectedSiswaIds.length === students.length ? 'Batal Semua' : 'Pilih Semua' }}
              </button>
            </div>

            <!-- Loading -->
            <div v-if="loadingStudents" class="p-8 text-center text-gray-500 dark:text-gray-400">
              <svg class="animate-spin h-6 w-6 mx-auto mb-2 text-amber-500" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              Memuat siswa...
            </div>

            <!-- Empty -->
            <div v-else-if="students.length === 0"
              class="p-6 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl border border-yellow-200 dark:border-yellow-800 text-sm text-yellow-700 dark:text-yellow-300 text-center">
              Tidak ada siswa aktif di rombel ini
            </div>

            <!-- Student Table -->
            <div v-else
              class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden max-h-96 overflow-y-auto">
              <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                  <tr>
                    <th class="w-10 px-3 py-2.5 text-center">
                      <input type="checkbox"
                        :checked="selectedSiswaIds.length === students.length && students.length > 0"
                        @change="toggleSelectAll" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                    </th>
                    <th class="px-3 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">
                      Username</th>
                    <th class="px-3 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">
                      Nama</th>
                    <th class="px-3 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">
                      Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="s in students" :key="s.username"
                    :class="['transition-colors cursor-pointer', selectedSiswaIds.includes(s.id) ? 'bg-amber-50 dark:bg-amber-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50']"
                    @click="toggleStudent(s.id)">
                    <td class="w-10 px-3 py-2.5 text-center">
                      <input type="checkbox" :checked="selectedSiswaIds.includes(s.id)"
                        @click.stop="toggleStudent(s.id)"
                        class="rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                    </td>
                    <td class="px-3 py-2.5 text-amber-600 dark:text-amber-400 font-semibold text-xs">{{ s.username }}
                    </td>
                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300 font-mono text-xs">{{ s.nis || '-' }}</td>
                    <td class="px-3 py-2.5 text-gray-900 dark:text-white font-medium">{{ s.nama }}</td>
                    <td class="px-3 py-2.5">
                      <span v-if="s.isActive === 1"
                        class="px-2 py-0.5 text-[10px] font-bold rounded bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">AKTIF</span>
                      <span v-else
                        class="px-2 py-0.5 text-[10px] font-bold rounded bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400">OFF</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Confirmation & Submit -->
          <div v-if="selectedSiswaIds.length > 0" class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div
              class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800 mb-4 shadow-sm">
              <div class="flex items-start gap-3">
                <ExclamationTriangleIcon class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                <div>
                  <p class="text-sm font-semibold text-amber-800 dark:text-amber-200">Perhatian</p>
                  <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                    <strong>{{ selectedSiswaIds.length }} siswa</strong> akan dijadikan alumni (status akhir akan
                    menjadi Alumni, namun tetap mempertahankan status Aktif/Off asli mereka).
                  </p>
                </div>
              </div>
            </div>

            <div class="flex justify-end">
              <button type="button" :disabled="submitting"
                class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                @click="handleSubmit">
                <UserMinusIcon class="w-5 h-5" />
                {{ submitting ? 'Memproses...' : 'Konfirmasi Jadikan Alumni' }}
              </button>
            </div>
          </div>

          <!-- Success Result -->
          <div v-if="result"
            class="mt-6 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800">
            <div class="flex items-center gap-2 text-green-800 dark:text-green-200">
              <CheckCircleIcon class="w-5 h-5" />
              <p class="font-semibold">Berhasil!</p>
            </div>
            <p class="mt-1 text-sm text-green-700 dark:text-green-300">
              {{ result.processed_count }} siswa berhasil dijadikan alumni.
            </p>
            <div v-if="result.errors && result.errors.length" class="mt-2">
              <p class="text-xs text-yellow-700 dark:text-yellow-300 font-medium">Peringatan:</p>
              <ul class="ml-4 list-disc text-xs text-yellow-600 dark:text-yellow-400">
                <li v-for="(err, i) in result.errors" :key="i">{{ err }}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Toast ref="toastRef" />

    <ConfirmModal :show="showConfirm" title="Konfirmasi Jadikan Alumni" :message="confirmMessage"
      confirm-text="Ya, Jadikan Alumni" cancel-text="Batal" @confirm="executeMarkAlumni"
      @cancel="showConfirm = false" />
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Layout from '../../components/layout/Layout.vue';
import SearchableSelect from '../../components/SearchableSelect.vue';
import ConfirmModal from '../../components/ConfirmModal.vue';
import Toast from '../../components/Toast.vue';
import { siswaAPI, siswaRombelAPI, masterDataAPI, cacheAPI } from '../../services/api';
import { useAppSettings } from '../../composables/useAppSettings';
import { useMasterDataCache } from '../../composables/useMasterDataCache';
import { UserMinusIcon, ExclamationTriangleIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

const KELAS_CATEGORIES = {
  'SD': ['1', '2', '3', '4', '5', '6'],
  'SMP': ['7', '8', '9'],
  'SMA_ARABIC': ['10', '11', '12'],
  'SMA_ROMAN': ['X', 'XI', 'XII'],
};

const isFinalKelas = (current) => {
  for (const cat in KELAS_CATEGORIES) {
    const list = KELAS_CATEGORIES[cat];
    if (list[list.length - 1] === current) return true;
  }
  return false;
};

const { appSettings } = useAppSettings();
const { clearCache } = useMasterDataCache();
const toastRef = ref(null);

const rombelList = ref([]);
const selectedRombelId = ref('');
const students = ref([]);
const selectedSiswaIds = ref([]);
const loadingStudents = ref(false);
const submitting = ref(false);
const result = ref(null);
const showConfirm = ref(false);

const confirmMessage = computed(() => {
  return `Apakah Anda yakin ingin menjadikan <strong>${selectedSiswaIds.value.length} siswa</strong> sebagai alumni? Proses ini akan menonaktifkan status mereka dan menandai mereka sebagai lulus.`;
});

// Filter rombels: only final kelas
const filteredRombelList = computed(() => {
  return rombelList.value
    .filter(r => {
      const kelasKode = r.kelas?.kode || '';
      return kelasKode && isFinalKelas(kelasKode);
    })
    .map(r => ({
      ...r,
      displayLabel: formatRombelOption(r)
    }));
});

const formatRombelOption = (r) => {
  const kelasKode = r.kelas?.kode || '';
  const parts = [kelasKode, r.nama].filter(Boolean);
  return parts.join(' ') || r.nama;
};

const loadRombelList = async () => {
  try {
    const response = await masterDataAPI.rombel.select({
      isActive: 1,
      hasNonAlumniStudents: 1
    });
    rombelList.value = response.data?.data || response.data || [];
  } catch (err) {
    console.error('Error loading rombel list:', err);
    toastRef.value?.error('Gagal memuat data rombel');
  }
};

const onRombelChange = async () => {
  students.value = [];
  selectedSiswaIds.value = [];
  result.value = null;

  if (!selectedRombelId.value) return;

  try {
    loadingStudents.value = true;
    const response = await siswaRombelAPI.list({ idRombel: selectedRombelId.value });
    const mappings = response.data?.data || response.data || [];
    students.value = mappings
      .filter(m => m.siswa && m.siswa.isAlumni === 0)
      .map(m => ({
        id: m.siswa.id,
        username: m.siswa.username,
        nis: m.siswa.nis,
        nama: m.siswa.nama,
        isActive: m.siswa.isActive
      }))
      .sort((a, b) => (a.username || '').localeCompare(b.username || ''));
  } catch (err) {
    console.error('Error loading students:', err);
    toastRef.value?.error('Gagal memuat data siswa');
  } finally {
    loadingStudents.value = false;
  }
};

const toggleStudent = (id) => {
  const idx = selectedSiswaIds.value.indexOf(id);
  if (idx === -1) selectedSiswaIds.value.push(id);
  else selectedSiswaIds.value.splice(idx, 1);
};

const toggleSelectAll = () => {
  if (selectedSiswaIds.value.length === students.value.length) selectedSiswaIds.value = [];
  else selectedSiswaIds.value = students.value.map(s => s.id);
};

const handleSubmit = () => {
  if (selectedSiswaIds.value.length === 0) return;
  showConfirm.value = true;
};

const executeMarkAlumni = async () => {
  showConfirm.value = false;
  try {
    submitting.value = true;
    result.value = null;

    const response = await siswaAPI.markAsAlumni({
      siswa_ids: selectedSiswaIds.value,
    });

    result.value = response.data?.data || response.data;
    toastRef.value?.success('Siswa berhasil dijadikan alumni!');

    selectedSiswaIds.value = [];
    selectedRombelId.value = ''; // Reset selection

    // Clear frontend and backend cache
    clearCache('rombel');
    try { await cacheAPI.clearLaravel(); } catch (e) { console.error('Cache clear failed', e); }

    await loadRombelList(); // Reload list to hide empty rombels
    await onRombelChange();
  } catch (err) {
    console.error('Error marking alumni:', err);
    toastRef.value?.error(err.response?.data?.message || 'Gagal menjadikan alumni');
  } finally {
    submitting.value = false;
  }
};

onMounted(async () => {
  document.title = 'Jadikan Alumni - ' + (appSettings.nama_aplikasi || 'Sistem Pembayaran SPP');
  await loadRombelList();
});
</script>
