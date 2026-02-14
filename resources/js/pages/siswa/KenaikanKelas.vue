<template>
  <Layout :active-menu="'Students'">
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg">
              <ArrowUpCircleIcon class="w-8 h-8 text-white" />
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Kenaikan Kelas</h1>
              <p class="text-sm text-gray-500 dark:text-gray-400">Naikkan siswa ke tingkat kelas berikutnya</p>
            </div>
          </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
          <!-- Step 1: Select Source Rombel -->
          <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              Pilih Rombel <span class="text-red-500">*</span>
            </label>
            <SearchableSelect v-model="fromRombelId" :options="filteredRombelList"
              placeholder="Cari dan pilih rombel..." search-placeholder="Ketik nama rombel atau kelas..."
              label-key="displayLabel" value-key="id" @change="onFromRombelChange" />
          </div>

          <!-- Kelas Progression Badge -->
          <div v-if="selectedRombelInfo"
            class="mb-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-200 dark:border-indigo-800">
            <div class="flex items-center gap-3">
              <span
                class="inline-flex items-center px-4 py-1.5 bg-indigo-100 dark:bg-indigo-800 text-indigo-800 dark:text-indigo-200 rounded-lg text-sm font-bold shadow-sm">
                {{ selectedRombelInfo.fromKelas }}
              </span>
              <div class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-200 dark:bg-indigo-700">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-300" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </div>
              <span
                class="inline-flex items-center px-4 py-1.5 bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200 rounded-lg text-sm font-bold shadow-sm">
                {{ selectedRombelInfo.toKelas }}
              </span>
              <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">
                Naik ke rombel: <strong class="text-indigo-600 dark:text-indigo-400">{{ selectedRombelInfo.nama
                }}</strong>
              </span>
            </div>
          </div>

          <!-- Step 2: Student List -->
          <div v-if="fromRombelId" class="mb-6">
            <div class="flex items-center justify-between mb-3">
              <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                Siswa di Rombel
                <span v-if="students.length" class="text-gray-500 font-normal">({{ selectedSiswaIds.length }}/{{
                  students.length }} dipilih)</span>
              </label>
              <button v-if="students.length > 0" type="button"
                class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline"
                @click="toggleSelectAll">
                {{ selectedSiswaIds.length === students.length ? 'Batal Semua' : 'Pilih Semua' }}
              </button>
            </div>

            <!-- Loading -->
            <div v-if="loadingStudents" class="p-8 text-center text-gray-500 dark:text-gray-400">
              <svg class="animate-spin h-6 w-6 mx-auto mb-2 text-indigo-500" xmlns="http://www.w3.org/2000/svg"
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
              class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden max-h-80 overflow-y-auto">
              <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                  <tr>
                    <th class="w-10 px-3 py-2.5 text-center">
                      <input type="checkbox"
                        :checked="selectedSiswaIds.length === students.length && students.length > 0"
                        @change="toggleSelectAll"
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    </th>
                    <th class="px-3 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">
                      Username</th>
                    <th class="px-3 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">
                      NIS</th>
                    <th class="px-3 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">
                      Nama</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="s in students" :key="s.username"
                    :class="['transition-colors cursor-pointer', selectedSiswaIds.includes(s.id) ? 'bg-indigo-50 dark:bg-indigo-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50']"
                    @click="toggleStudent(s.id)">
                    <td class="w-10 px-3 py-2.5 text-center">
                      <input type="checkbox" :checked="selectedSiswaIds.includes(s.id)"
                        @click.stop="toggleStudent(s.id)"
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    </td>
                    <td class="px-3 py-2.5 text-indigo-600 dark:text-indigo-400 font-semibold text-xs">{{ s.username }}
                    </td>
                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300 font-mono text-xs">{{ s.nis || '-' }}</td>
                    <td class="px-3 py-2.5 text-gray-900 dark:text-white font-medium">{{ s.nama }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Submit Button -->
          <div v-if="selectedSiswaIds.length > 0"
            class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">
              <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ selectedSiswaIds.length }}
                siswa</span> akan dinaikkan ke kelas {{ selectedRombelInfo?.toKelas }}
            </p>
            <button type="button" :disabled="submitting"
              class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
              @click="handlePromote">
              <ArrowUpCircleIcon class="w-5 h-5" />
              {{ submitting ? 'Memproses...' : 'Proses Kenaikan Kelas' }}
            </button>
          </div>

          <!-- Success Result -->
          <div v-if="result"
            class="mt-6 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800">
            <div class="flex items-center gap-2 text-green-800 dark:text-green-200">
              <CheckCircleIcon class="w-5 h-5" />
              <p class="font-semibold">Berhasil!</p>
            </div>
            <p class="mt-1 text-sm text-green-700 dark:text-green-300">
              {{ result.promoted_count }} siswa rombel <strong>{{ result.rombel_nama }}</strong> berhasil dinaikkan dari
              kelas <strong>{{ result.from_kelas }}</strong> ke <strong>{{ result.to_kelas }}</strong>
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

    <ConfirmModal :show="showConfirm" title="Konfirmasi Kenaikan Kelas" :message="confirmMessage" type="warning"
      confirm-text="Ya, Naikkan Sekarang" :loading="submitting" @confirm="executePromote"
      @cancel="showConfirm = false" />
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Layout from '../../components/layout/Layout.vue';
import SearchableSelect from '../../components/SearchableSelect.vue';
import ConfirmModal from '../../components/ConfirmModal.vue';
import Toast from '../../components/Toast.vue';
import { siswaRombelAPI, masterDataAPI, cacheAPI } from '../../services/api';
import { useAppSettings } from '../../composables/useAppSettings';
import { useMasterDataCache } from '../../composables/useMasterDataCache';
import { ArrowUpCircleIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

const KELAS_CATEGORIES = {
  'SD': ['1', '2', '3', '4', '5', '6'],
  'SMP': ['7', '8', '9'],
  'SMA_ARABIC': ['10', '11', '12'],
  'SMA_ROMAN': ['X', 'XI', 'XII'],
};

const getNextKelas = (current) => {
  for (const cat in KELAS_CATEGORIES) {
    const list = KELAS_CATEGORIES[cat];
    const idx = list.indexOf(current);
    if (idx !== -1 && idx < list.length - 1) return list[idx + 1];
  }
  return null;
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
const fromRombelId = ref('');
const students = ref([]);
const selectedSiswaIds = ref([]);
const loadingStudents = ref(false);
const submitting = ref(false);
const result = ref(null);

// Confirmation
const showConfirm = ref(false);
const confirmMessage = computed(() => {
  if (!selectedRombelInfo.value) return '';
  return `Apakah Anda yakin ingin menaikkan <strong>${selectedSiswaIds.value.length} siswa</strong> dari kelas <strong>${selectedRombelInfo.value.fromKelas}</strong> ke <strong>${selectedRombelInfo.value.toKelas}</strong>?`;
});

// Filter rombels: exclude final kelas
const filteredRombelList = computed(() => {
  return rombelList.value
    .filter(r => {
      const kelasKode = r.kelas?.kode || '';
      return kelasKode && !isFinalKelas(kelasKode);
    })
    .map(r => ({
      ...r,
      displayLabel: formatRombelOption(r)
    }));
});

// Get kelas progression info for selected rombel
const selectedRombelInfo = computed(() => {
  if (!fromRombelId.value) return null;
  const rombel = rombelList.value.find(r => r.id === fromRombelId.value);
  if (!rombel) return null;
  const fromKelas = rombel.kelas?.kode || '';
  const toKelas = getNextKelas(fromKelas) || '---';
  const toRombelNama = toKelas !== '---' ? `${toKelas} ${rombel.nama}` : rombel.nama;
  return { fromKelas, toKelas, nama: toRombelNama };
});

const formatRombelOption = (r) => {
  const kelasKode = r.kelas?.kode || '';
  const parts = [kelasKode, r.nama].filter(Boolean);
  return parts.join(' ') || r.nama;
};

const loadRombelList = async () => {
  try {
    const response = await masterDataAPI.rombel.select({ isActive: 1 });
    rombelList.value = response.data?.data || response.data || [];
  } catch (err) {
    console.error('Error loading rombel list:', err);
    toastRef.value?.error('Gagal memuat data rombel');
  }
};

const onFromRombelChange = async () => {
  students.value = [];
  selectedSiswaIds.value = [];
  result.value = null;

  if (!fromRombelId.value) return;

  try {
    loadingStudents.value = true;
    const response = await siswaRombelAPI.list({ idRombel: fromRombelId.value });
    const mappings = response.data?.data || response.data || [];
    students.value = mappings
      .filter(m => m.siswa && m.siswa.isActive === 1 && !m.siswa.isAlumni)
      .map(m => ({ id: m.siswa.id, username: m.siswa.username, nis: m.siswa.nis, nama: m.siswa.nama }))
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

const handlePromote = () => {
  if (!fromRombelId.value || selectedSiswaIds.value.length === 0) return;
  showConfirm.value = true;
};

const executePromote = async () => {
  try {
    submitting.value = true;
    result.value = null;

    const response = await siswaRombelAPI.promote({
      fromRombelId: fromRombelId.value,
      siswa_ids: selectedSiswaIds.value,
    });

    result.value = response.data?.data || response.data;
    toastRef.value?.success('Kenaikan kelas berhasil diproses!');

    selectedSiswaIds.value = [];
    showConfirm.value = false;

    // Clear frontend and backend cache
    clearCache('rombel');
    try { await cacheAPI.clearLaravel(); } catch (e) { console.error('Cache clear failed', e); }

    await onFromRombelChange();
  } catch (err) {
    console.error('Error promoting students:', err);
    toastRef.value?.error(err.response?.data?.message || 'Gagal memproses kenaikan kelas');
  } finally {
    submitting.value = false;
  }
};

onMounted(async () => {
  document.title = 'Kenaikan Kelas - ' + (appSettings.nama_aplikasi || 'Sistem Pembayaran SPP');
  await loadRombelList();
});
</script>
