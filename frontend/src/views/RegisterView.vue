<template>
  <main class="auth-page">
    <section class="auth-card" aria-labelledby="register-title">
      <div class="auth-topline">
        <div class="brand-mark" aria-hidden="true">M</div>
        <LanguageSwitcher />
        <ThemeToggle />
      </div>
      <p class="eyebrow">{{ t('getStarted') }}</p>
      <h1 id="register-title">{{ t('register') }}</h1>
      <p class="auth-intro">{{ t('registerIntro') }}</p>

      <form class="auth-form" @submit.prevent="handleRegister" novalidate>
        <label for="name">{{ t('name') }}</label>
        <input
          id="name"
          v-model.trim="form.name"
          type="text"
          autocomplete="name"
          :placeholder="t('yourName')"
          required
          maxlength="255"
        />

        <label for="email">{{ t('email') }}</label>
        <input
          id="email"
          v-model.trim="form.email"
          type="email"
          autocomplete="email"
          :placeholder="t('email')"
          required
        />

        <label for="password">{{ t('password') }}</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          autocomplete="new-password"
          :placeholder="t('passwordMin6')"
          required
          minlength="6"
        />

        <label for="password-confirmation">{{ t('confirmPassword') }}</label>
        <input
          id="password-confirmation"
          v-model="form.password_confirmation"
          type="password"
          autocomplete="new-password"
          :placeholder="t('repeatPassword')"
          required
          minlength="6"
        />

        <p v-if="errorMessage" class="auth-message" role="alert">{{ errorMessage }}</p>

        <button type="submit" :disabled="loading">
          {{ loading ? t('creatingAccount') : t('signUp') }}
        </button>
      </form>

      <p class="auth-prompt">
        {{ t('alreadyAccount') }}
        <RouterLink to="/login">{{ t('login') }}</RouterLink>
      </p>
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

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

function getErrorMessage(error: unknown): string {
  if (typeof error === 'string') return error
  if (error && typeof error === 'object') {
    const response = error as { message?: string; errors?: Record<string, string[]> }
    const firstFieldError = Object.values(response.errors ?? {})[0]?.[0]
    return firstFieldError || response.message || t('registerError')
  }
  return t('registerError')
}

async function handleRegister() {
  errorMessage.value = ''

  if (form.password !== form.password_confirmation) {
    errorMessage.value = t('passwordMismatch')
    return
  }

  loading.value = true
  try {
    await authStore.register({ ...form })
    const redirect = router.currentRoute.value.query.redirect
    const destination = typeof redirect === 'string' && redirect.startsWith('/') && !redirect.startsWith('//')
      ? redirect
      : '/'
    await router.push(destination)
  } catch (error: unknown) {
    errorMessage.value = getErrorMessage(error)
  } finally {
    loading.value = false
  }
}
</script>
