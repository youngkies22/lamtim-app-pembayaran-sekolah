<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
    <h3 class="font-bold text-gray-900 dark:text-white mb-4">Rasio Kepatuhan Bayar per Angkatan</h3>
    <div v-if="loading" class="h-[320px] flex items-center justify-center">
      <div class="animate-pulse w-full h-full bg-gray-100 dark:bg-gray-700 rounded-xl"></div>
    </div>
    <apexchart v-else type="bar" height="320" :options="chartOptions" :series="series" />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  data: { type: Array, required: true },
  loading: { type: Boolean, default: false },
  isDark: { type: Boolean, default: false }
});

const sortedData = computed(() =>
  [...props.data]
    .map(item => ({
      ...item,
      rasio: Number(item.total_tagihan) > 0
        ? Math.round((Number(item.total_terbayar) / Number(item.total_tagihan)) * 100)
        : 0
    }))
    .sort((a, b) => a.rasio - b.rasio)
);

const getBarColor = (rasio) => {
  if (rasio >= 80) return '#10b981';
  if (rasio >= 60) return '#f59e0b';
  if (rasio >= 40) return '#f97316';
  return '#ef4444';
};

const series = computed(() => [{
  name: 'Rasio Bayar',
  data: sortedData.value.map(item => item.rasio)
}]);

const chartOptions = computed(() => ({
  chart: {
    type: 'bar',
    toolbar: { show: false },
    fontFamily: 'Inter, sans-serif',
    background: 'transparent',
  },
  plotOptions: {
    bar: {
      horizontal: true,
      barHeight: '60%',
      borderRadius: 6,
      borderRadiusApplication: 'end',
      distributed: true,
    }
  },
  colors: sortedData.value.map(item => getBarColor(item.rasio)),
  xaxis: {
    max: 100,
    labels: {
      style: { colors: props.isDark ? '#9ca3af' : '#6b7280', fontSize: '12px' },
      formatter: (val) => `${val}%`
    },
    categories: sortedData.value.map(item => `Angkatan ${item.tahunAngkatan || 'N/A'}`)
  },
  yaxis: {
    labels: {
      style: { colors: props.isDark ? '#9ca3af' : '#6b7280', fontSize: '12px', fontWeight: 600 }
    }
  },
  grid: {
    borderColor: props.isDark ? '#374151' : '#f3f4f6',
    strokeDashArray: 4,
    xaxis: { lines: { show: true } },
    yaxis: { lines: { show: false } },
  },
  tooltip: {
    theme: props.isDark ? 'dark' : 'light',
    y: { formatter: (val) => `${val}%` }
  },
  legend: { show: false },
  dataLabels: {
    enabled: true,
    formatter: (val) => `${val}%`,
    style: { fontSize: '12px', fontWeight: 700, colors: ['#ffffff'] },
    offsetX: 0,
  },
  theme: { mode: props.isDark ? 'dark' : 'light' },
}));
</script>
