<template>
    <Layout :active-menu="'Payments'">
        <div class="space-y-6">
            <!-- Modern Header with Gradient -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-600 p-8 shadow-xl">
                <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                            Invoice
                        </h1>
                        <p class="mt-2 text-purple-100">Kelola dan lihat invoice pembayaran siswa</p>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <button @click="handleExportExcel" :disabled="exporting"
                            class="relative inline-flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-medium rounded-xl transition-all duration-300 shadow-sm hover:shadow-md backdrop-blur-sm cursor-pointer border border-white/10 disabled:opacity-50"
                            title="Export Excel">
                            <svg v-if="!exporting" class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span class="hidden md:inline">{{ exporting ? 'Exporting...' : 'Export Excel' }}</span>
                        </button>
                        <button @click="handlePrint" :disabled="printing"
                            class="relative inline-flex items-center gap-2 px-5 py-2.5 bg-white text-violet-600 font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:transform-none cursor-pointer"
                            title="Print">
                            <svg v-if="!printing" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                </path>
                            </svg>
                            <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span class="hidden md:inline">{{ printing ? 'Loading...' : 'Print' }}</span>
                        </button>
                        <button @click="showFilter = !showFilter"
                            class="relative inline-flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-300 shadow-sm hover:shadow-md cursor-pointer"
                            :class="showFilter ? 'bg-white text-violet-600' : 'bg-white/10 text-white hover:bg-white/20 border border-white/10'">
                            <svg class="w-5 h-5 transition-transform duration-300" :class="{ 'rotate-180': showFilter }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                                </path>
                            </svg>
                        </button>
                        <button @click="loadData"
                            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl transition-all duration-300 shadow-sm hover:shadow-md backdrop-blur-sm cursor-pointer border border-white/10 group"
                            title="Reload Data">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 group-hover:rotate-180 transition-transform duration-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Closing Warning -->
            <div v-if="isClosed"
                class="p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-r-xl shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">
                            Periode Hari Ini Ditutup
                        </p>
                        <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                            Perubahan data (Create/Update/Delete) tidak diperbolehkan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <Transition name="slide-fade">
                <div v-show="showFilter" class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                                </path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filter Data Invoice</h3>
                        </div>
                        <button @click="resetFilters"
                            class="text-sm text-purple-600 hover:text-purple-700 font-medium cursor-pointer">Reset
                            Filter</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status
                                Invoice</label>
                            <select v-model="filters.status"
                                class="w-full px-4 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all outline-none"
                                @change="loadData">
                                <option value="">Semua Status</option>
                                <option value="0">Pending</option>
                                <option value="1">Lunas</option>
                                <option value="2">Dibatalkan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Periode
                                Awal</label>
                            <input v-model="filters.startDate" type="date"
                                class="w-full px-4 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all outline-none"
                                @change="loadData" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Periode
                                Akhir</label>
                            <input v-model="filters.endDate" type="date"
                                class="w-full px-4 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all outline-none"
                                @change="loadData" />
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- DataTable Section -->
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table ref="tableRef" class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/80 border-b border-gray-200 dark:border-gray-600">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    #</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    No Invoice</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Siswa</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Rombel</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Tanggal</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Nominal</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700/50">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Detail Invoice -->
        <Teleport to="body">
            <div v-if="showDetailModal"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
                @click.self="showDetailModal = false">
                <div
                    class="relative mx-auto w-full max-w-3xl shadow-2xl rounded-2xl bg-white dark:bg-gray-800 max-h-[90vh] overflow-y-auto">
                    <!-- Header Modal -->
                    <div
                        class="sticky top-0 bg-gradient-to-r from-violet-600 to-fuchsia-600 px-6 py-5 rounded-t-2xl flex items-center justify-between z-10">
                        <div>
                            <h3 class="text-xl font-bold text-white">Detail Invoice</h3>
                            <p class="text-sm text-purple-100 mt-1">Informasi lengkap invoice pembayaran</p>
                        </div>
                        <button @click="showDetailModal = false"
                            class="text-white hover:text-purple-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="p-6">
                        <div v-if="detailData" class="space-y-6">
                            <!-- Invoice Info -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">No Invoice</p>
                                    <p class="font-bold text-lg text-gray-900 dark:text-white">{{ detailData.noInvoice
                                    }}</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Tanggal & Waktu</p>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{
                                        formatDateTime(detailData.created_at) }}</p>
                                </div>
                            </div>

                            <!-- Siswa Info -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Siswa</p>
                                <p class="font-bold text-lg text-gray-900 dark:text-white">{{ detailData.siswa?.nama ||
                                    '-' }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">NIS: {{ detailData.siswa?.nis || '-'
                                }}</p>
                            </div>

                            <!-- Tagihan Info -->
                            <div v-if="detailData.tagihan" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Informasi Tagihan</p>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            {{ detailData.tagihan.masterPembayaran?.nama || '-' }}
                                        </p>
                                        <p class="text-xs text-gray-500">Kode: {{ detailData.tagihan.kodeTagihan }}</p>
                                    </div>
                                    <p class="font-bold text-gray-900 dark:text-white">
                                        Rp {{ formatNumber(detailData.tagihan.nominalTagihan) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-medium text-gray-700 dark:text-gray-300">Total
                                        Invoice</span>
                                    <span class="text-2xl font-bold text-violet-600 dark:text-violet-400">
                                        Rp {{ formatNumber(detailData.nominalInvoice) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="flex justify-center border-t border-gray-200 dark:border-gray-700 pt-4">
                                <span :class="getStatusClass(detailData.status)"
                                    class="px-6 py-2 rounded-full text-sm font-bold uppercase tracking-wider">
                                    {{ getStatusText(detailData.status) }}
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3 pt-4">
                                <button @click="openPrintModal(detailData)"
                                    class="flex-1 px-6 py-3 bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white rounded-xl hover:from-violet-700 hover:to-fuchsia-700 transition-all flex items-center justify-center gap-2 shadow-lg hover:shadow-xl font-bold">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                        </path>
                                    </svg>
                                    Print Invoice
                                </button>
                                <button @click="showDetailModal = false"
                                    class="flex-1 px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all font-semibold">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Print Modal -->
        <InvoicePrintModal :show="showPrintModal" :invoice="printInvoice" @close="showPrintModal = false" />

        <Toast ref="toastRef" />
    </Layout>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, nextTick } from 'vue';
import Layout from '../../components/layout/Layout.vue';
import Toast from '../../components/Toast.vue';
import InvoicePrintModal from '../../components/InvoicePrintModal.vue';
import { invoiceAPI } from '../../services/api';
import { useAppSettings } from '../../composables/useAppSettings';
import { useClosingCheck } from '../../composables/useClosingCheck';

const { appSettings } = useAppSettings();
const { isClosed, checkDateStatus } = useClosingCheck();

// Refs
const tableRef = ref(null);
const dataTable = ref(null);
const toastRef = ref(null);
const showDetailModal = ref(false);
const detailData = ref(null);
const showPrintModal = ref(false);
const printInvoice = ref(null);
const exporting = ref(false);
const printing = ref(false);
const showFilter = ref(false);

// Filters
const filters = reactive({
    status: '',
    startDate: new Date().toISOString().split('T')[0], // Hari ini
    endDate: new Date().toISOString().split('T')[0],   // Hari ini
});

const resetFilters = () => {
    filters.status = '';
    filters.startDate = new Date().toISOString().split('T')[0];
    filters.endDate = new Date().toISOString().split('T')[0];
    loadData();
};

// Helper
const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0);
};

const formatDateTime = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    const seconds = String(d.getSeconds()).padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
};

