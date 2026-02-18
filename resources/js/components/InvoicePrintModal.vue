<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center"
      @click.self="$emit('close')">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 rounded-t-2xl">
          <h3 class="text-lg font-bold text-white">Pilih Jenis Printer</h3>
        </div>
        <div class="p-6 space-y-3">
          <button @click="print('dotmatrix')"
            class="w-full p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-all text-left cursor-pointer">
            <div class="font-semibold text-gray-900 dark:text-white">Epson LQ310 Dotmatrix</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">24 Pin - Landscape</div>
          </button>
          <button @click="print('a4')"
            class="w-full p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-all text-left cursor-pointer">
            <div class="font-semibold text-gray-900 dark:text-white">Printer A4</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">A4 - Landscape</div>
          </button>
          <button @click="print('thermal')"
            class="w-full p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-all text-left cursor-pointer">
            <div class="font-semibold text-gray-900 dark:text-white">Thermal Bluetooth</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">58mm/80mm - Portrait</div>
          </button>
        </div>
        <div class="px-6 pb-6">
          <button @click="$emit('close')"
            class="w-full py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 cursor-pointer">Batal</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  show: Boolean,
  invoice: Object
});

const emit = defineEmits(['close']);

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
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

const getLogoUrl = (logoPath) => {
  if (!logoPath) return '';
  if (logoPath.startsWith('http://') || logoPath.startsWith('https://')) return logoPath;
  return `${window.location.origin}/storage/${logoPath}`;
};

