<template>
  <Layout :active-menu="'Profile'">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-6 md:p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative">
          <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center gap-3">
            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
              <UserIcon class="w-6 h-6 md:w-8 md:h-8 text-white" />
            </div>
            Profil Saya
          </h1>
          <p class="mt-2 text-indigo-100 text-sm md:text-base">Kelola informasi profil dan keamanan akun Anda</p>
        </div>
      </div>

      <!-- Profile Card -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <!-- Profile Header -->
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 px-6 py-8 border-b border-gray-200 dark:border-gray-700">
          <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
            <!-- Avatar -->
            <div class="relative">
              <div v-if="avatarPreview || user?.avatar" class="w-24 h-24 md:w-32 md:h-32 rounded-2xl overflow-hidden shadow-lg border-2 border-indigo-500">
                <img 
                  :src="avatarPreview || getAvatarUrl(user?.avatar)" 
                  :alt="user?.name || 'Avatar'"
                  class="w-full h-full object-cover"
                />
              </div>
              <div v-else class="w-24 h-24 md:w-32 md:h-32 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white font-bold text-3xl md:text-4xl shadow-lg">
                {{ getInitials(user?.name || user?.username || 'U') }}
              </div>
              <button
                @click="avatarInput?.click()"
                class="absolute -bottom-2 -right-2 p-2 bg-white dark:bg-gray-800 rounded-full shadow-lg border-2 border-indigo-500 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors"
                title="Ubah foto profil"
              >
                <CameraIcon class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
              </button>
              <input
                ref="avatarInput"
                type="file"
                @change="handleAvatarUpload"
                accept="image/*"
                class="hidden"
              />
            </div>

            <!-- User Info -->
            <div class="flex-1 text-center md:text-left">
              <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                {{ user?.name || user?.username || 'User' }}
              </h2>
              <p class="text-gray-600 dark:text-gray-400 mt-1">{{ user?.email || '-' }}</p>
              <div class="mt-3 flex flex-wrap items-center justify-center md:justify-start gap-2">
                <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-semibold">
                  {{ getRoleLabel(user?.role) }}
                </span>
                <span v-if="user?.isActive" class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full text-xs font-semibold">
                  Aktif
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Section -->
        <div class="p-6 md:p-8">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Error Message -->
            <div v-if="formError" class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
              <p class="text-sm text-red-800 dark:text-red-200">{{ formError }}</p>
            </div>

            <!-- Personal Information -->
            <div class="space-y-5">
              <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <div class="w-1 h-6 bg-indigo-600 rounded-full"></div>
                Informasi Pribadi
              </h3>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Username -->
                <div>
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Username <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.username"
                    type="text"
                    required
                    :disabled="true"
                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                    placeholder="Username"
                  />
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Username tidak dapat diubah</p>
                </div>

                <!-- Name -->
                <div>
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Nama Lengkap <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white"
                    placeholder="Nama lengkap"
                  />
                </div>

                <!-- Email -->
                <div>
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Email
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white"
                    placeholder="email@example.com"
                  />
                </div>

                <!-- Role (Read Only) -->
                <div>
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Role
                  </label>
                  <input
                    :value="getRoleLabel(user?.role)"
                    type="text"
                    disabled
                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                  />
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Role tidak dapat diubah</p>
                </div>
              </div>
            </div>

            <!-- Password Section -->
            <div class="space-y-5 pt-6 border-t border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <div class="w-1 h-6 bg-indigo-600 rounded-full"></div>
                Ubah Password
              </h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">Kosongkan jika tidak ingin mengubah password</p>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- New Password -->
                <div>
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Password Baru
                  </label>
                  <div class="relative">
                    <input
                      v-model="form.password"
                      :type="showPassword ? 'text' : 'password'"
                      autocomplete="new-password"
                      class="w-full px-4 py-3 pr-12 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white"
                      placeholder="Password baru"
                    />
                    <button
                      type="button"
                      @click="showPassword = !showPassword"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                      <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                      <EyeSlashIcon v-else class="w-5 h-5" />
                    </button>
                  </div>
                </div>

                <!-- Confirm Password -->
                <div>
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Konfirmasi Password
                  </label>
                  <div class="relative">
                    <input
                      v-model="form.password_confirmation"
                      :type="showPasswordConfirmation ? 'text' : 'password'"
                      autocomplete="new-password"
                      class="w-full px-4 py-3 pr-12 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white"
                      placeholder="Konfirmasi password baru"
                    />
                    <button
                      type="button"
                      @click="showPasswordConfirmation = !showPasswordConfirmation"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                      <EyeIcon v-if="!showPasswordConfirmation" class="w-5 h-5" />
                      <EyeSlashIcon v-else class="w-5 h-5" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
              <button
                type="button"
                @click="handleCancel"
                class="w-full sm:w-auto px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="submitLoading"
                class="w-full sm:w-auto px-6 py-3 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              >
                <svg v-if="submitLoading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ submitLoading ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Layout from '../components/layout/Layout.vue';
