import { ref, onMounted } from 'vue';
import { settingsAPI } from '../services/api';

const appSettings = ref({
  nama_aplikasi: 'SPP Management',
  logo_aplikasi: null,
});

const loading = ref(false);

/**
 * Get logo URL
 */
const getLogoUrl = (logoPath) => {
  if (!logoPath) return null;
  // If it's already a full URL, return as is
  if (logoPath.startsWith('http://') || logoPath.startsWith('https://')) {
    return logoPath;
  }
  // Otherwise, construct the storage URL
  return `/storage/${logoPath}`;
};

/**
 * Update document title
 */
const updateTitle = (namaAplikasi) => {
  if (namaAplikasi) {
    document.title = namaAplikasi;
  } else {
    document.title = 'SPP Management';
  }
};

/**
 * Update favicon
 */
const updateFavicon = (logoPath) => {
  // Remove existing favicon links
  const existingLinks = document.querySelectorAll('link[rel="icon"], link[rel="shortcut icon"]');
  existingLinks.forEach(link => link.remove());

  if (logoPath) {
    const logoUrl = getLogoUrl(logoPath);
    if (logoUrl) {
      // Create new favicon link
      const link = document.createElement('link');
      link.rel = 'icon';
      link.type = 'image/png';
      link.href = logoUrl;
      document.head.appendChild(link);

      // Also add shortcut icon for older browsers
      const shortcutLink = document.createElement('link');
      shortcutLink.rel = 'shortcut icon';
      shortcutLink.type = 'image/png';
      shortcutLink.href = logoUrl;
      document.head.appendChild(shortcutLink);
    }
  } else {
    // Set default favicon if no logo
    const defaultLink = document.createElement('link');
    defaultLink.rel = 'icon';
    defaultLink.type = 'image/png';
    defaultLink.href = '/favicon.ico';
    document.head.appendChild(defaultLink);
  }
};

/**
 * Load app settings and update title/favicon
 */
const loadAppSettings = async () => {
  try {
    loading.value = true;
    const response = await settingsAPI.get();
    
    // Handle different response structures
    let settings = null;
    if (response.data?.success && response.data.data) {
      settings = response.data.data;
    } else if (response.data?.data) {
      settings = response.data.data;
    } else if (response.data) {
      settings = response.data;
    }
    
    if (settings) {
      appSettings.value = {
        nama_aplikasi: settings.nama_aplikasi || 'SPP Management',
        logo_aplikasi: settings.logo_aplikasi || null,
      };

      // Update title and favicon
      updateTitle(appSettings.value.nama_aplikasi);
      updateFavicon(appSettings.value.logo_aplikasi);
    } else {
      // Use defaults
      updateTitle('SPP Management');
      updateFavicon(null);
    }
  } catch (err) {
    console.error('Error loading app settings:', err);
    // Use defaults on error
    updateTitle('SPP Management');
    updateFavicon(null);
  } finally {
    loading.value = false;
  }
};

/**
 * Refresh app settings (useful after update)
 */
const refreshAppSettings = async () => {
  await loadAppSettings();
};

export function useAppSettings() {
  return {
    appSettings,
    loading,
    loadAppSettings,
    refreshAppSettings,
    getLogoUrl,
    updateTitle,
    updateFavicon,
  };
}
