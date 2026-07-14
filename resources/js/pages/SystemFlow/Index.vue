<template>
  <Layout active-menu="Alur Sistem">
    <div class="space-y-6">
      <!-- Header -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-sky-600 via-blue-600 to-indigo-600 p-6 md:p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative">
          <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center gap-3">
            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
              <MapIcon class="w-6 h-6 md:w-8 md:h-8 text-white" />
            </div>
            Alur Sistem
          </h1>
          <p class="mt-2 text-sky-100 text-sm md:text-base">
            Gambaran alur data dan hubungan antar menu di seluruh aplikasi — khusus Admin.
          </p>
        </div>
      </div>

      <!-- In-page nav -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4">
        <div class="flex flex-wrap gap-2">
          <a v-for="s in sections" :key="s.key" :href="`#${s.key}`"
            class="px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-sky-50 dark:hover:bg-sky-900/20 hover:text-sky-700 dark:hover:text-sky-300 transition-colors">
            {{ s.title }}
          </a>
        </div>
      </div>

      <!-- Overview -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Gambaran Umum</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
          Data akademik ditarik dari sistem eksternal, menjadi fondasi Master Data & Siswa, lalu mengalir ke hierarki
          Pembayaran (Master Pembayaran → Tagihan → Invoice → Pembayaran). Hasilnya dipakai di Laporan &amp; Diagram,
          dan bisa dikunci lewat Closing. Modul administratif (Users, Settings, Backups, Trash, Reset Data, Failed Jobs)
          mendukung operasional di belakang layar.
        </p>
        <MermaidDiagram :code="overviewDiagram" />
      </div>

      <!-- Per-module sections -->
      <div v-for="s in sections" :key="s.key" :id="s.key"
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 scroll-mt-4">
        <div class="flex items-center gap-3 mb-2">
          <div class="p-2 rounded-xl" :class="s.color">
            <component :is="s.icon" class="w-5 h-5 text-white" />
          </div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ s.title }}</h2>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ s.description }}</p>

        <MermaidDiagram :code="s.diagram" />

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
          <div v-for="m in s.menus" :key="m.path"
            class="flex items-start gap-2 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
            <div class="w-1.5 h-1.5 rounded-full bg-sky-500 mt-1.5 flex-shrink-0"></div>
            <div>
              <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ m.label }}
                <span class="text-xs font-normal text-gray-400 dark:text-gray-500">{{ m.path }}</span>
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ m.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Layout from '../../components/layout/Layout.vue'
import MermaidDiagram from '../../components/MermaidDiagram.vue'
import { useRoleAccess } from '../../composables/useRoleAccess'
import {
  MapIcon,
  BuildingLibraryIcon,
  UserGroupIcon,
  CreditCardIcon,
  ArrowPathIcon,
  ChartBarIcon,
  LockClosedIcon,
  Cog6ToothIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()
const { isAdminUser } = useRoleAccess()

const overviewDiagram = `flowchart TB
    Sync["Sync Eksternal"] --> MD["Master Data"]
    MD --> SW["Data Siswa"]
    SW --> PY["Payment Hierarchy"]
    MD --> PY
    PY --> RP["Reports & Diagrams"]
    MD --> CL["Closing"]
    PY --> CL
    subgraph Admin["Administratif"]
        US["Users"]
        ST["Settings"]
        BK["Backups"]
        TR["Trash"]
        RD["Reset Data"]
    end
    Admin -.mendukung.-> MD
    Admin -.mendukung.-> PY`

const sections = [
  {
    key: 'master-data',
    title: 'Master Data',
    icon: BuildingLibraryIcon,
    color: 'bg-amber-500',
    description: 'Data referensi/fondasi yang dipakai oleh modul Siswa dan Pembayaran. Sebagian besar ditarik dari sistem eksternal lewat menu Sync, sebagian bisa dikelola manual (Jenis/Kategori/Tipe Pembayaran).',
    diagram: `flowchart LR
    TA["Tahun Ajaran"] --> RB["Rombel"]
    SM["Semester"] --> TG["Tagihan"]
    SK["Sekolah"] --> JR["Jurusan"]
    JR --> RB
    KL["Kelas"] --> RB
    RB --> SW["Data Siswa"]
    JP["Jenis Pembayaran"] --> MP["Master Pembayaran"]
    KP["Kategori Pembayaran"] --> MP
    TP["Tipe Pembayaran"] --> MP`,
    menus: [
      { label: 'Sekolah', path: '/master-data/sekolah', desc: 'Identitas sekolah/unit pendidikan' },
      { label: 'Jurusan', path: '/master-data/jurusan', desc: 'Program/jurusan di bawah sekolah' },
      { label: 'Kelas', path: '/master-data/kelas', desc: 'Tingkat kelas: X, XI, XII' },
      { label: 'Rombel', path: '/master-data/rombel', desc: 'Rombongan belajar, gabungan sekolah+jurusan+kelas+tahun ajaran' },
      { label: 'Tahun Ajaran', path: '/master-data/tahun-ajaran', desc: 'Periode tahun ajaran berjalan' },
      { label: 'Semester', path: '/master-data/semester', desc: 'Ganjil/Genap, dipakai sebagai snapshot transaksi' },
      { label: 'Jenis Pembayaran', path: '/master-data/jenis-pembayaran', desc: 'Klasifikasi jenis pembayaran (SPP, dsb)' },
      { label: 'Kategori Pembayaran', path: '/master-data/kategori-pembayaran', desc: 'Pengelompokan kategori tagihan' },
      { label: 'Tipe Pembayaran', path: '/master-data/tipe-pembayaran', desc: 'Tipe/metode pembayaran' },
    ],
  },
  {
    key: 'siswa',
    title: 'Siswa',
    icon: UserGroupIcon,
    color: 'bg-indigo-500',
    description: 'Data siswa ditarik dari sistem eksternal lalu dipetakan ke rombel. Siswa naik kelas dengan memindahkan rombel, atau dijadikan alumni saat lulus/keluar. Penting: "Jadikan Alumni" hanya mengubah flag isAlumni (tidak menyentuh tagihan), dan halaman Data Alumni hanya daftar — untuk cek tagihan/tunggakan alumni harus lewat menu Billing.',
    diagram: `flowchart LR
    Sy["Sync Siswa"] --> DS["Daftar Siswa"]
    DS --> MR["Mapping ke Rombel"]
    MR --> KK["Kenaikan Kelas"]
    KK --> AL["Jadikan Alumni\n(set isAlumni=1)"]
    AL --> DA["Data Alumni\n(daftar saja)"]
    AL --> BL["Billing: toggle Alumni Tunggakan"]
    BL -->|"hanya alumni bersisa tagihan"| PS["Pilih Alumni"]
    PS -->|"GET tagihan/siswa/id"| CT["Tagihan Belum Lunas Tampil"]
    CT --> BP["Proses Pembayaran"]`,
    menus: [
      { label: 'Daftar Siswa', path: '/siswa', desc: 'CRUD data siswa aktif' },
      { label: 'Mapping Rombel', path: '/siswa-rombel/mapping', desc: 'Menempatkan siswa ke rombel tertentu' },
      { label: 'Kenaikan Kelas', path: '/siswa/kenaikan-kelas', desc: 'Pindah rombel massal ke tingkat berikutnya' },
      { label: 'Jadikan Alumni', path: '/siswa/alumni', desc: 'Menandai siswa lulus/keluar sebagai alumni (isAlumni=1), tagihan tidak berubah' },
      { label: 'Data Alumni', path: '/siswa/data-alumni', desc: 'Daftar alumni saja — tombol "Detail" ke form edit siswa, BUKAN ke tagihan' },
      { label: 'Cek Tagihan Alumni', path: '/billing', desc: 'Di halaman Billing, toggle "Alumni (Tunggakan)" untuk pilih alumni yang masih punya sisa tagihan lalu proses bayar' },
    ],
  },
  {
    key: 'payments',
    title: 'Payments (Hierarki 4 Level)',
    icon: CreditCardIcon,
    color: 'bg-emerald-500',
    description: 'Inti transaksi keuangan: Master Pembayaran adalah template tagihan, digenerate massal jadi Tagihan per siswa, tiap Tagihan punya satu/lebih Invoice, dan tiap Invoice dilunasi lewat satu/lebih Pembayaran.',
    diagram: `flowchart LR
    MP["Master Pembayaran"] -->|"Generate Batch"| TG["Tagihan"]
    TG --> IV["Invoice"]
    IV -->|"Proses Bayar"| PB["Pembayaran"]
    PB -->|"Verifikasi"| VF["Terverifikasi"]
    TG -.push sync.-> EXT["Sistem Eksternal"]
    PB -.push sync.-> EXT`,
    menus: [
      { label: 'Master Pembayaran', path: '/master-pembayaran', desc: 'Template/aturan tagihan (nominal, target rombel, dsb)' },
      { label: 'Tagihan', path: '/tagihan', desc: 'Tagihan per siswa hasil generate dari Master Pembayaran' },
      { label: 'Invoice', path: '/invoice', desc: 'Rincian tagihan yang harus dibayar' },
      { label: 'Pembayaran', path: '/pembayaran', desc: 'Transaksi pelunasan invoice' },
      { label: 'Billing', path: '/billing', desc: 'Ringkasan tagihan & pembayaran per siswa' },
    ],
  },
  {
    key: 'sync',
    title: 'Sync (Sinkronisasi Eksternal)',
    icon: ArrowPathIcon,
    color: 'bg-teal-500',
    description: 'Menarik (pull) data akademik dari sistem eksternal ke database lokal, dan mendorong (push) data billing (Tagihan & Pembayaran) balik ke sistem eksternal. Urutan pull mengikuti dependensi data.',
    diagram: `flowchart TB
    subgraph Pull["Tarik Data Akademik"]
        TA2["Tahun Ajaran"] --> SM2["Semester"] --> SK2["Sekolah"] --> KL2["Kelas"] --> JR2["Jurusan"] --> RB2["Rombel"] --> SW2["Siswa"]
    end
    subgraph Push["Kirim Data Billing"]
        TG2["Tagihan"] --> EXT2["Sistem Eksternal"]
        PB3["Pembayaran"] --> EXT2
    end`,
    menus: [
      { label: 'Sinkronisasi Data', path: '/sync', desc: 'Tarik data akademik & pantau status sinkronisasi terakhir, dengan filter Kelas untuk Rombel/Siswa' },
    ],
  },
  {
    key: 'reports',
    title: 'Reports & Diagrams',
    icon: ChartBarIcon,
    color: 'bg-rose-500',
    description: 'Laporan dan visualisasi dibangun dari data Siswa, Rombel, dan Alumni yang sudah tersinkron/tercatat.',
    diagram: `flowchart LR
    DS3["Data Siswa"] --> LR1["Laporan Siswa"]
    RB3["Rombel"] --> LR2["Laporan Rombel"]
    AL2["Data Alumni"] --> LR3["Laporan Alumni"]
    LR1 --> DG["Diagram Visual"]
    LR2 --> DG
    LR3 --> DG`,
    menus: [
      { label: 'Laporan Harian', path: '/reports', desc: 'Ringkasan transaksi harian' },
      { label: 'Laporan Siswa', path: '/reports/siswa', desc: 'Rekap data siswa, bisa dicetak' },
      { label: 'Laporan Rombel', path: '/reports/rombel', desc: 'Rekap siswa per rombel, bisa dicetak' },
      { label: 'Laporan Alumni', path: '/reports/alumni-analysis', desc: 'Agregat tunggakan alumni per tahun angkatan — bukan rincian per-alumni (untuk itu pakai Billing)' },
      { label: 'Diagram Alumni', path: '/diagrams/alumni', desc: 'Visualisasi statistik alumni' },
      { label: 'Diagram Siswa Aktif', path: '/diagrams/student', desc: 'Visualisasi statistik siswa aktif' },
    ],
  },
  {
    key: 'closing',
    title: 'Closing',
    icon: LockClosedIcon,
    color: 'bg-fuchsia-500',
    description: 'Mengunci periode transaksi tertentu supaya Invoice dan Pembayaran pada tanggal tsb tidak bisa diubah lagi — mencegah manipulasi data setelah periode ditutup.',
    diagram: `flowchart LR
    PD["Pilih Periode/Tanggal"] --> CL["Set Status Closed"]
    CL --> BL["Blokir write Invoice & Pembayaran pada periode itu"]`,
    menus: [
      { label: 'Closing Management', path: '/closing', desc: 'Buka/tutup periode transaksi per tanggal' },
    ],
  },
  {
    key: 'admin',
    title: 'Administratif',
    icon: Cog6ToothIcon,
    color: 'bg-slate-500',
    description: 'Modul pendukung operasional aplikasi — hanya bisa diakses Admin. Reset Data dan Trash sama-sama menghapus data tapi berbeda sifat: Trash mengelola Tagihan/Pembayaran yang di-soft-delete (bisa dipulihkan), Reset Data menghapus permanen kategori data terpilih untuk mulai dari awal.',
    diagram: `flowchart TB
    US["Users"] -->|"kelola akses"| RL["Role: Admin / Operator / Kepsek"]
    ST["Settings"] --> AP["Konfigurasi Aplikasi & Job"]
    BK["Backups"] --> DB[("Database")]
    TR["Trash"] -->|"Restore / Hapus Permanen"| TG3["Tagihan & Pembayaran ter-soft-delete"]
    RD["Reset Data"] -->|"Hard delete kategori terpilih"| DB
    FJ["Failed Jobs"] --> QR["Retry / Flush Antrean"]`,
    menus: [
      { label: 'Users', path: '/users', desc: 'Kelola akun pengguna & role (Admin/Operator/Kepsek)' },
      { label: 'Settings', path: '/settings', desc: 'Konfigurasi aplikasi & pengaturan job terjadwal' },
      { label: 'Backups', path: '/backups', desc: 'Backup & restore database' },
      { label: 'Trash', path: '/trash', desc: 'Pulihkan atau hapus permanen Tagihan/Pembayaran yang di-soft-delete' },
      { label: 'Reset Data', path: '/reset-data', desc: 'Hapus permanen kategori data terpilih untuk mulai dari awal' },
      { label: 'Failed Jobs', path: '/failed-jobs', desc: 'Pantau & retry pekerjaan antrean yang gagal' },
    ],
  },
]

onMounted(() => {
  if (!isAdminUser.value) {
    router.replace('/dashboard')
  }
})
</script>

<style scoped>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
