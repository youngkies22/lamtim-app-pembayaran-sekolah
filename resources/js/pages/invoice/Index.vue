<template>
    <Layout :active-menu="'Payments'">
        <div class="space-y-6">
            <!-- Header dengan Card Modern -->
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl shadow-lg p-6 text-white">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Invoice</h1>
                    <p class="text-purple-100">Kelola dan lihat invoice pembayaran siswa</p>
                </div>
            </div>

            <!-- Filters dengan Design Modern -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5">
                <div class="flex items-center gap-4 mb-4">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filter</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status
                            Invoice</label>
                        <select v-model="filters.status"
                            class="w-full px-4 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
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
                            class="w-full px-4 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                            @change="loadData" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Periode
                            Akhir</label>
                        <input v-model="filters.endDate" type="date"
                            class="w-full px-4 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                            @change="loadData" />
                    </div>
                </div>
            </div>

            <!-- DataTable dengan Design Modern -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table ref="tableRef" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                    No</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                    No Invoice</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                    Siswa</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                    Tanggal</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                    Nominal</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Detail Invoice -->
        <Teleport to="body">
            <div v-if="showDetailModal"
                class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
                @click.self="showDetailModal = false">
                <div
                    class="relative mx-auto w-full max-w-3xl shadow-2xl rounded-2xl bg-white dark:bg-gray-800 max-h-[90vh] overflow-y-auto">
                    <div
                        class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-5 rounded-t-2xl flex items-center justify-between sticky top-0 z-10">
                        <div>
                            <h3 class="text-xl font-bold text-white">Detail Invoice</h3>
                            <p class="text-sm text-purple-100 mt-1">Informasi lengkap invoice pembayaran</p>
                        </div>
                        <button @click="showDetailModal = false"
                            class="text-white hover:text-purple-100 transition-colors cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <div v-if="detailData" class="space-y-6">
                            <!-- Invoice Info -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">No Invoice</p>
                                        <p class="font-semibold text-gray-900 dark:text-white text-lg">{{
                                            detailData.noInvoice }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal & Waktu</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{
                                            formatDateTime(detailData.created_at) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Siswa Info -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Informasi Siswa
                                </h4>
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ detailData.siswa?.nama ||
                                        '-' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">NIS: {{ detailData.siswa?.nis ||
                                        '-' }}</p>
                                </div>
                            </div>

                            <!-- Tagihan Info -->
                            <div v-if="detailData.tagihan">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tagihan</h4>
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{
                                                detailData.tagihan.masterPembayaran?.nama || '-' }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{
                                                detailData.tagihan.kodeTagihan }}</p>
                                        </div>
                                        <p class="font-semibold text-gray-900 dark:text-white">Rp {{
                                            formatNumber(detailData.tagihan.nominalTagihan) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-medium text-gray-700 dark:text-gray-300">Total</span>
                                    <span class="text-2xl font-bold text-gray-900 dark:text-white">Rp {{
                                        formatNumber(detailData.nominalInvoice) }}</span>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="flex justify-center">
                                <span :class="getStatusClass(detailData.status)"
                                    class="px-4 py-2 rounded-full text-sm font-semibold">
                                    {{ getStatusText(detailData.status) }}
                                </span>
                            </div>

                            <!-- Print Button -->
                            <div class="flex justify-center pt-4">
                                <button @click="openPrintModal(detailData)"
                                    class="px-6 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all flex items-center gap-2 cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                        </path>
                                    </svg>
                                    Print Invoice
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

// Refs
const tableRef = ref(null);
const dataTable = ref(null);
const toastRef = ref(null);
const showDetailModal = ref(false);
const detailData = ref(null);
const showPrintModal = ref(false);
const printInvoice = ref(null);

// Filters
const filters = reactive({
    status: '',
    startDate: '',
    endDate: '',
});

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
            { data: 'tanggal_formatted', name: 'tanggalInvoice' },
            { data: 'nominal_formatted', name: 'nominalInvoice', orderable: false },
            { data: 'status_badge', name: 'status', orderable: false },
            {
                data: null,
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'cursor-pointer',
                render: function (data) {
                    return `
                        <div class="flex gap-2">
                            <button data-action="detail" data-id="${data.id}" class="px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm cursor-pointer">Detail</button>
                            <button data-action="print" data-id="${data.id}" class="px-3 py-1.5 text-xs font-semibold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors shadow-sm cursor-pointer">Print</button>
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
                    handlePrint(id);
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

const handlePrint = async (id) => {
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

onUnmounted(() => {
    if (dataTable.value) {
        dataTable.value.destroy();
        dataTable.value = null;
    }
});

onMounted(async () => {
    await nextTick();
    initDataTable();
});
</script>
