<template>
  <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
    <div v-if="error" class="text-sm text-red-500">Gagal merender diagram: {{ error }}</div>
    <div v-else ref="containerRef" class="min-w-fit flex justify-center"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useDarkMode } from '../composables/useDarkMode'

const props = defineProps({
  code: {
    type: String,
    required: true,
  },
})

const { isDark } = useDarkMode()
const containerRef = ref(null)
const error = ref('')

let uid = `mermaid-${Math.random().toString(36).slice(2)}`

const render = async () => {
  if (!containerRef.value) return
  error.value = ''

  try {
    const { default: mermaid } = await import('mermaid')

    mermaid.initialize({
      startOnLoad: false,
      theme: isDark.value ? 'dark' : 'default',
      securityLevel: 'loose',
      fontFamily: 'inherit',
      flowchart: { htmlLabels: true, curve: 'basis' },
    })

    const { svg } = await mermaid.render(uid, props.code)
    containerRef.value.innerHTML = svg
  } catch (e) {
    console.error('Mermaid render error:', e)
    error.value = e.message || 'Terjadi kesalahan'
  }
}

onMounted(render)
watch(isDark, render)
watch(() => props.code, render)
</script>
