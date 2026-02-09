import { ref } from 'vue';
import { masterDataAPI, configAPI } from '../services/api';

// Global cache state (shared across all components)
const cache = {
  kelas: {
    data: [],
    loaded: false,
    loading: false,
  },
  jurusan: {
    data: [],
    loaded: false,
    loading: false,
  },
  rombel: {
    data: [],
    loaded: false,
    loading: false,
  },
  config: {
    labelJurusan: 'Group',
    loaded: false,
    loading: false,
  },
};

export function useMasterDataCache() {
  // Load Kelas
  const loadKelas = async (force = false) => {
    if (cache.kelas.loaded && !force) {
      return cache.kelas.data;
    }

    if (cache.kelas.loading) {
      // Wait for ongoing request
      return new Promise((resolve) => {
        const checkInterval = setInterval(() => {
          if (!cache.kelas.loading) {
            clearInterval(checkInterval);
            resolve(cache.kelas.data);
          }
        }, 100);
      });
    }

    try {
      cache.kelas.loading = true;
      const response = await masterDataAPI.kelas.select();
      cache.kelas.data = response.data?.data || response.data || [];
      cache.kelas.loaded = true;
      return cache.kelas.data;
    } catch (err) {
      console.error('Error loading kelas:', err);
      return [];
    } finally {
      cache.kelas.loading = false;
    }
  };

  // Load Jurusan
  const loadJurusan = async (force = false) => {
    if (cache.jurusan.loaded && !force) {
      return cache.jurusan.data;
    }

    if (cache.jurusan.loading) {
      return new Promise((resolve) => {
        const checkInterval = setInterval(() => {
          if (!cache.jurusan.loading) {
            clearInterval(checkInterval);
            resolve(cache.jurusan.data);
          }
        }, 100);
      });
    }

    try {
      cache.jurusan.loading = true;
      const response = await masterDataAPI.jurusan.select();
      cache.jurusan.data = response.data?.data || response.data || [];
      cache.jurusan.loaded = true;
      return cache.jurusan.data;
    } catch (err) {
      console.error('Error loading jurusan:', err);
      return [];
    } finally {
      cache.jurusan.loading = false;
    }
  };

  // Load Rombel
  const loadRombel = async (force = false) => {
    if (cache.rombel.loaded && !force) {
      return cache.rombel.data;
    }

    if (cache.rombel.loading) {
      return new Promise((resolve) => {
        const checkInterval = setInterval(() => {
          if (!cache.rombel.loading) {
            clearInterval(checkInterval);
            resolve(cache.rombel.data);
          }
        }, 100);
      });
    }

    try {
      cache.rombel.loading = true;
      const response = await masterDataAPI.rombel.select();
      cache.rombel.data = response.data?.data || response.data || [];
      cache.rombel.loaded = true;
      return cache.rombel.data;
    } catch (err) {
      console.error('Error loading rombel:', err);
      return [];
    } finally {
      cache.rombel.loading = false;
    }
  };

  // Load Config
  const loadConfig = async (force = false) => {
    if (cache.config.loaded && !force) {
      return cache.config.labelJurusan;
    }

    if (cache.config.loading) {
      return new Promise((resolve) => {
        const checkInterval = setInterval(() => {
          if (!cache.config.loading) {
            clearInterval(checkInterval);
            resolve(cache.config.labelJurusan);
          }
        }, 100);
      });
    }

    try {
      cache.config.loading = true;
      const configRes = await configAPI.get();
      if (configRes.data && configRes.data.success && configRes.data.data) {
        cache.config.labelJurusan = configRes.data.data.label_jurusan || 'Group';
      } else if (configRes.data && configRes.data.label_jurusan) {
        cache.config.labelJurusan = configRes.data.label_jurusan || 'Group';
      }
      cache.config.loaded = true;
      return cache.config.labelJurusan;
    } catch (err) {
      console.error('Error loading config:', err);
      return 'Group';
    } finally {
      cache.config.loading = false;
    }
  };

  // Load all master data in parallel
  const loadAll = async (force = false) => {
    const [kelas, jurusan, rombel, labelJurusan] = await Promise.all([
      loadKelas(force),
      loadJurusan(force),
      loadRombel(force),
      loadConfig(force),
    ]);

    return {
      kelas,
      jurusan,
      rombel,
      labelJurusan,
    };
  };

  // Clear cache (useful when data is updated)
  const clearCache = (type = null) => {
    if (type === 'kelas') {
      cache.kelas.loaded = false;
      cache.kelas.data = [];
    } else if (type === 'jurusan') {
      cache.jurusan.loaded = false;
      cache.jurusan.data = [];
    } else if (type === 'rombel') {
      cache.rombel.loaded = false;
      cache.rombel.data = [];
    } else if (type === 'config') {
      cache.config.loaded = false;
    } else {
      // Clear all
      cache.kelas.loaded = false;
      cache.kelas.data = [];
      cache.jurusan.loaded = false;
      cache.jurusan.data = [];
      cache.rombel.loaded = false;
      cache.rombel.data = [];
      cache.config.loaded = false;
    }
  };

  // Get cached data without loading
  const getCached = () => {
    return {
      kelas: cache.kelas.data,
      jurusan: cache.jurusan.data,
      rombel: cache.rombel.data,
      labelJurusan: cache.config.labelJurusan,
      isLoaded: {
        kelas: cache.kelas.loaded,
        jurusan: cache.jurusan.loaded,
        rombel: cache.rombel.loaded,
        config: cache.config.loaded,
      },
    };
  };

  return {
    loadKelas,
    loadJurusan,
    loadRombel,
    loadConfig,
    loadAll,
    clearCache,
    getCached,
  };
}
