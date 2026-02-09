<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="show"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                @click.self="close"
            >
                <div class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden animate-modal">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Import {{ typeLabel }}
                            </h3>
                            <button @click="close" class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6">
                        <!-- Step 1: Upload File -->
                        <div v-if="step === 1" class="space-y-6">
                            <div class="text-center py-4">
                                <div class="mx-auto w-16 h-16 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Upload File Excel</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Pilih file Excel (.xlsx, .xls, .csv) yang akan diimport</p>
                            </div>

                            <!-- File Upload Area -->
                            <div
                                @click="triggerFileInput"
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop"
                                :class="[
                                    'border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all',
                                    isDragging ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-indigo-400',
                                    error ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : ''
                                ]"
                            >
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept=".xlsx,.xls,.csv"
                                    @change="handleFileSelect"
                                    class="hidden"
                                />
                                
                                <div v-if="!selectedFile" class="space-y-3">
                                    <svg class="mx-auto w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Klik untuk memilih file atau drag & drop di sini
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500">
                                        Format: .xlsx, .xls, .csv (Maks. 10MB)
                                    </p>
                                </div>
                                
                                <div v-else class="space-y-2">
                                    <svg class="mx-auto w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white">{{ selectedFile.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatFileSize(selectedFile.size) }}
                                    </p>
                                    <button
                                        @click.stop="clearFile"
                                        class="text-xs text-red-600 hover:text-red-800 dark:text-red-400"
                                    >
                                        Hapus file
                                    </button>
                                </div>
                            </div>

                            <div v-if="error" class="p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-r-xl">
                                <p class="text-sm text-red-600 dark:text-red-400 font-medium">{{ error }}</p>
                            </div>

                            <!-- Download Template Button -->
                            <div class="flex items-center justify-center gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button
                                    @click="downloadTemplate"
                                    :disabled="downloadingTemplate"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50 rounded-lg transition-colors disabled:opacity-50"
                                >
                                    <svg v-if="!downloadingTemplate" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <svg v-else class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ downloadingTemplate ? 'Mengunduh...' : 'Download Template' }}
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Progress -->
                        <div v-if="step === 2" class="space-y-6">
                            <div class="text-center py-4">
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Memproses Import</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Mohon tunggu, file sedang diproses...</p>
                            </div>

                            <!-- Progress Bar -->
                            <ProgressBar
                                :progress="progress"
                                :status="status"
                                :total-rows="totalRows"
                                :processed-rows="processedRows"
                                :success-rows="successRows"
                                :failed-rows="failedRows"
                            />

                            <!-- Error Message -->
                            <div v-if="errorMessage" class="p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-r-xl">
                                <p class="text-sm text-red-600 dark:text-red-400 font-medium">{{ errorMessage }}</p>
                            </div>

                            <!-- Download Error File -->
                            <div v-if="errorFilePath && status === 'completed'" class="flex justify-center">
                                <button
                                    @click="downloadErrorFile"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Download File Error
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div v-if="step === 1" class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex gap-3">
                        <button
                            type="button"
                            @click="close"
                            class="flex-1 px-6 py-3 text-gray-700 dark:text-gray-300 font-semibold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all"
                        >
                            Batal
                        </button>
                        <button
                            @click="handleImport"
                            :disabled="!selectedFile || uploading"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 transition-all flex items-center justify-center gap-2"
                        >
                            <svg v-if="uploading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            {{ uploading ? 'Mengupload...' : 'Import' }}
                        </button>
                    </div>

                    <div v-if="step === 2 && (status === 'completed' || status === 'failed')" class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700">
                        <button
                            @click="close"
                            class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import ProgressBar from './ProgressBar.vue';
import api from '../services/api';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        required: true, // 'siswa', 'jurusan', 'kelas', 'rombel'
    },
});

const emit = defineEmits(['close', 'success']);

const fileInput = ref(null);
const selectedFile = ref(null);
const isDragging = ref(false);
const error = ref('');
const uploading = ref(false);
const downloadingTemplate = ref(false);
const step = ref(1); // 1: upload, 2: progress
const importLogId = ref(null);
const progress = ref(0);
const status = ref('pending'); // pending, processing, completed, failed
const totalRows = ref(0);
const processedRows = ref(0);
const successRows = ref(0);
const failedRows = ref(0);
const errorMessage = ref('');
const errorFilePath = ref(null);
let progressInterval = null;

