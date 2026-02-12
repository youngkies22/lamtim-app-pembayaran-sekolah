import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import { initFlowbite } from 'flowbite';

// Initialize dark mode as early as possible
import { useDarkMode } from './composables/useDarkMode';
const { initDarkMode } = useDarkMode();
initDarkMode();


// Import Root Component
import App from './App.vue';

// Import Pages
import Dashboard from './pages/Dashboard.vue';
import Login from './pages/Login.vue';
import SiswaIndex from './pages/siswa/Index.vue';
import SiswaCreate from './pages/siswa/Create.vue';
import SiswaEdit from './pages/siswa/Edit.vue';
import SiswaRombelMapping from './pages/siswa-rombel/Mapping.vue';
import SiswaRombelIndex from './pages/siswa-rombel/Index.vue';
import MasterPembayaranIndex from './pages/master-pembayaran/Index.vue';
import TagihanIndex from './pages/tagihan/Index.vue';
import InvoiceIndex from './pages/invoice/Index.vue';
import PembayaranIndex from './pages/pembayaran/Index.vue';
import BillingIndex from './pages/billing/Index.vue';
import SekolahIndex from './pages/master-data/sekolah/Index.vue';
import MasterDataIndex from './pages/master-data/Index.vue';
import JurusanIndex from './pages/master-data/jurusan/Index.vue';
import KelasIndex from './pages/master-data/kelas/Index.vue';
import RombelIndex from './pages/master-data/rombel/Index.vue';
import JenisPembayaranIndex from './pages/master-data/jenis-pembayaran/Index.vue';
import KategoriPembayaranIndex from './pages/master-data/kategori-pembayaran/Index.vue';
import TipePembayaranIndex from './pages/master-data/tipe-pembayaran/Index.vue';
import TahunAjaranIndex from './pages/master-data/tahun-ajaran/Index.vue';
import SemesterIndex from './pages/master-data/semester/Index.vue';
import Reports from './pages/Reports.vue';
import ReportSiswa from './pages/reports/ReportSiswa.vue';
import ReportRombel from './pages/reports/ReportRombel.vue';
import PrintReportRombel from './pages/reports/PrintReportRombel.vue';
import PrintSiswa from './pages/reports/PrintSiswa.vue';
import Settings from './pages/Settings.vue';
import Profile from './pages/Profile.vue';
import UsersIndex from './pages/users/Index.vue';
import TrashIndex from './pages/trash/Index.vue';
import ClosingIndex from './pages/Closing/Index.vue';
import BackupIndex from './pages/Backups/Index.vue';

// Import API service
import { authAPI } from './services/api';

// Import app settings composable
import { useAppSettings } from './composables/useAppSettings';
const { loadAppSettings } = useAppSettings();

