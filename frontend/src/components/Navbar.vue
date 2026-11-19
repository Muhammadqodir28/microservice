<template>
  <header class="admin-header">
    <div class="page-shell admin-header-inner">
      <RouterLink class="brand" to="/" :aria-label="t('home')">
        <span class="brand-mark">M</span>
        <span>microstore <small>STUDIO</small></span>
      </RouterLink>
      <div class="admin-header-actions">
        <LanguageSwitcher />
        <ThemeToggle />
        <RouterLink class="admin-user" to="/chat">{{ t('chat') }}</RouterLink>
        <RouterLink class="admin-user" to="/profile">{{ authStore.user?.name || t('profile') }}</RouterLink>
        <button class="button button-secondary" type="button" @click="logout">{{ t('logout') }}</button>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { t } from '@/i18n'
import { RouterLink, useRouter } from 'vue-router'
import ThemeToggle from '@/components/ThemeToggle.vue'
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'
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
