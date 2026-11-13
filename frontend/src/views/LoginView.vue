<template>
  <main class="auth-page">
    <section class="auth-card" aria-labelledby="login-title">
      <div class="brand-mark" aria-hidden="true">M</div>
      <p class="eyebrow">С возвращением</p>
      <h1 id="login-title">Войти в аккаунт</h1>
      <p class="auth-intro">Войдите, чтобы управлять товарами и настройками.</p>

      <form class="auth-form" @submit.prevent="handleLogin">
        <label for="email">Электронная почта</label>
        <input id="email" v-model.trim="form.email" type="email" autocomplete="email" placeholder="name@example.com" required />

        <label for="password">Пароль</label>
        <input id="password" v-model="form.password" type="password" autocomplete="current-password" placeholder="Ваш пароль" required />

        <p v-if="errorMessage" class="auth-message" role="alert">{{ errorMessage }}</p>
        <button type="submit" :disabled="loading">{{ loading ? 'Входим…' : 'Войти' }}</button>
      </form>

      <p class="auth-prompt">Нет аккаунта? <RouterLink to="/register">Создать аккаунт</RouterLink></p>
    </section>
  </main>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.ts'

const authStore = useAuthStore()
const router = useRouter()
const loading = ref(false)
const errorMessage = ref('')
const form = reactive({ email: '', password: '' })

async function handleLogin() {
  loading.value = true
  errorMessage.value = ''
  try {
    await authStore.login(form)
    const redirect = router.currentRoute.value.query.redirect
    const destination = typeof redirect === 'string' && redirect.startsWith('/') && !redirect.startsWith('//')
      ? redirect
      : '/'
    await router.push(destination)
  } catch (error: unknown) {
    if (typeof error === 'string') errorMessage.value = error
    else if (error && typeof error === 'object' && 'message' in error) errorMessage.value = String(error.message)
    else errorMessage.value = 'Не удалось войти. Проверьте данные и попробуйте снова.'
  } finally {
    loading.value = false
  }
}
</script>
