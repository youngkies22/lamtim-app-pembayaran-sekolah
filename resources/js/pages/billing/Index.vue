<template>
    <Layout :active-menu="'Payments'">
        <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Header -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Billing Pembayaran</h1>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Input pembayaran siswa dengan cepat dan mudah</p>
                            </div>
                        </div>
                        <div class="hidden md:flex items-center gap-2 bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-2">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Form Input (Left Side) -->
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Form Pembayaran</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Isi data pembayaran siswa</p>
                                </div>
                            </div>

                            <form @submit.prevent="handleSubmit" class="space-y-5">
                                <!-- Pilih Siswa -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Pilih Siswa <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <input
                                            v-model="searchSiswa"
                                            type="text"
                                            placeholder="Cari siswa (NIS atau Nama)..."
                                            class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                            @input="filterSiswaList"
                                        />
                                    </div>
                                    <select
                                        v-model="form.idSiswa"
                                        required
                                        class="w-full mt-2 px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                        @change="onSiswaChange"
                                    >
                                        <option value="">Pilih Siswa</option>
                                        <option v-for="siswa in filteredSiswaList" :key="siswa.id" :value="siswa.id">
                                            {{ siswa.nis || '-' }} - {{ siswa.nama || '-' }} - {{ siswa.rombel || '-' }}
                                        </option>
                                    </select>
                                    <div v-if="selectedSiswa" class="mt-3 p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-200 dark:border-emerald-800">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-semibold text-emerald-900 dark:text-emerald-100">{{ selectedSiswa.nama }}</p>
                                                <p class="text-sm text-emerald-700 dark:text-emerald-300">NIS: {{ selectedSiswa.nis || '-' }} | NISN: {{ selectedSiswa.nisn || '-' }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs text-emerald-600 dark:text-emerald-400">Siswa Terpilih</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pilih Tagihan (Jenis Pembayaran) -->
                                <div v-if="form.idSiswa">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Pilih Tagihan (Jenis Pembayaran) <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <select
                                            v-model="form.idTagihan"
                                            required
                                            :disabled="loadingTagihan"
                                            class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                            @change="onTagihanChange"
                                        >
                                            <option value="">{{ loadingTagihan ? 'Memuat tagihan...' : 'Pilih Tagihan' }}</option>
                                            <option v-for="tagihan in tagihanList" :key="tagihan.id" :value="tagihan.id">
                                                [{{ tagihan.masterPembayaran?.kategori || '-' }}] {{ tagihan.masterPembayaran?.kode || '-' }} (Rp {{ formatNumber(tagihan.nominalTagihan) }}) - Sisa: Rp {{ formatNumber(tagihan.totalSisa) }}
                                            </option>
                                        </select>
                                    </div>
                                    <div v-if="selectedTagihan" class="mt-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-semibold text-blue-900 dark:text-blue-100">{{ selectedTagihan.masterPembayaran?.kode || '-' }}</p>
                                                <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">
                                                    Kategori: <span class="font-medium">[{{ selectedTagihan.masterPembayaran?.kategori || '-' }}]</span>
                                                </p>
                                                <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                                    Tagihan: <span class="font-bold">Rp {{ formatNumber(selectedTagihan.nominalTagihan) }}</span>
                                                    | Sisa: <span class="font-bold">Rp {{ formatNumber(selectedTagihan.totalSisa) }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="!loadingTagihan && tagihanList.length === 0 && form.idSiswa" class="mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl border border-yellow-200 dark:border-yellow-800">
                                        <p class="text-sm text-yellow-700 dark:text-yellow-300">Tidak ada tagihan yang belum lunas untuk siswa ini</p>
                                    </div>
                                </div>
                                <div v-else class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Pilih siswa terlebih dahulu untuk melihat tagihan</p>
                                </div>

                                <!-- Nominal dan Metode Bayar -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            Nominal Bayar <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-medium">Rp</span>
                                            <input
                                                v-model="form.nominalBayarFormatted"
                                                type="text"
                                                required
                                                :maxlength="20"
                                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-lg font-semibold"
                                                placeholder="0"
                                                @input="onNominalInput"
                                                @blur="onNominalBlur"
                                            />
                                        </div>
                                        <p v-if="selectedTagihan" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Maksimal: Rp {{ formatNumber(selectedTagihan.totalSisa) }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            Metode Bayar <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            v-model="form.metodeBayar"
                                            required
                                            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                        >
                                            <option value="">Pilih Metode</option>
                                            <option value="Tunai">Tunai</option>
                                            <option value="Transfer">Transfer Bank</option>
                                            <option value="QRIS">QRIS</option>
                                            <option value="Debit">Debit</option>
                                            <option value="Kredit">Kredit</option>
                                            <option value="LAINNYA">Lainnya</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Keterangan -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Keterangan
                                        <span class="text-xs text-gray-500 font-normal">(opsional - bisa tulis channel bayar di sini)</span>
                                    </label>
                                    <textarea
                                        v-model="form.keterangan"
                                        rows="3"
                                        class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all resize-none"
                                        placeholder="Keterangan tambahan (contoh: Transfer BCA, Atas nama: ...)"
                                    ></textarea>
                                </div>

                                <!-- Error Message -->
                                <div v-if="formError" class="p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-lg">
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ formError }}</p>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex gap-3 pt-4">
                                    <button
                                        type="button"
                                        @click="resetForm"
                                        class="flex-1 px-6 py-3.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all"
                                    >
                                        Reset
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="loading"
                                        class="flex-1 px-6 py-3.5 text-sm font-semibold bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-xl hover:from-emerald-700 hover:to-teal-800 disabled:opacity-50 transition-all flex items-center justify-center shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                    >
                                        <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ loading ? 'Memproses...' : 'Proses Pembayaran' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Preview Billing (Right Side) -->
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 sticky top-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Preview Billing</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Pratinjau struk pembayaran</p>
                                </div>
                            </div>

                            <!-- Billing Preview -->
                            <div id="billing-preview" class="bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-6 space-y-4">
                                <!-- Header -->
                                <div class="text-center border-b-2 border-gray-200 dark:border-gray-700 pb-4">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">BUKTI PEMBAYARAN</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ appSettings.nama_aplikasi || 'Sistem Pembayaran SPP' }}</p>
                                </div>

                                <!-- Info Siswa -->
                                <div v-if="selectedSiswa" class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">NIS:</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ selectedSiswa.nis || '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Nama:</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ selectedSiswa.nama || '-' }}</span>
                                    </div>
                                </div>
                                <div v-else class="text-center py-4 text-gray-400">
                                    <p class="text-sm">Pilih siswa terlebih dahulu</p>
                                </div>

                                <!-- Info Pembayaran -->
                                <div v-if="selectedTagihan" class="space-y-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Jenis:</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ selectedTagihan.masterPembayaran?.nama || '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Kode:</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ selectedTagihan.masterPembayaran?.kode || '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Kategori:</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ getKategoriNama(selectedTagihan.masterPembayaran?.kategori) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Tagihan:</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">Rp {{ formatNumber(selectedTagihan.nominalTagihan) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Sisa:</span>
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ formatNumber(selectedTagihan.totalSisa) }}</span>
                                    </div>
                                </div>

                                <!-- Detail Pembayaran -->
                                <div v-if="form.nominalBayar > 0" class="space-y-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Nominal Bayar:</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">Rp {{ formatNumber(form.nominalBayar) }}</span>
                                    </div>
                                    <div v-if="form.metodeBayar" class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Metode:</span>
                                        <span class="font-semibold text-gray-900 dark:text-white capitalize">{{ form.metodeBayar }}</span>
                                    </div>
                                </div>

                                <!-- Total -->
                                <div v-if="form.nominalBayar > 0" class="pt-4 border-t-2 border-gray-300 dark:border-gray-600">
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">TOTAL</span>
                                        <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">Rp {{ formatNumber(form.nominalBayar) }}</span>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="pt-4 border-t border-gray-200 dark:border-gray-700 text-center">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ new Date().toLocaleString('id-ID') }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Terima kasih atas pembayaran Anda</p>
                                </div>
                            </div>

                            <!-- Print Button -->
                            <button
                                v-if="form.idSiswa && form.idTagihan && form.nominalBayar > 0"
                                @click="printBilling"
                                class="w-full mt-4 px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all flex items-center justify-center gap-2 shadow-lg hover:shadow-xl"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Print Billing
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Toast ref="toastRef" />
    </Layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Layout from '../../components/layout/Layout.vue';
import Toast from '../../components/Toast.vue';
import { pembayaranAPI, tagihanAPI } from '../../services/api';
import { usePaymentCache } from '../../composables/usePaymentCache';
import { useAppSettings } from '../../composables/useAppSettings';

const router = useRouter();
const toastRef = ref(null);
const loading = ref(false);
const loadingTagihan = ref(false);
const formError = ref('');
const searchSiswa = ref('');

const { appSettings } = useAppSettings();
const { loadAll, getCached, getKategoriNama } = usePaymentCache();

// Get cached data immediately (synchronous)
const cached = getCached();
const siswaList = ref(Array.isArray(cached.siswa) ? [...cached.siswa] : []);
const tagihanList = ref([]);

// Form
const initialForm = {
    idSiswa: '',
    idTagihan: '',
    nominalBayar: 0,
    metodeBayar: 'Tunai',
    keterangan: '',
};

const form = reactive({ ...initialForm, nominalBayarFormatted: '0' });

// Computed
const selectedSiswa = computed(() => {
    return siswaList.value.find(s => s && s.id === form.idSiswa);
});

const selectedTagihan = computed(() => {
    return tagihanList.value.find(t => t && t.id === form.idTagihan);
});

const filteredSiswaList = computed(() => {
    if (!searchSiswa.value) {
        return siswaList.value.filter(siswa => siswa && siswa.id);
    }
    const search = searchSiswa.value.toLowerCase();
    return siswaList.value.filter(siswa => 
        siswa && siswa.id && (
            (siswa.nis && siswa.nis.toLowerCase().includes(search)) ||
            (siswa.nama && siswa.nama.toLowerCase().includes(search)) ||
            (siswa.rombel && siswa.rombel.toLowerCase().includes(search))
        )
    );
});


// Helper
const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0);
};

