<template>
  <aside class="admin-sidebar" aria-label="Разделы панели">
    <p class="sidebar-label">Рабочее пространство</p>
    <RouterLink class="sidebar-link" to="/admin/products">
      <span class="sidebar-icon" aria-hidden="true">▦</span>
      Товары
    </RouterLink>
    <RouterLink class="sidebar-link" to="/admin/products/create">
      <span class="sidebar-icon" aria-hidden="true">＋</span>
      Добавить товар
    </RouterLink>
    <div class="sidebar-divider"></div>
    <RouterLink class="sidebar-link" to="/">
      <span class="sidebar-icon" aria-hidden="true">↗</span>
      Открыть витрину
    </RouterLink>
  </aside>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.sidebar-divider { height: 1px; margin: 20px 12px; background: var(--line); }
</style>
