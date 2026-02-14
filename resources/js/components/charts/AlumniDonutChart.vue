<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
    <h3 class="font-bold text-gray-900 dark:text-white mb-4">Rasio Terbayar vs Tunggakan</h3>
    <div v-if="loading" class="h-[320px] flex items-center justify-center">
      <div class="animate-pulse w-48 h-48 bg-gray-100 dark:bg-gray-700 rounded-full mx-auto"></div>
    </div>
    <apexchart v-else type="donut" height="320" :options="chartOptions" :series="series" />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  summary: { type: Object, required: true },
  loading: { type: Boolean, default: false },
  isDark: { type: Boolean, default: false }
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR',
    minimumFractionDigits: 0, maximumFractionDigits: 0
  }).format(val || 0);
};

const series = computed(() => [
  Number(props.summary.totalTerbayar) || 0,
  Number(props.summary.totalTunggakan) || 0
]);

const chartOptions = computed(() => ({
  chart: {
    type: 'donut',
    fontFamily: 'Inter, sans-serif',
    background: 'transparent',
  },
  labels: ['Terbayar', 'Tunggakan'],
  colors: ['#10b981', '#ef4444'],
  plotOptions: {
    pie: {
      donut: {
        size: '65%',
        labels: {
          show: true,
          name: {
            show: true,
            fontSize: '14px',
            fontWeight: 600,
            color: props.isDark ? '#d1d5db' : '#374151',
          },
          value: {
            show: true,
            fontSize: '20px',
            fontWeight: 700,
            color: props.isDark ? '#f9fafb' : '#111827',
            formatter: (val) => {
              const total = Number(props.summary.totalTerbayar || 0) + Number(props.summary.totalTunggakan || 0);
              return total > 0 ? `${Math.round((val / total) * 100)}%` : '0%';
            }
          },
          total: {
            show: true,
            label: 'Total Tagihan',
            fontSize: '12px',
            color: props.isDark ? '#9ca3af' : '#6b7280',
            formatter: () => {
              const total = Number(props.summary.totalTagihan) || 0;
              return formatCurrency(total);
            }
          }
        }
      }
    }
  },
  stroke: { width: 2, colors: [props.isDark ? '#1f2937' : '#ffffff'] },
  legend: {
    position: 'bottom',
    labels: { colors: props.isDark ? '#d1d5db' : '#374151' },
    fontWeight: 600,
    fontSize: '13px',
  },
  tooltip: {
    theme: props.isDark ? 'dark' : 'light',
    y: { formatter: (val) => formatCurrency(val) }
  },
  dataLabels: { enabled: false },
  theme: { mode: props.isDark ? 'dark' : 'light' },
}));
</script>
