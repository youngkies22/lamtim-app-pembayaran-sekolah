<template>
  <Layout :active-menu="'Users'">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-6 md:p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                <UserGroupIcon class="w-6 h-6 md:w-8 md:h-8 text-white" />
              </div>
              Manajemen Akun
            </h1>
            <p class="mt-2 text-indigo-100 text-sm md:text-base">Kelola pengguna sistem (Admin, Operator, Kepala Sekolah)</p>
          </div>
          <button
            @click="openCreateModal"
            class="group relative inline-flex items-center gap-2 px-5 py-3 bg-white text-indigo-600 font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
          >
            <PlusIcon class="w-5 h-5 transition-transform group-hover:rotate-90 duration-300" />
            Tambah Akun
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-4 md:p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                v-model="filters.search"
                @input="debouncedSearch"
                type="text"
                placeholder="Cari username, nama, atau email..."
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white"
              />
            </div>
          </div>
          <!-- Role Filter -->
          <div>
            <select
              v-model="filters.role"
              @change="loadData"
              class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white"
            >
              <option value="">Semua Role</option>
              <option value="1">Admin</option>
              <option value="2">Operator</option>
              <option value="3">Kepala Sekolah</option>
            </select>
          </div>
          <!-- Status Filter -->
          <div>
            <select
              v-model="filters.isActive"
              @change="loadData"
              class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white"
            >
              <option :value="null">Semua Status</option>
              <option :value="1">Aktif</option>
              <option :value="0">Nonaktif</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="n in 6" :key="n" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 animate-pulse">
          <div class="h-16 w-16 bg-gray-200 dark:bg-gray-700 rounded-full mb-4"></div>
          <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="tableData.length === 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
        <div class="flex flex-col items-center gap-4">
          <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full">
            <UserGroupIcon class="w-12 h-12 text-gray-400" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Belum ada data pengguna</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Mulai dengan menambahkan pengguna pertama</p>
          </div>
        </div>
      </div>

      <!-- User Cards Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="user in tableData"
          :key="user.id"
          class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 hover:shadow-xl transition-all duration-200"
        >
          <!-- User Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-4">
              <!-- Avatar -->
              <div class="relative">
                <div v-if="user.avatar" class="w-16 h-16 rounded-full overflow-hidden border-2 border-indigo-500">
                  <img :src="`/storage/${user.avatar}`" :alt="user.name" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-xl border-2 border-indigo-500">
                  {{ getInitials(user.name || user.username) }}
                </div>
                <div
                  :class="[
                    'absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white dark:border-gray-800',
                    user.isActive ? 'bg-green-500' : 'bg-gray-400'
                  ]"
                ></div>
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ user.name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">@{{ user.username }}</p>
              </div>
            </div>
            <!-- Actions Dropdown -->
            <div class="relative" v-click-outside="() => activeMenuId = null">
              <button
                @click="activeMenuId = activeMenuId === user.id ? null : user.id"
                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
              >
                <EllipsisVerticalIcon class="w-5 h-5" />
              </button>
              <div
                v-if="activeMenuId === user.id"
                v-click-outside="() => activeMenuId = null"
                class="absolute right-0 top-full mt-1 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-10"
              >
                <button
                  @click="handleEdit(user); activeMenuId = null"
                  class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                >
                  <PencilIcon class="w-4 h-4" />
                  Edit
                </button>
                <button
                  @click="handleToggleActive(user); activeMenuId = null"
                  class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                >
                  <ArrowPathIcon class="w-4 h-4" />
                  {{ user.isActive ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
                <button
                  v-if="user.id !== currentUserId"
                  @click="handleDelete(user); activeMenuId = null"
                  class="w-full px-4 py-2 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 flex items-center gap-2"
                >
                  <TrashIcon class="w-4 h-4" />
                  Hapus
                </button>
              </div>
            </div>
          </div>

          <!-- User Info -->
          <div class="space-y-2 mb-4">
            <div class="flex items-center gap-2">
              <EnvelopeIcon class="w-4 h-4 text-gray-400" />
              <span class="text-sm text-gray-600 dark:text-gray-400">{{ user.email || '-' }}</span>
            </div>
            <div class="flex items-center gap-2">
              <ShieldCheckIcon class="w-4 h-4 text-gray-400" />
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ user.role_label }}</span>
            </div>
            <div v-html="user.isActive_badge" class="inline-block"></div>
          </div>

          <!-- Quick Actions -->
          <div class="flex items-center gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button
              @click="handleEdit(user)"
              class="flex-1 px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors"
            >
              Edit
            </button>
            <button
              @click="handleToggleActive(user)"
              :class="[
                'flex-1 px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                user.isActive
                  ? 'text-orange-600 bg-orange-50 dark:bg-orange-900/30 dark:text-orange-400 hover:bg-orange-100 dark:hover:bg-orange-900/50'
                  : 'text-green-600 bg-green-50 dark:bg-green-900/30 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/50'
              ]"
            >
              {{ user.isActive ? 'Nonaktifkan' : 'Aktifkan' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <FormModal
      :show="showModal"
      :loading="submitLoading"
      :error="formError"
      @close="closeModal"
      @submit="handleSubmit"
      :title="editingId ? 'Edit Akun' : 'Tambah Akun Baru'"
    >
      <template #form>
        <div class="space-y-5">
          <!-- Avatar -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Foto Profil</label>
            <input type="file" @change="handleAvatarUpload" accept="image/*" class="hidden" ref="avatarInput" />
            <div
              @click="avatarInput?.click()"
              class="flex items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 dark:bg-gray-900 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            >
              <div v-if="avatarPreview" class="relative">
                <img :src="avatarPreview" class="h-28 w-28 object-cover rounded-full" alt="Avatar Preview" />
                <button @click.stop="removeAvatar" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 text-xs">
                  <XMarkIcon class="w-3 h-3" />
                </button>
              </div>
              <div v-else class="text-center">
                <UserIcon class="mx-auto h-10 w-10 text-gray-400" />
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Klik untuk upload foto</p>
              </div>
            </div>
          </div>

          <!-- Username -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Username *</label>
            <input
              v-model="form.username"
              type="text"
              required
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white"
              placeholder="Username"
            />
          </div>

          <!-- Name -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama Lengkap *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white"
              placeholder="Nama lengkap"
            />
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white"
              placeholder="email@example.com"
            />
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              Password {{ editingId ? '(Kosongkan jika tidak ingin mengubah)' : '*' }}
            </label>
            <input
              v-model="form.password"
              type="password"
              :required="!editingId"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white"
              placeholder="Minimal 8 karakter"
            />
          </div>

          <!-- Role -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Role *</label>
            <select
              v-model="form.role"
              required
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white"
            >
              <option value="">Pilih Role</option>
              <option :value="1">Admin</option>
              <option :value="2">Operator</option>
              <option :value="3">Kepala Sekolah</option>
            </select>
          </div>

          <!-- Status -->
          <div>
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="form.isActive"
                type="checkbox"
                class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
              />
              <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Aktif</span>
            </label>
          </div>
        </div>
      </template>
    </FormModal>

    <!-- Confirm Delete Modal -->
    <ConfirmModal
      :show="showDeleteModal"
      type="danger"
      title="Hapus Akun"
      :message="`Apakah Anda yakin ingin menghapus akun '${deletingItem?.name || ''}'?`"
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
import { ref, reactive, onMounted } from 'vue';
import Layout from '../../components/layout/Layout.vue';
import FormModal from '../../components/FormModal.vue';
import ConfirmModal from '../../components/ConfirmModal.vue';
import Toast from '../../components/Toast.vue';
import { usersAPI, authAPI } from '../../services/api';
import {
  UserGroupIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  PencilIcon,
  TrashIcon,
  EllipsisVerticalIcon,
  EnvelopeIcon,
  ShieldCheckIcon,
  ArrowPathIcon,
  UserIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';

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
const avatarInput = ref(null);
const avatarFile = ref(null);
const avatarPreview = ref(null);
const currentUserId = ref(null);

const tableData = ref([]);

const filters = reactive({
  search: '',
  role: '',
  isActive: null,
});

const form = reactive({
  username: '',
  name: '',
  email: '',
  password: '',
  role: '',
  isActive: true,
});

const initialForm = {
  username: '',
  name: '',
  email: '',
  password: '',
  role: '',
  isActive: true,
};

// Get user initials
const getInitials = (name) => {
  if (!name) return 'U';
  const parts = name.trim().split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
};

// Debounce search
let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadData();
  }, 500);
};

