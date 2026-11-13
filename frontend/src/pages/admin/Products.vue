<template>
  <section>
    <header class="page-heading">
      <div>
        <p class="eyebrow">Каталог</p>
        <h1>Товары</h1>
        <p>Управляйте карточками товаров в вашей витрине.</p>
      </div>
      <RouterLink to="/admin/products/create" class="button">＋ Добавить товар</RouterLink>
    </header>

    <div v-if="loading" class="status-state">Загружаем товары…</div>
    <div v-else-if="error" class="status-state is-error">{{ error }}</div>
    <div v-else-if="products.length === 0" class="empty-state">
      <p>В каталоге пока нет товаров.</p>
      <RouterLink to="/admin/products/create" class="button">Добавить первый товар</RouterLink>
    </div>
    <div v-else class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr><th>Товар</th><th>ID</th><th>Отметки</th><th><span class="sr-only">Действия</span></th></tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td>
              <div class="table-product">
                <img v-if="product.image" :src="product.image" :alt="''" />
                <span v-else class="table-product-placeholder" aria-hidden="true">✦</span>
                <span>{{ product.title }}</span>
              </div>
            </td>
            <td>#{{ product.id }}</td>
            <td>♥ {{ product.likes }}</td>
            <td>
              <div class="table-actions">
                <RouterLink :to="`/admin/products/${product.id}/edit`" class="button button-secondary">Изменить</RouterLink>
                <button class="button button-danger" type="button" @click="destroy(product.id)">Удалить</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import type { Product } from '@/interfaces/product.ts'

const products = ref<Product[]>([])
const loading = ref(true)
const error = ref('')

async function loadProducts() {
  loading.value = true
  error.value = ''
  try {
    const response = await fetch('http://localhost:8004/api/products')
    if (!response.ok) throw new Error('Не удалось загрузить товары.')
    products.value = await response.json()
  } catch {
    error.value = 'Не удалось загрузить товары. Проверьте подключение к серверу.'
  } finally {
    loading.value = false
  }
}

onMounted(loadProducts)

async function destroy(id: number) {
  if (!confirm('Удалить этот товар?')) return
  const response = await fetch(`http://localhost:8004/api/products/${id}`, { method: 'DELETE' })
  if (response.ok) products.value = products.value.filter((product) => product.id !== id)
}
</script>

<style scoped>
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; }
.empty-state p { margin: 0 0 18px; }
</style>