import Toast from '../components/Toast.vue';
import { authAPI } from '../services/api';
import {
  UserIcon,
  CameraIcon,
  EyeIcon,
  EyeSlashIcon,
} from '@heroicons/vue/24/outline';

const router = useRouter();
const toastRef = ref(null);
const submitLoading = ref(false);
const formError = ref('');
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const showAvatarUpload = ref(false);
const avatarInput = ref(null);
const avatarFile = ref(null);
const avatarPreview = ref(null);

const user = ref(null);

const form = reactive({
  username: '',
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

// Get user initials
const getInitials = (name) => {
  if (!name) return 'U';
  const parts = name.trim().split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
};

// Get role label
const getRoleLabel = (role) => {
  const roles = {
    1: 'Admin',
    2: 'Operator',
    3: 'Kepala Sekolah',
  };
  return roles[role] || 'User';
};

// Get avatar URL
const getAvatarUrl = (avatarPath) => {
  if (!avatarPath) return null;
  if (avatarPath.startsWith('http://') || avatarPath.startsWith('https://')) {
    return avatarPath;
  }
  return `/storage/${avatarPath}`;
};

// Handle avatar upload
const handleAvatarUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    // Validate file size (2MB)
    if (file.size > 2 * 1024 * 1024) {
      toastRef.value?.error('Ukuran file maksimal 2MB');
      return;
    }
    // Validate file type
    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
    if (!validTypes.includes(file.type)) {
      toastRef.value?.error('Format file tidak didukung. Gunakan JPG, PNG, GIF, SVG, atau WEBP');
      return;
    }
    avatarFile.value = file;
    avatarPreview.value = URL.createObjectURL(file);
    showAvatarUpload.value = true;
  }
};

// Remove avatar
const removeAvatar = () => {
  avatarFile.value = null;
  avatarPreview.value = null;
  if (avatarInput.value) {
    avatarInput.value.value = '';
  }
  // Will be handled in submit to remove from server
};

// Load user data
const loadUser = () => {
  const currentUser = authAPI.getCurrentUser();
  if (currentUser) {
    user.value = currentUser;
    form.username = currentUser.username || '';
    form.name = currentUser.name || '';
    form.email = currentUser.email || '';
    if (currentUser.avatar) {
      avatarPreview.value = getAvatarUrl(currentUser.avatar);
    }
  }
};

// Handle submit
const handleSubmit = async () => {
  try {
    submitLoading.value = true;
    formError.value = '';

    // Validate password if provided
    if (form.password) {
      if (form.password.length < 8) {
        formError.value = 'Password minimal 8 karakter';
        return;
      }
      if (form.password !== form.password_confirmation) {
        formError.value = 'Password dan konfirmasi password tidak cocok';
        return;
      }
    }

    // Use FormData if avatar is being uploaded
    const formData = new FormData();
    formData.append('name', form.name);
    if (form.email) {
      formData.append('email', form.email);
    }
    
    // Only include password if provided
    if (form.password) {
      formData.append('password', form.password);
      formData.append('password_confirmation', form.password_confirmation);
    }

    // Handle avatar
    if (avatarFile.value) {
      formData.append('avatar', avatarFile.value);
    } else if (!avatarPreview.value && !user.value?.avatar) {
      // Remove avatar if no preview and no existing avatar
      formData.append('remove_avatar', '1');
    }

    await authAPI.updateProfile(formData);
    
    // Update local storage
    const response = await authAPI.getCurrentUser();
    if (response) {
      localStorage.setItem('auth_user', JSON.stringify(response));
      user.value = response;
    }

    // Clear avatar preview and file
    avatarFile.value = null;
    avatarPreview.value = user.value?.avatar ? getAvatarUrl(user.value.avatar) : null;
    showAvatarUpload.value = false;

    toastRef.value?.success('Profil berhasil diperbarui');
  } catch (err) {
    formError.value = err.response?.data?.message || 'Terjadi kesalahan saat memperbarui profil';
    toastRef.value?.error(formError.value);
  } finally {
    submitLoading.value = false;
  }
};

// Handle cancel
const handleCancel = () => {
  loadUser();
  form.password = '';
  form.password_confirmation = '';
  formError.value = '';
  avatarFile.value = null;
  avatarPreview.value = user.value?.avatar ? getAvatarUrl(user.value.avatar) : null;
  showAvatarUpload.value = false;
  if (avatarInput.value) {
    avatarInput.value.value = '';
  }
};

onMounted(() => {
  loadUser();
});
</script>

<style scoped>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