const getStatusClass = (status) => {
    switch (status) {
        case 1:
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 2:
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        default:
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
    }
};

const getStatusText = (status) => {
    switch (status) {
        case 1:
            return 'Lunas';
        case 2:
            return 'Dibatalkan';
        default:
            return 'Pending';
    }
};

// Initialize DataTable
const initDataTable = () => {
    if (!tableRef.value || !window.$ || !window.$.fn.DataTable) {
        return;
    }

    dataTable.value = window.$(tableRef.value).DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/invoice/datatable',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
            },
            data: function (d) {
                d.status = filters.status;
                d.startDate = filters.startDate;
                d.endDate = filters.endDate;
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    window.location.href = '/login';
                }
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
            { data: 'noInvoice', name: 'noInvoice' },
            { data: 'siswa_nama', name: 'siswa.nama' },
            { data: 'rombel_nama', name: 'siswa.currentRombel.rombel.nama', orderable: false },
            { data: 'tanggal_formatted', name: 'tanggalInvoice' },
            { data: 'nominal_formatted', name: 'nominalInvoice', orderable: false },
            { data: 'status_badge', name: 'status', orderable: false },
            {
                data: null,
                name: 'action',
                orderable: false,
                searchable: false,
                render: function (data) {
                    return `
                        <div class="flex gap-2">
                            <button data-action="detail" data-id="${data.id}" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors cursor-pointer">Detail</button>
                            <button data-action="print" data-id="${data.id}" class="px-3 py-1.5 text-xs font-medium text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors cursor-pointer">Print</button>
                        </div>
                    `;
                }
            },
        ],
        order: [[3, 'desc']],
        pageLength: 10,
        language: {
            processing: 'Memproses...',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
            infoFiltered: '(disaring dari _MAX_ total data)',
            loadingRecords: 'Memuat data...',
            zeroRecords: 'Tidak ada data yang ditemukan',
            emptyTable: 'Tidak ada data dalam tabel',
            paginate: {
                first: 'Pertama',
                previous: 'Sebelumnya',
                next: 'Selanjutnya',
                last: 'Terakhir'
            },
            aria: {
                sortAscending: ': aktifkan untuk mengurutkan kolom naik',
                sortDescending: ': aktifkan untuk mengurutkan kolom turun'
            }
        },
        drawCallback: function () {
            const table = this.api().table().node();
            window.$(table).off('click', '[data-action]').on('click', '[data-action]', function () {
                const action = this.dataset.action;
                const id = this.dataset.id;

                if (action === 'detail') {
                    handleDetail(id);
                } else if (action === 'print') {
                    handleRowPrint(id);
                }
            });
        }
    });
};

