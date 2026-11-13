<template>
  <main class="auth-page">
    <section class="auth-card" aria-labelledby="register-title">
      <div class="brand-mark" aria-hidden="true">M</div>
      <p class="eyebrow">Начните работу</p>
      <h1 id="register-title">Создать аккаунт</h1>
      <p class="auth-intro">Зарегистрируйтесь, чтобы получить доступ к панели управления.</p>

      <form class="auth-form" @submit.prevent="handleRegister" novalidate>
        <label for="name">Имя</label>
        <input
          id="name"
          v-model.trim="form.name"
          type="text"
          autocomplete="name"
          placeholder="Ваше имя"
          required
          maxlength="255"
        />

        <label for="email">Электронная почта</label>
        <input
          id="email"
          v-model.trim="form.email"
          type="email"
          autocomplete="email"
          placeholder="name@example.com"
          required
        />

        <label for="password">Пароль</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          autocomplete="new-password"
          placeholder="Не менее 6 символов"
          required
          minlength="6"
        />

        <label for="password-confirmation">Подтвердите пароль</label>
        <input
          id="password-confirmation"
          v-model="form.password_confirmation"
          type="password"
          autocomplete="new-password"
          placeholder="Введите пароль ещё раз"
          required
          minlength="6"
        />

        <p v-if="errorMessage" class="auth-message" role="alert">{{ errorMessage }}</p>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Создаём аккаунт…' : 'Зарегистрироваться' }}
        </button>
      </form>

      <p class="auth-prompt">
        Уже есть аккаунт?
        <RouterLink to="/login">Войти</RouterLink>
      </p>
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
    return firstFieldError || response.message || 'Не удалось создать аккаунт. Проверьте данные и попробуйте снова.'
  }
  return 'Не удалось создать аккаунт. Попробуйте ещё раз.'
}

async function handleRegister() {
  errorMessage.value = ''

  if (form.password !== form.password_confirmation) {
    errorMessage.value = 'Пароли не совпадают.'
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
