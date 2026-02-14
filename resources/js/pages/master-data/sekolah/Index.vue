<template>
  <Layout :active-menu="'Master Data'">
    <div class="space-y-6">
      <!-- Modern Compact Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <div class="p-2 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl text-white shadow-lg">
              <svg class="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                </path>
              </svg>
            </div>
            <span>Data Sekolah</span>
          </h1>
          <p class="mt-1.5 text-sm text-gray-600 dark:text-gray-400">Kelola informasi sekolah</p>
        </div>
        <button v-if="isAdminUser" @click="openCreateModal"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Tambah Sekolah
        </button>
      </div>

      <!-- Grid Card Layout -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="n in 6" :key="n"
          class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 animate-pulse">
          <div class="h-12 w-12 bg-gray-200 dark:bg-gray-700 rounded-full mb-4"></div>
          <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
        </div>
      </div>

      <div v-else-if="tableData.length === 0"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
        <div class="flex flex-col items-center gap-4">
          <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
              </path>
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Belum ada data sekolah</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Mulai dengan menambahkan sekolah pertama</p>
          </div>
          <button @click="openCreateModal"
            class="mt-2 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Sekolah
          </button>
        </div>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="item in tableData" :key="item.id"
          class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-200">
          <!-- Card Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div v-if="item.logo"
                class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 shadow-md ring-2 ring-gray-200 dark:ring-gray-700">
                <img :src="getLogoUrl(item.logo)" :alt="item.nama" class="w-full h-full object-cover" />
              </div>
              <div v-else
                class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md">
                {{ getInitials(item.nama) }}
              </div>
              <div class="flex-1 min-w-0">
                <h3
                  class="text-lg font-bold text-gray-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                  {{ item.nama }}
                </h3>
                <p v-if="item.npsn" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">NPSN: {{ item.npsn }}</p>
              </div>
            </div>
            <div class="relative">
              <button @click.stop="toggleCardMenu(item.id)"
                class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z">
                  </path>
                </svg>
              </button>
              <!-- Dropdown Menu -->
              <div v-if="activeMenuId === item.id" v-click-outside="() => activeMenuId = null"
                class="absolute right-0 top-full mt-1 w-40 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-10">
                <button @click="handleEdit(item); activeMenuId = null"
                  class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                    </path>
                  </svg>
                  Edit
                </button>
                <button @click="handleDelete(item); activeMenuId = null"
                  class="w-full px-4 py-2 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                    </path>
                  </svg>
                  Hapus
                </button>
              </div>
            </div>
          </div>

          <!-- Card Content -->
          <div class="space-y-3">
            <div v-if="item.alamat" class="flex items-start gap-2">
              <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
              <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ item.alamat }}</p>
            </div>

            <div class="flex items-center gap-4 flex-wrap">
              <div v-if="item.telepon" class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                  </path>
                </svg>
                <span class="text-xs text-gray-600 dark:text-gray-400">{{ item.telepon }}</span>
              </div>
              <div v-if="item.email" class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                  </path>
                </svg>
                <span class="text-xs text-gray-600 dark:text-gray-400 truncate max-w-[150px]">{{ item.email }}</span>
              </div>
            </div>

            <div v-if="item.kepala_sekolah" class="mt-2 pt-2 border-t border-gray-50 dark:border-gray-700/50">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <div>
                  <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ item.kepala_sekolah }}</p>
                  <p v-if="item.nip_kepsek" class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ appSettings.label_nip }}: {{ item.nip_kepsek }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Card Footer -->
          <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <span class="text-xs text-gray-500 dark:text-gray-400">
              {{ formatDate(item.created_at) }}
            </span>
            <div class="flex items-center gap-2">
              <button @click="handleEdit(item)"
                class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                  </path>
                </svg>
              </button>
              <button @click="handleDelete(item)"
                class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                title="Hapus">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                  </path>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <FormModal :show="showModal" :title="editingId ? 'Edit Sekolah' : 'Tambah Sekolah Baru'" :loading="submitLoading"
      :error="formError" @close="closeModal" @submit="handleSubmit">
      <template #form>
        <div class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama Sekolah *</label>
            <input v-model="form.nama" type="text" required
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white"
              placeholder="Nama lengkap sekolah" />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama Yayasan</label>
            <input v-model="form.namaYayasan" type="text"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white"
              placeholder="Nama yayasan (contoh: Yayasan Pendidikan Budi Utomo)" />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Kode Sekolah</label>
            <input v-model="form.kode" type="text"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white"
              placeholder="Kode sekolah (contoh: SMK001)" />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">NPSN</label>
            <input v-model="form.npsn" type="text"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white"
              placeholder="Nomor Pokok Sekolah Nasional" />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Alamat</label>
            <textarea v-model="form.alamat" rows="3"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white resize-none"
              placeholder="Alamat lengkap sekolah"></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Telepon</label>
              <input v-model="form.telepon" type="text"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white"
                placeholder="Nomor telepon" />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email</label>
              <input v-model="form.email" type="email"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white"
                placeholder="Email sekolah" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama Kepala Sekolah</label>
              <input v-model="form.kepala_sekolah" type="text"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white"
                placeholder="Nama kepala sekolah" />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ appSettings.label_nip }} Kepala Sekolah</label>
              <input v-model="form.nip_kepsek" type="text"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white"
                :placeholder="`${appSettings.label_nip} kepala sekolah`" />
            </div>
          </div>

          <!-- Logo Upload -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Logo Sekolah</label>
            <div class="space-y-3">
              <!-- Preview -->
              <div v-if="logoPreview" class="relative inline-block">
                <div
                  class="w-32 h-32 rounded-xl overflow-hidden border-2 border-gray-200 dark:border-gray-700 shadow-md">
                  <img :src="logoPreview" alt="Logo preview" class="w-full h-full object-cover" />
                </div>
                <button type="button" @click="removeLogo"
                  class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors"
                  title="Hapus logo">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                  </svg>
                </button>
              </div>

              <!-- Upload Input -->
              <div>
                <label for="logo-input"
                  class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                  <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                      </path>
                    </svg>
                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                      <span class="font-semibold">Klik untuk upload</span> atau drag and drop
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, GIF, SVG atau WEBP (MAX. 2MB)</p>
                  </div>
                  <input id="logo-input" type="file" accept="image/*" @change="handleLogoChange" class="hidden" />
                </label>
              </div>
            </div>
          </div>
        </div>
      </template>
    </FormModal>

    <!-- Confirm Delete Modal -->
    <ConfirmModal :show="showDeleteModal" type="danger" title="Hapus Sekolah"
      :message="`Apakah Anda yakin ingin menghapus sekolah '${deletingItem?.nama || ''}'?`" confirm-text="Ya, Hapus"
      :loading="deleteLoading" @confirm="confirmDelete" @cancel="showDeleteModal = false" />

    <!-- Toast -->
    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import Layout from '../../../components/layout/Layout.vue';
