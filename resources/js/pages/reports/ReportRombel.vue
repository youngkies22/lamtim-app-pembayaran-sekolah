<template>
  <Layout :active-menu="'Reports'">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 p-8 shadow-xl no-print">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative">
          <h1 class="text-3xl font-bold text-white flex items-center gap-3">
            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
              <Squares2X2Icon class="w-8 h-8 text-white" />
            </div>
            Laporan per Rombel
          </h1>
          <p class="mt-2 text-emerald-100">Analisis data tagihan dan pembayaran per rombel (kelas)</p>
        </div>
      </div>

      <!-- Filter Card -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 no-print">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pilih Rombel</label>
            <SearchableSelect
              v-model="filters.idRombel"
              :options="rombelList"
              placeholder="Cari dan pilih rombel..."
              search-placeholder="Ketik nama atau kode rombel..."
              @change="loadData"
            />
          </div>
          <div class="flex items-end gap-2 lg:col-span-3">
            <button @click="loadData"
              class="flex-1 px-4 py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition-all flex items-center justify-center gap-2">
              Tampilkan Laporan
            </button>
            <button @click="resetFilters"
              class="p-3 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">
              <ArrowPathIcon class="w-6 h-6" />
            </button>
          </div>
        </div>

      </div>

      <!-- Summary Cards -->
      <div v-if="filters.idRombel" class="grid grid-cols-1 md:grid-cols-3 gap-6 no-print">
        <div v-for="stat in summaryStats" :key="stat.name"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 hover:shadow-xl transition-all">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ stat.name }}</p>
              <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                {{ stat.value }}
              </p>
            </div>
            <div :class="['p-3 rounded-xl', stat.color]">
              <component :is="stat.icon" class="w-6 h-6" />
            </div>
          </div>
        </div>
      </div>

      <!-- Report Card -->
      <div v-if="filters.idRombel" id="report-content" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-2">
            <DocumentTextIcon class="w-6 h-6 text-emerald-600" />
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Detail Tagihan per Rombel</h3>
          </div>
          <div class="flex items-center gap-3 no-print">
            <button @click="exportExcel" :disabled="isExporting"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-emerald-600 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50 rounded-lg transition-colors">
              <ArrowDownTrayIcon v-if="!isExporting" class="w-4 h-4" />
              <svg v-else class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              Export Excel
            </button>
            <button @click="handlePrint"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-cyan-600 bg-cyan-50 hover:bg-cyan-100 dark:bg-cyan-900/30 dark:text-cyan-400 dark:hover:bg-cyan-900/50 rounded-lg transition-colors">
              <PrinterIcon class="w-4 h-4" />
              Cetak
            </button>
          </div>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="rombel-report-table" ref="tableRef" class="w-full text-sm text-left text-gray-500 dark:text-gray-400 border-collapse">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-4 border-b dark:border-gray-700 whitespace-nowrap text-center w-12">No</th>
                            <th scope="col" class="px-6 py-4 border-b dark:border-gray-700 whitespace-nowrap text-left min-w-[200px]">Nama Siswa</th>
                            <th scope="col" class="px-6 py-4 border-b dark:border-gray-700 whitespace-nowrap text-center">NIS</th>
                            <th scope="col" class="px-6 py-4 border-b dark:border-gray-700 whitespace-nowrap text-center">Rombel</th>
                            <!-- Dynamic Billing Category Columns -->
                            <th v-for="header in dynamicHeaders" :key="header.slug" scope="col" class="px-6 py-4 border-b dark:border-gray-700 whitespace-nowrap text-center min-w-[120px] bg-sky-50 dark:bg-sky-900/10">
                                {{ header.slug }}
                            </th>
                            <th scope="col" class="px-6 py-4 border-b dark:border-gray-700 whitespace-nowrap text-right font-bold bg-gray-100 dark:bg-gray-800">Total Tagihan</th>
                            <th scope="col" class="px-6 py-4 border-b dark:border-gray-700 whitespace-nowrap text-right font-bold bg-emerald-50 dark:bg-emerald-900/20">Terbayar</th>
                            <th scope="col" class="px-6 py-4 border-b dark:border-gray-700 whitespace-nowrap text-right font-bold bg-amber-50 dark:bg-amber-900/20">Sisa</th>
                            <th scope="col" class="px-6 py-4 border-b dark:border-gray-700 whitespace-nowrap text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        <!-- DataTables will fill this -->
                    </tbody>
                </table>
            </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-12 text-center">
        <div class="max-w-md mx-auto">
          <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
            <Squares2X2Icon class="w-10 h-10 text-emerald-600" />
          </div>
          <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Pilih Rombel Terlebih Dahulu</h3>
          <p class="text-gray-600 dark:text-gray-400">Silakan pilih rombel dari menu filter di atas untuk menampilkan laporan tagihan siswa.</p>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue';
