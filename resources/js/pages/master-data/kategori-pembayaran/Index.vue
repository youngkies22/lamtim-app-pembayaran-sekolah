<template>
  <Layout :active-menu="'Master Data'">
    <div class="space-y-6">
      <!-- Header -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-600 via-orange-600 to-red-600 p-8 shadow-xl">
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                <TagIcon class="w-8 h-8 text-white" />
              </div>
              Data Kategori Pembayaran
            </h1>
            <p class="mt-2 text-amber-100 flex items-center gap-2">
              Kelola kategori pembayaran (Bulanan, Rutin, Tambahan, dll)
              <button
                @click="showInfoModal = true"
                class="p-1.5 rounded-lg hover:bg-white/20 transition-colors group"
                title="Informasi Kategori Pembayaran"
              >
                <InformationCircleIcon class="w-5 h-5 text-white group-hover:scale-110 transition-transform" />
              </button>
            </p>
          </div>
          <button
            v-if="canCreateData"
            @click="openCreateModal"
            class="group relative inline-flex items-center gap-2 px-6 py-3 bg-white text-amber-600 font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
          >
            <PlusIcon class="w-5 h-5 transition-transform group-hover:rotate-90 duration-300" />
            Tambah Kategori
          </button>
        </div>
      </div>

      <!-- Search Card -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
        <div class="relative">
          <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
          <input
            type="text"
            v-model="searchQuery"
            @input="debouncedSearch"
            placeholder="Cari kategori pembayaran..."
            class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:text-white transition-all duration-200"
          />
        </div>
      </div>

      <!-- Table Card -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gray-50 dark:bg-gray-900/50">
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Deskripsi</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Urutan</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-if="loading">
                <td colspan="6" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center gap-3">
                    <svg class="animate-spin h-10 w-10 text-amber-600" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Memuat data...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="tableData.length === 0">
                <td colspan="6" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center gap-3">
                    <InboxStackIcon class="w-10 h-10 text-gray-400" />
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Tidak ada data ditemukan</span>
                  </div>
                </td>
              </tr>
              <tr v-else v-for="(item, index) in tableData" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ index + 1 }}</td>
                <td class="px-6 py-4">
                  <span class="text-sm font-medium text-gray-900 dark:text-white">{{ item.kode }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ item.nama }}</td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ item.deskripsi || '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ item.urutan }}</td>
                <td class="px-6 py-4">
                  <ActionButton
                    v-if="canEditData || canDeleteData"
                    @edit="canEditData ? handleEdit(item.id) : null"
                    @delete="canDeleteData ? handleDelete(item) : null"
                    :show-edit="canEditData"
                    :show-delete="canDeleteData"
                  />
                  <span v-else class="text-xs text-gray-400 dark:text-gray-500">Read Only</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <FormModal
      :show="showModal"
      :title="editingId ? 'Edit Kategori Pembayaran' : 'Tambah Kategori Pembayaran Baru'"
      :loading="submitLoading"
      :error="formError"
      @close="closeModal"
      @submit="handleSubmit"
    >
      <template #icon>
        <TagIcon class="w-6 h-6" />
      </template>
      <template #form>
        <div class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Kode *</label>
            <input
              v-model="form.kode"
              type="text"
              required
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white"
              placeholder="Contoh: RUTIN, TAMBAHAN"
            />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama *</label>
            <input
              v-model="form.nama"
              type="text"
              required
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white"
              placeholder="Nama lengkap kategori"
            />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Deskripsi</label>
            <textarea
              v-model="form.deskripsi"
              rows="3"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white"
              placeholder="Deskripsi kategori"
            ></textarea>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Urutan</label>
            <input
              v-model.number="form.urutan"
              type="number"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-amber-500 dark:text-white"
              placeholder="0"
            />
          </div>
        </div>
      </template>
    </FormModal>

    <!-- Confirm Delete Modal -->
    <ConfirmModal
      :show="showDeleteModal"
      type="danger"
      title="Hapus Kategori Pembayaran"
      :message="`Apakah Anda yakin ingin menghapus kategori pembayaran '${deletingItem?.nama || ''}'?`"
      confirm-text="Ya, Hapus"
      :loading="deleteLoading"
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />

    <!-- Info Modal -->
    <InfoModal
      :show="showInfoModal"
      title="Informasi Kategori Pembayaran"
      subtitle="Pelajari lebih lanjut tentang kategori pembayaran yang bisa Anda isi"
      :sections="infoSections"
      @close="showInfoModal = false"
    />

    <!-- Toast -->
    <Toast ref="toastRef" />
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted, markRaw } from 'vue';
import Layout from '../../../components/layout/Layout.vue';
import ActionButton from '../../../components/ActionButton.vue';
import FormModal from '../../../components/FormModal.vue';
import ConfirmModal from '../../../components/ConfirmModal.vue';
import Toast from '../../../components/Toast.vue';
import { masterDataAPI } from '../../../services/api';
import { useRoleAccess } from '../../../composables/useRoleAccess';
import {
  TagIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  InboxStackIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline';
import InfoModal from '../../../components/InfoModal.vue';

const { canCreateData, canEditData, canDeleteData } = useRoleAccess();

const toastRef = ref(null);
const showModal = ref(false);
const showDeleteModal = ref(false);
const showInfoModal = ref(false);
const editingId = ref(null);
const deletingItem = ref(null);
const loading = ref(false);
const submitLoading = ref(false);
const deleteLoading = ref(false);
const formError = ref('');

const infoSections = [
  {
    title: 'Apa itu Kategori Pembayaran?',
    description: 'Kategori Pembayaran digunakan untuk mengelompokkan pembayaran berdasarkan sifatnya. Kategori ini akan membantu dalam pelaporan dan pengelolaan keuangan sekolah.',
    icon: markRaw(TagIcon),
    items: [
      {
        name: 'Rutin',
        description: 'Pembayaran yang dilakukan secara rutin/berkala, seperti SPP bulanan, iuran bulanan, dll'
      },
      {
        name: 'Tambahan',
        description: 'Pembayaran yang bersifat tambahan atau insidental, seperti PKL, KI, UKOM, uang gedung, dll'
      },
      {
        name: 'Kegiatan',
        description: 'Pembayaran untuk kegiatan sekolah seperti study tour, ekstrakurikuler, dll'
      },
      {
        name: 'Lainnya',
        description: 'Kategori pembayaran lainnya yang tidak termasuk kategori di atas'
      }
    ]
  },
  {
    title: 'Cara Menggunakan',
    description: 'Anda dapat menambahkan kategori pembayaran sesuai kebutuhan sekolah. Setelah dibuat, kategori ini akan muncul di dropdown saat membuat Master Pembayaran.',
    icon: markRaw(InformationCircleIcon),
    items: []
  }
];

const tableData = ref([]);
const searchQuery = ref('');

const initialForm = {
  kode: '',
  nama: '',
  deskripsi: '',
  urutan: 0,
};

const form = reactive({ ...initialForm });

let searchTimer = null;

const loadData = async () => {
  try {
    loading.value = true;
    const response = await masterDataAPI.kategoriPembayaran.list({ search: searchQuery.value });
    tableData.value = response.data.data || response.data || [];
  } catch (err) {
    toastRef.value?.error('Gagal memuat data kategori pembayaran');
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
  showModal.value = true;
};

const handleEdit = async (id) => {
  try {
    submitLoading.value = true;
    const response = await masterDataAPI.kategoriPembayaran.get(id);
    const kategori = response.data.data;

    editingId.value = id;
    Object.assign(form, {
      kode: kategori.kode || '',
      nama: kategori.nama || '',
      deskripsi: kategori.deskripsi || '',
      urutan: kategori.urutan || 0,
    });
    formError.value = '';
    showModal.value = true;
  } catch (err) {
    toastRef.value?.error('Gagal memuat data kategori pembayaran');
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
    await masterDataAPI.kategoriPembayaran.delete(deletingItem.value.id);
    showDeleteModal.value = false;
    deletingItem.value = null;
    toastRef.value?.success('Kategori pembayaran berhasil dihapus');
    loadData();
  } catch (err) {
    toastRef.value?.error(err.response?.data?.message || 'Gagal menghapus kategori pembayaran');
  } finally {
    deleteLoading.value = false;
  }
};

const handleSubmit = async () => {
  try {
    submitLoading.value = true;
    formError.value = '';

    const payload = { ...form };

    if (editingId.value) {
      await masterDataAPI.kategoriPembayaran.update(editingId.value, payload);
      toastRef.value?.success('Kategori pembayaran berhasil diperbarui');
    } else {
      await masterDataAPI.kategoriPembayaran.create(payload);
      toastRef.value?.success('Kategori pembayaran berhasil ditambahkan');
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

onMounted(() => {
  loadData();
});
</script>

<style scoped>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.1)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}
</style>