// Format nominal input dengan separator ribuan
const formatNominalInput = (value) => {
    // Hapus semua karakter selain angka
    const numericValue = value.replace(/\D/g, '');
    
    if (!numericValue) {
        return '0';
    }
    
    // Format dengan separator ribuan
    return new Intl.NumberFormat('id-ID').format(parseInt(numericValue));
};

// Parse nominal dari format string ke number
const parseNominal = (formattedValue) => {
    // Hapus semua karakter selain angka
    const numericValue = formattedValue.replace(/\D/g, '');
    return parseInt(numericValue) || 0;
};

// Handle input nominal
const onNominalInput = (event) => {
    const inputValue = event.target.value;
    const formatted = formatNominalInput(inputValue);
    form.nominalBayarFormatted = formatted;
    form.nominalBayar = parseNominal(formatted);
};

// Handle blur nominal (pastikan valid)
const onNominalBlur = () => {
    if (!form.nominalBayarFormatted || form.nominalBayarFormatted === '0') {
        form.nominalBayarFormatted = '0';
        form.nominalBayar = 0;
    } else {
        // Pastikan format tetap konsisten
        form.nominalBayarFormatted = formatNominalInput(form.nominalBayarFormatted);
        form.nominalBayar = parseNominal(form.nominalBayarFormatted);
    }
    
    // Validasi maksimal
    if (selectedTagihan.value && form.nominalBayar > selectedTagihan.value.totalSisa) {
        form.nominalBayar = selectedTagihan.value.totalSisa;
        form.nominalBayarFormatted = formatNumber(selectedTagihan.value.totalSisa);
    }
};

