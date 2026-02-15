<template>
    <Layout :active-menu="'System'">
        <div class="space-y-6">
            <!-- Header -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500 p-8 shadow-xl">
                <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                <ExclamationTriangleIcon class="w-8 h-8 text-white" />
                            </div>
                            Failed Jobs
                        </h1>
                        <p class="mt-2 text-rose-100">Kelola dan pantau antrean pekerjaan yang gagal</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="handleRetryAll" :disabled="loading || jobs.length === 0"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-rose-600 font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:transform-none">
                            <ArrowPathIcon class="w-5 h-5" :class="{ 'animate-spin': loading }" />
                            <span>Retry All</span>
                        </button>
                        <button @click="handleFlush" :disabled="loading || jobs.length === 0"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:transform-none">
                            <TrashIcon class="w-5 h-5" />
                            <span>Flush All</span>
                        </button>
                        <button @click="loadJobs"
                            class="p-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl transition-all duration-300 backdrop-blur-sm border border-white/10">
                            <ArrowPathIcon class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Jobs Table -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/80 border-b border-gray-200 dark:border-gray-600">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Job Name</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Queue</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Failed At</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            <tr v-if="jobs.length === 0" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-full">
                                            <CheckCircleIcon class="w-8 h-8 text-green-500" />
                                        </div>
                                        <p class="font-medium">Tidak ada pekerjaan yang gagal</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="job in jobs" :key="job.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">
                                <td class="px-6 py-4 font-mono text-xs text-gray-500">#{{ job.id }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900 dark:text-white">{{ job.display_name }}</span>
                                        <span class="text-xs text-gray-500 font-mono truncate max-w-xs">{{ job.uuid }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg uppercase tracking-wide">
                                        {{ job.queue }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300 font-medium">
                                    {{ formatDate(job.failed_at) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="showErrorDetail(job)" title="View Error"
                                            class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-all">
                                            <EyeIcon class="w-5 h-5" />
                                        </button>
                                        <button @click="handleRetry(job.id)" title="Retry Job"
                                            class="p-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg transition-all">
                                            <ArrowPathIcon class="w-5 h-5" />
                                        </button>
                                        <button @click="handleDelete(job.id)" title="Delete Job"
                                            class="p-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-all">
                                            <TrashIcon class="w-5 h-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Error Detail Modal -->
        <Teleport to="body">
            <div v-if="selectedJob"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
                @click.self="selectedJob = null">
                <div class="relative mx-auto w-full max-w-4xl shadow-2xl rounded-2xl bg-white dark:bg-gray-800">
                    <div class="bg-gradient-to-r from-rose-500 to-pink-500 px-6 py-5 rounded-t-2xl flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white">Error Detail</h3>
                            <p class="text-sm text-rose-100 mt-1">#{{ selectedJob.id }} - {{ selectedJob.display_name }}</p>
                        </div>
                        <button @click="selectedJob = null" class="text-white hover:text-rose-100 transition-colors">
                            <XMarkIcon class="w-6 h-6" />
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="bg-gray-900 rounded-xl p-5 overflow-x-auto max-h-[60vh]">
                            <pre class="text-rose-400 font-mono text-xs leading-relaxed">{{ selectedJob.exception }}</pre>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button @click="selectedJob = null"
                                class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:bg-gray-200 transition-all">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <ConfirmModal :show="showDeleteModal" type="danger" title="Hapus Job"
            message="Apakah Anda yakin ingin menghapus data pekerjaan yang gagal ini?"
            confirm-text="Ya, Hapus" :loading="actionLoading" @confirm="confirmDelete" @cancel="showDeleteModal = false" />

        <ConfirmModal :show="showFlushModal" type="danger" title="Bersihkan Semua"
            message="Apakah Anda yakin ingin menghapus SEMUA data pekerjaan yang gagal?"
            confirm-text="Ya, Bersihkan" :loading="actionLoading" @confirm="confirmFlush" @cancel="showFlushModal = false" />

        <Toast ref="toastRef" />
    </Layout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Layout from '../../components/layout/Layout.vue';
import ConfirmModal from '../../components/ConfirmModal.vue';
import Toast from '../../components/Toast.vue';
import { jobsAPI } from '../../services/api';
import { 
    ExclamationTriangleIcon, 
    ArrowPathIcon, 
    TrashIcon, 
    EyeIcon,
    XMarkIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline';

const jobs = ref([]);
const loading = ref(false);
const actionLoading = ref(false);
const toastRef = ref(null);
const selectedJob = ref(null);
const showDeleteModal = ref(false);
const showFlushModal = ref(false);
const selectedJobId = ref(null);

const loadJobs = async () => {
    try {
        loading.value = true;
        const response = await jobsAPI.getFailed();
        if (response.data.success) {
            jobs.value = response.data.data;
        }
    } catch (err) {
        toastRef.value?.error('Gagal memuat data pekerjaan');
    } finally {
        loading.value = false;
    }
};

const handleRetry = async (id) => {
    try {
        const response = await jobsAPI.retry(id);
        if (response.data.success) {
            toastRef.value?.success('Job telah dijadwalkan ulang');
            loadJobs();
        }
    } catch (err) {
        toastRef.value?.error(err.response?.data?.message || 'Gagal menjadwalkan ulang job');
    }
};

const handleRetryAll = async () => {
    try {
        loading.value = true;
        const response = await jobsAPI.retryAll();
        if (response.data.success) {
            toastRef.value?.success('Semua job telah dijadwalkan ulang');
            loadJobs();
        }
    } catch (err) {
        toastRef.value?.error(err.response?.data?.message || 'Gagal menjadwalkan ulang semua job');
    } finally {
        loading.value = false;
    }
};

const handleDelete = (id) => {
    selectedJobId.value = id;
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    try {
        actionLoading.value = true;
        await jobsAPI.delete(selectedJobId.value);
        toastRef.value?.success('Job dihapus');
        showDeleteModal.value = false;
        loadJobs();
    } catch (err) {
        toastRef.value?.error('Gagal menghapus job');
    } finally {
        actionLoading.value = false;
    }
};

const handleFlush = () => {
    showFlushModal.value = true;
};

const confirmFlush = async () => {
    try {
        actionLoading.value = true;
        await jobsAPI.flush();
        toastRef.value?.success('Semua job dibersihkan');
        showFlushModal.value = false;
        loadJobs();
    } catch (err) {
        toastRef.value?.error('Gagal membersihkan job');
    } finally {
        actionLoading.value = false;
    }
};

const showErrorDetail = (job) => {
    selectedJob.value = job;
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
};

onMounted(() => {
    loadJobs();
});
</script>

<style scoped>
.bg-grid-pattern {
    background-image: radial-gradient(circle, #fff 1px, transparent 1px);
    background-size: 20px 20px;
}
</style>
