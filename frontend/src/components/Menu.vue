<template>
  <aside class="admin-sidebar" :aria-label="t('workspace')">
    <p class="sidebar-label">{{ t('workspace') }}</p>
    <RouterLink class="sidebar-link" to="/admin/products">
      <span class="sidebar-icon" aria-hidden="true">▦</span>
      {{ t('products') }}
    </RouterLink>
    <RouterLink class="sidebar-link" to="/chat">
      <span class="sidebar-icon" aria-hidden="true">◌</span>
      {{ t('chat') }}
    </RouterLink>
    <RouterLink class="sidebar-link" to="/admin/products/create">
      <span class="sidebar-icon" aria-hidden="true">＋</span>
      {{ t('addProduct') }}
    </RouterLink>
    <div class="sidebar-divider"></div>
    <RouterLink class="sidebar-link" to="/">
      <span class="sidebar-icon" aria-hidden="true">↗</span>
      {{ t('storefront') }}
    </RouterLink>
  </aside>
</template>

<script setup lang="ts">
import { t } from '@/i18n'
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.sidebar-divider { height: 1px; margin: 20px 12px; background: var(--line); }
</style>