const loadData = () => {
    if (dataTable.value) {
        dataTable.value.ajax.reload();
    }
};

const handleDetail = async (id) => {
    try {
        const response = await invoiceAPI.get(id);
        detailData.value = response.data.data;
        showDetailModal.value = true;
    } catch (err) {
        toastRef.value?.error(err.response?.data?.message || 'Gagal memuat detail invoice');
    }
};

const handleRowPrint = async (id) => {
    try {
        const response = await invoiceAPI.get(id);
        printInvoice.value = response.data.data;
        showPrintModal.value = true;
    } catch (err) {
        toastRef.value?.error('Gagal memuat data invoice');
    }
};

const openPrintModal = (invoice) => {
    printInvoice.value = invoice;
    showPrintModal.value = true;
};

// Export Excel
const handleExportExcel = async () => {
    try {
        exporting.value = true;
        const response = await invoiceAPI.exportExcel({
            status: filters.status,
            startDate: filters.startDate,
            endDate: filters.endDate,
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `invoice_${new Date().toISOString().slice(0, 10)}.xlsx`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        toastRef.value?.success('Export berhasil');
    } catch (err) {
        toastRef.value?.error('Gagal export data');
    } finally {
        exporting.value = false;
    }
};

// Print List
const handlePrint = async () => {
    try {
        printing.value = true;
        const response = await invoiceAPI.list({
            status: filters.status,
            startDate: filters.startDate,
            endDate: filters.endDate,
            per_page: 9999,
        });

        const items = response.data?.data?.data || response.data?.data || [];
        const sekolahNama = appSettings.value?.sekolah?.nama || 'Sekolah';
        const tahunAjaran = appSettings.value?.tahun_ajaran?.tahun || '';
        const namaAplikasi = appSettings.value?.nama_aplikasi || 'SPP Management';

        const statusLabels = { 0: 'Pending', 1: 'Lunas', 2: 'Dibatalkan' };

        let totalNominal = 0;
        const rows = items.map((item, idx) => {
            const nominal = parseFloat(item.nominalInvoice) || 0;
            totalNominal += nominal;
            return `<tr>
                <td style="padding:6px 10px;border:1px solid #ddd;text-align:center">${idx + 1}</td>
                <td style="padding:6px 10px;border:1px solid #ddd">${item.noInvoice || '-'}</td>
                <td style="padding:6px 10px;border:1px solid #ddd">${item.siswa?.nama || '-'}</td>
                <td style="padding:6px 10px;border:1px solid #ddd">${item.rombel_nama || '-'}</td>
                <td style="padding:6px 10px;border:1px solid #ddd">${item.siswa?.nis || '-'}</td>
                <td style="padding:6px 10px;border:1px solid #ddd;text-align:right">Rp ${formatNumber(nominal)}</td>
                <td style="padding:6px 10px;border:1px solid #ddd;text-align:center">${item.tanggalInvoiceFormatted || formatDateTime(item.tanggalInvoice)}</td>
                <td style="padding:6px 10px;border:1px solid #ddd;text-align:center">${statusLabels[item.status] || '-'}</td>
            </tr>`;
        }).join('');

        const periodeLabel = filters.startDate && filters.endDate
            ? `Periode: ${filters.startDate} s/d ${filters.endDate}`
            : 'Semua Periode';

        const win = window.open('', '_blank');
        win.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>Laporan Invoice</title>
<style>
  @page { size: A4 portrait; margin: 12mm; }
  body { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; margin: 0; padding: 20px; color: #222; }
  .header { text-align: center; margin-bottom: 16px; }
  .header h2 { margin: 0; font-size: 14pt; }
  .header h3 { margin: 4px 0 0; font-size: 12pt; color: #2563eb; }
  .header p { margin: 4px 0 0; font-size: 9pt; color: #666; }
  .divider { border: none; border-top: 2px solid #333; margin: 12px 0 6px; }
  .divider-thin { border: none; border-top: 1px solid #333; margin: 0 0 16px; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #2563eb; color: #fff; padding: 8px 10px; border: 1px solid #1d4ed8; font-size: 9pt; text-align: left; }
  td { font-size: 9pt; }
  tr:nth-child(even) { background: #f0f7ff; }
  .total-row td { background: #dbeafe; font-weight: bold; border-top: 2px solid #2563eb; }
  .footer { margin-top: 16px; font-size: 8pt; color: #888; text-align: center; }
  @media print {
    body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
  }
</style></head><body>
  <div class="header">
    <h2>${sekolahNama.toUpperCase()}</h2>
    <h3>LAPORAN INVOICE</h3>
    ${tahunAjaran ? `<p>Tahun Ajaran ${tahunAjaran}</p>` : ''}
    <p>${periodeLabel}</p>
  </div>
  <hr class="divider"><hr class="divider-thin">
  <table>
    <thead>
      <tr>
        <th style="text-align:center;width:30px">No</th>
        <th>No Invoice</th>
        <th>Nama Siswa</th>
        <th>Rombel</th>
        <th>NIS</th>
        <th style="text-align:right">Nominal</th>
        <th style="text-align:center">Tanggal</th>
        <th style="text-align:center">Status</th>
      </tr>
    </thead>
    <tbody>
      ${rows || '<tr><td colspan="7" style="text-align:center;padding:20px;border:1px solid #ddd;color:#999">Tidak ada data</td></tr>'}
      ${items.length > 0 ? `<tr class="total-row">
        <td colspan="4" style="padding:8px 10px;border:1px solid #ddd;text-align:center">TOTAL (${items.length} transaksi)</td>
        <td style="padding:8px 10px;border:1px solid #ddd;text-align:right">Rp ${formatNumber(totalNominal)}</td>
        <td colspan="2" style="padding:8px 10px;border:1px solid #ddd"></td>
      </tr>` : ''}
    </tbody>
  </table>
  <div class="footer">
    <p>Dicetak pada: ${new Date().toLocaleString('id-ID')} | ${namaAplikasi}</p>
  </div>
</body></html>`);
        win.document.close();
        setTimeout(() => win.print(), 400);
    } catch (err) {
        toastRef.value?.error('Gagal memuat data for print');
    } finally {
        printing.value = false;
    }
};

onUnmounted(() => {
    if (dataTable.value) {
        dataTable.value.destroy();
        dataTable.value = null;
    }
});

onMounted(async () => {
    await nextTick();
    initDataTable();
    await checkDateStatus(new Date());
});
</script>

<style scoped>
.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
    transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateY(-20px);
    opacity: 0;
}

.bg-grid-pattern {
    background-image: radial-gradient(circle, #fff 1px, transparent 1px);
    background-size: 20px 20px;
}
</style>
