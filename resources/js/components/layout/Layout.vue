<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <Header @toggle-sidebar="toggleSidebar" />

    <Sidebar :active-menu="activeMenu" :is-open="sidebarOpen" @menu-selected="handleMenuSelected"
      @close="sidebarOpen = false" />

    <main class="pt-16 pb-16 transition-all duration-300 lg:ml-64">
      <div class="px-4 py-6 sm:px-6 lg:px-8">
        <slot />
      </div>
    </main>

    <Footer />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Header from './Header.vue'
import Sidebar from './Sidebar.vue'
import Footer from './Footer.vue'
import { useDarkMode } from '../../composables/useDarkMode'
import { useAppSettings } from '../../composables/useAppSettings'

const props = defineProps({
  activeMenu: {
    type: String,
    default: 'Dashboard'
  }
})

const emit = defineEmits(['menu-selected'])

const sidebarOpen = ref(false)

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const handleMenuSelected = (item) => {
  emit('menu-selected', item)
}

const { loadAppSettings } = useAppSettings();

onMounted(() => {
  loadAppSettings();
})
</script>
