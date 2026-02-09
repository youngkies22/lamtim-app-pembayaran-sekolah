<template>
  <div class="flex items-center justify-center gap-2">
    <button
      v-if="showEdit"
      @click="$emit('edit')"
      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 rounded-lg transition-all duration-200 hover:shadow-md cursor-pointer"
      :title="editTitle || 'Edit'"
    >
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
      </svg>
      <span v-if="showLabel">Edit</span>
    </button>
    <button
      v-if="showDelete"
      @click="$emit('delete')"
      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 rounded-lg transition-all duration-200 hover:shadow-md cursor-pointer"
      :title="deleteTitle || 'Hapus'"
    >
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
      </svg>
      <span v-if="showLabel">Hapus</span>
    </button>
  </div>
</template>

<script setup>
defineProps({
  showEdit: {
    type: Boolean,
    default: true,
  },
  showDelete: {
    type: Boolean,
    default: true,
  },
  showLabel: {
    type: Boolean,
    default: false,
  },
  editTitle: {
    type: String,
    default: 'Edit',
  },
  deleteTitle: {
    type: String,
    default: 'Hapus',
  },
});

defineEmits(['edit', 'delete']);
</script>