const print = (type) => {
  if (!props.invoice) return;

  const inv = props.invoice;

  // Data dari API - sekolah diambil langsung dari response (dinamis dari database)
  const sekolah = inv.sekolah || inv.siswa?.sekolah || {};
  const namaYayasan = sekolah.namaYayasan || 'YAYASAN PENDIDIKAN';
  const namaSekolah = sekolah.nama || 'SEKOLAH';
  const alamatSekolah = [sekolah.alamat, sekolah.kota].filter(Boolean).join(', ');
  const teleponSekolah = sekolah.telepon || '';
  const emailSekolah = sekolah.email || '';
  const logoSekolah = sekolah.logo ? getLogoUrl(sekolah.logo) : '';
  const tahunAjaran = inv.tahunAjaran?.tahun || '2025/2026';
  const kelas = inv.siswa?.kelas || inv.siswa?.rombel?.kelas + ' ' + inv.siswa?.rombel?.nama || '-';

  // Admin/Bendahara yang memproses
  const namaBendahara = inv.admin?.nama || 'Admin';

  // Tagihan untuk invoice ini
  const tagihanInvoice = inv.tagihan;
  const namaPembayaran = tagihanInvoice?.masterPembayaran?.nama || tagihanInvoice?.kodeTagihan || '-';

  // Detail transaksi invoice ini
  const nominalTagihanInvoice = tagihanInvoice?.nominalTagihan || inv.nominalInvoice || 0;
  const sudahBayarTagihan = tagihanInvoice?.totalSudahBayar || inv.nominalBayar || 0;
  const sisaTagihan = tagihanInvoice?.totalSisa || inv.nominalSisa || 0;

  // Uang yang sudah dibayar pada invoice ini
  const uangDibayar = inv.nominalBayar || 0;

  // Semua tagihan siswa
  const semuaTagihan = inv.semuaTagihan || [];

  // Hitung total semua tagihan
  const totalSemuaTagihan = semuaTagihan.reduce((sum, t) => sum + (t.nominalTagihan || 0), 0);
  const totalTerbayar = semuaTagihan.reduce((sum, t) => sum + (t.totalSudahBayar || 0), 0);
  const totalSisa = semuaTagihan.reduce((sum, t) => sum + (t.totalSisa || 0), 0);

  // Generate tabel semua tagihan
  const tagihanRows = semuaTagihan.map(t => {
    const statusClass = t.status === 1 ? 'color:green' : (t.totalSisa > 0 ? 'color:red' : '');
    return `<tr>
      <td style="padding:3px 5px;border:1px solid #ccc">${t.nama}</td>
      <td style="padding:3px 5px;border:1px solid #ccc;text-align:right">Rp ${formatNumber(t.nominalTagihan)}</td>
      <td style="padding:3px 5px;border:1px solid #ccc;text-align:right">Rp ${formatNumber(t.totalSudahBayar)}</td>
      <td style="padding:3px 5px;border:1px solid #ccc;text-align:right;${statusClass}">Rp ${formatNumber(t.totalSisa)}</td>
    </tr>`;
  }).join('');

  // Baris kontak sekolah untuk header
  const kontakParts = [teleponSekolah ? `Telp: ${teleponSekolah}` : '', emailSekolah ? `Email: ${emailSekolah}` : ''].filter(Boolean).join(' | ');

  // Logo HTML helper
  const logoImg = logoSekolah ? `<img src="${logoSekolah}" style="width:60px;height:60px;object-fit:contain" alt="Logo">` : '';
  const logoImgSmall = logoSekolah ? `<img src="${logoSekolah}" style="width:40px;height:40px;object-fit:contain" alt="Logo">` : '';

  let content = '';
  let style = '';

  // === CSS @media print (shared base) ===
  const printMediaCSS = `
    @media print {
      body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
      .no-print { display: none !important; }
    }`;

  if (type === 'dotmatrix') {
    style = `
      @page { size: landscape; margin: 5mm; }
      body { font-family: 'Courier New', monospace; font-size: 10pt; margin: 0; padding: 10px; }
      .header-wrap { display: table; width: 100%; margin-bottom: 8px; }
      .header-logo { display: table-cell; width: 50px; vertical-align: middle; padding-right: 8px; }
      .header-text { display: table-cell; vertical-align: middle; text-align: center; }
      .line { border-bottom: 1px solid #000; margin: 8px 0; }
      table { width: 100%; border-collapse: collapse; }
      .info-table td { padding: 2px 0; }
      .tagihan-table { margin-top: 10px; }
      .tagihan-table th { padding: 3px 5px; border: 1px solid #000; font-size: 9pt; font-weight: bold; text-align: left; }
      .sign { margin-top: 20px; }
      ${printMediaCSS}`;
    content = `
      <div class="header-wrap">
        ${logoSekolah ? `<div class="header-logo">${logoImgSmall}</div>` : ''}
        <div class="header-text">
          <div style="font-size:11pt;font-weight:bold;color:#8B0000">${namaYayasan.toUpperCase()}</div>
          <div style="font-size:10pt;font-weight:bold;color:#8B0000">${namaSekolah.toUpperCase()}</div>
          ${alamatSekolah ? `<div style="font-size:8pt">${alamatSekolah}</div>` : ''}
          <div style="font-size:9pt;font-weight:bold;margin-top:2px">TAHUN AJARAN ${tahunAjaran}</div>
        </div>
      </div>
      <div class="line"></div>
      <table class="info-table">
        <tr><td width="140">Di Terima Dari</td><td width="10">:</td><td style="color:#8B0000;font-weight:bold">${inv.siswa?.nama || '-'}</td><td width="60">Tanggal</td><td width="10">:</td><td>${formatDateTime(inv.created_at || inv.tanggalInvoice)}</td></tr>
        <tr><td>Uang Sejumlah</td><td>:</td><td style="font-weight:bold">Rp ${formatNumber(uangDibayar)}</td><td width="70" style="white-space:nowrap">ID Siswa</td><td>:</td><td style="font-weight:bold">${inv.siswa?.username || inv.siswa?.nis || '-'}</td></tr>
        <tr><td>Untuk Pembayaran</td><td>:</td><td style="color:#8B0000">${namaPembayaran}</td><td style="white-space:nowrap">Kelas | NIS/NISN</td><td>:</td><td style="white-space:nowrap">${kelas} | ${inv.siswa?.nis || '-'}/${inv.siswa?.nisn || '-'}</td></tr>
      </table>
      <div class="line"></div>
      <div style="font-weight:bold;text-decoration:underline;margin-bottom:5px">Detail Transaksi Invoice Ini</div>
      <table style="margin-bottom:10px">
        <tr><td width="150">Jenis Pembayaran</td><td width="10">:</td><td style="font-weight:bold">${namaPembayaran}</td></tr>
        <tr><td>Nominal Tagihan</td><td>:</td><td>Rp ${formatNumber(nominalTagihanInvoice)}</td></tr>
        <tr><td>Sudah Dibayar</td><td>:</td><td style="border-bottom:1px solid #000;padding-bottom:3px">Rp ${formatNumber(sudahBayarTagihan)}</td></tr>
        <tr><td>Sisa Tagihan</td><td>:</td><td style="color:${sisaTagihan > 0 ? 'red' : 'green'};font-weight:bold;padding-top:3px">Rp ${formatNumber(sisaTagihan)}</td></tr>
      </table>
      <div class="line"></div>
      <div style="font-weight:bold;text-decoration:underline;margin-bottom:5px">Rincian Biaya Pendidikan Siswa</div>
      <table class="tagihan-table">
        <tr><th>Nama Tagihan</th><th>Nominal</th><th>Terbayar</th><th>Sisa</th></tr>
        ${tagihanRows}
        <tr style="font-weight:bold">
          <td style="padding:3px 5px;border:1px solid #000;text-align:center">TOTAL</td>
          <td style="padding:3px 5px;border:1px solid #000;text-align:right">Rp ${formatNumber(totalSemuaTagihan)}</td>
          <td style="padding:3px 5px;border:1px solid #000;text-align:right">Rp ${formatNumber(totalTerbayar)}</td>
          <td style="padding:3px 5px;border:1px solid #000;text-align:right;color:${totalSisa > 0 ? 'red' : 'green'}">Rp ${formatNumber(totalSisa)}</td>
        </tr>
      </table>
      <div class="line"></div>
      <table class="sign"><tr><td width="50%"><div>Penyetor</div><div style="margin-top:40px;color:#8B0000;font-weight:bold">${inv.siswa?.nama || '-'}</div></td><td style="text-align:right"><div>Bendahara,</div><div style="margin-top:40px;font-weight:bold">${namaBendahara}</div></td></tr></table>`;

  } else if (type === 'a4') {
    style = `
      @page { size: A4 landscape; margin: 15mm; }
      body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; margin: 0; padding: 20px; color: #222; }
      .header-wrap { display: flex; align-items: center; gap: 16px; padding-bottom: 12px; }
      .header-logo { flex-shrink: 0; }
      .header-logo img { width: 70px; height: 70px; object-fit: contain; }
      .header-text { flex: 1; text-align: center; }
      .header-divider { border: none; border-top: 3px double #333; margin: 0 0 4px 0; }
      .header-divider-thin { border: none; border-top: 1px solid #333; margin: 0 0 14px 0; }
      .ta-badge { display: inline-block; background: #7c3aed; color: #fff; font-size: 10pt; font-weight: bold; padding: 4px 18px; border-radius: 4px; margin-top: 6px; letter-spacing: 0.5px; }
      .line { border-bottom: 2px solid #333; margin: 12px 0; }
      .line-thin { border-bottom: 1px solid #ccc; margin: 10px 0; }
      table { width: 100%; border-collapse: collapse; }
      .info-table td { padding: 4px 0; }
      .tagihan-table { margin-top: 15px; }
      .tagihan-table th { background: #7c3aed; color: #fff; padding: 8px 10px; border: 1px solid #6b21a8; font-size: 10pt; }
      .tagihan-table td { padding: 6px 8px; border: 1px solid #ddd; }
      .tagihan-table tr:nth-child(even) { background: #faf5ff; }
      .sign { margin-top: 30px; }
      .section-title { font-weight: bold; font-size: 12pt; margin-bottom: 10px; color: #7c3aed; }
      ${printMediaCSS}`;
    content = `
      <div class="header-wrap">
        ${logoSekolah ? `<div class="header-logo"><img src="${logoSekolah}" alt="Logo Sekolah"></div>` : ''}
        <div class="header-text">
          <div style="font-size:13pt;font-weight:bold;text-transform:uppercase;letter-spacing:1px">${namaYayasan.toUpperCase()}</div>
          <div style="font-size:17pt;font-weight:bold;text-transform:uppercase;color:#7c3aed">${namaSekolah.toUpperCase()}</div>
          ${alamatSekolah ? `<div style="font-size:9pt;color:#555;margin-top:2px">${alamatSekolah}</div>` : ''}
          ${kontakParts ? `<div style="font-size:9pt;color:#555">${kontakParts}</div>` : ''}
        </div>
        ${logoSekolah ? '<div style="width:70px"></div>' : ''}
      </div>
      <hr class="header-divider">
      <hr class="header-divider-thin">
      <div style="text-align:center;margin-bottom:14px">
        <span class="ta-badge">TAHUN AJARAN ${tahunAjaran}</span>
      </div>
      <table class="info-table">
        <tr><td width="160">Telah Di Terima Dari</td><td width="10">:</td><td style="color:#8B0000;font-weight:bold;font-size:12pt">${inv.siswa?.nama || '-'}</td><td width="80">Tanggal</td><td width="10">:</td><td>${formatDateTime(inv.created_at || inv.tanggalInvoice)}</td></tr>
        <tr><td>Uang Sejumlah</td><td>:</td><td style="font-weight:bold;font-size:12pt">Rp ${formatNumber(uangDibayar)}</td><td width="80" style="white-space:nowrap">ID Siswa</td><td>:</td><td style="font-weight:bold">${inv.siswa?.username || inv.siswa?.nis || '-'}</td></tr>
        <tr><td>Untuk Pembayaran</td><td>:</td><td style="color:#7c3aed;font-weight:bold">${namaPembayaran}</td><td style="white-space:nowrap">Kelas | NIS/NISN</td><td>:</td><td style="white-space:nowrap">${kelas} | ${inv.siswa?.nis || '-'}/${inv.siswa?.nisn || '-'}</td></tr>
      </table>
      <div class="line-thin"></div>
      <div class="section-title">Detail Transaksi Invoice Ini</div>
      <table style="margin-bottom:15px;font-size:11pt">
        <tr><td width="180">Jenis Pembayaran</td><td width="10">:</td><td style="font-weight:bold;color:#7c3aed">${namaPembayaran}</td></tr>
        <tr><td>Nominal Tagihan</td><td>:</td><td>Rp ${formatNumber(nominalTagihanInvoice)}</td></tr>
        <tr><td>Sudah Dibayar</td><td>:</td><td style="border-bottom:1px solid #333;padding-bottom:5px">Rp ${formatNumber(sudahBayarTagihan)}</td></tr>
        <tr><td>Sisa Tagihan</td><td>:</td><td style="color:${sisaTagihan > 0 ? '#c00' : '#080'};font-weight:bold;padding-top:5px">Rp ${formatNumber(sisaTagihan)}</td></tr>
      </table>
      <div class="line-thin"></div>
      <div class="section-title">Rincian Biaya Pendidikan Siswa</div>
      <table class="tagihan-table">
        <tr><th style="text-align:left">Nama Tagihan</th><th style="text-align:right">Nominal</th><th style="text-align:right">Terbayar</th><th style="text-align:right">Sisa</th></tr>
        ${tagihanRows}
        <tr style="font-weight:bold;background:#ede9fe">
          <td style="text-align:center;font-size:12pt">TOTAL</td>
          <td style="text-align:right">Rp ${formatNumber(totalSemuaTagihan)}</td>
          <td style="text-align:right">Rp ${formatNumber(totalTerbayar)}</td>
          <td style="text-align:right;color:${totalSisa > 0 ? '#c00' : '#080'};font-size:12pt">Rp ${formatNumber(totalSisa)}</td>
        </tr>
      </table>
      <div class="line"></div>
      <table class="sign"><tr><td width="50%"><div>Penyetor,</div><div style="margin-top:50px;color:#8B0000;font-weight:bold;border-top:1px solid #333;display:inline-block;padding-top:4px">${inv.siswa?.nama || '-'}</div></td><td style="text-align:right"><div>Bendahara,</div><div style="margin-top:50px;font-weight:bold;border-top:1px solid #333;display:inline-block;padding-top:4px">${namaBendahara}</div></td></tr></table>`;

  } else {
    // Thermal - format ringkas untuk struk 58mm/80mm
    const thermalTagihan = semuaTagihan.map(t =>
      `<div style="display:flex;justify-content:space-between;font-size:8pt;padding:2px 0;border-bottom:1px dotted #ccc"><span>${t.nama}</span><span>Rp ${formatNumber(t.totalSisa)}</span></div>`
    ).join('');

    style = `
      @page { size: 80mm auto; margin: 0; }
      body { font-family: Arial, sans-serif; font-size: 9pt; margin: 0; padding: 5px; width: 76mm; }
      .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 8px; }
      .row { display: flex; justify-content: space-between; padding: 2px 0; }
      .section { border-top: 1px dashed #000; margin-top: 8px; padding-top: 8px; }
      ${printMediaCSS}`;
    content = `
      <div class="header">
        ${logoSekolah ? `<div style="margin-bottom:4px"><img src="${logoSekolah}" style="width:36px;height:36px;object-fit:contain" alt="Logo"></div>` : ''}
        <div style="font-weight:bold;font-size:8pt;text-transform:uppercase">${namaYayasan.toUpperCase()}</div>
        <div style="font-weight:bold;font-size:10pt">${namaSekolah.toUpperCase()}</div>
        ${alamatSekolah ? `<div style="font-size:7pt;color:#555">${alamatSekolah}</div>` : ''}
        <div style="font-size:7pt;font-weight:bold;margin-top:2px">TA. ${tahunAjaran}</div>
      </div>
      <div style="text-align:center;font-weight:bold;margin-bottom:8px">KWITANSI</div>
      <div class="row"><span>No:</span><span>${inv.noInvoice}</span></div>
      <div class="row"><span>Tgl:</span><span>${formatDateTime(inv.created_at || inv.tanggalInvoice)}</span></div>
      <div class="row"><span>Siswa:</span><span>${inv.siswa?.nama || '-'}</span></div>
      <div class="row"><span>ID Siswa:</span><span>${inv.siswa?.username || inv.siswa?.nis || '-'}</span></div>
      <div class="row"><span>NIS/NISN:</span><span>${inv.siswa?.nis || '-'}/${inv.siswa?.nisn || '-'}</span></div>
      <div class="row"><span>Kelas:</span><span>${kelas}</span></div>
      <div class="row"><span>Pembayaran:</span><span>${namaPembayaran}</span></div>
      <div class="section">
        <div style="font-weight:bold;margin-bottom:3px;font-size:8pt">Detail Transaksi:</div>
        <div class="row"><span>Tagihan:</span><span>Rp ${formatNumber(nominalTagihanInvoice)}</span></div>
        <div class="row"><span>Terbayar:</span><span>Rp ${formatNumber(sudahBayarTagihan)}</span></div>
        <div class="row"><span>Sisa:</span><span style="color:${sisaTagihan > 0 ? 'red' : 'green'}">Rp ${formatNumber(sisaTagihan)}</span></div>
      </div>
      <div class="section">
        <div style="font-weight:bold;margin-bottom:5px;font-size:8pt">Rincian Biaya Pendidikan Siswa:</div>
        ${thermalTagihan}
      </div>
      <div class="section">
        <div class="row" style="font-weight:bold"><span>Total Sisa:</span><span style="color:${totalSisa > 0 ? 'red' : 'green'}">Rp ${formatNumber(totalSisa)}</span></div>
        <div style="text-align:center;font-weight:bold;font-size:12pt;margin-top:10px;padding:5px;border:1px solid #000">Dibayar: Rp ${formatNumber(uangDibayar)}</div>
      </div>
      <div style="text-align:center;margin-top:8px;font-size:7pt">Bendahara: ${namaBendahara}</div>
      <div style="text-align:center;margin-top:5px;font-size:7pt;color:#666">Terima Kasih</div>`;
  }

  const win = window.open('', '_blank');
  win.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Print Invoice ${inv.noInvoice}</title><style>${style}</style></head><body>${content}</body></html>`);
  win.document.close();
  setTimeout(() => win.print(), 300);
  emit('close');
};
</script>