import Layout from '../../components/layout/Layout.vue';
import {
  Squares2X2Icon,
  MagnifyingGlassIcon,
  ArrowDownTrayIcon,
  PrinterIcon,
  ArrowPathIcon,
  ChevronDownIcon,
  DocumentTextIcon,
  BanknotesIcon,
  CurrencyDollarIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';
import SearchableSelect from '../../components/SearchableSelect.vue';
import { masterDataAPI, reportAPI, masterPembayaranAPI } from '../../services/api';

const tableRef = ref(null);
const dataTable = ref(null);
const rombelList = ref([]);
const dynamicHeaders = ref([]);
const isExporting = ref(false);

const filters = reactive({
  idRombel: '',
});

const summaryStats = ref([
  {
    name: 'Total Tagihan',
    value: 'Rp 0',
    icon: DocumentTextIcon,
    color: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
  },
  {
    name: 'Total Dibayar',
    value: 'Rp 0',
    icon: BanknotesIcon,
    color: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
  },
  {
    name: 'Sisa Tunggakan',
    value: 'Rp 0',
    icon: ExclamationTriangleIcon,
    color: 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
  },
]);

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount || 0);
};

const resetFilters = () => {
    Object.assign(filters, {
        idRombel: '',
    });
    // No need to reload, the UI will switch to empty state
};

const initDataTable = () => {

  if (!tableRef.value || !window.$ || !window.$.fn.DataTable) {
    return;
  }

  // Destroy existing if any
  if (dataTable.value) {
    dataTable.value.destroy();
  }

  const columns = [
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-6 py-4 text-center align-middle whitespace-nowrap' },
    { data: 'siswa_nama', name: 'siswa_nama', className: 'px-6 py-4 text-left font-medium text-gray-900 dark:text-white align-middle whitespace-nowrap' },
    { data: 'siswa_nis', name: 'siswa_nis', className: 'px-6 py-4 text-center align-middle whitespace-nowrap' },
    { data: 'rombel_nama', name: 'rombel_nama', className: 'px-6 py-4 text-center align-middle whitespace-nowrap' },
  ];

  // Add dynamic master columns
  dynamicHeaders.value.forEach(header => {
    columns.push({
      data: header.slug,
      name: header.slug,
      orderable: false,
      searchable: false,
      className: 'px-6 py-4 text-center align-middle whitespace-nowrap font-mono text-gray-700 dark:text-gray-200 bg-sky-50/50 dark:bg-sky-900/5',
      render: (data) => formatCurrencyNoSymbol(data || 0)
    });
  });

  // Add summary columns
  columns.push(
    { data: 'nominal_formatted', name: 'total_nominal', className: 'px-6 py-4 text-right font-bold text-gray-900 dark:text-white align-middle whitespace-nowrap bg-gray-50 dark:bg-gray-800' },
    { data: 'terbayar_formatted', name: 'total_terbayar', className: 'px-6 py-4 text-right font-bold text-emerald-600 dark:text-emerald-400 align-middle whitespace-nowrap bg-emerald-50/50 dark:bg-emerald-900/10' },
    { data: 'sisa_formatted', name: 'total_sisa', className: 'px-6 py-4 text-right font-bold text-amber-600 dark:text-amber-400 align-middle whitespace-nowrap bg-amber-50/50 dark:bg-amber-900/10' },
    { 
      data: 'status_label', 
      name: 'status_label', 
      className: 'px-6 py-4 text-center align-middle whitespace-nowrap',
      render: function(data) {
          let bgColor = 'bg-gray-100 text-gray-800 border border-gray-200';
          if (data === 'Lunas') bgColor = 'bg-emerald-100 text-emerald-800 border border-emerald-200';
          else if (data === 'Sebagian') bgColor = 'bg-amber-100 text-amber-800 border border-amber-200';
          else if (data === 'Belum Bayar') bgColor = 'bg-rose-100 text-rose-800 border border-rose-200';
          
          return `<span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold ${bgColor} min-w-[80px]">${data}</span>`;
      }
    }
  );

  dataTable.value = window.$(tableRef.value).DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '/api/reports/rombel',
      data: function (d) {
        d.idRombel = filters.idRombel;
      },
      error: function (xhr, error, code) {
        console.error('ReportRombel: DataTable AJAX Error', { xhr, error, code });
        alert('Gagal memuat data laporan. Silakan coba lagi atau pilih rombel lain.');
      }
    },
    columns: columns,
    order: [[1, 'asc']], // Order by Name by default
    pageLength: 50,
    autoWidth: false, // Disable auto-width to allow browser to handle layout based on cells
    scrollX: false, // We use native div scrolling for better mobile experience
    language: {
      processing: '<div class="flex items-center justify-center gap-2 py-4"><svg class="animate-spin h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> <span class="font-medium text-emerald-700">Memuat data...</span></div>',
      search: 'Cari:',
      lengthMenu: 'Tampilkan _MENU_ data',
      info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
      zeroRecords: '<div class="p-8 text-center text-gray-500">Data tidak ditemukan untuk kriteria ini.</div>',
      paginate: {
        previous: 'Prev',
        next: 'Next'
      }
    }
  });
};

