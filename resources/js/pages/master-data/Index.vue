<template>
  <Layout :active-menu="'Master Data'">
    <div class="space-y-6">
      <!-- Modern Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl text-white shadow-lg">
              <BuildingLibraryIcon class="w-6 h-6 md:w-7 md:h-7" />
            </div>
            <span>Data Master</span>
            <button @click="showInfoModal = true" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group"
              title="Informasi Data Master">
              <InformationCircleIcon class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 group-hover:scale-110 transition-transform" />
            </button>
          </h1>
          <p class="mt-1.5 text-sm text-gray-600 dark:text-gray-400">Kelola data master sistem sekolah</p>
        </div>
      </div>

      <!-- Master Data Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Sekolah Card -->
        <div
          @click="goTo('/master-data/sekolah')"
          class="group bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 cursor-pointer hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300"
        >
          <div class="flex items-center justify-between mb-4">
            <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-xl group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition-colors">
              <BuildingLibraryIcon class="w-8 h-8 text-blue-600 dark:text-blue-400" />
            </div>
            <ChevronRightIcon class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" />
          </div>
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Sekolah</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Manajemen data sekolah</p>
        </div>

        <!-- Jurusan Card -->
        <div
          @click="goTo('/master-data/jurusan')"
          class="group bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 cursor-pointer hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300"
        >
          <div class="flex items-center justify-between mb-4">
            <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl group-hover:bg-indigo-200 dark:group-hover:bg-indigo-900/50 transition-colors">
              <AcademicCapIcon class="w-8 h-8 text-indigo-600 dark:text-indigo-400" />
            </div>
            <ChevronRightIcon class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors" />
          </div>
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Jurusan</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Manajemen data jurusan sekolah</p>
        </div>

        <!-- Kelas Card -->
        <div
          @click="goTo('/master-data/kelas')"
          class="group bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 cursor-pointer hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300"
        >
          <div class="flex items-center justify-between mb-4">
            <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-xl group-hover:bg-green-200 dark:group-hover:bg-green-900/50 transition-colors">
              <BuildingOfficeIcon class="w-8 h-8 text-green-600 dark:text-green-400" />
            </div>
            <ChevronRightIcon class="w-5 h-5 text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors" />
          </div>
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Kelas</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Manajemen data tingkatan kelas</p>
        </div>

        <!-- Rombel Card -->
        <div
          @click="goTo('/master-data/rombel')"
          class="group bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 cursor-pointer hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300"
        >
          <div class="flex items-center justify-between mb-4">
            <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-xl group-hover:bg-purple-200 dark:group-hover:bg-purple-900/50 transition-colors">
              <UsersIcon class="w-8 h-8 text-purple-600 dark:text-purple-400" />
            </div>
            <ChevronRightIcon class="w-5 h-5 text-gray-400 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors" />
          </div>
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Rombel</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Manajemen data rombongan belajar</p>
        </div>
      </div>
    </div>

    <!-- Info Modal -->
    <InfoModal :show="showInfoModal" title="Informasi Data Master"
      subtitle="Pelajari fungsi halaman data master" :sections="infoSections" @close="showInfoModal = false" />
  </Layout>
</template>

<script setup>
import { ref, markRaw } from 'vue';
import Layout from '../../components/layout/Layout.vue';
import InfoModal from '../../components/InfoModal.vue';
import { useRouter } from 'vue-router';
import {
  BuildingLibraryIcon,
  AcademicCapIcon,
  BuildingOfficeIcon,
  UsersIcon,
  ChevronRightIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline';

const router = useRouter();

const goTo = (path) => {
  router.push(path);
};

const showInfoModal = ref(false);
const infoSections = [
  {
    title: 'Fungsi Halaman Ini',
    description: 'Data Master adalah pusat navigasi ke data-data pokok yang menjadi dasar seluruh sistem: sekolah, jurusan, kelas, dan rombel. Perubahan di data master akan berdampak ke seluruh modul lain (siswa, tagihan, laporan, dll).',
    icon: markRaw(BuildingLibraryIcon),
    items: [
      { name: 'Sekolah', description: 'Profil sekolah: nama, alamat, logo' },
      { name: 'Jurusan', description: 'Kompetensi keahlian / jurusan yang tersedia' },
      { name: 'Kelas', description: 'Tingkat kelas, misalnya X, XI, XII' },
      { name: 'Rombel', description: 'Rombongan belajar — gabungan kelas + jurusan + nama rombel' },
    ],
  },
];
</script>
