import { defineStore } from 'pinia'

export type ThemeMode = 'light' | 'dark'

const THEME_KEY = 'microstore-theme'

function preferredTheme(): ThemeMode {
  try {
    const savedTheme = localStorage.getItem(THEME_KEY)
    if (savedTheme === 'light' || savedTheme === 'dark') return savedTheme
  } catch {
    // The system preference still works when browser storage is unavailable.
  }

  return window.matchMedia?.('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

export const useThemeStore = defineStore('theme', {
  state: () => ({
    mode: preferredTheme() as ThemeMode,
  }),

  getters: {
    isDark: (state) => state.mode === 'dark',
  },

  actions: {
    initialize() {
      this.mode = preferredTheme()
      this.apply()
    },

    toggle() {
      this.mode = this.mode === 'dark' ? 'light' : 'dark'
      this.apply()
    },

    apply() {
      document.documentElement.dataset.theme = this.mode
      document.documentElement.style.colorScheme = this.mode

      try {
        localStorage.setItem(THEME_KEY, this.mode)
      } catch {
        // Keep the theme active for this session if browser storage is unavailable.
      }
    },
  },
})