// Load data
const loadData = async () => {
  try {
    loading.value = true;
    const params = {};
    if (filters.search) params.search = filters.search;
    if (filters.role) params.role = filters.role;
    if (filters.isActive !== null) params.isActive = filters.isActive;
    
    const response = await usersAPI.list(params);
    tableData.value = response.data.data || response.data || [];
  } catch (err) {
    toastRef.value?.error('Gagal memuat data pengguna');
    tableData.value = [];
  } finally {
    loading.value = false;
  }
};

// Handle avatar upload
const handleAvatarUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    avatarFile.value = file;
    avatarPreview.value = URL.createObjectURL(file);
  }
};

const removeAvatar = () => {
  avatarFile.value = null;
  avatarPreview.value = null;
  if (avatarInput.value) {
    avatarInput.value.value = '';
  }
};

// Open create modal
const openCreateModal = () => {
  editingId.value = null;
  Object.assign(form, initialForm);
  avatarPreview.value = null;
  avatarFile.value = null;
  formError.value = '';
  showModal.value = true;
};

// Handle edit
const handleEdit = async (user) => {
  try {
    loading.value = true;
    const response = await usersAPI.get(user.id);
    const userData = response.data.data || response.data;
    
    editingId.value = userData.id;
    form.username = userData.username || '';
    form.name = userData.name || '';
    form.email = userData.email || '';
    form.password = '';
    form.role = userData.role || '';
    form.isActive = userData.isActive === 1 || userData.isActive === true;
    
    if (userData.avatar) {
      avatarPreview.value = `/storage/${userData.avatar}`;
    } else {
      avatarPreview.value = null;
    }
    avatarFile.value = null;
    formError.value = '';
    showModal.value = true;
  } catch (err) {
    toastRef.value?.error('Gagal memuat data pengguna');
  } finally {
    loading.value = false;
  }
};

