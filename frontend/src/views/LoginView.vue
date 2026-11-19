<template>
  <main class="auth-page">
    <section class="auth-card" aria-labelledby="login-title">
      <div class="auth-topline">
        <div class="brand-mark" aria-hidden="true">M</div>
        <LanguageSwitcher />
        <ThemeToggle />
      </div>
      <p class="eyebrow">{{ t('welcomeBack') }}</p>
      <h1 id="login-title">{{ t('loginTitle') }}</h1>
      <p class="auth-intro">{{ t('loginIntro') }}</p>

      <form class="auth-form" @submit.prevent="handleLogin">
        <label for="email">{{ t('email') }}</label>
        <input id="email" v-model.trim="form.email" type="email" autocomplete="email" :placeholder="t('email')" required />

        <label for="password">{{ t('password') }}</label>
        <input id="password" v-model="form.password" type="password" autocomplete="current-password" :placeholder="t('yourPassword')" required />

        <p v-if="errorMessage" class="auth-message" role="alert">{{ errorMessage }}</p>
        <button type="submit" :disabled="loading">{{ loading ? t('signingIn') : t('login') }}</button>
      </form>

      <p class="auth-prompt">{{ t('noAccount') }} <RouterLink to="/register">{{ t('register') }}</RouterLink></p>
    </section>
  </main>
</template>

<script setup lang="ts">
import { t } from '@/i18n'
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import ThemeToggle from '@/components/ThemeToggle.vue'
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'
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
    else errorMessage.value = t('loginError')
  } finally {
    loading.value = false
  }
}
</script>
