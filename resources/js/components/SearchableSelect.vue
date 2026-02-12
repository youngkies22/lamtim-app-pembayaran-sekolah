<template>
  <div class="relative" ref="containerRef">
    <!-- Click area/Input simulation -->
    <div
      @click="toggleDropdown"
      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus-within:ring-2 focus-within:ring-emerald-500 transition-all cursor-pointer flex items-center justify-between gap-2"
      :class="{ 'ring-2 ring-emerald-500': isOpen }"
    >
      <div class="flex-1 truncate">
        <span v-if="selectedOption" class="text-gray-900 dark:text-white font-medium">
          {{ selectedOption[labelField] }}
        </span>
        <span v-else class="text-gray-400">
          {{ placeholder }}
        </span>
      </div>
      <ChevronDownIcon 
        class="w-5 h-5 text-gray-400 transition-transform duration-200"
        :class="{ 'rotate-180': isOpen }"
      />
    </div>

    <!-- Dropdown Panel -->
    <transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div
        v-if="isOpen"
        class="absolute z-[60] mt-2 w-full bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden min-w-[200px]"
      >
        <!-- Search Input -->
        <div class="p-3 border-b border-gray-100 dark:border-gray-700">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              ref="searchInputRef"
              v-model="searchQuery"
              type="text"
              class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 dark:text-white transition-all"
              :placeholder="searchPlaceholder"
              @keydown.esc="closeDropdown"
              @keydown.down="navigateList('down')"
              @keydown.up="navigateList('up')"
              @keydown.enter="selectHighlighted"
            />
          </div>
        </div>

        <!-- Options list -->
        <div class="max-h-60 overflow-y-auto custom-scrollbar">
          <div v-if="filteredOptions.length === 0" class="p-8 text-center">
            <p class="text-gray-500 dark:text-gray-400 text-sm">Tidak ada data ditemukan</p>
          </div>
          <ul v-else class="py-1">
            <li
              v-for="(option, index) in filteredOptions"
              :key="option[valueField]"
              @click="selectOption(option)"
              @mouseenter="highlightedIndex = index"
              class="px-4 py-2.5 text-sm cursor-pointer transition-colors flex items-center justify-between"
              :class="[
                highlightedIndex === index ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50',
                modelValue === option[valueField] ? 'bg-emerald-50/50 dark:bg-emerald-900/10 font-bold text-emerald-600 dark:text-emerald-500' : ''
              ]"
            >
              <div class="flex flex-col">
                <span class="truncate">{{ option[labelField] }}</span>
                <span v-if="option.kode" class="text-[10px] opacity-70 leading-none mt-1 uppercase">{{ option.kode }}</span>
              </div>
              <CheckIcon v-if="modelValue === option[valueField]" class="w-4 h-4" />
            </li>
          </ul>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { 
  ChevronDownIcon, 
  MagnifyingGlassIcon,
  CheckIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  modelValue: [String, Number],
  options: {
    type: Array,
    default: () => []
  },
  placeholder: {
    type: String,
    default: 'Pilih opsi...'
  },
  searchPlaceholder: {
    type: String,
    default: 'Cari...'
  },
  labelField: {
    type: String,
    default: 'nama'
  },
  valueField: {
    type: String,
    default: 'id'
  }
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const searchQuery = ref('');
const containerRef = ref(null);
const searchInputRef = ref(null);
const highlightedIndex = ref(0);

const selectedOption = computed(() => {
  return props.options.find(opt => opt[props.valueField] === props.modelValue);
});

const filteredOptions = computed(() => {
  if (!searchQuery.value) return props.options;
  const q = searchQuery.value.toLowerCase();
  return props.options.filter(opt => {
    const label = String(opt[props.labelField] || '').toLowerCase();
    const subLabel = String(opt.kode || '').toLowerCase();
    return label.includes(q) || subLabel.includes(q);
  });
});

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    searchQuery.value = '';
    highlightedIndex.value = 0;
    setTimeout(() => {
      searchInputRef.value?.focus();
    }, 100);
  }
};

const closeDropdown = () => {
  isOpen.value = false;
};

const selectOption = (option) => {
  emit('update:modelValue', option[props.valueField]);
  emit('change', option);
  closeDropdown();
};

const navigateList = (direction) => {
  if (filteredOptions.value.length === 0) return;
  if (direction === 'down') {
    highlightedIndex.value = (highlightedIndex.value + 1) % filteredOptions.value.length;
  } else {
    highlightedIndex.value = (highlightedIndex.value - 1 + filteredOptions.value.length) % filteredOptions.value.length;
  }
};

const selectHighlighted = () => {
  if (filteredOptions.value[highlightedIndex.value]) {
    selectOption(filteredOptions.value[highlightedIndex.value]);
  }
};

// Handle clicks outside
const handleClickOutside = (event) => {
  if (containerRef.value && !containerRef.value.contains(event.target)) {
    closeDropdown();
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Reset highlight on search
watch(searchQuery, () => {
  highlightedIndex.value = 0;
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
}
</style>