import FormModal from '../../../components/FormModal.vue';
import ConfirmModal from '../../../components/ConfirmModal.vue';
import Toast from '../../../components/Toast.vue';
import { masterDataAPI } from '../../../services/api';
import { useRoleAccess } from '../../../composables/useRoleAccess';
import { useAppSettings } from '../../../composables/useAppSettings';

const { canCreateData, canEditData, canDeleteData, isAdminUser } = useRoleAccess();
const { appSettings } = useAppSettings();

const toastRef = ref(null);
const showModal = ref(false);
const showDeleteModal = ref(false);
const editingId = ref(null);
const deletingItem = ref(null);
const loading = ref(false);
const submitLoading = ref(false);
const deleteLoading = ref(false);
const formError = ref('');
const activeMenuId = ref(null);

const tableData = ref([]);

const initialForm = {
  nama: '',
  namaYayasan: '',
  kode: '',
  npsn: '',
  alamat: '',
  telepon: '',
  email: '',
  kepala_sekolah: '',
  nip_kepsek: '',
  logo: null,
};

const form = reactive({ ...initialForm });
const logoPreview = ref(null);
const logoFile = ref(null);


const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const getLogoUrl = (logoPath) => {
  if (!logoPath) return null;
  // If it's already a full URL, return as is
  if (logoPath.startsWith('http://') || logoPath.startsWith('https://')) {
    return logoPath;
  }
  // Otherwise, construct the storage URL
  return `/storage/${logoPath}`;
};

const handleLogoChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    // Validate file type
    if (!file.type.startsWith('image/')) {
      toastRef.value?.error('File harus berupa gambar');
      return;
    }
    // Validate file size (2MB)
    if (file.size > 2 * 1024 * 1024) {
      toastRef.value?.error('Ukuran file maksimal 2MB');
      return;
    }
    logoFile.value = file;
    // Create preview
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
  form.logo = null;
  // Reset file input
  const fileInput = document.getElementById('logo-input');
  if (fileInput) {
    fileInput.value = '';
  }
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
};

const toggleCardMenu = (id) => {
  activeMenuId.value = activeMenuId.value === id ? null : id;
};

const loadData = async () => {
  try {
    loading.value = true;
    const response = await masterDataAPI.sekolah.list();
    tableData.value = response.data.data || response.data || [];
  } catch (err) {
    toastRef.value?.error('Gagal memuat data sekolah');
    tableData.value = [];
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  editingId.value = null;
  Object.assign(form, initialForm);
  logoPreview.value = null;
  logoFile.value = null;
  formError.value = '';
  showModal.value = true;
};

const handleEdit = async (item) => {
  try {
    submitLoading.value = true;
    const response = await masterDataAPI.sekolah.get(item.id);
    const sekolah = response.data.data;

    editingId.value = item.id;
    Object.assign(form, {
      nama: sekolah.nama || '',
      namaYayasan: sekolah.namaYayasan || '',
      kode: sekolah.kode || '',
      npsn: sekolah.npsn || '',
      alamat: sekolah.alamat || '',
      telepon: sekolah.telepon || '',
      email: sekolah.email || '',
      kepala_sekolah: sekolah.kepala_sekolah || '',
      nip_kepsek: sekolah.nip_kepsek || '',
      logo: sekolah.logo || null,
    });
    // Set logo preview if exists
    if (sekolah.logo) {
      logoPreview.value = getLogoUrl(sekolah.logo);
    } else {
      logoPreview.value = null;
    }
    logoFile.value = null;
    formError.value = '';
    showModal.value = true;
  } catch (err) {
    toastRef.value?.error('Gagal memuat data sekolah');
  } finally {
    submitLoading.value = false;
  }
};

const handleDelete = (item) => {
  deletingItem.value = item;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!deletingItem.value) return;

  try {
    deleteLoading.value = true;
    await masterDataAPI.sekolah.delete(deletingItem.value.id);
    showDeleteModal.value = false;
    deletingItem.value = null;
    toastRef.value?.success('Sekolah berhasil dihapus');
    loadData();
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal menghapus sekolah');
  } finally {
    deleteLoading.value = false;
  }
};

const handleSubmit = async () => {
  try {
    submitLoading.value = true;
    formError.value = '';

    // Create FormData for file upload
    const formData = new FormData();
    formData.append('nama', form.nama);
    if (form.namaYayasan) formData.append('namaYayasan', form.namaYayasan);
    if (form.kode) formData.append('kode', form.kode);
    if (form.npsn) formData.append('npsn', form.npsn);
    if (form.alamat) formData.append('alamat', form.alamat);
    if (form.telepon) formData.append('telepon', form.telepon);
    if (form.email) formData.append('email', form.email);
    if (form.kepala_sekolah) formData.append('kepala_sekolah', form.kepala_sekolah);
    if (form.nip_kepsek) formData.append('nip_kepsek', form.nip_kepsek);

    // Append logo file if selected
    if (logoFile.value) {
      formData.append('image', logoFile.value);
    }

    if (editingId.value) {
      await masterDataAPI.sekolah.update(editingId.value, formData);
      toastRef.value?.success('Sekolah berhasil diperbarui');
    } else {
      await masterDataAPI.sekolah.create(formData);
      toastRef.value?.success('Sekolah berhasil ditambahkan');
    }

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
  logoPreview.value = null;
  logoFile.value = null;
  formError.value = '';
};

// Click outside directive
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value();
      }
    };
    document.addEventListener('click', el.clickOutsideEvent);
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent);
  },
};

onMounted(() => {
  loadData();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
