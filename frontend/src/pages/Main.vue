<template>
  <div class="storefront">
    <header class="store-header">
      <div class="page-shell store-header-inner">
        <RouterLink class="brand" to="/" :aria-label="t('home')">
          <span class="brand-mark">M</span>
          <span>microstore</span>
        </RouterLink>
        <nav class="store-nav" :aria-label="t('storefront')">
          <LanguageSwitcher />
          <ThemeToggle />
          <template v-if="authStore.isAuthenticated">
            <RouterLink to="/chat">{{ t('chat') }}</RouterLink>
            <RouterLink to="/profile">{{ t('myProfile') }}</RouterLink>
            <RouterLink to="/admin/products">{{ t('products') }}</RouterLink>
            <button class="nav-signout" type="button" @click="logout">{{ t('logout') }}</button>
          </template>
          <template v-else>
            <RouterLink to="/login">{{ t('login') }}</RouterLink>
            <RouterLink class="button" to="/register">{{ t('register') }}</RouterLink>
          </template>
        </nav>
      </div>
    </header>

    <main class="page-shell page-content">
      <section class="store-hero">
        <p class="eyebrow">{{ t('community') }}</p>
        <h1>{{ t('heroTitle') }}</h1>
        <p>{{ t('heroText') }}</p>
      </section>

      <div class="store-section-heading">
        <div>
          <p class="eyebrow">{{ t('catalog') }}</p>
          <h2>{{ t('popularProducts') }}</h2>
        </div>
        <span v-if="!loading && !error">{{ t('productCount', { count: products.length }) }}</span>
      </div>

      <div v-if="loading" class="status-state">{{ t('loadingCatalog') }}</div>
      <div v-else-if="error" class="status-state is-error">{{ error }}</div>
      <div v-else-if="products.length === 0" class="empty-state">
        {{ t('emptyCatalog') }}
      </div>
      <div v-else class="product-grid">
        <article
          v-for="(product, index) in products"
          :key="product.id"
          class="product-card"
        >
          <img v-if="product.image" class="product-image" :src="product.image" :alt="product.title" />
          <div v-else class="product-image-placeholder" aria-hidden="true">✦</div>
          <div class="product-card-body">
            <h3 class="product-card-title">{{ product.title }}</h3>
            <div class="product-card-meta">
              <button
                class="like-button"
                :class="{ 'is-liked': likedIds.has(product.id) }"
                type="button"
                :disabled="likingIds.has(product.id) || likedIds.has(product.id)"
                @click="like(product.id)"
              >
                {{ likingIds.has(product.id) ? t('saving') : likedIds.has(product.id) ? t('like') : t('like') }}
              </button>
              <span>♥ {{ t('likes', { count: product.likes }) }}</span>
            </div>
            <p v-if="likeErrors[product.id]" class="like-feedback" role="alert">{{ likeErrors[product.id] }}</p>
          </div>
        </article>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { t } from '@/i18n'
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import ThemeToggle from '@/components/ThemeToggle.vue'
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'
import type { ProductChange } from '@/interfaces/product-change'
import type { Product } from '@/interfaces/product.ts'
import echo from '@/realtime/echo'
import { useAuthStore } from '@/stores/auth.ts'

const authStore = useAuthStore()
const products = ref<Product[]>([])
const loading = ref(true)
const error = ref('')
const likingIds = reactive(new Set<number>())
const likedIds = reactive(new Set<number>())
const likeErrors = reactive<Record<number, string>>({})

async function logout() {
  await authStore.logout()
}

const pendingProductChanges: ProductChange[] = []

function applyProductChange(change: ProductChange) {
  const index = products.value.findIndex((product) => product.id === change.product.id)
  if (change.type === 'deleted') {
    products.value = products.value.filter((product) => product.id !== change.product.id)
    return
  }

  if (index >= 0) products.value[index] = change.product
  else products.value.unshift(change.product)
}

async function loadProducts() {
  try {
    const response = await fetch('http://localhost:8004/api/products')
    if (!response.ok) throw new Error(t('loadCatalogError'))
    products.value = await response.json()
    pendingProductChanges.splice(0).forEach(applyProductChange)
  } catch {
    error.value = t('loadCatalogError')
  } finally {
    loading.value = false
  }
}

function handleProductChange(change: ProductChange) {
  if (loading.value) pendingProductChanges.push(change)
  else applyProductChange(change)
}

onMounted(() => {
  echo.channel('products').listen('.product.changed', handleProductChange)
  void loadProducts()
})

onBeforeUnmount(() => echo.leave('products'))

async function like(id: number) {
  if (!authStore.isAuthenticated) {
    likeErrors[id] = t('loginToLike')
    return
  }

  if (likingIds.has(id) || likedIds.has(id)) return
  delete likeErrors[id]
  likingIds.add(id)
  try {
    const response = await fetch(`http://localhost:8005/api/products/${id}/like`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    const result = await response.json().catch(() => ({})) as { message?: string }
    if (!response.ok) {
      if (result.message?.toLowerCase().includes('already liked')) {
        likedIds.add(id)
        likeErrors[id] = t('alreadyLiked')
        return
      }
      throw new Error(result.message || t('likeError'))
    }
    likedIds.add(id)
  } catch {
    likeErrors[id] = t('likeError')
  } finally {
    likingIds.delete(id)
  }
}
</script>