// Load data with cache
const loadData = async () => {
    try {
        const result = await loadAll();
        siswaList.value = result.siswa || [];
    } catch (err) {
        console.error('Error loading data:', err);
        toastRef.value?.error('Gagal memuat data');
    }
};

const filterSiswaList = () => {
    // Filter is handled by computed property
};

const onSiswaChange = async () => {
    // Reset tagihan selection
    form.idTagihan = '';
    form.nominalBayar = 0;
    form.nominalBayarFormatted = '0';
    tagihanList.value = [];
    
    if (form.idSiswa) {
        await loadTagihanSiswa();
    }
};

const loadTagihanSiswa = async () => {
    if (!form.idSiswa) return;
    
    try {
        loadingTagihan.value = true;
        const response = await tagihanAPI.getBySiswa(form.idSiswa);
        if (response.data && response.data.success) {
            tagihanList.value = response.data.data || [];
        }
    } catch (err) {
        console.error('Error loading tagihan:', err);
        toastRef.value?.error('Gagal memuat tagihan siswa');
        tagihanList.value = [];
    } finally {
        loadingTagihan.value = false;
    }
};

const onTagihanChange = () => {
    // Reset nominal ke 0
    form.nominalBayar = 0;
    form.nominalBayarFormatted = '0';
};

