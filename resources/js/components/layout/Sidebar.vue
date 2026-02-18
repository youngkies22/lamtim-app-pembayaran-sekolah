<template>
  <!-- Backdrop (mobile only) -->
  <div v-if="isOpen && !isLargeScreen" class="fixed inset-0 z-30 bg-gray-900/50" @click="closeSidebar"></div>

  <!-- Sidebar -->
  <aside :class="[
    'fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto transition-transform bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 w-64',
    isLargeScreen || isOpen ? 'translate-x-0' : '-translate-x-full'
  ]" role="navigation" aria-label="Main navigation">
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-5 flex items-center justify-between">
      <h5 class="inline-flex items-center text-lg font-semibold text-gray-900 dark:text-white">
        <div v-if="appSettings.logo_aplikasi"
          class="w-8 h-8 rounded-lg overflow-hidden flex items-center justify-center mr-2 flex-shrink-0">
          <img :src="getLogoUrl(appSettings.logo_aplikasi)" :alt="appSettings.nama_aplikasi"
            class="w-full h-full object-contain" />
        </div>
        <div v-else class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center mr-2">
          <span class="text-white font-bold text-sm">SPP</span>
        </div>
        <span>{{ appSettings.nama_aplikasi || 'Menu' }}</span>
      </h5>
      <button v-if="!isLargeScreen" type="button" @click="closeSidebar"
        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
        aria-label="Close menu">
        <XMarkIcon class="w-5 h-5" />
      </button>
    </div>

    <nav class="space-y-2">
      <template v-for="item in menuItems" :key="item.name">
        <div v-if="!item.children">
          <div @click="selectMenu(item)" :class="[
            'flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-colors duration-200',
            activeMenu === item.name
              ? 'bg-blue-600 text-white dark:bg-blue-700'
              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]">
            <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
            <span class="font-medium">{{ item.name }}</span>
          </div>
        </div>
        <div v-else>
          <!-- Dropdown for grouped menus -->
          <button type="button" @click="toggleDropdown(item.name)" :class="[
            'flex items-center justify-between w-full px-4 py-3 rounded-lg transition-colors duration-200',
            (item.name === 'Master Data' && isMasterDataActive) || (item.name === 'Payments' && isPaymentsActive) || (item.name === 'Reports' && isReportsActive) || (item.name === 'Students' && isStudentsActive) || (item.name === 'Diagrams' && isDiagramsActive)
              ? 'bg-blue-600 text-white dark:bg-blue-700'
              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]" :aria-expanded="isDropdownOpen(item.name)">
            <div class="flex items-center gap-3">
              <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
              <span class="font-medium">{{ item.name }}</span>
            </div>
            <ChevronDownIcon :class="['w-4 h-4 transition-transform', { 'rotate-180': isDropdownOpen(item.name) }]" />
          </button>
          <div v-show="isDropdownOpen(item.name)" class="ml-6 mt-1 space-y-1">
            <div v-for="child in item.children" :key="child.name" @click="selectMenu(child)" :class="[
              'flex items-center gap-3 px-4 py-2 rounded-lg cursor-pointer transition-colors duration-200',
              child.route === currentRoute
                ? 'bg-blue-500 text-white dark:bg-blue-600'
                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]">
              <component :is="child.icon" class="w-4 h-4 flex-shrink-0" />
              <span class="text-sm font-medium truncate">{{ child.name }}</span>
              <svg v-if="child.target === '_blank'" class="w-3 h-3 ml-auto text-gray-400" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
              </svg>
            </div>
          </div>
        </div>
      </template>
    </nav>
  </aside>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
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
  TrashIcon,
  Squares2X2Icon,
  LockClosedIcon,
  CircleStackIcon,
  ArrowPathIcon,
  ArrowUpCircleIcon,
  UserMinusIcon,
  PresentationChartBarIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  activeMenu: {
    type: String,
    default: 'Dashboard'
  },
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['menu-selected', 'close'])

const openDropdown = ref(null)
const manualClosed = ref(new Set())
const isLargeScreen = ref(typeof window !== 'undefined' ? window.innerWidth >= 1024 : true)

const handleResize = () => {
  isLargeScreen.value = window.innerWidth >= 1024
}

const { getCached, loadAll } = useMasterDataCache();
const { appSettings, getLogoUrl } = useAppSettings();
const { hasMenuAccess } = useRoleAccess();

const cached = getCached();
const labelJurusan = ref(cached.labelJurusan || 'Jurusan');

const router = useRouter()
const route = useRoute()

