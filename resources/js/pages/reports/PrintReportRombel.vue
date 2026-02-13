<template>
  <div class="print-layout p-8 bg-white min-h-screen relative">
    <!-- Controls -->
    <div class="fixed top-8 right-8 flex flex-col gap-3 no-print print:hidden z-50">
        <button 
            @click="print" 
            class="bg-emerald-600 text-white px-4 py-2 rounded-lg shadow-lg hover:bg-emerald-700 transition-all flex items-center justify-center gap-2"
            title="Cetak Laporan"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
            </svg>
            <span class="font-medium">Cetak</span>
        </button>

        <label class="flex items-center gap-2 bg-white px-4 py-2 rounded-lg shadow-lg border border-gray-200 cursor-pointer hover:bg-gray-50 select-none">
            <input type="checkbox" v-model="showTotals" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300">
            <span class="text-sm font-medium text-gray-700">Tampilkan Total</span>
        </label>

        <label class="flex items-center gap-2 bg-white px-4 py-2 rounded-lg shadow-lg border border-gray-200 cursor-pointer hover:bg-gray-50 select-none">
            <input type="checkbox" v-model="showPrintInfo" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300">
            <span class="text-sm font-medium text-gray-700">Tampilkan Info Cetak</span>
        </label>
    </div>
    <div v-if="loading" class="flex items-center justify-center min-h-[50vh]">
        <div class="text-center">
            <svg class="animate-spin h-10 w-10 text-emerald-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            <p class="text-gray-500">Menyiapkan laporan...</p>
        </div>
    </div>
    
    <div v-else class="max-w-[297mm] mx-auto">
      <!-- Header -->
      <div class="flex items-center gap-6 mb-8 border-b-2 border-emerald-600 pb-4">
        <!-- Logo -->
        <div v-if="sekolahLogo" class="w-20 h-20 flex-shrink-0">
          <img :src="getLogoUrl(sekolahLogo)" class="w-full h-full object-contain" alt="Logo Sekolah" />
        </div>
        
        <!-- Title -->
        <div class="flex-1 text-center" :class="{ 'pr-20': sekolahLogo }">
          <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-wide">{{ sekolahNama }}</h1>
          <h2 class="text-xl font-bold text-emerald-700 mt-2 uppercase">LAPORAN BIAYA PENDIDIKAN PER ROMBEL</h2>
          
          <div class="mt-4">
              <span class="text-2xl font-bold text-gray-900 uppercase">
                  {{ rombelInfo?.kelas?.kode ? rombelInfo.kelas.kode + ' - ' : '' }}{{ rombelNama }}
              </span>
          </div>
        </div>
      </div>

      <!-- Table -->
      <table class="w-full text-sm text-left text-gray-700 border-collapse">
        <thead>
          <tr class="bg-gray-100 text-gray-800 uppercase text-xs tracking-wider">
            <th class="border border-gray-300 px-3 py-2 text-center w-10">No</th>
            <th class="border border-gray-300 px-3 py-2">Nama Siswa</th>
            <th class="border border-gray-300 px-3 py-2 text-center">NIS</th>
            
            <th v-for="header in dynamicHeaders" :key="header.slug" class="border border-gray-300 px-3 py-2 text-center bg-sky-50">
              {{ header.slug }}
            </th>
            
            <th class="border border-gray-300 px-3 py-2 text-right">Biaya</th>
            <th class="border border-gray-300 px-3 py-2 text-right">Terbayar</th>
            <th class="border border-gray-300 px-3 py-2 text-right">Tunggakan</th>
            <th class="border border-gray-300 px-3 py-2 text-center">Ket</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in reportData" :key="row.id" class="border-b border-gray-200">
            <td class="border border-gray-300 px-3 py-2 text-center">{{ index + 1 }}</td>
            <td class="border border-gray-300 px-3 py-2 font-medium">{{ row.siswa_nama }}</td>
            <td class="border border-gray-300 px-3 py-2 text-center">{{ row.siswa_nis }}</td>
            
            <td v-for="header in dynamicHeaders" :key="header.slug" class="border border-gray-300 px-3 py-2 text-right font-mono text-gray-600 bg-sky-50/30">
              {{ formatCurrency(row[header.slug]) }}
            </td>
            
            <td class="border border-gray-300 px-3 py-2 text-right font-bold">{{ formatCurrency(row.total_nominal) }}</td>
            <td class="border border-gray-300 px-3 py-2 text-right font-bold text-emerald-700">{{ formatCurrency(row.total_terbayar) }}</td>
            <td class="border border-gray-300 px-3 py-2 text-right font-bold text-amber-600">{{ formatCurrency(row.total_sisa) }}</td>
            
            <td class="border border-gray-300 px-3 py-2 text-center text-xs font-bold">
                <span v-if="row.total_sisa <= 0" class="text-emerald-700">LUNAS</span>
                <span v-else-if="row.total_terbayar > 0" class="text-amber-600">SEBAGIAN</span>
                <span v-else class="text-rose-600">BELUM</span>
            </td>
          </tr>
          
          <!-- Summary Row -->
          <!-- Summary Row (Conditional) -->
          <tr v-if="showTotals" class="bg-gray-50 font-bold border-t-2 border-gray-400">
              <td :colspan="3" class="border border-gray-300 px-3 py-2 text-center uppercase">Total Keseluruhan</td>
              
              <td v-for="header in dynamicHeaders" :key="header.slug" class="border border-gray-300 px-3 py-2 text-right text-gray-600">
                  {{ formatCurrency(computedTotals.dynamic[header.slug]) }}
              </td>

              <td class="border border-gray-300 px-3 py-2 text-right">{{ formatCurrency(computedTotals.tagihan) }}</td>
              <td class="border border-gray-300 px-3 py-2 text-right text-emerald-700">{{ formatCurrency(computedTotals.bayar) }}</td>
              <td class="border border-gray-300 px-3 py-2 text-right text-amber-600">{{ formatCurrency(computedTotals.sisa) }}</td>
              <td class="border border-gray-300 bg-gray-100"></td>
          </tr>
        </tbody>
      </table>

      <!-- Footer -->
      <div v-if="showPrintInfo" class="mt-8 flex justify-between text-xs text-gray-500">
          <div>Dicetak oleh: {{ userName }}</div>
          <div>Tanggal: {{ printDate }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { reportAPI, masterDataAPI, authAPI, configAPI } from '../../services/api';
import { useAppSettings } from '../../composables/useAppSettings';

const route = useRoute();
const loading = ref(true);
const reportData = ref([]);
const dynamicHeaders = ref([]);
const rombelNama = ref('');
const rombelInfo = ref(null);
const userName = ref('');
const { appSettings: settings, getLogoUrl, loadAppSettings } = useAppSettings();
const sekolah = computed(() => settings.value?.sekolah || null);
const sekolahNama = computed(() => sekolah.value?.nama || 'Sekolah');
const sekolahLogo = computed(() => sekolah.value?.logo || null);
const printDate = ref('');
const showTotals = ref(true);
const showPrintInfo = ref(true);

const print = () => {
    window.print();
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(val || 0);
};

// Computed Totals
const computedTotals = computed(() => {
    const totals = { dynamic: {}, tagihan: 0, bayar: 0, sisa: 0 };
    
    // Initialize dynamic totals
    dynamicHeaders.value.forEach(h => totals.dynamic[h.slug] = 0);

    reportData.value.forEach(row => {
        // Dynamic columns
        dynamicHeaders.value.forEach(h => {
             totals.dynamic[h.slug] += (Number(row[h.slug]) || 0);
        });
        
        // Summary columns
        totals.tagihan += (Number(row.total_nominal) || 0);
        totals.bayar += (Number(row.total_terbayar) || 0);
        totals.sisa += (Number(row.total_sisa) || 0);
    });

    return totals;
});

const fetchData = async () => {
    const idRombel = route.query.idRombel;
    if (!idRombel) {
        alert('Rombel ID missing');
        return;
    }

    try {
        // Load app settings first (for school logo/name)
        await loadAppSettings();

        // Parallel requests
        const [reportRes, headerRes, rombelRes, userRes] = await Promise.all([
            reportAPI.rombel({ idRombel, length: 1000 }), // Fetch ALL data for print
            reportAPI.rombelHeaders(),
            masterDataAPI.rombel.get(idRombel),
            authAPI.me(),
        ]);

        // Process Report Data
        let data = reportRes.data.data || [];
        if (reportRes.data.response && reportRes.data.response.data) {
             data = reportRes.data.response.data; 
        }
        reportData.value = data;

        dynamicHeaders.value = headerRes.data.data || [];
        
        // Rombel Name
        const rombel = rombelRes.data.data || rombelRes.data || null;
        rombelInfo.value = rombel;
        rombelNama.value = rombel ? rombel.nama : 'Unknown Rombel';

        // Meta
        const user = userRes.data.data || userRes.data;
        userName.value = user.nama || user.name || 'Administrator';
        printDate.value = new Date().toLocaleString('id-ID');

    } catch (err) {
        console.error('Print Error:', err);
        alert('Gagal memuat data laporan.');
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchData();
});
</script>

<style>
@media print {
    @page { 
        size: A4 landscape; 
        margin: 10mm; 
    }
    
    body { 
        visibility: visible !important;
        background-color: white !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Hide everything by default */
    body * {
        visibility: hidden;
    }

    /* Show only print layout and its children */
    .print-layout, .print-layout * {
        visibility: visible;
    }

    .print-layout {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }

    .no-print, .print\:hidden {
        display: none !important;
    }
    
    /* Ensure table borders print */
    table, th, td {
        border: 1px solid #e5e7eb !important;
    }
}
</style>
