<template>
  <header class="admin-header">
    <div class="page-shell admin-header-inner">
      <RouterLink class="brand" to="/" aria-label="Microstore — главная">
        <span class="brand-mark">M</span>
        <span>microstore <small>STUDIO</small></span>
      </RouterLink>
      <div class="admin-header-actions">
        <span class="admin-user">Панель управления</span>
        <button class="button button-secondary" type="button" @click="logout">Выйти</button>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.ts'

const authStore = useAuthStore()
const router = useRouter()

async function logout() {
  await authStore.logout()
  await router.push({ name: 'login' })
}
</script>

<style scoped>
.brand small { margin-left: 5px; color: #98a1b3; font-size: 9px; font-weight: 750; letter-spacing: .12em; }
</style>
