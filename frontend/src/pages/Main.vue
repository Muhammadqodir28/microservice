<template>
  <div class="storefront">
    <header class="store-header">
      <div class="page-shell store-header-inner">
        <RouterLink class="brand" to="/" aria-label="Microstore — главная">
          <span class="brand-mark">M</span>
          <span>microstore</span>
        </RouterLink>
        <nav class="store-nav" aria-label="Основная навигация">
          <template v-if="authStore.isAuthenticated">
            <RouterLink to="/admin/products">Панель управления</RouterLink>
            <button class="nav-signout" type="button" @click="logout">Выйти</button>
          </template>
          <template v-else>
            <RouterLink to="/login">Войти</RouterLink>
            <RouterLink class="button" to="/register">Создать аккаунт</RouterLink>
          </template>
        </nav>
      </div>
    </header>

    <main class="page-shell page-content">
      <section class="store-hero">
        <p class="eyebrow">Подборка сообщества</p>
        <h1>Вещи, которые хочется сохранить.</h1>
        <p>Открывайте интересные находки и делитесь тем, что нравится.</p>
      </section>

      <div class="store-section-heading">
        <div>
          <p class="eyebrow">Каталог</p>
          <h2>Популярные товары</h2>
        </div>
        <span v-if="!loading && !error">{{ products.length }} товаров</span>
      </div>

      <div v-if="loading" class="status-state">Загружаем каталог…</div>
      <div v-else-if="error" class="status-state is-error">{{ error }}</div>
      <div v-else-if="products.length === 0" class="empty-state">
        Пока здесь пусто. Загляните позже — скоро появятся новые товары.
      </div>
      <div v-else class="product-grid">
        <article v-for="product in products" :key="product.id" class="product-card">
          <img v-if="product.image" class="product-image" :src="product.image" :alt="product.title" />
          <div v-else class="product-image-placeholder" aria-hidden="true">✦</div>
          <div class="product-card-body">
            <h3 class="product-card-title">{{ product.title }}</h3>
            <div class="product-card-meta">
              <button class="like-button" type="button" @click="like(product.id)">♡ Нравится</button>
              <span>♥ {{ product.likes }} отметок</span>
            </div>
          </div>
        </article>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import type { Product } from '@/interfaces/product.ts'
import { useAuthStore } from '@/stores/auth.ts'

const authStore = useAuthStore()
const products = ref<Product[]>([])
const loading = ref(true)
const error = ref('')

async function logout() {
  await authStore.logout()
}

onMounted(async () => {
  try {
    const response = await fetch('http://localhost:8004/api/products')
    if (!response.ok) throw new Error('Не удалось загрузить каталог.')
    products.value = await response.json()
  } catch {
    error.value = 'Не удалось загрузить каталог. Проверьте подключение и попробуйте ещё раз.'
  } finally {
    loading.value = false
  }
})

async function like(id: number) {
  try {
    const response = await fetch(`http://localhost:8005/api/products/${id}/like`, { method: 'POST' })
    if (!response.ok) throw new Error('Не удалось отметить товар.')
    const product = products.value.find((item) => item.id === id)
    if (product) product.likes += 1
  } catch {
    error.value = 'Не удалось поставить отметку. Попробуйте ещё раз.'
  }
}
</script>
