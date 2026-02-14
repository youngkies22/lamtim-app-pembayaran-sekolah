<template>
    <Layout :active-menu="'Payments'">
        <div class="space-y-6">
            <!-- Modern Header with Gradient -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-500 via-sky-500 to-cyan-500 p-8 shadow-xl">
                <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </div>
                            Pembayaran
                        </h1>
                        <p class="mt-2 text-sky-100">Kelola dan proses pembayaran siswa dengan mudah</p>
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
                            class="relative inline-flex items-center gap-2 px-5 py-2.5 bg-white text-sky-600 font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:transform-none cursor-pointer"
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
                            :class="showFilter ? 'bg-white text-sky-600' : 'bg-white/10 text-white hover:bg-white/20 border border-white/10'">
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
                            Periode Transaksi Hari Ini Ditutup
                        </p>
                        <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                            Anda tidak dapat memproses, membatalkan, atau menghapus pembayaran untuk tanggal ini.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Pembayaran</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{
                                formatNumber(stats.total) }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Terverifikasi</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{
                                formatNumber(stats.verified) }}</p>
                        </div>
                        <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Pending</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{
                                formatNumber(stats.pending) }}</p>
                        </div>
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Dibatalkan</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{
                                formatNumber(stats.cancelled) }}</p>
                        </div>
                        <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <Transition name="slide-fade">
                <div v-show="showFilter" class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                                </path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filter Data Pembayaran</h3>
                        </div>
                        <button @click="resetFilters"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium cursor-pointer">Reset
                            Filter</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status
                                Pembayaran</label>
                            <select v-model="filters.status"
                                class="w-full px-4 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none"
                                @change="loadData">
                                <option value="">Semua Status</option>
                                <option value="1">Berhasil</option>
                                <option value="0">Pending</option>
                                <option value="2">Dibatalkan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status
                                Verifikasi</label>
                            <select v-model="filters.isVerified"
                                class="w-full px-4 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none"
                                @change="loadData">
                                <option value="">Semua</option>
                                <option value="1">Terverifikasi</option>
                                <option value="0">Belum Verifikasi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Periode
                                Awal</label>
                            <input v-model="filters.startDate" type="date"
                                class="w-full px-4 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none"
                                @change="loadData" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Periode
                                Akhir</label>
                            <input v-model="filters.endDate" type="date"
                                class="w-full px-4 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none"
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
                                    Kode</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Siswa</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Rombel</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Nominal</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Tanggal</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Verifikasi</th>
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

        <!-- Modal Proses Pembayaran dengan Design Modern -->
        <Teleport to="body">
            <div v-if="showProsesModal"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
                @click.self="closeProsesModal">
                <div
                    class="relative mx-auto w-full max-w-3xl shadow-2xl rounded-2xl bg-white dark:bg-gray-800 max-h-[90vh] overflow-y-auto">
                    <!-- Header Modal -->
                    <div
                        class="sticky top-0 bg-gradient-to-r from-blue-500 to-cyan-500 px-6 py-5 rounded-t-2xl flex items-center justify-between z-10">
                        <div>
                            <h3 class="text-xl font-bold text-white">Proses Pembayaran Baru</h3>
                            <p class="text-sm text-sky-100 mt-1">Input pembayaran siswa dengan mudah</p>
                        </div>
                        <button @click="closeProsesModal" class="text-white hover:text-sky-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="p-6">
                        <div v-if="formError"
                            class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-lg">
                            <p class="text-sm text-red-600 dark:text-red-400">{{ formError }}</p>
                        </div>

                        <form @submit.prevent="handleProsesPembayaran" class="space-y-5">
                            <!-- Pilih Siswa -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Pilih Siswa <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                    <select v-model="prosesForm.idSiswa" required
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                        @change="onSiswaChange">
                                        <option value="">Pilih Siswa</option>
                                        <template v-if="filteredSiswaList && filteredSiswaList.length > 0">
                                            <option v-for="siswa in filteredSiswaList" :key="siswa.id"
                                                :value="siswa.id">
                                                {{ siswa.nis || '' }} - {{ siswa.nama || '' }} - {{ siswa.rombel || ''
                                                }}
                                            </option>
                                        </template>
                                        <option v-else value="" disabled>Memuat data siswa...</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Pilih Jenis Pembayaran -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Jenis Pembayaran <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <select v-model="prosesForm.idMasterPembayaran" required
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                        @change="onMasterPembayaranChange">
                                        <option value="">Pilih Jenis Pembayaran</option>
                                        <template
                                            v-if="filteredMasterPembayaranList && filteredMasterPembayaranList.length > 0">
                                            <option v-for="mp in filteredMasterPembayaranList" :key="mp.id"
                                                :value="mp.id">
                                                [{{ mp.kategori || '-' }}] {{ mp.kode || '' }} (Rp {{
                                                    formatNumber(mp.nominal || 0) }})
                                            </option>
                                        </template>
                                        <option v-else value="" disabled>Memuat data master pembayaran...</option>
                                    </select>
                                </div>
                                <div v-if="selectedMaster"
                                    class="mt-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                    <p class="text-sm text-blue-700 dark:text-blue-300">
                                        <span class="font-semibold">Kategori:</span> [{{ selectedMaster.kategori || '-'
                                        }}]
                                        <span class="mx-2">|</span>
                                        <span class="font-semibold">Tagihan:</span> Rp {{
                                            formatNumber(selectedMaster.nominal) }}
                                        <span v-if="selectedMaster.isCicilan"
                                            class="ml-2 text-xs bg-blue-100 dark:bg-blue-900/30 px-2 py-1 rounded">Bisa
                                            Cicil</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Nominal dan Metode Bayar -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Nominal Bayar <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-medium">Rp</span>
                                        <input v-model.number="prosesForm.nominalBayar" type="number" required min="1"
                                            :max="selectedMaster?.nominal || 999999999"
                                            class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                            placeholder="0" />
                                    </div>
                                    <p v-if="selectedMaster" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Maksimal: Rp {{ formatNumber(selectedMaster.nominal) }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Metode Bayar <span class="text-red-500">*</span>
                                    </label>
                                    <select v-model="prosesForm.metodeBayar" required
                                        class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                        <option value="">Pilih Metode</option>
                                        <option value="TUNAI">Tunai</option>
                                        <option value="TRANSFER">Transfer Bank</option>
                                        <option value="E-WALLET">E-Wallet</option>
                                        <option value="QRIS">QRIS</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Channel Bayar (opsional) -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Channel Bayar
                                        <span class="text-xs text-gray-500 font-normal">(opsional)</span>
                                    </label>
                                    <input v-model="prosesForm.channelBayar" type="text"
                                        class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                        placeholder="BCA, Mandiri, dll" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Nama Channel
                                        <span class="text-xs text-gray-500 font-normal">(opsional)</span>
                                    </label>
                                    <input v-model="prosesForm.namaChannel" type="text"
                                        class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                        placeholder="Atas nama" />
                                </div>
                            </div>

                            <!-- Keterangan -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Keterangan
                                    <span class="text-xs text-gray-500 font-normal">(opsional)</span>
                                </label>
                                <textarea v-model="prosesForm.keterangan" rows="3"
                                    class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"
                                    placeholder="Keterangan tambahan..."></textarea>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button type="button" @click="closeProsesModal"
                                    class="flex-1 px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">
                                    Batal
                                </button>
                                <button type="submit" :disabled="loading"
                                    class="flex-1 px-6 py-3 text-sm font-semibold bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 disabled:opacity-50 transition-all flex items-center justify-center shadow-lg hover:shadow-xl">
                                    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    {{ loading ? 'Memproses...' : 'Proses Pembayaran' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Modal Detail Pembayaran -->
        <Teleport to="body">
            <div v-if="showDetailModal"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
                @click.self="showDetailModal = false">
                <div class="relative mx-auto w-full max-w-2xl shadow-2xl rounded-2xl bg-white dark:bg-gray-800">
                    <div
                        class="sticky top-0 bg-gradient-to-r from-blue-500 to-cyan-500 px-6 py-5 rounded-t-2xl flex items-center justify-between z-10">
                        <div>
                            <h3 class="text-xl font-bold text-white">Detail Pembayaran</h3>
                            <p class="text-sm text-sky-100 mt-1">Informasi lengkap transaksi</p>
                        </div>
                        <button @click="showDetailModal = false"
                            class="text-white hover:text-sky-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12">
                                </path>
                            </svg>
                        </button>
                    </div>

                    <div v-if="detailData" class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Kode Pembayaran</p>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ detailData.kodePembayaran }}
                                </p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Tanggal & Waktu</p>
                                <p class="font-semibold text-gray-900 dark:text-white">{{
                                    formatDateTime(detailData.created_at
                                        || detailData.tanggalBayar) }}</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Siswa</p>
                            <p class="font-semibold text-lg text-gray-900 dark:text-white">{{ detailData.siswa?.nama ||
                                '-' }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">NIS: {{ detailData.siswa?.nis || '-' }}
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Nominal</p>
                                <p class="font-bold text-xl text-gray-900 dark:text-white">Rp {{
                                    formatNumber(detailData.nominalBayar) }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Metode</p>
                                <p class="font-semibold text-gray-900 dark:text-white capitalize">{{
                                    detailData.metodeBayar ||
                                    '-' }}</p>
                            </div>
                        </div>
                        <div v-if="detailData.keterangan" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Keterangan</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ detailData.keterangan }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Confirm Verify Modal -->
        <ConfirmModal :show="showVerifyModal" type="success" title="Verifikasi Pembayaran"
            message="Apakah Anda yakin ingin memverifikasi pembayaran ini?" confirm-text="Ya, Verifikasi"
            :loading="actionLoading" @confirm="confirmVerify" @cancel="showVerifyModal = false" />

        <!-- Confirm Cancel Modal -->
        <ConfirmModal :show="showCancelModal" type="danger" title="Batalkan Pembayaran"
            message="Apakah Anda yakin ingin membatalkan pembayaran ini? Tindakan ini tidak dapat dibatalkan."
            confirm-text="Ya, Batalkan" :loading="actionLoading" @confirm="confirmCancel"
            @cancel="showCancelModal = false" />

        <!-- Confirm Delete Modal -->
        <ConfirmModal :show="showDeleteModal" type="danger" title="Hapus Pembayaran"
            message="Apakah Anda yakin ingin menghapus pembayaran ini? Data akan dipindahkan ke Trash."
            confirm-text="Ya, Hapus" :loading="actionLoading" @confirm="confirmDelete"
            @cancel="showDeleteModal = false" />

        <Toast ref="toastRef" />
    </Layout>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, nextTick, computed } from 'vue';
import Layout from '../../components/layout/Layout.vue';
import ConfirmModal from '../../components/ConfirmModal.vue';
import Toast from '../../components/Toast.vue';
import { pembayaranAPI, siswaAPI, masterPembayaranAPI } from '../../services/api';
import { useRoleAccess } from '../../composables/useRoleAccess';
import { useAppSettings } from '../../composables/useAppSettings';
import { useClosingCheck } from '../../composables/useClosingCheck';

const { appSettings } = useAppSettings();
const { isClosed, checkDateStatus } = useClosingCheck();

// Refs
const tableRef = ref(null);
const dataTable = ref(null);
const toastRef = ref(null);
const exporting = ref(false);
const printing = ref(false);
const showProsesModal = ref(false);
const showDetailModal = ref(false);
const showVerifyModal = ref(false);
const showCancelModal = ref(false);
const showDeleteModal = ref(false);
const loading = ref(false);
const actionLoading = ref(false);
const formError = ref('');
const detailData = ref(null);
const selectedPembayaranId = ref(null);
const showFilter = ref(false);

// Master data
const siswaList = ref([]);
const masterPembayaranList = ref([]);

// Stats
const stats = reactive({
    total: 0,
    verified: 0,
    pending: 0,
    cancelled: 0,
});

// Filters
const filters = reactive({
    status: '',
    isVerified: '',
    startDate: new Date().toISOString().split('T')[0], // Hari ini
    endDate: new Date().toISOString().split('T')[0],   // Hari ini
});

const resetFilters = () => {
    filters.status = '';
    filters.isVerified = '';
    filters.startDate = new Date().toISOString().split('T')[0];
    filters.endDate = new Date().toISOString().split('T')[0];
    loadData();
};

// Proses Form
const initialProsesForm = {
    idSiswa: '',
    idMasterPembayaran: '',
    nominalBayar: 0,
    metodeBayar: '',
    channelBayar: '',
    namaChannel: '',
    buktiBayar: '',
    keterangan: '',
};

const prosesForm = reactive({ ...initialProsesForm });

// Computed
const selectedMaster = computed(() => {
    return masterPembayaranList.value.find(mp => mp && mp.id === prosesForm.idMasterPembayaran);
});

// Filtered lists to ensure no null items
const filteredSiswaList = computed(() => {
    return siswaList.value.filter(siswa => siswa && siswa.id);
});

const filteredMasterPembayaranList = computed(() => {
    return masterPembayaranList.value.filter(mp => mp && mp.id);
});

// Helper
const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0);
};

