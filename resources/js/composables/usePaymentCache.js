import { ref } from 'vue';
import { siswaAPI, masterPembayaranAPI, masterDataAPI } from '../services/api';

// Global cache state (shared across all components)
const cache = {
  siswa: {
    data: [],
    loaded: false,
    loading: false,
    lastUpdate: null,
  },
  masterPembayaran: {
    data: [],
    loaded: false,
    loading: false,
    lastUpdate: null,
  },
  kategoriPembayaran: {
    data: [],
    loaded: false,
    loading: false,
    lastUpdate: null,
  },
};

// Cache expires after 5 minutes
const CACHE_EXPIRY = 5 * 60 * 1000;

export function usePaymentCache() {
  // Load Siswa
  const loadSiswa = async (force = false) => {
    // Check if cache is still valid
    const now = Date.now();
    const isExpired = cache.siswa.lastUpdate && (now - cache.siswa.lastUpdate) > CACHE_EXPIRY;
    
    if (cache.siswa.loaded && !force && !isExpired) {
      return cache.siswa.data;
    }

    if (cache.siswa.loading) {
      // Wait for ongoing request
      return new Promise((resolve) => {
        const checkInterval = setInterval(() => {
          if (!cache.siswa.loading) {
            clearInterval(checkInterval);
            resolve(cache.siswa.data);
          }
        }, 100);
      });
    }

    try {
      cache.siswa.loading = true;
      const response = await siswaAPI.select({ isActive: 1 });
      cache.siswa.data = response.data?.data || response.data || [];
      cache.siswa.loaded = true;
      cache.siswa.lastUpdate = Date.now();
      return cache.siswa.data;
    } catch (err) {
      console.error('Error loading siswa:', err);
      return [];
    } finally {
      cache.siswa.loading = false;
    }
  };

  // Load Master Pembayaran
  const loadMasterPembayaran = async (force = false) => {
    // Check if cache is still valid
    const now = Date.now();
    const isExpired = cache.masterPembayaran.lastUpdate && (now - cache.masterPembayaran.lastUpdate) > CACHE_EXPIRY;
    
    if (cache.masterPembayaran.loaded && !force && !isExpired) {
      return cache.masterPembayaran.data;
    }

    if (cache.masterPembayaran.loading) {
      // Wait for ongoing request
      return new Promise((resolve) => {
        const checkInterval = setInterval(() => {
          if (!cache.masterPembayaran.loading) {
            clearInterval(checkInterval);
            resolve(cache.masterPembayaran.data);
          }
        }, 100);
      });
    }

    try {
      cache.masterPembayaran.loading = true;
      const response = await masterPembayaranAPI.select({ isActive: 1 });
      cache.masterPembayaran.data = response.data?.data || response.data || [];
      cache.masterPembayaran.loaded = true;
      cache.masterPembayaran.lastUpdate = Date.now();
      return cache.masterPembayaran.data;
    } catch (err) {
      console.error('Error loading master pembayaran:', err);
      return [];
    } finally {
      cache.masterPembayaran.loading = false;
    }
  };

  // Load Kategori Pembayaran
  const loadKategoriPembayaran = async (force = false) => {
    // Check if cache is still valid
    const now = Date.now();
    const isExpired = cache.kategoriPembayaran.lastUpdate && (now - cache.kategoriPembayaran.lastUpdate) > CACHE_EXPIRY;
    
    if (cache.kategoriPembayaran.loaded && !force && !isExpired) {
      return cache.kategoriPembayaran.data;
    }

    if (cache.kategoriPembayaran.loading) {
      // Wait for ongoing request
      return new Promise((resolve) => {
        const checkInterval = setInterval(() => {
          if (!cache.kategoriPembayaran.loading) {
            clearInterval(checkInterval);
            resolve(cache.kategoriPembayaran.data);
          }
        }, 100);
      });
    }

    try {
      cache.kategoriPembayaran.loading = true;
      const response = await masterDataAPI.kategoriPembayaran.list();
      cache.kategoriPembayaran.data = response.data?.data || response.data || [];
      cache.kategoriPembayaran.loaded = true;
      cache.kategoriPembayaran.lastUpdate = Date.now();
      return cache.kategoriPembayaran.data;
    } catch (err) {
      console.error('Error loading kategori pembayaran:', err);
      return [];
    } finally {
      cache.kategoriPembayaran.loading = false;
    }
  };

  // Load all payment data in parallel
  const loadAll = async (force = false) => {
    const [siswa, masterPembayaran, kategoriPembayaran] = await Promise.all([
      loadSiswa(force),
      loadMasterPembayaran(force),
      loadKategoriPembayaran(force),
    ]);

    return {
      siswa,
      masterPembayaran,
      kategoriPembayaran,
    };
  };

  // Clear cache
  const clearCache = (type = null) => {
    if (type === 'siswa') {
      cache.siswa.loaded = false;
      cache.siswa.data = [];
      cache.siswa.lastUpdate = null;
    } else if (type === 'masterPembayaran') {
      cache.masterPembayaran.loaded = false;
      cache.masterPembayaran.data = [];
      cache.masterPembayaran.lastUpdate = null;
    } else if (type === 'kategoriPembayaran') {
      cache.kategoriPembayaran.loaded = false;
      cache.kategoriPembayaran.data = [];
      cache.kategoriPembayaran.lastUpdate = null;
    } else {
      // Clear all
      cache.siswa.loaded = false;
      cache.siswa.data = [];
      cache.siswa.lastUpdate = null;
      cache.masterPembayaran.loaded = false;
      cache.masterPembayaran.data = [];
      cache.masterPembayaran.lastUpdate = null;
      cache.kategoriPembayaran.loaded = false;
      cache.kategoriPembayaran.data = [];
      cache.kategoriPembayaran.lastUpdate = null;
    }
  };

  // Get cached data without loading
  const getCached = () => {
    return {
      siswa: cache.siswa.data,
      masterPembayaran: cache.masterPembayaran.data,
      kategoriPembayaran: cache.kategoriPembayaran.data,
      isLoaded: {
        siswa: cache.siswa.loaded,
        masterPembayaran: cache.masterPembayaran.loaded,
        kategoriPembayaran: cache.kategoriPembayaran.loaded,
      },
    };
  };

  // Helper function to get kategori name by kode
  const getKategoriNama = (kode) => {
    const kategori = cache.kategoriPembayaran.data.find(k => k.kode === kode);
    return kategori?.nama || kode || '-';
  };

  return {
    loadSiswa,
    loadMasterPembayaran,
    loadKategoriPembayaran,
    loadAll,
    clearCache,
    getCached,
    getKategoriNama,
  };
}