// Router configuration
const routes = [
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { requiresAuth: false }
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: { requiresAuth: true, activeMenu: 'Dashboard' }
    },
    {
        path: '/siswa',
        name: 'siswa',
        component: SiswaIndex,
        meta: { requiresAuth: true, activeMenu: 'Students' }
    },
    {
        path: '/siswa/create',
        name: 'siswa-create',
        component: SiswaCreate,
        meta: { requiresAuth: true, activeMenu: 'Students' }
    },
    {
        path: '/siswa/:id/edit',
        name: 'siswa-edit',
        component: SiswaEdit,
        meta: { requiresAuth: true, activeMenu: 'Students' }
    },
    {
        path: '/siswa-rombel',
        name: 'siswa-rombel',
        component: SiswaRombelIndex,
        meta: { requiresAuth: true, activeMenu: 'Mapping' }
    },
    {
        path: '/siswa-rombel/mapping',
        name: 'siswa-rombel-mapping',
        component: SiswaRombelMapping,
        meta: { requiresAuth: true, activeMenu: 'Students' }
    },
    {
        path: '/master-pembayaran',
        name: 'master-pembayaran',
        component: MasterPembayaranIndex,
        meta: { requiresAuth: true, activeMenu: 'Payments' }
    },
    {
        path: '/tagihan',
        name: 'tagihan',
        component: TagihanIndex,
        meta: { requiresAuth: true, activeMenu: 'Payments' }
    },
    {
        path: '/invoice',
        name: 'invoice',
        component: InvoiceIndex,
        meta: { requiresAuth: true, activeMenu: 'Payments' }
    },
    {
        path: '/pembayaran',
        name: 'pembayaran',
        component: PembayaranIndex,
        meta: { requiresAuth: true, activeMenu: 'Payments' }
    },
    {
        path: '/billing',
        name: 'billing',
        component: BillingIndex,
        meta: { requiresAuth: true, activeMenu: 'Payments' }
    },
    {
        path: '/master-data',
        name: 'master-data',
        component: MasterDataIndex,
        meta: { requiresAuth: true, activeMenu: 'Master Data' }
    },
    {
        path: '/master-data/sekolah',
        name: 'master-data-sekolah',
        component: SekolahIndex,
        meta: { requiresAuth: true, activeMenu: 'Master Data' }
    },
    {
        path: '/master-data/jurusan',
        name: 'jurusan',
        component: JurusanIndex,
        meta: { requiresAuth: true, activeMenu: 'Master Data' }
    },
    {
        path: '/master-data/kelas',
        name: 'kelas',
        component: KelasIndex,
        meta: { requiresAuth: true, activeMenu: 'Master Data' }
    },
    {
        path: '/master-data/rombel',
        name: 'rombel',
        component: RombelIndex,
        meta: { requiresAuth: true, activeMenu: 'Master Data' }
    },
    {
        path: '/master-data/jenis-pembayaran',
        name: 'jenis-pembayaran',
        component: JenisPembayaranIndex,
        meta: { requiresAuth: true, activeMenu: 'Master Data' }
    },
    {
        path: '/master-data/kategori-pembayaran',
        name: 'kategori-pembayaran',
        component: KategoriPembayaranIndex,
        meta: { requiresAuth: true, activeMenu: 'Master Data' }
    },
    {
        path: '/master-data/tipe-pembayaran',
        name: 'tipe-pembayaran',
        component: TipePembayaranIndex,
        meta: { requiresAuth: true, activeMenu: 'Master Data' }
    },
    {
        path: '/master-data/tahun-ajaran',
        name: 'tahun-ajaran',
        component: TahunAjaranIndex,
        meta: { requiresAuth: true, activeMenu: 'Master Data' }
    },
    {
        path: '/master-data/semester',
        name: 'semester',
        component: SemesterIndex,
        meta: { requiresAuth: true, activeMenu: 'Master Data' }
    },
    {
        path: '/reports',
        name: 'reports',
        component: Reports,
        meta: { requiresAuth: true, activeMenu: 'Reports' }
    },
    {
        path: '/reports/siswa',
        name: 'reports-siswa',
        component: ReportSiswa,
        meta: { requiresAuth: true, activeMenu: 'Reports' }
    },
    {
        path: '/reports/rombel',
        name: 'reports-rombel',
        component: ReportRombel,
        meta: { requiresAuth: true, activeMenu: 'Reports' }
    },
    {
        path: '/reports/rombel/print',
        name: 'reports-rombel-print',
        component: PrintReportRombel,
        meta: { requiresAuth: true, activeMenu: 'Reports' }
    },
    {
        path: '/reports/siswa/print/:id',
        name: 'reports-siswa-print',
        component: PrintSiswa,
        meta: { requiresAuth: true, activeMenu: 'Reports' }
    },
    {
        path: '/settings',
        name: 'settings',
        component: Settings,
        meta: { requiresAuth: true, activeMenu: 'Settings' }
    },
    {
        path: '/trash',
        name: 'trash',
        component: TrashIndex,
        meta: { requiresAuth: true, activeMenu: 'Settings' }
    },
    {
        path: '/profile',
        name: 'profile',
        component: Profile,
        meta: { requiresAuth: true, activeMenu: 'Profile' }
    },
    {
        path: '/users',
        name: 'users',
        component: UsersIndex,
        meta: { requiresAuth: true, activeMenu: 'Users' }
    },
    {
        path: '/closing',
        name: 'closing',
        component: ClosingIndex,
        meta: { requiresAuth: true, activeMenu: 'Closing Management' }
    },
    {
        path: '/backups',
        name: 'backups',
        component: BackupIndex,
        meta: { requiresAuth: true, activeMenu: 'Backups' }
    },
    {
        path: '/auth/login',
        redirect: '/login'
    },
    {
        path: '/',
        redirect: '/dashboard'
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/dashboard'
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Expose router globally for interceptor use
window.router = router;

// Navigation guard
router.beforeEach(async (to, from, next) => {
    // Skip auth check untuk halaman login
    if (to.path === '/login') {
        // Jika sudah login dan akses halaman login, redirect ke dashboard
        const hasLocalUser = authAPI.isAuthenticated();
        if (hasLocalUser) {
            try {
                const isAuth = await authAPI.checkAuth();
                if (isAuth) {
                    next('/dashboard');
                    return;
                }
            } catch (err) {
                // Auth check failed, allow access to login
                next();
                return;
            }
        }
        next();
        return;
    }

    // Jika route memerlukan auth, verifikasi dengan backend
    if (to.meta.requiresAuth) {
        // Cek dulu apakah ada user data di localStorage
        const hasLocalUser = authAPI.isAuthenticated();
        
        if (!hasLocalUser) {
            // Tidak ada user data, redirect ke login
            next('/login');
            return;
        }
        
        // Ada user data, verifikasi dengan backend
        try {
            const isAuth = await authAPI.checkAuth();
            if (!isAuth) {
                // Backend mengatakan tidak authenticated, redirect ke login
                localStorage.removeItem('auth_user');
                next('/login');
                return;
            }
        } catch (err) {
            // Error saat verifikasi, redirect ke login
            console.error('Auth check error:', err);
            localStorage.removeItem('auth_user');
            next('/login');
            return;
        }
    }
    
    next();
});

// Initialize CSRF token on app start
const initializeCsrfToken = async () => {
    try {
        // Get CSRF cookie from Sanctum endpoint
        await window.axios.get('/sanctum/csrf-cookie', {
            withCredentials: true,
            baseURL: ''
        });
    } catch (error) {
        console.warn('Failed to initialize CSRF token:', error);
    }
};

// Create Vue app
try {
    // Initialize CSRF token before mounting app
    initializeCsrfToken();
    
    const app = createApp(App);

    app.use(router);
    app.mount('#app');
    
    // Initialize Flowbite components after Vue app is mounted
    setTimeout(() => {
        initFlowbite();
    }, 100);
    
    // Load app settings after app is mounted (only if authenticated)
    if (authAPI.isAuthenticated()) {
        loadAppSettings();
    }
} catch (error) {
    console.error('Error mounting Vue app:', error);
}