const formatCurrencyNoSymbol = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
  }).format(amount || 0);
};

const loadData = async () => {
    if (!filters.idRombel) return;

    // Wait for DOM to update (v-if shows the table)
    await nextTick();

    if (!dataTable.value) {
        initDataTable();
    } else {
        dataTable.value.ajax.reload();
    }
    loadSummary();
};

const loadSummary = async () => {
    try {
        const response = await reportAPI.rombelStats(filters);
        const stats = response.data.data || {};
        
        summaryStats.value[0].value = formatCurrency(stats.totalTagihan || 0);
        summaryStats.value[1].value = formatCurrency(stats.totalDibayar || 0);
        summaryStats.value[2].value = formatCurrency(stats.totalSisa || 0);
    } catch (err) {
        console.error('Error loading summary:', err);
    }
};

const loadFilters = async () => {
    try {
        const [rombelRes, headerRes] = await Promise.all([
            masterDataAPI.rombel.select(),
            reportAPI.rombelHeaders()
        ]);
        rombelList.value = rombelRes.data.data || [];
        dynamicHeaders.value = headerRes.data.data || [];
        
        // Init table after headers are loaded
        nextTick(() => {
            initDataTable();
        });
    } catch (err) {
        console.error('Error loading filter options:', err);
    }
};

const exportExcel = async () => {
    try {
        isExporting.value = true;
        const response = await reportAPI.exportRombel(filters);
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `Laporan_Tagihan_Rombel_${new Date().getTime()}.xlsx`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (err) {
        console.error('Export failed:', err);
        alert('Gagal mendownload excel');
    } finally {
        isExporting.value = false;
    }
};

const handlePrint = () => {
    if (!filters.idRombel) return;
    const url = router.resolve({ 
        name: 'reports-rombel-print', 
        query: { idRombel: filters.idRombel } 
    }).href;
    window.open(url, '_blank');
};

onMounted(() => {
    loadFilters();
    loadSummary();
});
</script>

<style>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}

@media print {
  @page {
    size: A4 landscape;
    margin: 1cm;
  }

  body {
    background: white !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .no-print, 
  nav, 
  header, 
  aside,
  button,
  .bg-gradient-to-r,
  .bg-grid-pattern {
    display: none !important;
  }

  main {
      padding: 0 !important;
      margin: 0 !important;
      width: 100% !important;
      max-width: none !important;
  }

  #report-content {
      box-shadow: none !important;
      border: none !important;
      border-radius: 0 !important;
      width: 100% !important;
      margin: 0 !important;
      padding: 0 !important;
  }

  #rombel-report-table {
      width: 100% !important;
      border-collapse: collapse !important;
      font-size: 10px !important;
  }

  #rombel-report-table th, 
  #rombel-report-table td {
      border: 1px solid #e5e7eb !important;
      padding: 4px 6px !important;
  }

  #rombel-report-table th {
      background-color: #f3f4f6 !important;
      color: #111827 !important;
  }

  /* Force background colors for summary columns */
  .bg-gray-100 { background-color: #f3f4f6 !important; }
  .bg-emerald-50 { background-color: #ecfdf5 !important; }
  .bg-amber-50 { background-color: #fffbeb !important; }
  
  /* Ensure text colors print correctly */
  .text-emerald-600 { color: #059669 !important; }
  .text-amber-600 { color: #d97706 !important; }
  .text-rose-600 { color: #e11d48 !important; }

  /* Hide scrollbars */
  .overflow-x-auto {
      overflow: visible !important;
  }
}
</style>