const typeLabel = computed(() => {
    const labels = {
        siswa: 'Data Siswa',
        jurusan: 'Jurusan',
        kelas: 'Kelas',
        rombel: 'Rombel',
    };
    return labels[props.type] || props.type;
});

const triggerFileInput = () => {
    fileInput.value?.click();
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        validateAndSetFile(file);
    }
};

const handleDrop = (event) => {
    isDragging.value = false;
    const file = event.dataTransfer.files[0];
    if (file) {
        validateAndSetFile(file);
    }
};

const validateAndSetFile = (file) => {
    error.value = '';
    
    // Validate file type
    const allowedTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'text/csv',
    ];
    const allowedExtensions = ['.xlsx', '.xls', '.csv'];
    const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
    
    if (!allowedExtensions.includes(fileExtension) && !allowedTypes.includes(file.type)) {
        error.value = 'Format file tidak didukung. Gunakan file Excel (.xlsx, .xls) atau CSV (.csv)';
        return;
    }
    
    // Validate file size (10MB)
    const maxSize = 10 * 1024 * 1024;
    if (file.size > maxSize) {
        error.value = 'Ukuran file terlalu besar. Maksimal 10MB';
        return;
    }
    
    selectedFile.value = file;
};

const clearFile = () => {
    selectedFile.value = null;
    error.value = '';
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const downloadTemplate = async () => {
    try {
        downloadingTemplate.value = true;
        const response = await api.get(`/api/import/template?type=${props.type}`, {
            responseType: 'blob',
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `template_${props.type}.xlsx`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (err) {
        console.error('Download template failed:', err);
        error.value = 'Gagal mengunduh template';
    } finally {
        downloadingTemplate.value = false;
    }
};

const handleImport = async () => {
    if (!selectedFile.value) return;
    
    try {
        uploading.value = true;
        error.value = '';
        
        const formData = new FormData();
        formData.append('file', selectedFile.value);
        formData.append('type', props.type);
        
        const response = await api.post('/api/import/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        
        if (response.data.success) {
            importLogId.value = response.data.data.import_log_id;
            step.value = 2;
            startProgressPolling();
        } else {
            error.value = response.data.message || 'Gagal mengupload file';
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Terjadi kesalahan saat mengupload file';
    } finally {
        uploading.value = false;
    }
};

const startProgressPolling = () => {
    if (progressInterval) {
        clearInterval(progressInterval);
    }
    
    progressInterval = setInterval(async () => {
        if (!importLogId.value) return;
        
        try {
            const response = await api.get(`/api/import/progress/${importLogId.value}`);
            
            if (response.data.success) {
                const data = response.data.data;
                progress.value = data.progress || 0;
                status.value = data.status;
                totalRows.value = data.total_rows || 0;
                processedRows.value = data.processed_rows || 0;
                successRows.value = data.success_rows || 0;
                failedRows.value = data.failed_rows || 0;
                errorMessage.value = data.error_message || '';
                errorFilePath.value = data.error_file_path;
                
                // Stop polling if completed or failed
                if (status.value === 'completed' || status.value === 'failed') {
                    clearInterval(progressInterval);
                    if (status.value === 'completed') {
                        emit('success');
                    }
                }
            }
        } catch (err) {
            console.error('Get progress failed:', err);
        }
    }, 2000); // Poll every 2 seconds
};

const downloadErrorFile = async () => {
    if (!importLogId.value) return;
    
    try {
        const response = await api.get(`/api/import/error/${importLogId.value}`, {
            responseType: 'blob',
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `error_${props.type}_${Date.now()}.xlsx`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (err) {
        console.error('Download error file failed:', err);
        error.value = 'Gagal mengunduh file error';
    }
};

const close = () => {
    if (progressInterval) {
        clearInterval(progressInterval);
    }
    step.value = 1;
    selectedFile.value = null;
    error.value = '';
    progress.value = 0;
    status.value = 'pending';
    importLogId.value = null;
    emit('close');
};

watch(() => props.show, (newVal) => {
    if (!newVal) {
        close();
    }
});

onUnmounted(() => {
    if (progressInterval) {
        clearInterval(progressInterval);
    }
});
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.animate-modal {
    animation: modalIn 0.3s ease-out;
}

@keyframes modalIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
</style>