// Handle delete
const handleDelete = (user) => {
  deletingItem.value = user;
  showDeleteModal.value = true;
};

// Confirm delete
const confirmDelete = async () => {
  try {
    deleteLoading.value = true;
    await usersAPI.delete(deletingItem.value.id);
    toastRef.value?.success('Akun berhasil dihapus');
    showDeleteModal.value = false;
    deletingItem.value = null;
    loadData();
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal menghapus akun');
  } finally {
    deleteLoading.value = false;
  }
};

// Handle toggle active
const handleToggleActive = async (user) => {
  try {
    await usersAPI.toggleActive(user.id);
    toastRef.value?.success(user.isActive ? 'Akun berhasil dinonaktifkan' : 'Akun berhasil diaktifkan');
    loadData();
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal mengubah status akun');
  }
};

// Handle submit
const handleSubmit = async () => {
  try {
    submitLoading.value = true;
    formError.value = '';

    const formData = new FormData();
    formData.append('username', form.username);
    formData.append('name', form.name);
    if (form.email) {
      formData.append('email', form.email);
    }
    if (form.password) {
      formData.append('password', form.password);
    }
    formData.append('role', form.role);
    formData.append('isActive', form.isActive ? '1' : '0');
    
    if (avatarFile.value) {
      formData.append('avatar', avatarFile.value);
    } else if (!avatarPreview.value && editingId.value) {
      formData.append('remove_avatar', '1');
    }
    
    if (editingId.value) {
      formData.append('_method', 'PUT');
      await usersAPI.update(editingId.value, formData);
      toastRef.value?.success('Akun berhasil diperbarui');
    } else {
      await usersAPI.create(formData);
      toastRef.value?.success('Akun berhasil ditambahkan');
    }
    
    closeModal();
    loadData();
  } catch (err) {
    formError.value = err.response?.data?.message || 'Terjadi kesalahan';
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors;
      if (errors.username) formError.value = errors.username[0];
      if (errors.email) formError.value = errors.email[0];
      if (errors.password) formError.value = errors.password[0];
      if (errors.role) formError.value = errors.role[0];
    }
    toastRef.value?.error(formError.value);
  } finally {
    submitLoading.value = false;
  }
};

// Close modal
const closeModal = () => {
  showModal.value = false;
  editingId.value = null;
  Object.assign(form, initialForm);
  avatarPreview.value = null;
  avatarFile.value = null;
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
  const currentUser = authAPI.getCurrentUser();
  if (currentUser) {
    currentUserId.value = currentUser.id;
  }
});
</script>

<style scoped>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
