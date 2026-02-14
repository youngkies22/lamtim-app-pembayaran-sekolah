<template>
  <div class="relative" ref="container">
    <!-- Search / Selected View -->
    <div
      class="relative w-full cursor-pointer rounded-xl border-2 border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-700 transition-all focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500"
      @click="toggleDropdown">
      <div class="flex items-center px-4 py-3">
        <div class="flex-1 truncate">
          <span v-if="selectedOption" class="text-gray-900 dark:text-white">
            {{ label(selectedOption) }}
          </span>
          <span v-else class="text-gray-400 dark:text-gray-500">
            {{ placeholder }}
          </span>
        </div>
        <div class="ml-2 flex flex-shrink-0 items-center gap-2">
          <XMarkIcon v-if="modelValue" class="h-5 w-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
            @click.stop="clearSelection" />
          <ChevronDownIcon class="h-5 w-5 text-gray-400 transition-transform duration-200"
            :class="{ 'rotate-180': isOpen }" />
        </div>
      </div>
    </div>

    <!-- Dropdown Menu -->
    <div v-if="isOpen"
      class="absolute z-50 mt-2 w-full overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black ring-opacity-5 dark:bg-gray-800">
      <!-- Search Input in Dropdown -->
      <div class="border-b border-gray-100 dark:border-gray-700 p-2">
        <div class="relative">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
          <input ref="searchInput" v-model="searchQuery" type="text"
            class="w-full rounded-lg border-none bg-gray-50 py-2 pl-9 pr-4 text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
            :placeholder="searchPlaceholder" @click.stop @keydown.down.prevent="moveHighlight(1)"
            @keydown.up.prevent="moveHighlight(-1)" @keydown.enter.prevent="selectHighlighted" />
        </div>
      </div>

      <!-- Options List -->
      <ul class="max-h-60 overflow-y-auto py-1 text-sm">
        <li v-for="(option, index) in filteredOptions" :key="value(option)"
          class="flex cursor-pointer items-center justify-between px-4 py-2.5 transition-colors" :class="[
            highlightedIndex === index
              ? 'bg-indigo-50 text-indigo-900 dark:bg-indigo-900/30 dark:text-indigo-200'
              : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50',
            modelValue === value(option) ? 'font-bold text-indigo-600 dark:text-indigo-400' : ''
          ]" @click="selectOption(option)" @mouseover="highlightedIndex = index">
          <span>{{ label(option) }}</span>
          <CheckIcon v-if="modelValue === value(option)" class="h-4 w-4" />
        </li>
        <li v-if="filteredOptions.length === 0" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
          Tidak ada data ditemukan
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { ChevronDownIcon, MagnifyingGlassIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  options: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Pilih opsi' },
  searchPlaceholder: { type: String, default: 'Cari...' },
  labelKey: { type: String, default: 'label' },
  valueKey: { type: String, default: 'value' },
  filterFn: { type: Function, default: null }
});

const emit = defineEmits(['update:modelValue', 'change']);

const container = ref(null);
const searchInput = ref(null);
const isOpen = ref(false);
const searchQuery = ref('');
const highlightedIndex = ref(-1);

const label = (option) => (typeof option === 'object' ? option[props.labelKey] : option);
const value = (option) => (typeof option === 'object' ? option[props.valueKey] : option);

const filteredOptions = computed(() => {
  if (!searchQuery.value) return props.options;

  if (props.filterFn) {
    return props.options.filter(option => props.filterFn(option, searchQuery.value));
  }

  const query = searchQuery.value.toLowerCase();
  return props.options.filter(option =>
    label(option).toLowerCase().includes(query)
  );
});

const selectedOption = computed(() => {
  return props.options.find(opt => value(opt) === props.modelValue);
});

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    searchQuery.value = '';
    highlightedIndex.value = filteredOptions.value.findIndex(opt => value(opt) === props.modelValue);
    nextTick(() => {
      searchInput.value?.focus();
    });
  }
};

const selectOption = (option) => {
  const val = value(option);
  emit('update:modelValue', val);
  emit('change', option);
  isOpen.value = false;
};

const clearSelection = () => {
  emit('update:modelValue', '');
  emit('change', null);
  isOpen.value = false;
};

const moveHighlight = (dir) => {
  const len = filteredOptions.value.length;
  if (len === 0) return;
  highlightedIndex.value = (highlightedIndex.value + dir + len) % len;
};

const selectHighlighted = () => {
  if (highlightedIndex.value >= 0 && highlightedIndex.value < filteredOptions.value.length) {
    selectOption(filteredOptions.value[highlightedIndex.value]);
  }
};

const handleClickOutside = (event) => {
  if (container.value && !container.value.contains(event.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside);
});

// Reset highlight when filtering
watch(searchQuery, () => {
  highlightedIndex.value = filteredOptions.value.length > 0 ? 0 : -1;
});
</script>
