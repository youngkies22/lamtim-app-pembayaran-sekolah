<template>
  <Layout :active-menu="'Settings'">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-6 md:p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                <Cog6ToothIcon class="w-6 h-6 md:w-8 md:h-8 text-white" />
              </div>
              Pengaturan Aplikasi
            </h1>
            <p class="mt-2 text-indigo-100 text-sm md:text-base">Kelola logo, nama aplikasi, dan konfigurasi job</p>
          </div>
        </div>
      </div>

      <!-- Settings Form -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 md:p-8">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Error Message -->
          <div v-if="formError" class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-sm text-red-800 dark:text-red-200">{{ formError }}</p>
          </div>

          <!-- Nama Aplikasi -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              Nama Aplikasi <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.nama_aplikasi"
              type="text"
              required
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white transition-colors"
              placeholder="Masukkan nama aplikasi"
            />
            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Nama aplikasi akan ditampilkan di header dan berbagai tempat di sistem</p>
          </div>

          <!-- Logo Aplikasi -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              Logo Aplikasi
            </label>
            <div class="space-y-4">
              <!-- Preview -->
              <div v-if="logoPreview" class="flex items-start gap-4">
                <div class="relative">
                  <div class="w-32 h-32 rounded-xl overflow-hidden border-2 border-gray-200 dark:border-gray-700 shadow-md bg-white dark:bg-gray-900 flex items-center justify-center">
                    <img :src="logoPreview" alt="Logo preview" class="w-full h-full object-contain p-2" />
                  </div>
                  <button
                    type="button"
                    @click="removeLogo"
                    class="absolute -top-2 -right-2 w-7 h-7 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg"
                    title="Hapus logo"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </div>
                <div class="flex-1">
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Preview logo aplikasi</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">Logo akan ditampilkan di header aplikasi dan berbagai tempat di sistem</p>
                </div>
              </div>

              <!-- Upload Input -->
              <div>
                <label
                  for="logo-input"
                  class="flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                >
                  <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                      <span class="font-semibold">Klik untuk upload</span> atau drag and drop
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, GIF, SVG atau WEBP (MAX. 2MB)</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Rekomendasi: Logo dengan background transparan, ukuran 200x200px</p>
                  </div>
                  <input
                    id="logo-input"
                    type="file"
                    accept="image/*"
                    @change="handleLogoChange"
                    class="hidden"
                  />
                </label>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
            <button
              type="button"
              @click="loadSettings"
              class="px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
            >
              Reset
            </button>
            <button
              type="submit"
              :disabled="submitLoading"
              class="px-6 py-3 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <svg v-if="submitLoading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ submitLoading ? 'Menyimpan...' : 'Simpan Pengaturan' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Job Settings Section -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 md:p-8">
        <div class="mb-6">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <BoltIcon class="w-5 h-5 text-amber-500" />
            Konfigurasi Job
          </h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Aktifkan atau nonaktifkan job yang berjalan di sistem</p>
        </div>

        <div v-if="jobLoading" class="flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else class="space-y-4">
          <!-- Sync External Data -->
          <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Sync External Data</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Sinkronisasi data dari sumber eksternal</p>
            </div>
            <button
              @click="toggleJob('job_sync_external_enabled')"
              :disabled="jobSaving"
              :class="[
                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                jobSettings.job_sync_external_enabled ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600'
              ]"
            >
              <span
                :class="[
                  'inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 shadow-sm',
                  jobSettings.job_sync_external_enabled ? 'translate-x-6' : 'translate-x-1'
                ]"
              />
            </button>
          </div>

          <!-- Sync Siswa -->
          <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Sync Siswa</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Sinkronisasi data siswa di latar belakang</p>
            </div>
            <button
              @click="toggleJob('job_sync_siswa_enabled')"
              :disabled="jobSaving"
              :class="[
                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                jobSettings.job_sync_siswa_enabled ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600'
              ]"
            >
              <span
                :class="[
                  'inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 shadow-sm',
                  jobSettings.job_sync_siswa_enabled ? 'translate-x-6' : 'translate-x-1'
                ]"
              />
            </button>
          </div>

          <!-- Push Academic Data -->
          <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Push Academic Data</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kirim data tagihan dan pembayaran ke sistem akademik</p>
            </div>
            <button
              @click="toggleJob('job_push_academic_enabled')"
              :disabled="jobSaving"
              :class="[
                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                jobSettings.job_push_academic_enabled ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600'
              ]"
            >
              <span
                :class="[
                  'inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 shadow-sm',
                  jobSettings.job_push_academic_enabled ? 'translate-x-6' : 'translate-x-1'
                ]"
              />
            </button>
          </div>

          <!-- Process Import -->
          <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Process Import</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Proses import data Excel di latar belakang</p>
            </div>
            <button
              @click="toggleJob('job_process_import_enabled')"
              :disabled="jobSaving"
              :class="[
                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                jobSettings.job_process_import_enabled ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600'
              ]"
            >
              <span
                :class="[
                  'inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 shadow-sm',
                  jobSettings.job_process_import_enabled ? 'translate-x-6' : 'translate-x-1'
                ]"
              />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import Layout from '../components/layout/Layout.vue';
