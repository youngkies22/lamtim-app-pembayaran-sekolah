<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            @click.self="handleCancel"
        >
            <div class="relative mx-auto p-6 border w-full max-w-md shadow-2xl rounded-xl bg-white dark:bg-gray-800 transform transition-all">
                <!-- Icon -->
                <div class="flex justify-center mb-4">
                    <div :class="iconClasses">
                        <component :is="iconComponent" class="w-8 h-8" />
                    </div>
                </div>

                <!-- Title -->
                <h3 class="text-lg font-semibold text-center text-gray-900 dark:text-white mb-2">
                    {{ title }}
                </h3>

                <!-- Message -->
                <p class="text-sm text-center text-gray-600 dark:text-gray-400 mb-6">
                    {{ message }}
                </p>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button
                        type="button"
                        @click="handleCancel"
                        :disabled="loading"
                        class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors disabled:opacity-50"
                    >
                        {{ cancelText }}
                    </button>
                    <button
                        type="button"
                        @click="handleConfirm"
                        :disabled="loading"
                        :class="confirmButtonClasses"
                    >
                        <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ loading ? 'Memproses...' : confirmText }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { computed } from 'vue';
import {
    ExclamationTriangleIcon,
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Konfirmasi',
    },
    message: {
        type: String,
        default: 'Apakah Anda yakin ingin melanjutkan?',
    },
    type: {
        type: String,
        default: 'warning', // warning, danger, success, info
        validator: (value) => ['warning', 'danger', 'success', 'info'].includes(value),
    },
    confirmText: {
        type: String,
        default: 'Ya, Lanjutkan',
    },
    cancelText: {
        type: String,
        default: 'Batal',
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['confirm', 'cancel']);

const iconComponent = computed(() => {
    switch (props.type) {
        case 'danger':
            return TrashIcon;
        case 'success':
            return CheckCircleIcon;
        case 'info':
            return XCircleIcon;
        default:
            return ExclamationTriangleIcon;
    }
});

const iconClasses = computed(() => {
    const base = 'w-16 h-16 rounded-full flex items-center justify-center';
    switch (props.type) {
        case 'danger':
            return `${base} bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400`;
        case 'success':
            return `${base} bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400`;
        case 'info':
            return `${base} bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400`;
        default:
            return `${base} bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400`;
    }
});

const confirmButtonClasses = computed(() => {
    const base = 'flex-1 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors disabled:opacity-50 flex items-center justify-center';
    switch (props.type) {
        case 'danger':
            return `${base} bg-red-600 text-white hover:bg-red-700`;
        case 'success':
            return `${base} bg-green-600 text-white hover:bg-green-700`;
        case 'info':
            return `${base} bg-blue-600 text-white hover:bg-blue-700`;
        default:
            return `${base} bg-yellow-600 text-white hover:bg-yellow-700`;
    }
});

const handleConfirm = () => {
    emit('confirm');
};

const handleCancel = () => {
    if (!props.loading) {
        emit('cancel');
    }
};
</script>
