<template>
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
    <h3 class="font-bold text-gray-900 dark:text-white mb-4 text-sm">Tren Tunggakan per Bulan</h3>
    <div v-if="loading" class="h-72 bg-gray-100 dark:bg-gray-700 rounded-xl animate-pulse"></div>
    <apexchart v-else type="area" height="280" :options="chartOptions" :series="series" />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  data: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  isDark: { type: Boolean, default: false },
});

const series = computed(() => [{
  name: 'Tunggakan',
  data: props.data.map(d => Number(d.total_tunggakan) || 0),
}]);

const chartOptions = computed(() => ({
  chart: {
    type: 'area',
    toolbar: { show: false },
    background: 'transparent',
    fontFamily: 'Inter, system-ui, sans-serif',
    zoom: { enabled: false },
  },
  colors: ['#f43f5e'],
  fill: {
    type: 'gradient',
    gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 100] },
  },
  stroke: { curve: 'smooth', width: 3 },
  xaxis: {
    categories: props.data.map(d => d.namaBulan || `Bulan ${d.bulanKe}`),
    labels: { style: { colors: props.isDark ? '#9ca3af' : '#6b7280', fontSize: '11px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: {
      style: { colors: props.isDark ? '#9ca3af' : '#6b7280', fontSize: '11px' },
      formatter: v => 'Rp ' + (v / 1000000).toFixed(0) + ' jt',
    },
  },
  grid: { borderColor: props.isDark ? '#374151' : '#f3f4f6', strokeDashArray: 4 },
  markers: { size: 4, colors: ['#f43f5e'], strokeWidth: 0, hover: { size: 6 } },
  tooltip: {
    theme: props.isDark ? 'dark' : 'light',
    y: { formatter: v => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v) },
  },
  dataLabels: { enabled: false },
}));
</script>
