<template>
    <div class="space-y-4">
        <!-- Progress Bar -->
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress</span>
                <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">{{ progress }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                <div
                    :class="[
                        'h-full rounded-full transition-all duration-500 ease-out',
                        status === 'completed' ? 'bg-green-500' : status === 'failed' ? 'bg-red-500' : 'bg-indigo-500'
                    ]"
                    :style="{ width: `${progress}%` }"
                ></div>
            </div>
        </div>

        <!-- Status Badge -->
        <div class="flex items-center justify-center">
            <span :class="[
                'inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold',
                getStatusClass()
            ]">
                <span :class="['w-2 h-2 rounded-full', getStatusDotClass()]"></span>
                {{ getStatusLabel() }}
            </span>
        </div>

        <!-- Statistics -->
        <div v-if="totalRows > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-800 dark:text-white">{{ totalRows }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Total Data</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ processedRows }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Diproses</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ successRows }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Berhasil</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ failedRows }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Gagal</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    progress: {
        type: Number,
        default: 0,
    },
    status: {
        type: String,
        default: 'pending',
    },
    totalRows: {
        type: Number,
        default: 0,
    },
    processedRows: {
        type: Number,
        default: 0,
    },
    successRows: {
        type: Number,
        default: 0,
    },
    failedRows: {
        type: Number,
        default: 0,
    },
});

const getStatusLabel = () => {
    const labels = {
        pending: 'Menunggu',
        processing: 'Memproses',
        completed: 'Selesai',
        failed: 'Gagal',
    };
    return labels[props.status] || 'Tidak Diketahui';
};

const getStatusClass = () => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        failed: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    };
    return classes[props.status] || classes.pending;
};

const getStatusDotClass = () => {
    const classes = {
        pending: 'bg-yellow-500 animate-pulse',
        processing: 'bg-blue-500 animate-pulse',
        completed: 'bg-green-500',
        failed: 'bg-red-500',
    };
    return classes[props.status] || classes.pending;
};
</script>
