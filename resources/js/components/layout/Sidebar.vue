<template>
  <!-- Drawer toggle button (hidden, used programmatically) -->
  <button
    ref="drawerToggleBtn"
    type="button"
    :data-drawer-target="drawerId"
    :data-drawer-toggle="drawerId"
    :aria-controls="drawerId"
    class="hidden"
  >
    Toggle drawer
  </button>

  <!-- Drawer component -->
  <div
    :id="drawerId"
    :class="[
      'fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto transition-transform bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 w-64',
      'lg:translate-x-0',
      '-translate-x-full'
    ]"
    tabindex="-1"
    :aria-labelledby="drawerId + '-label'"
    data-drawer-backdrop="true"
  >
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-5 flex items-center justify-between">
      <h5 :id="drawerId + '-label'" class="inline-flex items-center text-lg font-semibold text-gray-900 dark:text-white">
        <div v-if="appSettings.logo_aplikasi" class="w-8 h-8 rounded-lg overflow-hidden flex items-center justify-center mr-2 flex-shrink-0">
          <img :src="getLogoUrl(appSettings.logo_aplikasi)" :alt="appSettings.nama_aplikasi" class="w-full h-full object-contain" />
        </div>
        <div v-else class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center mr-2">
          <span class="text-white font-bold text-sm">SPP</span>
        </div>
        <span>{{ appSettings.nama_aplikasi || 'Menu' }}</span>
      </h5>
      <button
        type="button"
        :data-drawer-hide="drawerId"
        :aria-controls="drawerId"
        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 end-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
      >
        <XMarkIcon class="w-5 h-5" />
        <span class="sr-only">Close menu</span>
      </button>
    </div>

    <nav class="space-y-2">
      <template v-for="item in menuItems" :key="item.name">
        <div v-if="!item.children">
          <div
            @click="selectMenu(item)"
            :class="[
              'flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-colors duration-200',
              activeMenu === item.name
                ? 'bg-blue-600 text-white dark:bg-blue-700'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
          >
            <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
            <span class="font-medium">{{ item.name }}</span>
          </div>
        </div>
        <div v-else>
          <!-- Dropdown for Master Data -->
          <button
            type="button"
            @click="toggleDropdown(item.name)"
            :class="[
              'flex items-center justify-between w-full px-4 py-3 rounded-lg transition-colors duration-200',
              (item.name === 'Master Data' && isMasterDataActive) || (item.name === 'Payments' && isPaymentsActive)
                ? 'bg-blue-600 text-white dark:bg-blue-700'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
            :aria-expanded="isDropdownOpen(item.name)"
          >
            <div class="flex items-center gap-3">
              <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
              <span class="font-medium">{{ item.name }}</span>
            </div>
            <ChevronDownIcon :class="['w-4 h-4 transition-transform', { 'rotate-180': isDropdownOpen(item.name) }]" />
          </button>
          <div v-show="isDropdownOpen(item.name)" class="ml-6 mt-1 space-y-1">
            <div
              v-for="child in item.children"
              :key="child.name"
              @click="selectMenu(child)"
              :class="[
                'flex items-center gap-3 px-4 py-2 rounded-lg cursor-pointer transition-colors duration-200',
                activeMenu === child.name || 
                (activeMenu === 'Master Data' && child.route === currentRoute) ||
                (activeMenu === 'Payments' && child.route === currentRoute)
                  ? 'bg-blue-500 text-white dark:bg-blue-600'
                  : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
              ]"
            >
              <component :is="child.icon" class="w-4 h-4 flex-shrink-0" />
              <span class="text-sm font-medium">{{ child.name }}</span>
              <svg v-if="child.target === '_blank'" class="w-3 h-3 ml-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
              </svg>
            </div>
          </div>
        </div>
      </template>
    </nav>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { initDrawers } from 'flowbite'
import { useRouter, useRoute } from 'vue-router'
import { useMasterDataCache } from '../../composables/useMasterDataCache'
import { useAppSettings } from '../../composables/useAppSettings'
import { useRoleAccess } from '../../composables/useRoleAccess'
import {
  HomeIcon,
  UserGroupIcon,
  CreditCardIcon,
  ChartBarIcon,
  XMarkIcon,
  BuildingLibraryIcon,
  ChevronDownIcon,
  AcademicCapIcon,
  BuildingOfficeIcon,
  UsersIcon,
  TagIcon,
  ArrowsRightLeftIcon,
  RectangleStackIcon,
  CalendarIcon,
  DocumentTextIcon,
  ReceiptRefundIcon,
  BanknotesIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  activeMenu: {
    type: String,
    default: 'Dashboard'
  }
})

const emit = defineEmits(['menu-selected'])

const drawerId = 'drawer-navigation'
const drawerToggleBtn = ref(null)
const openDropdown = ref(null)

const { getCached, loadAll } = useMasterDataCache();
const { appSettings, getLogoUrl } = useAppSettings();
const { hasMenuAccess } = useRoleAccess();

