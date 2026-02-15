import axios from 'axios';

// Helper function to get CSRF token
const getCsrfToken = () => {
    // Try to get from meta tag first
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }
    // Fallback: try to get from cookie
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'XSRF-TOKEN') {
            return decodeURIComponent(value);
        }
    }
    return null;
};

// Setup axios instance
const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: true, // Penting: kirim cookies otomatis (termasuk httpOnly cookies)
});

// Request interceptor - Add CSRF token and handle authentication
api.interceptors.request.use(
    async (config) => {
        // Get CSRF token
        const csrfToken = getCsrfToken();
        
        // For stateful API (Sanctum), we need CSRF token for POST/PUT/DELETE/PATCH requests
        if (csrfToken && ['post', 'put', 'delete', 'patch'].includes(config.method.toLowerCase())) {
            config.headers['X-CSRF-TOKEN'] = csrfToken;
        }
        
        // Token sekarang disimpan di httpOnly cookie, tidak perlu diambil dari localStorage
        // Sanctum akan otomatis membaca cookie untuk autentikasi
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor - Handle errors
api.interceptors.response.use(
    (response) => {
        return response;
    },
    async (error) => {
        if (error.response?.status === 419) {
            // CSRF token mismatch - try to refresh token
            try {
                // Get fresh CSRF cookie
                await axios.get('/sanctum/csrf-cookie', { 
                    withCredentials: true,
                    baseURL: '' // Use root URL for sanctum endpoint
                });
                
                // Retry the original request with new token
                const originalRequest = error.config;
                const csrfToken = getCsrfToken();
                if (csrfToken && ['post', 'put', 'delete', 'patch'].includes(originalRequest.method.toLowerCase())) {
                    originalRequest.headers['X-CSRF-TOKEN'] = csrfToken;
                }
                return api(originalRequest);
            } catch (retryError) {
                return Promise.reject(retryError);
            }
        }
        if (error.response?.status === 401) {
            // Unauthorized - cookie akan dihapus otomatis oleh server
            localStorage.removeItem('auth_user');
            // Jangan langsung redirect, biarkan router guard yang handle
            // Hanya redirect jika tidak di halaman login
            if (window.location.pathname !== '/login') {
                // Use router if available, otherwise use window.location
                if (window.router) {
                    window.router.push('/login');
                } else {
                    window.location.href = '/login';
                }
            }
        }
        return Promise.reject(error);
    }
);

// Auth API
export const authAPI = {
    login: async (email, password, remember = false) => {
        const response = await api.post('/login', { email, password, remember });
        // Token sekarang di httpOnly cookie, tidak perlu disimpan di localStorage
        // Hanya simpan user data untuk keperluan frontend
        if (response.data && response.data.success && response.data.data && response.data.data.user) {
            localStorage.setItem('auth_user', JSON.stringify(response.data.data.user));
        }
        return response.data;
    },
    logout: async () => {
        try {
            await api.post('/logout');
        } catch (err) {
            // Even if logout fails, clear local storage
            console.error('Logout error:', err);
        }
        // Cookie akan dihapus otomatis oleh server
        localStorage.removeItem('auth_user');
    },
    getCurrentUser: () => {
        const user = localStorage.getItem('auth_user');
        return user ? JSON.parse(user) : null;
    },
    me: () => api.get('/me'),
    checkAuth: async () => {
        // Verify authentication dengan backend
        try {
            const response = await api.get('/me');
            if (response.data && response.data.success && response.data.data) {
                // Update user data di localStorage
                localStorage.setItem('auth_user', JSON.stringify(response.data.data));
                return true;
            }
            return false;
        } catch (err) {
            // If 401, user is not authenticated
            if (err.response?.status === 401) {
                localStorage.removeItem('auth_user');
                return false;
            }
            // For other errors, assume not authenticated
            return false;
        }
    },
    isAuthenticated: () => {
        // Cek apakah user data ada (token sekarang di httpOnly cookie, tidak bisa dicek langsung)
        // Jika user data ada, berarti sudah login (cookie masih valid)
        return !!localStorage.getItem('auth_user');
    },
    updateProfile: async (data) => {
        // Check if data is FormData (for file upload)
        const isFormData = data instanceof FormData;
        
        let response;
        if (isFormData) {
            // For FormData, use POST with _method=PUT
            data.append('_method', 'PUT');
            response = await api.post('/profile', data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
        } else {
            response = await api.put('/profile', data);
        }
        
        if (response.data && response.data.success && response.data.data) {
            localStorage.setItem('auth_user', JSON.stringify(response.data.data.user));
        }
        return response.data;
    },
};

// Config API
export const configAPI = {
    get: () => api.get('/config'),
    getPublicSettings: () => api.get('/public/settings'),
    getPublicSekolah: () => api.get('/public/sekolah'),
};

// Settings API
export const settingsAPI = {
    get: () => api.get('/settings'),
    getPublic: () => api.get('/settings/public'), // Public endpoint (no auth required)
    create: (data) => {
        // Check if data is FormData
        if (data instanceof FormData) {
            return api.post('/settings', data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
        }
        return api.post('/settings', data);
    },
    update: (id, data) => {
        // Check if data is FormData
        if (data instanceof FormData) {
            // For file upload, use POST with _method=PUT
            data.append('_method', 'PUT');
            return api.post(`/settings/${id}`, data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
        }
        return api.put(`/settings/${id}`, data);
    },
    delete: (id) => api.delete(`/settings/${id}`),
};

// Dashboard API
export const dashboardAPI = {
    stats: () => api.get('/dashboard/stats'),
    recentPayments: (params = {}) => api.get('/dashboard/recent-payments', { params }),
};

// Master Data API
export const masterDataAPI = {
    // Sekolah
    sekolah: {
        list: (params = {}) => api.get('/master-data/sekolah', { params }),
        select: (params = {}) => api.get('/master-data/sekolah/select', { params }),
        datatable: (params = {}) => api.get('/master-data/sekolah/datatable', { params }),
        get: (id) => api.get(`/master-data/sekolah/${id}`),
        create: (data) => {
            // Check if data is FormData
            if (data instanceof FormData) {
                return api.post('/master-data/sekolah', data, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
            }
            return api.post('/master-data/sekolah', data);
        },
        update: (id, data) => {
            // Check if data is FormData
            if (data instanceof FormData) {
                // For file upload, use POST with _method=PUT
                data.append('_method', 'PUT');
                return api.post(`/master-data/sekolah/${id}`, data, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
            }
            return api.put(`/master-data/sekolah/${id}`, data);
        },
        delete: (id) => api.delete(`/master-data/sekolah/${id}`),
    },
    // Jurusan
    jurusan: {
        list: (params = {}) => api.get('/master-data/jurusan', { params }),
        select: (params = {}) => api.get('/master-data/jurusan/select', { params }),
        datatable: (params = {}) => api.get('/master-data/jurusan/datatable', { params }),
        get: (id) => api.get(`/master-data/jurusan/${id}`),
        create: (data) => api.post('/master-data/jurusan', data),
        update: (id, data) => api.put(`/master-data/jurusan/${id}`, data),
        delete: (id) => api.delete(`/master-data/jurusan/${id}`),
    },
    // Kelas
    kelas: {
        list: (params = {}) => api.get('/master-data/kelas', { params }),
        select: (params = {}) => api.get('/master-data/kelas/select', { params }),
        datatable: (params = {}) => api.get('/master-data/kelas/datatable', { params }),
        get: (id) => api.get(`/master-data/kelas/${id}`),
        create: (data) => api.post('/master-data/kelas', data),
        update: (id, data) => api.put(`/master-data/kelas/${id}`, data),
        delete: (id) => api.delete(`/master-data/kelas/${id}`),
    },
    // Rombel
    rombel: {
        list: (params = {}) => api.get('/master-data/rombel', { params }),
        select: (params = {}) => api.get('/master-data/rombel/select', { params }),
        datatable: (params = {}) => api.get('/master-data/rombel/datatable', { params }),
        get: (id) => api.get(`/master-data/rombel/${id}`),
        create: (data) => api.post('/master-data/rombel', data),
        update: (id, data) => api.put(`/master-data/rombel/${id}`, data),
        delete: (id) => api.delete(`/master-data/rombel/${id}`),
    },
    // Tahun Ajaran
    tahunAjaran: {
        list: (params = {}) => api.get('/master-data/tahun-ajaran', { params }),
        datatable: (params = {}) => api.get('/master-data/tahun-ajaran/datatable', { params }),
        get: (id) => api.get(`/master-data/tahun-ajaran/${id}`),
        create: (data) => api.post('/master-data/tahun-ajaran', data),
        update: (id, data) => api.put(`/master-data/tahun-ajaran/${id}`, data),
        delete: (id) => api.delete(`/master-data/tahun-ajaran/${id}`),
        setActive: (id) => api.post(`/master-data/tahun-ajaran/${id}/set-active`),
    },
    // Semester
    semester: {
        list: (params = {}) => api.get('/master-data/semester', { params }),
        datatable: (params = {}) => api.get('/master-data/semester/datatable', { params }),
        get: (id) => api.get(`/master-data/semester/${id}`),
        create: (data) => api.post('/master-data/semester', data),
        update: (id, data) => api.put(`/master-data/semester/${id}`, data),
        delete: (id) => api.delete(`/master-data/semester/${id}`),
        setActive: (id) => api.post(`/master-data/semester/${id}/set-active`),
    },
    // Jenis Pembayaran
    jenisPembayaran: {
        list: (params = {}) => api.get('/master-data/jenis-pembayaran', { params }),
        get: (id) => api.get(`/master-data/jenis-pembayaran/${id}`),
        create: (data) => api.post('/master-data/jenis-pembayaran', data),
        update: (id, data) => api.put(`/master-data/jenis-pembayaran/${id}`, data),
        delete: (id) => api.delete(`/master-data/jenis-pembayaran/${id}`),
    },
    // Kategori Pembayaran
    kategoriPembayaran: {
        list: (params = {}) => api.get('/master-data/kategori-pembayaran', { params }),
        get: (id) => api.get(`/master-data/kategori-pembayaran/${id}`),
        create: (data) => api.post('/master-data/kategori-pembayaran', data),
        update: (id, data) => api.put(`/master-data/kategori-pembayaran/${id}`, data),
        delete: (id) => api.delete(`/master-data/kategori-pembayaran/${id}`),
    },
    // Tipe Pembayaran
    tipePembayaran: {
        list: (params = {}) => api.get('/master-data/tipe-pembayaran', { params }),
        get: (id) => api.get(`/master-data/tipe-pembayaran/${id}`),
        create: (data) => api.post('/master-data/tipe-pembayaran', data),
        update: (id, data) => api.put(`/master-data/tipe-pembayaran/${id}`, data),
        delete: (id) => api.delete(`/master-data/tipe-pembayaran/${id}`),
    },
    // Agama
    agama: {
        list: (params = {}) => api.get('/master-data/agama', { params }),
        datatable: (params = {}) => api.get('/master-data/agama/datatable', { params }),
        get: (id) => api.get(`/master-data/agama/${id}`),
        create: (data) => api.post('/master-data/agama', data),
        update: (id, data) => api.put(`/master-data/agama/${id}`, data),
        delete: (id) => api.delete(`/master-data/agama/${id}`),
    },
};

// Siswa API
export const siswaAPI = {
    list: (params = {}) => api.get('/siswa', { params }),
    select: (params = {}) => api.get('/siswa/select', { params }),
    datatable: (params = {}) => api.get('/siswa/datatable', { params }),
    get: (id) => api.get(`/siswa/${id}`),
    create: (data) => api.post('/siswa', data),
    update: (id, data) => api.put(`/siswa/${id}`, data),
    delete: (id) => api.delete(`/siswa/${id}`),
    markAsAlumni: (data) => api.post('/siswa/mark-alumni', data),
};

// Siswa Rombel Mapping API
export const siswaRombelAPI = {
    list: (params = {}) => api.get('/siswa-rombel', { params }),
    datatable: (params = {}) => api.get('/siswa-rombel/datatable', { params }),
    get: (id) => api.get(`/siswa-rombel/${id}`),
    getUnmapped: (params = {}) => api.get('/siswa-rombel/unmapped', { params }),
    create: (data) => api.post('/siswa-rombel', data),
    batchCreate: (data) => api.post('/siswa-rombel/batch', data),
    promote: (data) => api.post('/siswa-rombel/promote', data),
    update: (id, data) => api.put(`/siswa-rombel/${id}`, data),
    delete: (id) => api.delete(`/siswa-rombel/${id}`),
};

// Master Pembayaran API
export const masterPembayaranAPI = {
    list: (params = {}) => api.get('/master-pembayaran', { params }),
    select: (params = {}) => api.get('/master-pembayaran/select', { params }),
    datatable: (params = {}) => api.get('/master-pembayaran/datatable', { params }),
    get: (id) => api.get(`/master-pembayaran/${id}`),
    create: (data) => api.post('/master-pembayaran', data),
    update: (id, data) => api.put(`/master-pembayaran/${id}`, data),
    delete: (id) => api.delete(`/master-pembayaran/${id}`),
};

// Tagihan API
export const tagihanAPI = {
    list: (params = {}) => api.get('/tagihan', { params }),
    datatable: (params = {}) => api.get('/tagihan/datatable', { params }),
    get: (id) => api.get(`/tagihan/${id}`),
    getBySiswa: (idSiswa) => api.get(`/tagihan/siswa/${idSiswa}`),
    create: (data) => api.post('/tagihan', data),
    update: (id, data) => api.put(`/tagihan/${id}`, data),
    delete: (id) => api.delete(`/tagihan/${id}`),
    generateBatch: (data) => api.post('/tagihan/generate-batch', data),
    retrySync: (id) => api.post(`/tagihan/${id}/retry-sync`),
};

// Invoice API
export const invoiceAPI = {
    list: (params = {}) => api.get('/invoice', { params }),
    stats: () => api.get('/invoice/stats'),
    datatable: (params = {}) => api.get('/invoice/datatable', { params }),
    get: (id) => api.get(`/invoice/${id}`),
    create: (data) => api.post('/invoice', data),
    update: (id, data) => api.put(`/invoice/${id}`, data),
    delete: (id) => api.delete(`/invoice/${id}`),
    exportExcel: (params = {}) => api.get('/invoice/export', { params, responseType: 'blob' }),
};

// Pembayaran API
export const pembayaranAPI = {
    list: (params = {}) => api.get('/pembayaran', { params }),
    stats: () => api.get('/pembayaran/stats'),
    datatable: (params = {}) => api.get('/pembayaran/datatable', { params }),
    get: (id) => api.get(`/pembayaran/${id}`),
    proses: (data) => api.post('/pembayaran/proses', data),
    verify: (id) => api.post(`/pembayaran/${id}/verify`),
    cancel: (id, alasan = null) => api.post(`/pembayaran/${id}/cancel`, { alasan }),
    delete: (id) => api.delete(`/pembayaran/${id}`),
    exportExcel: (params = {}) => api.get('/pembayaran/export', { params, responseType: 'blob' }),
    retrySync: (id) => api.post(`/pembayaran/${id}/retry-sync`),
};

// Users API (Admin only)
export const usersAPI = {
    list: (params = {}) => api.get('/users', { params }),
    get: (id) => api.get(`/users/${id}`),
    create: (data) => api.post('/users', data, {
        headers: { 'Content-Type': 'multipart/form-data' }
    }),
    update: (id, data) => api.post(`/users/${id}`, data, {
        headers: { 'Content-Type': 'multipart/form-data' }
    }),
    delete: (id) => api.delete(`/users/${id}`),
    toggleActive: (id) => api.post(`/users/${id}/toggle-active`),
};

// Reports API
export const reportAPI = {
    rombel: (params = {}) => api.get('/reports/rombel', { params }),
    rombelHeaders: () => api.get('/reports/rombel/headers'),
    rombelStats: (params = {}) => api.get('/reports/rombel/stats', { params }),
    exportRombel: (params = {}) => api.get('/reports/rombel/export', { params, responseType: 'blob' }),
    exportSiswa: (params = {}) => api.get('/reports/siswa/export', { params, responseType: 'blob' }),
    analyticsStats: (params = {}) => api.get('/reports/analytics-stats', { params }),
    alumniAnalysis: (params = {}) => api.get('/reports/alumni-analysis', { params }),
    exportAlumniAnalysis: (params = {}) => api.get('/reports/alumni-analysis/export', { params, responseType: 'blob' }),
    activeStudentAnalysis: (params = {}) => api.get('/reports/active-student-analysis', { params }),
    getSekolahSelect: (params = {}) => api.get('/master-data/sekolah/select', { params }),
};

// External Sync API (Admin only)
export const syncAPI = {
    run: (data = {}) => api.post('/sync/run', data),
    status: () => api.get('/sync/status'),
    testConnection: () => api.get('/sync/test-connection'),
    siswaBackground: () => api.post('/sync/siswa/background'),
    getProgress: () => api.get('/sync/progress'),
    siswaDownload: () => api.post('/sync/siswa/download'),
    siswaProcessChunk: (data) => api.post('/sync/siswa/process-chunk', data),
    siswaCleanup: () => api.post('/sync/siswa/cleanup'),
};

export const cacheAPI = {
    getStatus: () => api.get('/cache-manager/status'),
    clearLaravel: () => api.post('/cache-manager/clear-laravel'),
    clearRedis: () => api.post('/cache-manager/clear-redis'),
    optimize: () => api.post('/cache-manager/optimize'),
};

// Queue Jobs API (Admin only)
export const jobsAPI = {
    getFailed: () => api.get('/jobs/failed'),
    retry: (id) => api.post(`/jobs/failed/${id}/retry`),
    retryAll: () => api.post('/jobs/failed/retry-all'),
    delete: (id) => api.delete(`/jobs/failed/${id}`),
    flush: () => api.post('/jobs/failed/flush'),
};

export default api;
