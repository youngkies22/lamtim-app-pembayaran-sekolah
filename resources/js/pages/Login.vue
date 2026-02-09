<template>
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
        <div class="max-w-md w-full">
            <!-- Logo & Title -->
            <div class="text-center mb-8">
                <div v-if="appSettings.logo_aplikasi" class="mx-auto h-20 w-20 flex items-center justify-center rounded-2xl shadow-lg transform hover:scale-105 transition-transform duration-200 overflow-hidden bg-white dark:bg-gray-800 p-2">
                    <img 
                        :src="getLogoUrl(appSettings.logo_aplikasi)" 
                        :alt="appSettings.nama_aplikasi || 'Logo'"
                        class="w-full h-full object-contain"
                    />
                </div>
                <div v-else class="mx-auto h-16 w-16 flex items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 shadow-lg transform hover:scale-105 transition-transform duration-200">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-4xl font-bold text-gray-900 dark:text-white">
                    Silahkan Login
                </h2>
            </div>

            <!-- Login Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
                <form @submit.prevent="handleLogin" class="space-y-6">
                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <input 
                                id="email" 
                                v-model="form.email"
                                type="email" 
                                autocomplete="email" 
                                required 
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 transition duration-200"
                                placeholder="nama@email.com"
                                :class="{ 'border-red-500': errors.email }"
                            >
                        </div>
                        <p v-if="errors.email" class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ errors.email }}
                        </p>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input 
                                id="password" 
                                v-model="form.password"
                                type="password" 
                                autocomplete="current-password" 
                                required 
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 transition duration-200"
                                placeholder="••••••••"
                                :class="{ 'border-red-500': errors.password }"
                            >
                        </div>
                        <p v-if="errors.password" class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ errors.password }}
                        </p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                id="remember" 
                                v-model="form.remember"
                                type="checkbox" 
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition duration-200">
                            <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Ingat saya
                            </label>
                        </div>
                        <div class="text-sm">
                            <a href="#" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 transition duration-200">
                                Lupa password?
                            </a>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button 
                            type="submit" 
                            :disabled="loading"
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="!loading">Masuk</span>
                            <span v-else class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    © {{ new Date().getFullYear() }} {{ sekolahNama || 'Sekolah' }}. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { authAPI, configAPI } from '../services/api';
import { useAppSettings } from '../composables/useAppSettings';

const router = useRouter();
const { appSettings, getLogoUrl, updateTitle, updateFavicon } = useAppSettings();
const sekolahNama = ref('Sekolah');

const form = reactive({
    email: '',
    password: '',
    remember: false,
});

const errors = ref({});
const error = ref(null);
const loading = ref(false);

const handleLogin = async () => {
    errors.value = {};
    error.value = null;
    loading.value = true;

    try {
        const response = await authAPI.login(form.email, form.password);
        
        // Cek response - sekarang token di cookie, jadi cek success dan user data
        // Response format: { success: true, message: "...", data: { user: {...} } }
        if (response && response.success) {
            // Cookie sudah di-set otomatis oleh browser
            // User data sudah disimpan di localStorage oleh authAPI.login
            // Redirect ke dashboard
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 100);
        } else {
            error.value = response?.message || 'Login gagal. Silakan coba lagi.';
        }
    } catch (err) {
        console.error('Login error:', err);
        if (err.response?.data?.errors) {
            errors.value = err.response.data.errors;
        } else if (err.response?.data?.message) {
            error.value = err.response.data.message;
        } else {
            error.value = 'Email atau password salah';
        }
    } finally {
        loading.value = false;
    }
};

// Load public data (logo dan nama sekolah) - no auth required
onMounted(async () => {
    try {
        // Load logo aplikasi
        const settingsResponse = await configAPI.getPublicSettings();
        if (settingsResponse.data?.success && settingsResponse.data.data) {
            appSettings.value = {
                logo_aplikasi: settingsResponse.data.data.logo_aplikasi || null,
                nama_aplikasi: settingsResponse.data.data.nama_aplikasi || 'Sistem Manajemen SPP',
            };
            
            // Update title bar dan favicon menggunakan helper dari useAppSettings
            updateTitle(appSettings.value.nama_aplikasi);
            updateFavicon(appSettings.value.logo_aplikasi);
        } else {
            // Set default jika tidak ada data
            updateTitle('Sistem Manajemen SPP');
            updateFavicon(null);
        }
    } catch (err) {
        console.error('Error loading settings:', err);
        // Set default jika error
        updateTitle('Sistem Manajemen SPP');
        updateFavicon(null);
    }

    try {
        // Load nama sekolah untuk footer
        const sekolahResponse = await configAPI.getPublicSekolah();
        if (sekolahResponse.data?.success && sekolahResponse.data.data) {
            sekolahNama.value = sekolahResponse.data.data.nama || 'Sekolah';
        }
    } catch (err) {
        console.error('Error loading sekolah:', err);
    }
});
</script>