// Get cached config immediately (synchronous)
const cached = getCached();
const labelJurusan = ref(cached.labelJurusan || 'Jurusan');

const router = useRouter()
const route = useRoute()

// Load config (will use cache if already loaded)
const loadConfig = async () => {
  try {
    const result = await loadAll();
    labelJurusan.value = result.labelJurusan || 'Jurusan';
  } catch (err) {
    console.error('Error loading config:', err);
    // Keep cached value or default
    if (!labelJurusan.value) {
      labelJurusan.value = 'Jurusan';
    }
  }
};

const menuItems = computed(() => {
  const allMenus = [
    { name: 'Dashboard', icon: HomeIcon, route: '/dashboard' },
    {
      name: 'Master Data',
      icon: BuildingLibraryIcon,
      route: '/master-data',
      children: [
        { name: 'Sekolah', icon: BuildingLibraryIcon, route: '/master-data/sekolah' },
        { name: labelJurusan.value, icon: AcademicCapIcon, route: '/master-data/jurusan' },
        { name: 'Kelas', icon: BuildingOfficeIcon, route: '/master-data/kelas' },
        { name: 'Rombel', icon: UsersIcon, route: '/master-data/rombel' },
        { name: 'Tahun Ajaran', icon: CalendarIcon, route: '/master-data/tahun-ajaran' },
        { name: 'Semester', icon: AcademicCapIcon, route: '/master-data/semester' },
        { name: 'Jenis Pembayaran', icon: CreditCardIcon, route: '/master-data/jenis-pembayaran' },
        { name: 'Kategori Pembayaran', icon: TagIcon, route: '/master-data/kategori-pembayaran' },
        { name: 'Tipe Pembayaran', icon: RectangleStackIcon, route: '/master-data/tipe-pembayaran' },
      ]
    },
  { name: 'Students', icon: UserGroupIcon, route: '/siswa' },
  {
    name: 'Payments',
    icon: CreditCardIcon,
    route: '/master-pembayaran',
    children: [
      { name: 'Master Pembayaran', icon: CreditCardIcon, route: '/master-pembayaran' },
      { name: 'Tagihan', icon: DocumentTextIcon, route: '/tagihan' },
      { name: 'Invoice', icon: ReceiptRefundIcon, route: '/invoice' },
      { name: 'Pembayaran', icon: BanknotesIcon, route: '/pembayaran' },
      { name: 'Billing', icon: DocumentTextIcon, route: '/billing', target: '_blank' },
    ]
  },
  { name: 'Reports', icon: ChartBarIcon, route: '/reports' },
  { name: 'Users', icon: UsersIcon, route: '/users' }
  ];

  // Filter menus based on role
  return allMenus.filter(menu => {
    if (menu.children) {
      // Filter children menus
      menu.children = menu.children.filter(child => hasMenuAccess(menu.name));
      // Show parent if it has accessible children
      return menu.children.length > 0;
    }
    return hasMenuAccess(menu.name);
  });
})

const currentRoute = computed(() => route.path)

const isMasterDataActive = computed(() => {
  return props.activeMenu === 'Master Data' ||
         route.path.startsWith('/master-data')
})

const isPaymentsActive = computed(() => {
  return props.activeMenu === 'Payments' ||
         route.path.startsWith('/master-pembayaran') ||
         route.path.startsWith('/tagihan') ||
         route.path.startsWith('/invoice') ||
         route.path.startsWith('/pembayaran')
})

const toggleDropdown = (menuName) => {
  openDropdown.value = openDropdown.value === menuName ? null : menuName
}

const isDropdownOpen = (menuName) => {
  if (menuName === 'Master Data') {
    return openDropdown.value === menuName || isMasterDataActive.value
  }
  if (menuName === 'Payments') {
    return openDropdown.value === menuName || isPaymentsActive.value
  }
  return openDropdown.value === menuName
}

const selectMenu = (item) => {
  if (item.route) {
    // Check if menu should open in new tab
    if (item.target === '_blank') {
      window.open(item.route, '_blank')
    } else {
      router.push(item.route)
    }
  }
  emit('menu-selected', item)
  // Close drawer on mobile after selection
  if (window.innerWidth < 1024) {
    const drawer = document.getElementById(drawerId)
    if (drawer) {
      const hideBtn = drawer.querySelector(`[data-drawer-hide="${drawerId}"]`)
      if (hideBtn) {
        hideBtn.click()
      }
    }
  }
}

// Expose method to toggle drawer
const toggleDrawer = () => {
  if (drawerToggleBtn.value) {
    drawerToggleBtn.value.click()
  }
}

// Initialize Flowbite drawers
onMounted(async () => {
  await loadConfig()
  initDrawers()
  // Auto expand dropdowns if on their routes
  if (isMasterDataActive.value) {
    openDropdown.value = 'Master Data'
  }
  if (isPaymentsActive.value) {
    openDropdown.value = 'Payments'
  }
})

defineExpose({
  toggleDrawer
})
</script>
