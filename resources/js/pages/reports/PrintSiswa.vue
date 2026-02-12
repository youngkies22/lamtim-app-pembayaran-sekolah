<template>
  <!-- Loading State -->
  <div v-if="loading" class="flex justify-center items-center min-h-screen bg-white">
    <div class="text-center">
      <svg class="animate-spin h-10 w-10 text-emerald-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
      </svg>
      <p class="text-gray-500 text-sm">Menyiapkan laporan...</p>
    </div>
  </div>

  <!-- Error State -->
  <div v-else-if="error" class="flex justify-center items-center min-h-screen bg-white">
    <div class="text-center max-w-md px-6">
      <svg class="w-16 h-16 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
      </svg>
      <h3 class="text-lg font-semibold text-gray-800 mb-2">Gagal Memuat Data</h3>
      <p class="text-sm text-gray-500 mb-4">{{ error }}</p>
      <button @click="fetchData"
        class="px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700 transition-colors">
        Coba Lagi
      </button>
    </div>
  </div>

  <!-- Report Content -->
  <div v-else-if="siswaData" id="report-content"
    class="max-w-[210mm] mx-auto bg-white px-10 py-8 print:px-0 print:py-0 text-black font-serif">

    <!-- KOP SURAT -->
    <header class="border-b-[3px] border-double border-black pb-4 mb-6">
      <div class="flex items-center gap-5">
        <!-- Logo -->
        <div class="w-[72px] h-[72px] flex-shrink-0">
          <img v-if="sekolah?.logo" :src="getLogoUrl(sekolah.logo)" class="w-full h-full object-contain"
            alt="Logo Sekolah" />
          <div v-else
            class="w-full h-full bg-gray-50 flex items-center justify-center border border-gray-200 rounded text-gray-300">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
            </svg>
          </div>
        </div>

        <!-- School Info -->
        <div class="flex-1 text-center leading-tight">
          <p v-if="sekolah?.namaYayasan" class="text-[10px] tracking-wide uppercase mb-0.5">
            {{ sekolah.namaYayasan }}
          </p>
          <h1 class="text-xl font-bold uppercase tracking-wide">
            {{ sekolah?.nama || 'Nama Sekolah' }}
          </h1>
          <p class="text-[11px] mt-1">
            {{ sekolah?.alamat || 'Alamat belum diatur' }}{{ sekolah?.kota ? ', ' + sekolah.kota : '' }}{{ sekolah?.provinsi ? ', ' + sekolah.provinsi : '' }}
          </p>
          <p class="text-[10px] mt-0.5 text-gray-600">
            <span v-if="sekolah?.telepon">Telp: {{ sekolah.telepon }}</span>
            <span v-if="sekolah?.email"> | Email: {{ sekolah.email }}</span>
            <span v-if="sekolah?.website"> | Web: {{ sekolah.website }}</span>
          </p>
        </div>
      </div>
    </header>

    <!-- JUDUL LAPORAN -->
    <div class="text-center mb-6">
      <h2 class="text-base font-bold uppercase tracking-wider">LAPORAN KEUANGAN SISWA</h2>
      <p v-if="tahunAjaran" class="text-[11px] font-semibold mt-1">
        Tahun Ajaran {{ tahunAjaran.tahun }}
      </p>
      <p class="text-[10px] mt-1 text-gray-500">
        Dicetak pada: {{ currentDateTime }}
      </p>
    </div>

    <!-- INFO SISWA -->
    <div class="grid grid-cols-2 gap-x-8 gap-y-0 mb-6 text-[11px]">
      <table class="w-full">
        <tbody>
          <tr>
            <td class="w-28 py-0.5 font-semibold align-top">Nama Siswa</td>
            <td class="w-3 align-top">:</td>
            <td class="font-medium">{{ siswaData.nama || '-' }}</td>
          </tr>
          <tr>
            <td class="py-0.5 font-semibold align-top">NIS / NISN</td>
            <td class="align-top">:</td>
            <td>{{ siswaData.nis || '-' }} / {{ siswaData.nisn || '-' }}</td>
          </tr>
          <tr>
            <td class="py-0.5 font-semibold align-top">Jenis Kelamin</td>
            <td class="align-top">:</td>
            <td>{{ siswaData.jsk == 1 ? 'Laki-laki' : 'Perempuan' }}</td>
          </tr>
        </tbody>
      </table>
      <table class="w-full">
        <tbody>
          <tr>
            <td class="w-28 py-0.5 font-semibold align-top">Rombel</td>
            <td class="w-3 align-top">:</td>
            <td>{{ siswaData.currentRombel?.rombel?.nama || '-' }}</td>
          </tr>
          <tr>
            <td class="py-0.5 font-semibold align-top">{{ labelJurusan }}</td>
            <td class="align-top">:</td>
            <td>{{ siswaData.currentRombel?.rombel?.jurusan?.nama || '-' }}</td>
          </tr>
          <tr>
            <td class="py-0.5 font-semibold align-top">Angkatan</td>
            <td class="align-top">:</td>
            <td>{{ siswaData.tahunAngkatan || '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- RINGKASAN KEUANGAN -->
    <div class="grid grid-cols-4 gap-2 mb-6">
      <div class="border border-blue-300 bg-blue-50 text-center py-2 px-1 rounded print-color-exact">
        <div class="text-[9px] text-blue-600 uppercase tracking-wider font-semibold mb-0.5">Total Tagihan</div>
        <div class="font-bold text-sm text-blue-800">{{ formatCurrency(summary.totalTagihan) }}</div>
      </div>
      <div class="border border-emerald-300 bg-emerald-50 text-center py-2 px-1 rounded print-color-exact">
        <div class="text-[9px] text-emerald-600 uppercase tracking-wider font-semibold mb-0.5">Sudah Dibayar</div>
        <div class="font-bold text-sm text-emerald-700">{{ formatCurrency(summary.totalDibayar) }}</div>
      </div>
      <div class="border border-red-300 bg-red-50 text-center py-2 px-1 rounded print-color-exact">
        <div class="text-[9px] text-red-600 uppercase tracking-wider font-semibold mb-0.5">Tunggakan</div>
        <div class="font-bold text-sm text-red-600">{{ formatCurrency(summary.sisaTunggakan) }}</div>
      </div>
      <div class="border border-gray-300 bg-gray-50 text-center py-2 px-1 rounded print-color-exact">
        <div class="text-[9px] text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Persentase Lunas</div>
        <div class="font-bold text-lg text-gray-800 leading-tight">{{ summary.persentaseLunas }}%</div>
      </div>
    </div>

    <!-- TABEL RINCIAN TAGIHAN -->
    <div class="mb-6">
      <h3 class="font-bold text-xs mb-2 border-l-4 border-black pl-2 uppercase tracking-wide">Rincian Tagihan</h3>
      <table class="w-full text-[11px] border-collapse">
        <thead>
          <tr class="bg-gray-800 text-white print-color-exact">
            <th class="border border-gray-600 px-2 py-1.5 text-center w-8">No</th>
            <th class="border border-gray-600 px-2 py-1.5 text-left">Uraian Pembayaran</th>
            <th class="border border-gray-600 px-2 py-1.5 text-right w-24">Tagihan</th>
            <th class="border border-gray-600 px-2 py-1.5 text-right w-24">Terbayar</th>
            <th class="border border-gray-600 px-2 py-1.5 text-right w-24">Sisa</th>
            <th class="border border-gray-600 px-2 py-1.5 text-center w-16">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="tagihanList.length === 0">
            <td colspan="6" class="border border-gray-300 px-2 py-4 text-center text-gray-400 italic">
              Belum ada data tagihan
            </td>
          </tr>
          <tr v-else v-for="(item, index) in tagihanList" :key="item.id"
            :class="index % 2 === 1 ? 'bg-gray-50 print-color-exact' : ''">
            <td class="border border-gray-300 px-2 py-1 text-center">{{ index + 1 }}</td>
            <td class="border border-gray-300 px-2 py-1 font-medium">
              {{ item.masterPembayaran?.nama || '-' }}
            </td>
            <td class="border border-gray-300 px-2 py-1 text-right tabular-nums">
              {{ formatCurrency(item.nominalTagihan) }}
            </td>
            <td class="border border-gray-300 px-2 py-1 text-right tabular-nums">
              {{ formatCurrency(item.totalSudahBayar || 0) }}
            </td>
            <td class="border border-gray-300 px-2 py-1 text-right tabular-nums font-medium">
              {{ formatCurrency(item.nominalTagihan - (item.totalSudahBayar || 0)) }}
            </td>
            <td class="border border-gray-300 px-2 py-1 text-center text-[10px] font-bold">
              <span v-if="item.status == 1" class="text-emerald-700">LUNAS</span>
              <span v-else-if="item.status == 2" class="text-gray-400">BATAL</span>
              <span v-else-if="item.status == 3" class="text-amber-600">SEBAGIAN</span>
              <span v-else class="text-red-500">BELUM</span>
            </td>
          </tr>
          <!-- Total Row -->
          <tr v-if="tagihanList.length > 0" class="bg-gray-100 font-bold print-color-exact">
            <td colspan="2" class="border border-gray-400 px-2 py-1.5 text-center uppercase text-[10px]">
              Total
            </td>
            <td class="border border-gray-400 px-2 py-1.5 text-right tabular-nums">
              {{ formatCurrency(summary.totalTagihan) }}
            </td>
            <td class="border border-gray-400 px-2 py-1.5 text-right tabular-nums text-emerald-700">
              {{ formatCurrency(summary.totalDibayar) }}
            </td>
            <td class="border border-gray-400 px-2 py-1.5 text-right tabular-nums text-red-600">
              {{ formatCurrency(summary.sisaTunggakan) }}
            </td>
            <td class="border border-gray-400 px-2 py-1.5"></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- TABEL RIWAYAT PEMBAYARAN -->
    <div class="mb-6 break-before-auto">
      <h3 class="font-bold text-xs mb-2 border-l-4 border-black pl-2 uppercase tracking-wide">Riwayat Pembayaran</h3>
      <table class="w-full text-[11px] border-collapse">
        <thead>
          <tr class="bg-gray-800 text-white print-color-exact">
            <th class="border border-gray-600 px-2 py-1.5 text-center w-8">No</th>
            <th class="border border-gray-600 px-2 py-1.5 text-left w-20">Kode</th>
            <th class="border border-gray-600 px-2 py-1.5 text-center w-24">Tanggal</th>
            <th class="border border-gray-600 px-2 py-1.5 text-left">Uraian</th>
            <th class="border border-gray-600 px-2 py-1.5 text-right w-24">Nominal</th>
            <th class="border border-gray-600 px-2 py-1.5 text-center w-16">Via</th>
            <th class="border border-gray-600 px-2 py-1.5 text-center w-16">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="pembayaranList.length === 0">
            <td colspan="7" class="border border-gray-300 px-2 py-4 text-center text-gray-400 italic">
              Belum ada riwayat pembayaran
            </td>
          </tr>
          <tr v-else v-for="(item, index) in pembayaranList" :key="item.id"
            :class="index % 2 === 1 ? 'bg-gray-50 print-color-exact' : ''">
            <td class="border border-gray-300 px-2 py-1 text-center">{{ index + 1 }}</td>
            <td class="border border-gray-300 px-2 py-1 font-mono text-[10px]">
              {{ item.kodePembayaran || item.kodeBayar || '-' }}
            </td>
            <td class="border border-gray-300 px-2 py-1 text-center">
              {{ formatDate(item.tanggalBayar) }}
            </td>
            <td class="border border-gray-300 px-2 py-1">
              {{ item.masterPembayaran?.nama || item.tagihan?.masterPembayaran?.nama || '-' }}
            </td>
            <td class="border border-gray-300 px-2 py-1 text-right tabular-nums font-medium">
              {{ formatCurrency(item.nominalBayar) }}
            </td>
            <td class="border border-gray-300 px-2 py-1 text-center text-[10px]">
              {{ item.metodeBayar || '-' }}
            </td>
            <td class="border border-gray-300 px-2 py-1 text-center text-[10px] font-bold">
              <span v-if="item.status == 1" class="text-emerald-700">OK</span>
              <span v-else class="text-amber-600">Pending</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- TANDA TANGAN (conditional) -->
    <div v-if="showSignature" class="mt-8 break-inside-avoid print:break-inside-avoid w-full flex justify-end">
        <div class="text-center w-64 text-[11px] relative">
          <!-- Location and Date -->
          <p class="mb-2">{{ sekolah?.kota || '-' }}, {{ currentDateOnly }}</p>

          <!-- Title -->
          <p class="mb-4 font-medium">Mengetahui,</p>

          <!-- Signature Space -->
          <div class="signature-space w-full h-24 print:h-[100px] print:block"></div>

          <!-- Name and NIP -->
          <p class="font-bold underline uppercase">{{ sekolah?.kepala_sekolah || 'Kepala Sekolah' }}</p>
          <p v-if="sekolah?.nip_kepsek" class="text-[10px] mt-1">NIP. {{ sekolah.nip_kepsek }}</p>
        </div>
    </div>
  </div>

  <!-- Floating Controls (screen only) -->
  <div class="fixed top-6 right-6 flex flex-col gap-2 no-print print:hidden z-50">
    <button @click="printReport"
      class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 px-5 rounded-xl shadow-lg flex items-center gap-2 transition-all hover:-translate-y-0.5">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
      </svg>
      <span>Cetak</span>
    </button>

    <label
      class="flex items-center gap-2 bg-white px-4 py-2.5 rounded-xl shadow-lg border border-gray-200 cursor-pointer hover:bg-gray-50 select-none transition-colors">
      <input type="checkbox" v-model="showSignature"
        class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300" />
      <span class="text-sm font-medium text-gray-700">TTD Kepala Sekolah</span>
    </label>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { siswaAPI, tagihanAPI, pembayaranAPI } from '../../services/api';
import { useAppSettings } from '../../composables/useAppSettings';

const route = useRoute();
const { appSettings: settings, getLogoUrl, loadAppSettings } = useAppSettings();

const loading = ref(true);
const error = ref(null);
const siswaData = ref(null);
const tagihanList = ref([]);
const pembayaranList = ref([]);
const showSignature = ref(true);

const summary = reactive({
  totalTagihan: 0,
  totalDibayar: 0,
  sisaTunggakan: 0,
  persentaseLunas: 0,
});

// Computed: school data from settings
const sekolah = computed(() => settings.value?.sekolah || null);
const tahunAjaran = computed(() => settings.value?.tahun_ajaran || null);
const labelJurusan = computed(() => settings.value?.label_jurusan || 'Jurusan');

// Current date+time (full)
const currentDateTime = computed(() => {
  const now = new Date();
  const date = now.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
  const time = now.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
  });
  return `${date}, ${time}`;
});