import Toast from '../components/Toast.vue';
import { settingsAPI } from '../services/api';
import { useAppSettings } from '../composables/useAppSettings';
import { Cog6ToothIcon, BoltIcon } from '@heroicons/vue/24/outline';

const { refreshAppSettings } = useAppSettings();

const toastRef = ref(null);
const submitLoading = ref(false);
const formError = ref('');
const logoPreview = ref(null);
const logoFile = ref(null);
const settingsId = ref(null);

const form = reactive({
  nama_aplikasi: '',
  logo_aplikasi: null,
});

// Job Settings
const jobLoading = ref(true);
const jobSaving = ref(false);
const jobSettings = reactive({
  job_sync_external_enabled: false,
  job_sync_siswa_enabled: false,
  job_push_academic_enabled: false,
  job_process_import_enabled: true,
});

const getLogoUrl = (logoPath) => {
  if (!logoPath) return null;
  if (logoPath.startsWith('http://') || logoPath.startsWith('https://')) {
    return logoPath;
  }
  return `/storage/${logoPath}`;
};

const handleLogoChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (!file.type.startsWith('image/')) {
      toastRef.value?.error('File harus berupa gambar');
      return;
    }
    if (file.size > 2 * 1024 * 1024) {
      toastRef.value?.error('Ukuran file maksimal 2MB');
      return;
    }
    logoFile.value = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const removeLogo = () => {
  logoFile.value = null;
  logoPreview.value = null;
  form.logo_aplikasi = null;
  const fileInput = document.getElementById('logo-input');
  if (fileInput) {
    fileInput.value = '';
  }
};

const loadSettings = async () => {
  try {
    const response = await settingsAPI.get();
    if (response.data?.success && response.data.data) {
      const settings = response.data.data;
      settingsId.value = settings.id;
      form.nama_aplikasi = settings.nama_aplikasi || '';
      form.logo_aplikasi = settings.logo_aplikasi || null;

      if (settings.logo_aplikasi) {
        logoPreview.value = getLogoUrl(settings.logo_aplikasi);
      } else {
        logoPreview.value = null;
      }
      logoFile.value = null;
    }
  } catch (err) {
    console.error('Error loading settings:', err);
  }
};

const loadJobSettings = async () => {
  try {
    jobLoading.value = true;
    const response = await settingsAPI.getJobSettings();
    if (response.data?.success && response.data.data) {
      const data = response.data.data;
      jobSettings.job_sync_external_enabled = data.job_sync_external_enabled ?? false;
      jobSettings.job_sync_siswa_enabled = data.job_sync_siswa_enabled ?? false;
      jobSettings.job_push_academic_enabled = data.job_push_academic_enabled ?? false;
      jobSettings.job_process_import_enabled = data.job_process_import_enabled ?? true;
    }
  } catch (err) {
    console.error('Error loading job settings:', err);
  } finally {
    jobLoading.value = false;
  }
};

const toggleJob = async (key) => {
  try {
    jobSaving.value = true;
    const newValue = !jobSettings[key];

    const response = await settingsAPI.updateJobSettings({ [key]: newValue });
    if (response.data?.success) {
      jobSettings[key] = newValue;
      toastRef.value?.success(`Job ${newValue ? 'diaktifkan' : 'dinonaktifkan'}`);
    }
  } catch (err) {
    console.error('Error toggling job:', err);
    toastRef.value?.error('Gagal mengubah status job');
  } finally {
    jobSaving.value = false;
  }
};

const handleSubmit = async () => {
  try {
    submitLoading.value = true;
    formError.value = '';

    const formData = new FormData();
    formData.append('nama_aplikasi', form.nama_aplikasi);

    if (logoFile.value) {
      formData.append('logo_aplikasi', logoFile.value);
    }

    if (settingsId.value) {
      await settingsAPI.update(settingsId.value, formData);
      toastRef.value?.success('Pengaturan berhasil diperbarui');
    } else {
      await settingsAPI.create(formData);
      toastRef.value?.success('Pengaturan berhasil disimpan');
    }

    await loadSettings();
    await refreshAppSettings();
  } catch (err) {
    formError.value = err.response?.data?.message || 'Terjadi kesalahan';
    toastRef.value?.error(formError.value);
  } finally {
    submitLoading.value = false;
  }
};

onMounted(() => {
  loadSettings();
  loadJobSettings();
});
</script>

<style scoped>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
