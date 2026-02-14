<template>
  <Layout :active-menu="'Payments'">
    <div class="space-y-6">
      <!-- Modern Header with Gradient -->
      <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-600 via-orange-600 to-red-600 p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                <CreditCardIcon class="w-8 h-8 text-white" />
              </div>
              Master Pembayaran
            </h1>
            <p class="mt-2 text-amber-100">Kelola template pembayaran (SPP, PKL, KI, UKOM, dll)</p>
          </div>
          <button @click="openCreateModal"
            class="group relative inline-flex items-center gap-2 px-6 py-3 bg-white text-amber-600 font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
            <svg class="w-5 h-5 transition-transform group-hover:rotate-90 duration-300" fill="none"
              stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Master
          </button>
        </div>
      </div>

      <!-- Modern Filter Card -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-center gap-2 mb-4">
          <div class="p-2 bg-amber-50 dark:bg-amber-900/30 rounded-lg">
            <FunnelIcon class="w-5 h-5 text-amber-600 dark:text-amber-400" />
          </div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Filter & Pencarian</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="relative">
            <MagnifyingGlassIcon
              class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none w-5 h-5 text-gray-400" />
            <input type="text" v-model="searchQuery" @input="debouncedSearch" placeholder="Cari kode atau nama..."
              class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:text-white transition-all duration-200" />
          </div>
          <div>
            <select v-model="filters.jenisPembayaran" @change="loadData"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white">
              <option value="">Semua Jenis</option>
              <option v-for="jenis in jenisList" :key="jenis.id" :value="jenis.kode">{{ jenis.nama }}</option>
            </select>
          </div>
          <div>
            <select v-model="filters.kategori" @change="loadData"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white">
              <option value="">Semua Kategori</option>
              <option v-for="kategori in kategoriList" :key="kategori.id" :value="kategori.kode">{{ kategori.nama }}
              </option>
            </select>
          </div>
          <div>
            <select v-model="filters.isActive" @change="loadData"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white">
              <option value="">Semua Status</option>
              <option value="1">Aktif</option>
              <option value="0">Tidak Aktif</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Modern Table Card -->
      <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div
          class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Menampilkan {{ tableData.length }} data
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gray-50 dark:bg-gray-900/50">
                <th
                  class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  No</th>
                <th
                  class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Kode</th>
                <th
                  class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Nama</th>
                <th
                  class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Slug</th>
                <th
                  class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Jenis</th>
                <th
                  class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Kategori</th>
                <th
                  class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Nominal</th>
                <th
                  class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Cicilan</th>
                <th
                  class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status</th>
                <th
                  class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-if="loading">
                <td colspan="9" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center gap-3">
                    <svg class="animate-spin h-10 w-10 text-amber-600" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                      </path>
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Memuat data...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="tableData.length === 0">
                <td colspan="9" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center gap-3">
                    <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full">
                      <CreditCardIcon class="w-10 h-10 text-gray-400" />
                    </div>
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Tidak ada data ditemukan</span>
                  </div>
                </td>
              </tr>
              <tr v-else v-for="(item, index) in tableData" :key="item.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ index + 1 }}</td>
                <td class="px-6 py-4">
                  <span class="text-sm font-medium text-gray-900 dark:text-white">{{ item.kode }}</span>
                </td>
                <td class="px-6 py-4">
                  <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.nama }}</p>
                    <p v-if="item.keterangan" class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">{{
                      item.keterangan }}</p>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span
                    class="text-xs font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-600 dark:text-gray-300 font-bold uppercase tracking-tight">{{
                      item.slug || '-' }}</span>
                </td>
                <td class="px-6 py-4">
                  <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                    {{ item.jenisPembayaran || '-' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                    {{ item.kategori || '-' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white text-right">
                  {{ formatCurrency(item.nominal) }}
                </td>
                <td class="px-6 py-4 text-center">
                  <span :class="[
                    'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold',
                    item.isCicilan ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400'
                  ]">
                    {{ item.isCicilan ? 'Ya' : 'Tidak' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span :class="[
                    'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold',
                    item.isActive ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                  ]">
                    <span
                      :class="['w-1.5 h-1.5 rounded-full mr-1.5', item.isActive ? 'bg-green-500' : 'bg-red-500']"></span>
                    {{ item.isActive ? 'Aktif' : 'Tidak Aktif' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <ActionButton @edit="handleEdit(item)" @delete="handleDelete(item)" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <FormModal :show="showModal" :title="editingId ? 'Edit Master Pembayaran' : 'Tambah Master Pembayaran Baru'"
      :loading="submitLoading" :error="formError" @close="closeModal" @submit="handleSubmit">
      <template #form>
        <div class="space-y-5">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Kode <span
                  class="text-xs text-gray-500">(Otomatis)</span></label>
              <div class="relative">
                <input v-model="form.kode" type="text" readonly
                  class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-600 dark:text-gray-400 cursor-not-allowed"
                  placeholder="Akan di-generate otomatis" />
                <button type="button" @click="generateKode"
                  class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 text-xs font-medium text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 bg-amber-50 dark:bg-amber-900/30 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors">
                  Generate
                </button>
              </div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Jenis Pembayaran
                *</label>
              <select v-model="form.jenisPembayaran" @change="generateKode" required
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white">
                <option value="">Pilih Jenis</option>
                <option v-for="jenis in jenisList" :key="jenis.id" :value="jenis.kode">{{ jenis.nama }}</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama *</label>
            <input v-model="form.nama" type="text" required @input="autoSlug"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white"
              placeholder="Nama pembayaran" />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Slug * <span
                class="text-xs text-gray-500">(Digunakan sebagai kolom laporan)</span></label>
            <input v-model="form.slug" type="text" required
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white font-mono"
              placeholder="contoh: BP-XI-2526, KI-X-2526" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Kategori *</label>
              <select v-model="form.kategori" @change="generateKode" required
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white">
                <option value="">Pilih Kategori</option>
                <option v-for="kategori in kategoriList" :key="kategori.id" :value="kategori.kode">{{ kategori.nama }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nominal *</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">Rp</span>
                <input :value="formatInputDisplay(form.nominal)"
                  @input="form.nominal = parseInputRaw($event.target.value)" type="text" required
                  class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white"
                  placeholder="0" />
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" v-model="form.isCicilan"
                  class="w-4 h-4 text-amber-600 rounded focus:ring-amber-500" />
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Bisa Dicicil</span>
              </label>
            </div>
            <div>
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" v-model="form.isWajib"
                  class="w-4 h-4 text-amber-600 rounded focus:ring-amber-500" />
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Wajib</span>
              </label>
            </div>
          </div>

          <div v-if="form.isCicilan" class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Minimal Cicilan</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">Rp</span>
                <input :value="formatInputDisplay(form.minCicilan)"
                  @input="form.minCicilan = parseInputRaw($event.target.value)" type="text"
                  class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white"
                  placeholder="0" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Jumlah Bulan (untuk
                SPP)</label>
              <input v-model.number="form.jumlahBulan" type="number" min="1"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white"
                placeholder="12" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Keterangan</label>
            <textarea v-model="form.keterangan" rows="3"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white resize-none"
              placeholder="Deskripsi pembayaran (opsional)"></textarea>
          </div>

          <div>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.isActive"
                class="w-4 h-4 text-amber-600 rounded focus:ring-amber-500" />
              <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Aktif</span>
            </label>
          </div>
        </div>
      </template>
    </FormModal>

    <!-- Confirm Delete Modal -->
    <ConfirmModal :show="showDeleteModal" type="danger" title="Hapus Master Pembayaran"
      :message="`Apakah Anda yakin ingin menghapus master pembayaran '${deletingItem?.nama || ''}'?`"
      confirm-text="Ya, Hapus" :loading="deleteLoading" @confirm="confirmDelete" @cancel="showDeleteModal = false" />

    <!-- Toast -->
    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import Layout from '../../components/layout/Layout.vue';
import ActionButton from '../../components/ActionButton.vue';
import FormModal from '../../components/FormModal.vue';
import ConfirmModal from '../../components/ConfirmModal.vue';
import Toast from '../../components/Toast.vue';
import { masterPembayaranAPI, masterDataAPI } from '../../services/api';
import {
  CreditCardIcon,
  FunnelIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';

const toastRef = ref(null);
const showModal = ref(false);
const showDeleteModal = ref(false);
const editingId = ref(null);
const deletingItem = ref(null);
const loading = ref(false);
const submitLoading = ref(false);
const deleteLoading = ref(false);
const formError = ref('');

const tableData = ref([]);
const searchQuery = ref('');
const jenisList = ref([]);
const kategoriList = ref([]);

const filters = reactive({
  jenisPembayaran: '',
  kategori: '',
  isActive: '',
});

const initialForm = {
  kode: '',
  nama: '',
  slug: '',
  jenisPembayaran: '',
  kategori: '',
  nominal: 0,
  isCicilan: true,
  minCicilan: null,
  jumlahBulan: null,
  isWajib: true,
  keterangan: '',
  isActive: true,
};

const form = reactive({ ...initialForm });

let searchTimer = null;

const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const autoSlug = () => {
  if (!editingId.value) {
    form.slug = form.nama
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '')
      .replace(/[\s_-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount || 0);
};

const formatInputDisplay = (val) => {
  if (val === null || val === undefined || val === '') return '';
  return new Intl.NumberFormat('id-ID').format(val);
};

const parseInputRaw = (val) => {
  if (!val) return 0;
  return Number(val.replace(/\D/g, ''));
};

const getJenisNama = (kode) => {
  const jenis = jenisList.value.find(j => j.kode === kode);
  return jenis?.nama || null;
};

const getKategoriNama = (kode) => {
  const kategori = kategoriList.value.find(k => k.kode === kode);
  return kategori?.nama || null;
};

const generateKode = () => {
  if (!form.jenisPembayaran) {
    form.kode = '';
    return;
  }

  // Ambil kode dari jenis pembayaran
  const jenis = jenisList.value.find(j => j.kode === form.jenisPembayaran);
  const jenisKode = jenis?.kode ? jenis.kode.toUpperCase() : form.jenisPembayaran.substring(0, 3).toUpperCase();

  // Generate shorter unique code using timestamp in Base36
  const timestamp = Date.now().toString(36).toUpperCase().slice(-6);

  // Format: {JENIS}-{TIMESTAMP}
  // Contoh: SPP-XYZ123
  form.kode = `${jenisKode}-${timestamp}`;
};

const loadData = async () => {
  try {
    loading.value = true;
    const params = {
      search: searchQuery.value,
      ...filters,
    };
    // Remove empty filters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null) {
        delete params[key];
      }
    });

    const response = await masterPembayaranAPI.list(params);
    const data = response.data.data || response.data;
    tableData.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (err) {
    toastRef.value?.error('Gagal memuat data master pembayaran');
    tableData.value = [];
  } finally {
    loading.value = false;
  }
};

const debouncedSearch = () => {
  if (searchTimer) clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    loadData();
  }, 500);
};

const openCreateModal = () => {
  editingId.value = null;
  Object.assign(form, initialForm);
  formError.value = '';
  form.kode = ''; // Reset kode untuk auto-generate
  showModal.value = true;
};

const handleEdit = async (item) => {
  try {
    submitLoading.value = true;
    const response = await masterPembayaranAPI.get(item.id);
    const data = response.data.data;

    editingId.value = item.id;
    Object.assign(form, {
      kode: data.kode || '',
      nama: data.nama || '',
      slug: data.slug || '',
      jenisPembayaran: data.jenisPembayaran || '',
      kategori: data.kategori || '',
      nominal: data.nominal || 0,
      isCicilan: data.isCicilan ?? true,
      minCicilan: data.minCicilan || null,
      jumlahBulan: data.jumlahBulan || null,
      isWajib: data.isWajib ?? true,
      keterangan: data.keterangan || '',
      isActive: data.isActive ?? true,
    });
    formError.value = '';
    showModal.value = true;
  } catch (err) {
    toastRef.value?.error('Gagal memuat data master pembayaran');
  } finally {
    submitLoading.value = false;
  }
};

const handleDelete = (item) => {
  deletingItem.value = item;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!deletingItem.value) return;

  try {
    deleteLoading.value = true;
    await masterPembayaranAPI.delete(deletingItem.value.id);
    showDeleteModal.value = false;
    deletingItem.value = null;
    toastRef.value?.success('Master pembayaran berhasil dihapus');
    loadData();
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal menghapus master pembayaran');
  } finally {
    deleteLoading.value = false;
  }
};

const handleSubmit = async () => {
  try {
    submitLoading.value = true;
    formError.value = '';

    // Generate kode jika kosong (untuk create)
    if (!editingId.value && !form.kode && form.jenisPembayaran && form.kategori) {
      generateKode();
    }

    const payload = {
      ...form,
      isCicilan: form.isCicilan ? 1 : 0,
      isWajib: form.isWajib ? 1 : 0,
      isActive: form.isActive ? 1 : 0,
    };

    // Remove null values, tapi jangan hapus kode jika kosong (biarkan backend generate)
    Object.keys(payload).forEach(key => {
      if (payload[key] === null || (payload[key] === '' && key !== 'kode')) {
        delete payload[key];
      }
    });

    // Jika kode kosong, hapus dari payload agar backend generate
    if (!payload.kode || payload.kode === '') {
      delete payload.kode;
    }

    if (editingId.value) {
      await masterPembayaranAPI.update(editingId.value, payload);
      toastRef.value?.success('Master pembayaran berhasil diperbarui');
    } else {
      await masterPembayaranAPI.create(payload);
      toastRef.value?.success('Master pembayaran berhasil ditambahkan');
    }

    closeModal();
    loadData();
  } catch (err) {
    formError.value = err.response?.data?.message || 'Terjadi kesalahan';
  } finally {
    submitLoading.value = false;
  }
};

const closeModal = () => {
  showModal.value = false;
  editingId.value = null;
  Object.assign(form, initialForm);
  formError.value = '';
};

const loadMasterData = async () => {
  try {
    const [jenisRes, kategoriRes] = await Promise.all([
      masterDataAPI.jenisPembayaran.list(),
      masterDataAPI.kategoriPembayaran.list(),
    ]);

    jenisList.value = jenisRes.data.data || jenisRes.data || [];
    kategoriList.value = kategoriRes.data.data || kategoriRes.data || [];
  } catch (err) {
    console.error('Error loading master data:', err);
  }
};

onMounted(async () => {
  await loadMasterData();
  loadData();
});
</script>

<style>
.bg-grid-pattern {
  background-image: radial-gradient(circle, #fff 1px, transparent 1px);
  background-size: 20px 20px;
}
</style>