const formatDateTime = (date) => {
    if (!date) return '-';
    // Format dd/mm/yyyy HH:mm:ss
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    const seconds = String(d.getSeconds()).padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
};

// Load master data
const loadMasterData = async () => {
    try {
        const [siswaRes, mpRes] = await Promise.all([
            siswaAPI.list({ isActive: 1 }),
            masterPembayaranAPI.list({ isActive: 1 }),
        ]);

        // Handle response structure - could be data.data or data.items or just data
        const siswaData = siswaRes.data?.data || siswaRes.data?.items || siswaRes.data || [];
        const mpData = mpRes.data?.data || mpRes.data?.items || mpRes.data || [];

        // Filter out null/undefined items and ensure they have id
        siswaList.value = Array.isArray(siswaData)
            ? siswaData.filter(siswa => siswa && siswa.id)
            : [];
        masterPembayaranList.value = Array.isArray(mpData)
            ? mpData.filter(mp => mp && mp.id)
            : [];
    } catch (err) {
        console.error('Error loading master data:', err);
        toastRef.value?.error('Gagal memuat data master');
        siswaList.value = [];
        masterPembayaranList.value = [];
    }
};

// Load stats
const loadStats = async () => {
    try {
        const response = await pembayaranAPI.stats();
        if (response.data && response.data.success) {
            Object.assign(stats, response.data.data);
        }
    } catch (err) {
        console.error('Error loading stats:', err);
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
            url: '/api/pembayaran/datatable',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
            },
            data: function (d) {
                d.status = filters.status;
                d.isVerified = filters.isVerified;
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
            { data: 'kodePembayaran', name: 'kodePembayaran' },
            { data: 'siswa_nama', name: 'siswa.nama' },
            { data: 'rombel_nama', name: 'siswa.currentRombel.rombel.nama', orderable: false },
            { data: 'nominal_formatted', name: 'nominalBayar', orderable: false },
            { data: 'tanggal_formatted', name: 'tanggalBayar' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'verifikasi_badge', name: 'isVerified', orderable: false },
            {
                data: null,
                name: 'action',
                orderable: false,
                searchable: false,
                render: function (data) {
                    const { currentRole, ROLES } = useRoleAccess();
                    const userRole = currentRole.value;
                    const isAdmin = userRole === ROLES.ADMIN;

                    let buttons = `<button data-action="detail" data-id="${data.id}" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors cursor-pointer">Detail</button>`;

                    if (!data.isVerified && data.status === 1) {
                        buttons += `<button data-action="verify" data-id="${data.id}" class="px-3 py-1.5 text-xs font-medium text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors cursor-pointer ml-1">Verifikasi</button>`;
                    }

                    if (data.status === 1 && !data.isVerified) {
                        buttons += `<button data-action="cancel" data-id="${data.id}" class="px-3 py-1.5 text-xs font-medium text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-300 bg-orange-50 dark:bg-orange-900/20 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors cursor-pointer ml-1">Batal</button>`;
                    }

                    // Tombol Hapus: Admin selalu bisa, Operator hanya jika status batal (2) atau pending (0), TAPI tidak boleh jika periode closed
                    if (!data.is_closed && (isAdmin || data.status === 0 || data.status === 2)) {
                        buttons += `<button data-action="delete" data-id="${data.id}" class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors ml-1 cursor-pointer">Hapus</button>`;
                    }

                    return `<div class="flex gap-2">${buttons}</div>`;
                }
            },
        ],
        order: [[4, 'desc']],
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
                } else if (action === 'verify') {
                    handleVerify(id);
                } else if (action === 'cancel') {
                    handleCancel(id);
                } else if (action === 'delete') {
                    handleDelete(id);
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

// Modal handlers
const openProsesModal = () => {
    Object.assign(prosesForm, initialProsesForm);
    formError.value = '';
    showProsesModal.value = true;
};

const closeProsesModal = () => {
    showProsesModal.value = false;
    Object.assign(prosesForm, initialProsesForm);
    formError.value = '';
};

const onSiswaChange = () => {
    // Can load siswa-specific data here if needed
};

const onMasterPembayaranChange = () => {
    const selected = masterPembayaranList.value.find(mp => mp.id === prosesForm.idMasterPembayaran);
    if (selected) {
        prosesForm.nominalBayar = selected.nominal;
    }
};

const handleProsesPembayaran = async () => {
    try {
        loading.value = true;
        formError.value = '';

        await pembayaranAPI.proses(prosesForm);
        toastRef.value?.success('Pembayaran berhasil diproses');
        closeProsesModal();
        loadData();
        loadStats();
    } catch (err) {
        formError.value = err.response?.data?.message || 'Terjadi kesalahan saat memproses pembayaran';
    } finally {
        loading.value = false;
    }
};

const handleDetail = async (id) => {
    try {
        const response = await pembayaranAPI.get(id);
        detailData.value = response.data.data;
        showDetailModal.value = true;
    } catch (err) {
        toastRef.value?.error(err.response?.data?.message || 'Gagal memuat detail pembayaran');
    }
};

const handleVerify = (id) => {
    selectedPembayaranId.value = id;
    showVerifyModal.value = true;
};

const confirmVerify = async () => {
    try {
        actionLoading.value = true;
        await pembayaranAPI.verify(selectedPembayaranId.value);
        showVerifyModal.value = false;
        toastRef.value?.success('Pembayaran berhasil diverifikasi');
        loadData();
        loadStats();
    } catch (err) {
        toastRef.value?.error(err.response?.data?.message || 'Gagal memverifikasi pembayaran');
    } finally {
        actionLoading.value = false;
    }
};

const handleCancel = (id) => {
    selectedPembayaranId.value = id;
    showCancelModal.value = true;
};

const confirmCancel = async () => {
    try {
        actionLoading.value = true;
        await pembayaranAPI.cancel(selectedPembayaranId.value);
        showCancelModal.value = false;
        toastRef.value?.success('Pembayaran berhasil dibatalkan');
        loadData();
        loadStats();
    } catch (err) {
        toastRef.value?.error(err.response?.data?.message || 'Gagal membatalkan pembayaran');
    } finally {
        actionLoading.value = false;
    }

};

const handleDelete = (id) => {
    selectedPembayaranId.value = id;
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    try {
        actionLoading.value = true;
        await pembayaranAPI.delete(selectedPembayaranId.value);
        showDeleteModal.value = false;
        toastRef.value?.success('Pembayaran berhasil dihapus');
        loadData();
        loadStats();
    } catch (err) {
        toastRef.value?.error(err.response?.data?.message || 'Gagal menghapus pembayaran');
    } finally {
        actionLoading.value = false;
    }
};

// Export Excel
const handleExportExcel = async () => {
    try {
        exporting.value = true;
        const response = await pembayaranAPI.exportExcel({
            status: filters.status,
            isVerified: filters.isVerified,
            startDate: filters.startDate,
            endDate: filters.endDate,
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `pembayaran_${new Date().toISOString().slice(0, 10)}.xlsx`);
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

// Print
const handlePrint = async () => {
    try {
        printing.value = true;
        const response = await pembayaranAPI.list({
            status: filters.status,
            isVerified: filters.isVerified,
            startDate: filters.startDate,
            endDate: filters.endDate,
            per_page: 9999,
        });

        const items = response.data?.data?.data || response.data?.data || [];
        const sekolahNama = appSettings.value?.sekolah?.nama || 'Sekolah';
        const tahunAjaran = appSettings.value?.tahun_ajaran?.tahun || '';
        const namaAplikasi = appSettings.value?.nama_aplikasi || 'SPP Management';

        const statusLabels = { 0: 'Pending', 1: 'Berhasil', 2: 'Dibatalkan' };
        const verifikasiLabels = { 0: 'Belum', 1: 'Terverifikasi' };

        let totalNominal = 0;
        const rows = items.map((item, idx) => {
            const nominal = parseFloat(item.nominalBayar) || 0;
            totalNominal += nominal;
            return `<tr>
                <td style="padding:6px 10px;border:1px solid #ddd;text-align:center">${idx + 1}</td>
                <td style="padding:6px 10px;border:1px solid #ddd">${item.kodePembayaran || '-'}</td>
                <td style="padding:6px 10px;border:1px solid #ddd">${item.siswa?.nama || '-'}</td>
                <td style="padding:6px 10px;border:1px solid #ddd">${item.rombel_nama || '-'}</td>
                <td style="padding:6px 10px;border:1px solid #ddd">${item.siswa?.nis || '-'}</td>
                <td style="padding:6px 10px;border:1px solid #ddd;text-align:right">Rp ${formatNumber(nominal)}</td>
                <td style="padding:6px 10px;border:1px solid #ddd">${item.metodeBayar || '-'}</td>
                <td style="padding:6px 10px;border:1px solid #ddd;text-align:center">${item.tanggalBayarFormatted || formatDateTime(item.tanggalBayar)}</td>
                <td style="padding:6px 10px;border:1px solid #ddd;text-align:center">${statusLabels[item.status] || '-'}</td>
                <td style="padding:6px 10px;border:1px solid #ddd;text-align:center">${verifikasiLabels[item.isVerified ? 1 : 0]}</td>
            </tr>`;
        }).join('');

        const periodeLabel = filters.startDate && filters.endDate
            ? `Periode: ${filters.startDate} s/d ${filters.endDate}`
            : 'Semua Periode';

        const win = window.open('', '_blank');
        win.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>Laporan Pembayaran</title>
<style>
  @page { size: A4 landscape; margin: 12mm; }
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
    <h3>LAPORAN PEMBAYARAN</h3>
    ${tahunAjaran ? `<p>Tahun Ajaran ${tahunAjaran}</p>` : ''}
    <p>${periodeLabel}</p>
  </div>
  <hr class="divider"><hr class="divider-thin">
  <table>
    <thead>
      <tr>
        <th style="text-align:center;width:30px">No</th>
        <th>Kode</th>
        <th>Nama Siswa</th>
        <th>Rombel</th>
        <th>NIS</th>
        <th style="text-align:right">Nominal</th>
        <th>Metode</th>
        <th style="text-align:center">Tanggal</th>
        <th style="text-align:center">Status</th>
        <th style="text-align:center">Verifikasi</th>
      </tr>
    </thead>
    <tbody>
      ${rows || '<tr><td colspan="9" style="text-align:center;padding:20px;border:1px solid #ddd;color:#999">Tidak ada data</td></tr>'}
      ${items.length > 0 ? `<tr class="total-row">
        <td colspan="4" style="padding:8px 10px;border:1px solid #ddd;text-align:center">TOTAL (${items.length} transaksi)</td>
        <td style="padding:8px 10px;border:1px solid #ddd;text-align:right">Rp ${formatNumber(totalNominal)}</td>
        <td colspan="4" style="padding:8px 10px;border:1px solid #ddd"></td>
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
        toastRef.value?.error('Gagal memuat data untuk print');
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
    loadStats();
    initDataTable();
    loadMasterData();
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
