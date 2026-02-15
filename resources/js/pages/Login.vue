<template>
    <div
        class="min-h-screen flex items-center justify-center py-6 px-4 sm:px-6 lg:px-8 bg-[#0f172a] relative overflow-hidden">
        <!-- Abstract Background Elements -->
        <div
            class="absolute top-0 -left-10 w-96 h-96 bg-blue-600 rounded-full mix-blend-multiply filter blur-[100px] opacity-20 animate-blob">
        </div>
        <div
            class="absolute top-0 -right-10 w-96 h-96 bg-purple-600 rounded-full mix-blend-multiply filter blur-[100px] opacity-20 animate-blob animation-delay-2000">
        </div>
        <div
            class="absolute -bottom-20 left-40 w-96 h-96 bg-indigo-600 rounded-full mix-blend-multiply filter blur-[100px] opacity-20 animate-blob animation-delay-4000">
        </div>

        <div class="max-w-md w-full relative z-10">
            <!-- Logo & Title -->
            <div class="text-center mb-6">
                <div v-if="appSettings.logo_aplikasi"
                    class="mx-auto h-20 w-20 flex items-center justify-center rounded-2xl shadow-2xl transform hover:scale-110 transition-all duration-300 overflow-hidden bg-white/10 backdrop-blur-md border border-white/20 p-2.5">
                    <img :src="getLogoUrl(appSettings.logo_aplikasi)" :alt="appSettings.nama_aplikasi || 'Logo'"
                        class="w-full h-full object-contain filter drop-shadow-lg" />
                </div>
                <div v-else
                    class="mx-auto h-16 w-16 flex items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 shadow-2xl transform hover:scale-110 transition-all duration-300 border border-white/20">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                        </path>
                    </svg>
                </div>
                <h2 class="mt-4 text-center text-3xl font-extrabold text-white tracking-tight">
                    {{ appSettings.nama_aplikasi || 'Welcome Back' }}
                </h2>
                <p class="mt-1 text-blue-200/50 text-sm font-medium">Silahkan masuk ke akun Anda</p>
            </div>

            <!-- Login Card -->
            <div
                class="bg-white/5 backdrop-blur-xl rounded-[2.5rem] shadow-2xl p-8 border border-white/10 ring-1 ring-white/5 animate-fade-in-up">
                <form @submit.prevent="handleLogin" class="space-y-6">
                    <!-- Email Input -->
                    <div class="group">
                        <label for="email"
                            class="block text-sm font-semibold text-blue-100/70 mb-2.5 ml-1 transition-colors group-focus-within:text-blue-400">
                            Email Address
                        </label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-transform group-focus-within:scale-110">
                                <svg class="h-5 w-5 text-blue-400/40 group-focus-within:text-blue-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207">
                                    </path>
                                </svg>
                            </div>
                            <input id="email" v-model="form.email" type="email" autocomplete="email" required
                                class="block w-full pl-12 pr-4 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-white/20 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 hover:bg-white/[0.08]"
                                placeholder="nama@email.com"
                                :class="{ 'border-red-500/50 ring-1 ring-red-500/20 bg-red-500/5': errors.email }">
                        </div>
                        <p v-if="errors.email" class="mt-2.5 text-xs text-red-400 flex items-center ml-1">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            {{ errors.email }}
                        </p>
                    </div>

                    <!-- Password Input -->
                    <div class="group">
                        <label for="password"
                            class="block text-sm font-semibold text-blue-100/70 mb-2.5 ml-1 transition-colors group-focus-within:text-blue-400">
                            Password
                        </label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-transform group-focus-within:scale-110">
                                <svg class="h-5 w-5 text-blue-400/40 group-focus-within:text-blue-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                            <input id="password" v-model="form.password" type="password" autocomplete="current-password"
                                required
                                class="block w-full pl-12 pr-4 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-white/20 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 hover:bg-white/[0.08]"
                                placeholder="••••••••"
                                :class="{ 'border-red-500/50 ring-1 ring-red-500/20 bg-red-500/5': errors.password }">
                        </div>
                        <p v-if="errors.password" class="mt-2.5 text-xs text-red-400 flex items-center ml-1">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            {{ errors.password }}
                        </p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-center px-1">
                        <div class="flex items-center">
                            <input id="remember" v-model="form.remember" type="checkbox"
                                class="h-4.5 w-4.5 bg-white/5 border-white/10 rounded text-blue-600 focus:ring-blue-500/50 focus:ring-offset-0 transition-all cursor-pointer">
                            <label for="remember"
                                class="ml-2.5 block text-sm text-blue-100/50 hover:text-blue-100 transition-colors cursor-pointer">
                                Ingat saya
                            </label>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="error" class="bg-red-500/10 border border-red-500/20 rounded-2xl p-4 animate-shake">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-400 mr-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm text-red-400 font-medium">{{ error }}</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" :disabled="loading"
                            class="w-full relative group overflow-hidden py-4 px-6 rounded-2xl shadow-[0_20px_50px_rgba(37,99,235,0.3)] text-white font-bold text-lg transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed transform hover:-translate-y-1 active:translate-y-0">
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-600 group-hover:from-blue-500 group-hover:to-indigo-500 transition-all duration-300">
                            </div>
                            <span class="relative flex justify-center items-center">
                                <span v-if="!loading">Masuk Sekarang</span>
                                <span v-else class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Memproses...
                                </span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-xs text-blue-200/30 font-medium tracking-wide">
                    © 2026 <span class="text-blue-200/50">{{ appSettings.nama_aplikasi || 'Aplikasi' }}</span>. All
                    rights reserved.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }

    33% {
        transform: translate(30px, -50px) scale(1.1);
    }

    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }

    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-shake {
    animation: shake 0.5s cubic-bezier(.36, .07, .19, .97) both;
}

@keyframes shake {

    10%,
    90% {
        transform: translate3d(-1px, 0, 0);
    }

    20%,
    80% {
        transform: translate3d(2px, 0, 0);
    }

    30%,
    50%,
    70% {
        transform: translate3d(-4px, 0, 0);
    }

    40%,
    60% {
        transform: translate3d(4px, 0, 0);
    }
}
</style>


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
        const response = await authAPI.login(form.email, form.password, form.remember);

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
