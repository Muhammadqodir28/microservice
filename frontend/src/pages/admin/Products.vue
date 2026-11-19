<template>
  <section>
    <header class="page-heading">
      <div>
        <p class="eyebrow">{{ t('catalog') }}</p>
        <h1>{{ t('products') }}</h1>
        <p>{{ t('manageProducts') }}</p>
      </div>
      <RouterLink to="/admin/products/create" class="button">{{ t('addNewProduct') }}</RouterLink>
    </header>

    <div v-if="loading" class="status-state">{{ t('loadingProducts') }}</div>
    <div v-else-if="error" class="status-state is-error">{{ error }}</div>
    <div v-else-if="products.length === 0" class="empty-state">
      <p>{{ t('noProducts') }}</p>
      <RouterLink to="/admin/products/create" class="button">{{ t('addFirstProduct') }}</RouterLink>
    </div>
    <div v-else class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr><th>{{ t('product') }}</th><th>ID</th><th>{{ t('likesColumn') }}</th><th><span class="sr-only">{{ t('actions') }}</span></th></tr>
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
                <RouterLink :to="`/admin/products/${product.id}/edit`" class="button button-secondary">{{ t('edit') }}</RouterLink>
                <button class="button button-danger" type="button" @click="destroy(product.id)">{{ t('delete') }}</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<script setup lang="ts">
import { t } from '@/i18n'
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import type { ProductChange } from '@/interfaces/product-change'
import type { Product } from '@/interfaces/product.ts'
import echo from '@/realtime/echo'

const products = ref<Product[]>([])
const loading = ref(true)
const error = ref('')
const pendingProductChanges: ProductChange[] = []

async function loadProducts() {
  loading.value = true
  error.value = ''
  try {
    const response = await fetch('http://localhost:8004/api/products')
    if (!response.ok) throw new Error(t('loadProductsError'))
    products.value = await response.json()
    pendingProductChanges.splice(0).forEach(applyProductChange)
  } catch {
    error.value = t('loadProductsError')
  } finally {
    loading.value = false
  }
}

function applyProductChange(change: ProductChange) {
  const index = products.value.findIndex((product) => product.id === change.product.id)
  if (change.type === 'deleted') {
    products.value = products.value.filter((product) => product.id !== change.product.id)
  } else if (index >= 0) {
    products.value[index] = change.product
  } else {
    products.value.unshift(change.product)
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

async function destroy(id: number) {
  if (!confirm(t('deleteConfirm'))) return
  const response = await fetch(`http://localhost:8004/api/products/${id}`, { method: 'DELETE' })
  if (response.ok) products.value = products.value.filter((product) => product.id !== id)
}
</script>

<style scoped>
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; }
.empty-state p { margin: 0 0 18px; }
</style>
