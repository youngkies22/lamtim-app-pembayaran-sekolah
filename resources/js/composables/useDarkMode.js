import { ref, watch } from 'vue'

// Shared state - global untuk semua komponen
const isDark = ref(false)
let initialized = false

// Apply theme to document
const applyTheme = () => {
  if (typeof document === 'undefined') return
  
  const html = document.documentElement
  if (isDark.value) {
    html.classList.add('dark')
    html.setAttribute('data-theme', 'dark')
  } else {
    html.classList.remove('dark')
    html.setAttribute('data-theme', 'light')
  }
}

// Initialize from localStorage or system preference
const initDarkMode = () => {
  if (typeof window === 'undefined') return
  if (initialized) {
    // If already initialized, just apply current theme
    applyTheme()
    return
  }
  
  initialized = true
  
  try {
    const stored = localStorage.getItem('darkMode')
    if (stored !== null) {
      isDark.value = stored === 'true'
    } else {
      // Check system preference
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
      isDark.value = prefersDark
    }
  } catch (e) {
    console.error('Error reading dark mode preference:', e)
    isDark.value = false
  }
  
  applyTheme()
}

// Toggle dark mode
const toggleDarkMode = () => {
  isDark.value = !isDark.value
  
  try {
    localStorage.setItem('darkMode', isDark.value.toString())
  } catch (e) {
    console.error('Error saving dark mode preference:', e)
  }
  
  applyTheme()
}

// Watch for changes and persist
watch(isDark, (newValue) => {
  try {
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('darkMode', newValue.toString())
    }
  } catch (e) {
    console.error('Error saving dark mode preference:', e)
  }
  applyTheme()
})

// Initialize immediately if in browser
if (typeof window !== 'undefined') {
  // Use nextTick to ensure DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDarkMode)
  } else {
    initDarkMode()
  }
}

export function useDarkMode() {
  // Return shared state and functions
  return {
    isDark,
    toggleDarkMode,
    initDarkMode
  }
}
