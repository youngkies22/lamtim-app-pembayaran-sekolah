<template>
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
    <h3 class="font-bold text-gray-900 dark:text-white mb-4 text-sm">Rasio Pembayaran per Bulan</h3>
    <div v-if="loading" class="h-72 bg-gray-100 dark:bg-gray-700 rounded-xl animate-pulse"></div>
    <apexchart v-else type="bar" height="280" :options="chartOptions" :series="series" />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  data: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  isDark: { type: Boolean, default: false },
});

const sortedData = computed(() => {
  return [...props.data].sort((a, b) => {
    const ratioA = Number(a.total_tagihan) > 0 ? Number(a.total_terbayar) / Number(a.total_tagihan) : 0;
    const ratioB = Number(b.total_tagihan) > 0 ? Number(b.total_terbayar) / Number(b.total_tagihan) : 0;
    return ratioA - ratioB;
  });
});

const series = computed(() => [{
  name: 'Rasio Terbayar',
  data: sortedData.value.map(d => {
    const tagihan = Number(d.total_tagihan) || 1;
    return Math.round((Number(d.total_terbayar) / tagihan) * 100);
  }),
}]);

const chartOptions = computed(() => ({
  chart: {
    type: 'bar',
    toolbar: { show: false },
    background: 'transparent',
    fontFamily: 'Inter, system-ui, sans-serif',
  },
  plotOptions: {
    bar: {
      horizontal: true,
      barHeight: '60%',
      borderRadius: 4,
      distributed: true,
      colors: {
        ranges: [
          { from: 0, to: 40, color: '#ef4444' },
          { from: 41, to: 70, color: '#f59e0b' },
          { from: 71, to: 100, color: '#10b981' },
        ],
      },
    },
  },
  xaxis: {
    max: 100,
    labels: {
      style: { colors: props.isDark ? '#9ca3af' : '#6b7280', fontSize: '11px' },
      formatter: v => v + '%',
    },
  },
  yaxis: {
    labels: {
      style: { colors: props.isDark ? '#9ca3af' : '#6b7280', fontSize: '11px' },
    },
    categories: sortedData.value.map(d => d.namaBulan || `Bulan ${d.bulanKe}`),
  },
  grid: { borderColor: props.isDark ? '#374151' : '#f3f4f6', strokeDashArray: 4 },
  legend: { show: false },
  tooltip: {
    theme: props.isDark ? 'dark' : 'light',
    y: { formatter: v => v + '%' },
  },
  dataLabels: {
    enabled: true,
    formatter: v => v + '%',
    style: { fontSize: '11px', fontWeight: 600, colors: ['#fff'] },
  },
}));
</script>