const loadConfig = async () => {
  try {
    const result = await loadAll();
    labelJurusan.value = result.labelJurusan || 'Jurusan';
  } catch (err) {
    console.error('Error loading config:', err);
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
    {
      name: 'Students',
      icon: UserGroupIcon,
      route: '/siswa',
      children: [
        { name: 'Daftar Siswa', icon: UserGroupIcon, route: '/siswa' },
        { name: 'Kenaikan Kelas', icon: ArrowUpCircleIcon, route: '/siswa/kenaikan-kelas' },
        { name: 'Jadikan Alumni', icon: UserMinusIcon, route: '/siswa/alumni' },
        { name: 'Data Alumni', icon: AcademicCapIcon, route: '/siswa/data-alumni' },
      ]
    },
    {
      name: 'Payments',
      icon: CreditCardIcon,
      route: '/master-pembayaran',
      children: [
        { name: 'Master Pembayaran', icon: CreditCardIcon, route: '/master-pembayaran' },
        { name: 'Tagihan', icon: DocumentTextIcon, route: '/tagihan' },
        { name: 'Invoice', icon: ReceiptRefundIcon, route: '/invoice' },
        { name: 'Pembayaran', icon: BanknotesIcon, route: '/pembayaran' },
        { name: 'Billing', icon: DocumentTextIcon, route: '/billing' },
      ]
    },
    {
      name: 'Reports',
      icon: ChartBarIcon,
      route: '/reports',
      children: [
        { name: 'Laporan Harian', icon: ChartBarIcon, route: '/reports' },
        { name: 'Laporan Siswa', icon: UserGroupIcon, route: '/reports/siswa' },
        { name: 'Laporan Rombel', icon: Squares2X2Icon, route: '/reports/rombel' },
        { name: 'Laporan Alumni', icon: AcademicCapIcon, route: '/reports/alumni-analysis' },
      ]
    },
    {
      name: 'Diagrams',
      icon: PresentationChartBarIcon,
      route: '/diagrams',
      children: [
        { name: 'Alumni', icon: ChartBarIcon, route: '/diagrams/alumni' },
        { name: 'Siswa Aktif', icon: ChartBarIcon, route: '/diagrams/student' },
      ]
    },
    { name: 'Closing', icon: LockClosedIcon, route: '/closing' },
    { name: 'Users', icon: UsersIcon, route: '/users' },
    { name: 'Backups', icon: CircleStackIcon, route: '/backups' },
    { name: 'Sync', icon: ArrowPathIcon, route: '/sync' },
    { name: 'Trash', icon: TrashIcon, route: '/trash' }
  ];

  return allMenus.filter(menu => {
    if (menu.children) {
      menu.children = menu.children.filter(child => hasMenuAccess(menu.name));
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

const isReportsActive = computed(() => {
  return props.activeMenu === 'Reports' ||
    (route.path.startsWith('/reports') && !route.path.includes('diagram'))
})

const isDiagramsActive = computed(() => {
  return props.activeMenu === 'Diagrams' ||
    route.path.startsWith('/diagrams')
})

const isStudentsActive = computed(() => {
  return props.activeMenu === 'Students' ||
    route.path.startsWith('/siswa')
})

const toggleDropdown = (menuName) => {
  if (openDropdown.value === menuName) {
    openDropdown.value = null
    manualClosed.value.add(menuName)
  } else {
    openDropdown.value = menuName
    manualClosed.value.delete(menuName)
  }
}

const isDropdownOpen = (menuName) => {
  // If user manually closed it, respect that
  if (manualClosed.value.has(menuName)) {
    return openDropdown.value === menuName
  }
  if (menuName === 'Master Data') {
    return openDropdown.value === menuName || isMasterDataActive.value
  }
  if (menuName === 'Payments') {
    return openDropdown.value === menuName || isPaymentsActive.value
  }
  if (menuName === 'Reports') {
    return openDropdown.value === menuName || isReportsActive.value
  }
  if (menuName === 'Diagrams') {
    return openDropdown.value === menuName || isDiagramsActive.value
  }
  if (menuName === 'Students') {
    return openDropdown.value === menuName || isStudentsActive.value
  }
  return openDropdown.value === menuName
}

const closeSidebar = () => {
  emit('close')
}

const selectMenu = (item) => {
  if (item.route) {
    if (item.target === '_blank') {
      window.open(item.route, '_blank')
    } else {
      router.push(item.route)
    }
  }
  emit('menu-selected', item)
  // Close sidebar on mobile after selection
  if (!isLargeScreen.value) {
    closeSidebar()
  }
}

onMounted(async () => {
  await loadConfig()
  // Auto expand dropdowns if on their routes
  if (isMasterDataActive.value) {
    openDropdown.value = 'Master Data'
  }
  if (isPaymentsActive.value) {
    openDropdown.value = 'Payments'
  }
  if (isReportsActive.value) {
    openDropdown.value = 'Reports'
  }
  if (isStudentsActive.value) {
    openDropdown.value = 'Students'
  }
  if (isDiagramsActive.value) {
    openDropdown.value = 'Diagrams'
  }
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>
