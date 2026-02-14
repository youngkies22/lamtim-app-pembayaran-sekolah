<template>
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
    <h3 class="font-bold text-gray-900 dark:text-white mb-4 text-sm">Rasio Terbayar vs Tunggakan</h3>
    <div v-if="loading" class="h-72 bg-gray-100 dark:bg-gray-700 rounded-xl animate-pulse"></div>
    <div v-else>
      <apexchart type="donut" height="240" :options="chartOptions" :series="series" />
      <div class="mt-3 grid grid-cols-2 gap-2 text-center text-xs">
        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-2">
          <p class="text-emerald-600 font-bold">{{ formatCurrency(props.summary.totalTerbayar) }}</p>
          <p class="text-gray-500 dark:text-gray-400">Terbayar</p>
        </div>
        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-2">
          <p class="text-red-600 font-bold">{{ formatCurrency(props.summary.totalTunggakan) }}</p>
          <p class="text-gray-500 dark:text-gray-400">Tunggakan</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  summary: { type: Object, default: () => ({ totalTerbayar: 0, totalTunggakan: 0 }) },
  loading: { type: Boolean, default: false },
  isDark: { type: Boolean, default: false },
});

const formatCurrency = (amount) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount || 0);

const series = computed(() => [
  Number(props.summary.totalTerbayar) || 0,
  Number(props.summary.totalTunggakan) || 0,
]);

const chartOptions = computed(() => ({
  chart: { type: 'donut', background: 'transparent', fontFamily: 'Inter, system-ui, sans-serif' },
  labels: ['Terbayar', 'Tunggakan'],
  colors: ['#10b981', '#ef4444'],
  legend: { show: false },
  plotOptions: {
    pie: {
      donut: {
        size: '70%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total',
            color: props.isDark ? '#d1d5db' : '#374151',
            formatter: () => formatCurrency(Number(props.summary.totalTerbayar || 0) + Number(props.summary.totalTunggakan || 0)),
          },
          value: {
            color: props.isDark ? '#f9fafb' : '#111827',
            formatter: v => formatCurrency(v),
          },
        },
      },
    },
  },
  stroke: { width: 2, colors: [props.isDark ? '#1f2937' : '#ffffff'] },
  tooltip: {
    theme: props.isDark ? 'dark' : 'light',
    y: { formatter: v => formatCurrency(v) },
  },
  dataLabels: { enabled: false },
}));
</script>
