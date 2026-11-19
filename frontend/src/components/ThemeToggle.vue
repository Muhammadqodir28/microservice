<template>
  <button
    class="theme-toggle"
    type="button"
    :aria-label="label"
    :title="label"
    @click="themeStore.toggle"
  >
    <svg v-if="themeStore.isDark" viewBox="0 0 24 24" aria-hidden="true">
      <circle cx="12" cy="12" r="4" />
      <path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" />
    </svg>
    <svg v-else viewBox="0 0 24 24" aria-hidden="true">
      <path d="M20.2 15.6A8.5 8.5 0 0 1 8.4 3.8 8.6 8.6 0 1 0 20.2 15.6Z" />
    </svg>
    <span class="theme-toggle-label">{{ themeStore.isDark ? t('lightTheme') : t('darkTheme') }}</span>
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useThemeStore } from '@/stores/theme'
import { t } from '@/i18n'

const themeStore = useThemeStore()
const label = computed(() => themeStore.isDark ? t('enableLightTheme') : t('enableDarkTheme'))
</script>
