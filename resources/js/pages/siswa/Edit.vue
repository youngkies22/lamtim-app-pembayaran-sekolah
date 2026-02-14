<template>
  <Layout :active-menu="'Students'">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Siswa</h1>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">Ubah data siswa di bawah</p>
        </div>
        <button @click="$router.push('/siswa')"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors flex items-center gap-2">
          <ArrowLeftIcon class="w-4 h-4" />
          Kembali
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading"
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12">
        <div class="flex flex-col items-center justify-center gap-3">
          <svg class="animate-spin h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
          </svg>
          <span class="text-gray-500 dark:text-gray-400 font-medium">Memuat data siswa...</span>
        </div>
      </div>

      <!-- Form Card -->
      <div v-else
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Error Message -->
          <div v-if="formError"
            class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-sm text-red-800 dark:text-red-200">{{ formError }}</p>
          </div>

          <!-- Validation Errors -->
          <div v-if="validationErrors && Object.keys(validationErrors).length > 0"
            class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <ul class="list-disc list-inside text-sm text-red-800 dark:text-red-200 space-y-1">
              <li v-for="(errors, field) in validationErrors" :key="field">
                <span class="font-semibold">{{ field }}:</span> {{ Array.isArray(errors) ? errors[0] : errors }}
              </li>
            </ul>
          </div>

          <!-- Form Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Username -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Username <span class="text-red-500">*</span>
              </label>
              <input v-model="form.username" type="text" required
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="Masukkan username" />
            </div>

            <!-- Nama -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Nama Lengkap <span class="text-red-500">*</span>
              </label>
              <input v-model="form.nama" type="text" required
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="Masukkan nama lengkap" />
            </div>

            <!-- Password -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Password <span class="text-gray-500 text-xs">(kosongkan jika tidak ingin mengubah)</span>
              </label>
              <input v-model="form.password" type="password" minlength="6"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="Minimal 6 karakter" />
            </div>

            <!-- Jenis Kelamin -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Jenis Kelamin <span class="text-red-500">*</span>
              </label>
              <select v-model.number="form.jsk" required
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                <option value="">Pilih Jenis Kelamin</option>
                <option :value="1">Laki-laki</option>
                <option :value="0">Perempuan</option>
              </select>
            </div>

            <!-- NIS -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                NIS
              </label>
              <input v-model="form.nis" type="text"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="Masukkan NIS" />
            </div>

            <!-- NISN -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                NISN
              </label>
              <input v-model="form.nisn" type="text"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="Masukkan NISN" />
            </div>

            <!-- Agama -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Agama
              </label>
              <select v-model="form.idAgama"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                <option value="">Pilih Agama</option>
                <option v-for="agama in agamaList" :key="agama.id" :value="agama.id">
                  {{ agama.nama }}
                </option>
              </select>
            </div>

            <!-- Tahun Angkatan -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Tahun Angkatan
              </label>
              <input v-model="form.tahunAngkatan" type="text" maxlength="10"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="Contoh: 2024" />
            </div>

            <!-- Rombel -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Rombel
              </label>
              <select v-model="form.idRombel"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                <option value="">Pilih Rombel</option>
                <option v-for="rombel in rombelList" :key="rombel.id" :value="rombel.id">
                  {{ rombel.kelas ? rombel.kelas.kode + ' ' : '' }}{{ rombel.nama }}
                </option>
              </select>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Status
              </label>
              <select v-model.number="form.isActive"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                <option :value="1">Aktif</option>
                <option :value="0">Tidak Aktif</option>
                <option :value="2">Off</option>
              </select>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" @click="$router.push('/siswa')"
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
              {{ submitLoading ? 'Menyimpan...' : 'Update' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Toast -->
    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import Layout from '../../components/layout/Layout.vue';
import Toast from '../../components/Toast.vue';
import { siswaAPI, masterDataAPI } from '../../services/api';
import { useMasterDataCache } from '../../composables/useMasterDataCache';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

const router = useRouter();
const route = useRoute();
const toastRef = ref(null);
const { loadAll } = useMasterDataCache();

// Master data
const agamaList = ref([]);
const rombelList = ref([]);

// Form data
const form = reactive({
  username: '',
  nama: '',
  password: '',
  jsk: null,
  nis: '',
  nisn: '',
  idAgama: '',
  tahunAngkatan: '',
  idRombel: '',
  isActive: 1,
});

const loading = ref(true);
const submitLoading = ref(false);
const formError = ref('');
const validationErrors = ref({});

// Load siswa data
const loadSiswaData = async () => {
  try {
    loading.value = true;
    const siswaId = route.params.id;
    const response = await siswaAPI.get(siswaId);

    if (response.data?.success && response.data.data) {
      const siswa = response.data.data;

      form.username = siswa.username || '';
      form.nama = siswa.nama || '';
      form.jsk = siswa.jsk !== undefined ? siswa.jsk : null;
      form.nis = siswa.nis || '';
      form.nisn = siswa.nisn || '';
      form.idAgama = siswa.idAgama || '';
      form.tahunAngkatan = siswa.tahunAngkatan || '';
      form.idRombel = siswa.idRombel || '';
      form.isActive = siswa.isActive !== undefined ? siswa.isActive : 1;
      // Password tidak di-load, user harus isi manual jika ingin mengubah
    } else {
      toastRef.value?.error('Data siswa tidak ditemukan');
      router.push('/siswa');
    }
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal memuat data siswa');
    router.push('/siswa');
  } finally {
    loading.value = false;
  }
};

// Load master data
const loadMasterData = async () => {
  try {
    // Load agama
    const agamaResponse = await masterDataAPI.agama.list();
    if (agamaResponse.data?.success) {
      agamaList.value = agamaResponse.data.data || [];
    }

    // Load rombel from cache
    const masterData = await loadAll();
    rombelList.value = masterData.rombel || [];
  } catch (err) {
    console.error('Error loading master data:', err);
  }
};

// Handle submit
const handleSubmit = async () => {
  try {
    submitLoading.value = true;
    formError.value = '';
    validationErrors.value = {};

    const payload = {
      username: form.username,
      nama: form.nama,
      jsk: form.jsk,
      nis: form.nis || null,
      nisn: form.nisn || null,
      idAgama: form.idAgama || null,
      tahunAngkatan: form.tahunAngkatan || null,
      idRombel: form.idRombel || null,
      isActive: form.isActive,
    };

    // Only include password if it's provided
    if (form.password) {
      payload.password = form.password;
    }

    const siswaId = route.params.id;
    await siswaAPI.update(siswaId, payload);
    toastRef.value?.success('Siswa berhasil diperbarui');

    // Redirect to siswa list
    setTimeout(() => {
      router.push('/siswa');
    }, 1000);
  } catch (err) {
    if (err.response?.status === 422) {
      validationErrors.value = err.response.data?.errors || {};
      formError.value = err.response.data?.message || 'Terdapat kesalahan pada form';
    } else {
      formError.value = err.response?.data?.message || 'Gagal memperbarui siswa';
      toastRef.value?.error(formError.value);
    }
  } finally {
    submitLoading.value = false;
  }
};

onMounted(async () => {
  await Promise.all([loadMasterData(), loadSiswaData()]);
});
</script>
