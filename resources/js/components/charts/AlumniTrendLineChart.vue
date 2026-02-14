<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
    <h3 class="font-bold text-gray-900 dark:text-white mb-4">Tren Tunggakan per Angkatan</h3>
    <div v-if="loading" class="h-[320px] flex items-center justify-center">
      <div class="animate-pulse w-full h-full bg-gray-100 dark:bg-gray-700 rounded-xl"></div>
    </div>
    <apexchart v-else type="area" height="320" :options="chartOptions" :series="series" />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  data: { type: Array, required: true },
  loading: { type: Boolean, default: false },
  isDark: { type: Boolean, default: false }
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR',
    minimumFractionDigits: 0, maximumFractionDigits: 0
  }).format(val || 0);
};

const sortedData = computed(() =>
  [...props.data].sort((a, b) => (a.tahunAngkatan || 0) - (b.tahunAngkatan || 0))
);

const series = computed(() => [{
  name: 'Total Tunggakan',
  data: sortedData.value.map(item => Number(item.total_tunggakan) || 0)
}]);

const chartOptions = computed(() => ({
  chart: {
    type: 'area',
    toolbar: { show: false },
    fontFamily: 'Inter, sans-serif',
    background: 'transparent',
    zoom: { enabled: false },
  },
  colors: ['#ef4444'],
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.45,
      opacityTo: 0.05,
      stops: [0, 100]
    }
  },
  stroke: { curve: 'smooth', width: 3 },
  markers: {
    size: 5,
    colors: ['#ef4444'],
    strokeColors: props.isDark ? '#1f2937' : '#ffffff',
    strokeWidth: 2,
    hover: { size: 7 }
  },
  xaxis: {
    categories: sortedData.value.map(item => item.tahunAngkatan || 'N/A'),
    labels: { style: { colors: props.isDark ? '#9ca3af' : '#6b7280', fontSize: '12px' } }
  },
  yaxis: {
    labels: {
      style: { colors: props.isDark ? '#9ca3af' : '#6b7280', fontSize: '12px' },
      formatter: (val) => {
        if (val >= 1_000_000_000) return `${(val / 1_000_000_000).toFixed(1)}M`;
        if (val >= 1_000_000) return `${(val / 1_000_000).toFixed(0)}Jt`;
        if (val >= 1_000) return `${(val / 1_000).toFixed(0)}Rb`;
        return val;
      }
    }
  },
  grid: {
    borderColor: props.isDark ? '#374151' : '#f3f4f6',
    strokeDashArray: 4,
  },
  tooltip: {
    theme: props.isDark ? 'dark' : 'light',
    y: { formatter: (val) => formatCurrency(val) }
  },
  dataLabels: { enabled: false },
  theme: { mode: props.isDark ? 'dark' : 'light' },
}));
</script>