const handleSubmit = async () => {
    try {
        loading.value = true;
        formError.value = '';
        
        await pembayaranAPI.proses(form);
        toastRef.value?.success('Pembayaran berhasil diproses');
        
        // Reset form
        resetForm();
        
        // Redirect to pembayaran list
        setTimeout(() => {
            router.push('/pembayaran');
        }, 1500);
    } catch (err) {
        formError.value = err.response?.data?.message || 'Terjadi kesalahan saat memproses pembayaran';
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    Object.assign(form, { ...initialForm, nominalBayarFormatted: '0' });
    searchSiswa.value = '';
    formError.value = '';
};

const printBilling = () => {
    const printContent = document.getElementById('billing-preview');
    if (!printContent) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Billing Pembayaran</title>
            <style>
                @media print {
                    @page {
                        size: 80mm auto;
                        margin: 0;
                    }
                    body {
                        margin: 0;
                        padding: 10px;
                    }
                }
                body {
                    font-family: Arial, sans-serif;
                    max-width: 80mm;
                    margin: 0 auto;
                    padding: 20px;
                }
                .text-center { text-align: center; }
                .border-b-2 { border-bottom: 2px solid #e5e7eb; }
                .border-t { border-top: 1px solid #e5e7eb; }
                .pb-4 { padding-bottom: 1rem; }
                .pt-2 { padding-top: 0.5rem; }
                .pt-4 { padding-top: 1rem; }
                .space-y-2 > * + * { margin-top: 0.5rem; }
                .space-y-4 > * + * { margin-top: 1rem; }
                .flex { display: flex; }
                .justify-between { justify-content: space-between; }
                .text-sm { font-size: 0.875rem; }
                .text-xs { font-size: 0.75rem; }
                .text-lg { font-size: 1.125rem; }
                .text-2xl { font-size: 1.5rem; }
                .font-bold { font-weight: 700; }
                .font-semibold { font-weight: 600; }
                .text-gray-600 { color: #4b5563; }
                .text-gray-900 { color: #111827; }
                .text-emerald-600 { color: #059669; }
            </style>
        </head>
        <body>
            ${printContent.innerHTML}
        </body>
        </html>
    `);
    printWindow.document.close();
    
    setTimeout(() => {
        printWindow.print();
    }, 250);
};

onMounted(async () => {
    // Set document title
    document.title = 'Billing Pembayaran - ' + (appSettings.nama_aplikasi || 'Sistem Pembayaran SPP');
    await loadData();
});
</script>

<style>
@media print {
    @page {
        size: 80mm auto;
        margin: 0;
    }
    body {
        margin: 0;
        padding: 10px;
    }
}
</style>
