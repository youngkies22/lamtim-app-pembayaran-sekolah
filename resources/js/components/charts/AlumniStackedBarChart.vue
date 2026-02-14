<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
    <h3 class="font-bold text-gray-900 dark:text-white mb-4">Tagihan vs Terbayar per Angkatan</h3>
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

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR',
    minimumFractionDigits: 0, maximumFractionDigits: 0
  }).format(val || 0);
};

const sortedData = computed(() =>
  [...props.data].sort((a, b) => (a.tahunAngkatan || 0) - (b.tahunAngkatan || 0))
);

const series = computed(() => [
  {
    name: 'Terbayar',
    data: sortedData.value.map(item => Number(item.total_terbayar) || 0)
  },
  {
    name: 'Tunggakan',
    data: sortedData.value.map(item => Number(item.total_tunggakan) || 0)
  }
]);

const chartOptions = computed(() => ({
  chart: {
    type: 'bar',
    stacked: true,
    toolbar: { show: false },
    fontFamily: 'Inter, sans-serif',
    background: 'transparent',
  },
  plotOptions: {
    bar: { horizontal: false, columnWidth: '55%', borderRadius: 6, borderRadiusApplication: 'end' }
  },
  colors: ['#10b981', '#ef4444'],
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
  legend: {
    position: 'top',
    horizontalAlign: 'left',
    labels: { colors: props.isDark ? '#d1d5db' : '#374151' },
    fontWeight: 600,
    fontSize: '13px',
  },
  dataLabels: { enabled: false },
  theme: { mode: props.isDark ? 'dark' : 'light' },
}));
</script>
