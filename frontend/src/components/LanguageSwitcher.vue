<template>
  <label class="language-picker">
    <span class="sr-only">{{ t('language') }}</span>
    <select :value="locale" :aria-label="t('language')" @change="changeLanguage">
      <option value="en">EN</option>
      <option value="ru">RU</option>
      <option value="uz">UZ</option>
    </select>
  </label>
</template>

<script setup lang="ts">
import { locale, setLocale, t, type Locale } from '@/i18n'

function changeLanguage(event: Event) {
  setLocale((event.target as HTMLSelectElement).value as Locale)
}
</script>

<style scoped>
.language-picker {
  position: relative;
  display: inline-flex;
  flex: 0 0 auto;
  align-items: center;
}

.language-picker::after {
  position: absolute;
  top: 50%;
  right: 15px;
  width: 8px;
  height: 8px;
  border-right: 1.8px solid currentColor;
  border-bottom: 1.8px solid currentColor;
  content: '';
  pointer-events: none;
  transform: translateY(-70%) rotate(45deg);
}

.language-picker select {
  width: 86px;
  min-height: 46px;
  padding: 0 34px 0 16px;
  appearance: none;
  color: var(--ink);
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: 14px;
  box-shadow: 0 2px 6px rgba(20, 30, 55, .04);
  font: inherit;
  font-size: 15px;
  font-weight: 650;
  letter-spacing: .035em;
  cursor: pointer;
}

.language-picker select:hover {
  border-color: var(--brand-outline);
  background: var(--surface-hover);
}

.language-picker select:focus-visible {
  outline: 3px solid var(--brand-outline);
  outline-offset: 2px;
}

.language-picker option { color: var(--ink); background: var(--surface); }

:root[data-theme="dark"] .language-picker select {
  color: #edf1fb;
  background: #182131;
  border-color: #303d53;
  box-shadow: inset 0 1px rgba(255, 255, 255, .035);
}

:root[data-theme="dark"] .language-picker select:hover {
  background: #202b3e;
  border-color: #536b9f;
}

.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; }
</style>