// Current date only (for signature)
const currentDateOnly = computed(() => {
  return new Date().toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(val || 0);
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
};

const printReport = () => {
  window.focus();
  window.print();
};

const fetchData = async () => {
  const idSiswa = route.params.id;
  if (!idSiswa) {
    error.value = 'ID Siswa tidak ditemukan.';
    loading.value = false;
    return;
  }

  try {
    loading.value = true;
    error.value = null;

    // Load settings if not yet loaded
    if (!settings.value?.sekolah) {
      await loadAppSettings();
    }

    // Parallel API calls
    const [resSiswa, resTagihan, resPembayaran] = await Promise.all([
      siswaAPI.get(idSiswa),
      tagihanAPI.list({ idSiswa }),
      pembayaranAPI.list({ idSiswa }),
    ]);

    // Process siswa
    siswaData.value = resSiswa.data?.data || resSiswa.data;
    if (!siswaData.value) {
      throw new Error('Data siswa tidak ditemukan.');
    }

    // Process tagihan
    tagihanList.value = resTagihan.data?.data || resTagihan.data || [];

    // Process pembayaran
    pembayaranList.value = resPembayaran.data?.data || resPembayaran.data || [];

    // Calculate summary
    let totalTagihan = 0;
    let totalDibayar = 0;

    tagihanList.value.forEach((item) => {
      if (item.status !== 2) {
        totalTagihan += item.nominalTagihan || 0;
        totalDibayar += item.totalSudahBayar || item.totalBayar || 0;
      }
    });

    const sisa = totalTagihan - totalDibayar;
    const persen = totalTagihan > 0 ? Math.round((totalDibayar / totalTagihan) * 100) : 0;

    summary.totalTagihan = totalTagihan;
    summary.totalDibayar = totalDibayar;
    summary.sisaTunggakan = sisa > 0 ? sisa : 0;
    summary.persentaseLunas = Math.min(persen, 100);
  } catch (err) {
    console.error('Error loading print data:', err);
    error.value = err?.response?.data?.message || err?.message || 'Gagal memuat data laporan.';
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);
</script>

<style>
/* Force background colors in print */
.print-color-exact {
  -webkit-print-color-adjust: exact !important;
  print-color-adjust: exact !important;
  color-adjust: exact !important;
}

/* Tabular numbers for aligned columns */
.tabular-nums {
  font-variant-numeric: tabular-nums;
}

@media print {
  @page {
    size: A4 portrait;
    margin: 1cm;
  }

  /* Reset body for print */
  body {
    background: white !important;
    color: black !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  /* Hide elements marked as no-print or controls */
  .no-print,
  .print\:hidden {
    display: none !important;
  }

  /* Ensure report content is visible and layout flows naturally */
  #report-content {
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
    background: white !important;
    font-size: 11px;
    visibility: visible !important;
    display: block !important;
    position: relative !important;
    overflow: visible !important;
  }

  /* Force header visibility */
  header {
    display: block !important;
    visibility: visible !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    break-inside: avoid;
  }

  /* Reset some styles for print */
  .shadow-lg,
  .shadow-xl,
  .shadow-md {
    box-shadow: none !important;
  }

  .bg-white,
  .dark\:bg-gray-800 {
    background: white !important;
    color: black !important;
    border: none !important;
  }

  /* Tables */
  table {
    width: 100% !important;
    border-collapse: collapse !important;
    page-break-inside: auto;
    break-inside: auto;
  }

  tr {
    page-break-inside: avoid;
    break-inside: avoid;
    page-break-after: auto;
  }

  thead {
    display: table-header-group;
  }

  tfoot {
    display: table-footer-group;
  }

  th {
    background-color: #1f2937 !important;
    color: white !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    font-size: 10px !important;
    padding: 4px 6px !important;
  }

  td {
    border: 1px solid #d1d5db !important;
    padding: 3px 6px !important;
    color: black !important;
    font-size: 10px !important;
  }

  /* Summary cards */
  .grid-cols-4 > div {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* Zebra striping */
  tr.bg-gray-50 {
    background-color: #f9fafb !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* Total row */
  tr.bg-gray-100 {
    background-color: #f3f4f6 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* Status text colors */
  .text-emerald-700 {
    color: #047857 !important;
  }

  .text-red-500 {
    color: #ef4444 !important;
  }

  .text-red-600 {
    color: #dc2626 !important;
  }

  .text-amber-600 {
    color: #d97706 !important;
  }

  /* Signature section */
  .break-inside-avoid {
    break-inside: avoid;
    page-break-inside: avoid;
  }

  /* Ensure footer doesn't break awkwardly */
  .break-before-auto {
    break-before: auto;
    page-break-before: auto;
  }

  /* Typography */
  h1 {
    font-size: 16px !important;
  }

  h2 {
    font-size: 13px !important;
  }

  h3 {
    font-size: 11px !important;
  }

  p {
    margin: 0;
  }
}
</style>
