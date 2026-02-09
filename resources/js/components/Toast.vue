<template>
    <Teleport to="body">
        <div class="fixed top-4 right-4 z-[9999] space-y-3 pointer-events-none max-w-md w-full sm:w-auto">
            <TransitionGroup name="toast" tag="div">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[
                        'flex items-start gap-4 p-4 rounded-xl shadow-2xl pointer-events-auto backdrop-blur-sm',
                        'border-l-4 transform transition-all duration-300 hover:scale-[1.02]',
                        toastClasses[toast.type]
                    ]"
                >
                    <div class="flex-shrink-0 mt-0.5">
                        <div :class="[
                            'p-2 rounded-lg',
                            iconBgClasses[toast.type]
                        ]">
                            <component :is="toastIcons[toast.type]" :class="[
                                'w-5 h-5',
                                iconColorClasses[toast.type]
                            ]" />
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold leading-relaxed break-words">
                            {{ toast.message }}
                        </p>
                        <div v-if="toast.duration > 0" class="mt-2 h-1 bg-black/10 dark:bg-white/10 rounded-full overflow-hidden">
                            <div
                                :class="['h-full transition-all duration-linear', progressBarClasses[toast.type]]"
                                :style="{ width: `${toast.progress}%` }"
                            ></div>
                        </div>
                    </div>
                    <button
                        @click="removeToast(toast.id)"
                        class="flex-shrink-0 p-1.5 rounded-lg hover:bg-black/10 dark:hover:bg-white/10 transition-colors group"
                        aria-label="Close"
                    >
                        <XMarkIcon class="w-4 h-4 text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200 transition-colors" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, markRaw, onMounted, onUnmounted } from 'vue';
import {
    CheckCircleIcon,
    XCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/solid';

const toasts = ref([]);
let toastId = 0;
const progressIntervals = new Map();

const toastClasses = {
    success: 'bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/40 dark:to-emerald-900/40 text-green-900 dark:text-green-100 border-green-500 shadow-green-500/20',
    error: 'bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/40 dark:to-rose-900/40 text-red-900 dark:text-red-100 border-red-500 shadow-red-500/20',
    warning: 'bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900/40 dark:to-amber-900/40 text-yellow-900 dark:text-yellow-100 border-yellow-500 shadow-yellow-500/20',
    info: 'bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/40 dark:to-indigo-900/40 text-blue-900 dark:text-blue-100 border-blue-500 shadow-blue-500/20',
};

const iconBgClasses = {
    success: 'bg-green-100 dark:bg-green-900/50',
    error: 'bg-red-100 dark:bg-red-900/50',
    warning: 'bg-yellow-100 dark:bg-yellow-900/50',
    info: 'bg-blue-100 dark:bg-blue-900/50',
};

const iconColorClasses = {
    success: 'text-green-600 dark:text-green-400',
    error: 'text-red-600 dark:text-red-400',
    warning: 'text-yellow-600 dark:text-yellow-400',
    info: 'text-blue-600 dark:text-blue-400',
};

const progressBarClasses = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    warning: 'bg-yellow-500',
    info: 'bg-blue-500',
};

const toastIcons = {
    success: markRaw(CheckCircleIcon),
    error: markRaw(XCircleIcon),
    warning: markRaw(ExclamationTriangleIcon),
    info: markRaw(InformationCircleIcon),
};

const addToast = (message, type = 'info', duration = 5000) => {
    const id = ++toastId;
    const toast = {
        id,
        message,
        type,
        duration,
        progress: 100,
    };
    
    toasts.value.push(toast);

    if (duration > 0) {
        // Progress bar animation
        const startTime = Date.now();
        const interval = setInterval(() => {
            const elapsed = Date.now() - startTime;
            const remaining = Math.max(0, duration - elapsed);
            toast.progress = (remaining / duration) * 100;

            if (remaining <= 0) {
                clearInterval(interval);
                progressIntervals.delete(id);
                removeToast(id);
            }
        }, 50);
        
        progressIntervals.set(id, interval);
    }

    return id;
};

const removeToast = (id) => {
    const index = toasts.value.findIndex((t) => t.id === id);
    if (index > -1) {
        // Clear progress interval if exists
        const interval = progressIntervals.get(id);
        if (interval) {
            clearInterval(interval);
            progressIntervals.delete(id);
        }
        toasts.value.splice(index, 1);
    }
};

const success = (message, duration) => addToast(message, 'success', duration);
const error = (message, duration) => addToast(message, 'error', duration);
const warning = (message, duration) => addToast(message, 'warning', duration);
const info = (message, duration) => addToast(message, 'info', duration);

// Cleanup on unmount
onUnmounted(() => {
    progressIntervals.forEach(interval => clearInterval(interval));
    progressIntervals.clear();
});

defineExpose({
    addToast,
    removeToast,
    success,
    error,
    warning,
    info,
});
</script>

<style scoped>
.toast-enter-active {
    animation: toast-in 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-leave-active {
    animation: toast-out 0.3s cubic-bezier(0.7, 0, 0.84, 0);
}

@keyframes toast-in {
    from {
        opacity: 0;
        transform: translateX(100%) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateX(0) scale(1);
    }
}

@keyframes toast-out {
    from {
        opacity: 1;
        transform: translateX(0) scale(1);
    }
    to {
        opacity: 0;
        transform: translateX(100%) scale(0.9);
    }
}
</style>
