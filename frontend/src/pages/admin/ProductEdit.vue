<template>
  <section>
    <header class="page-heading">
      <div>
        <p class="eyebrow">{{ t('catalog') }}</p>
        <h1>{{ t('editProduct') }}</h1>
        <p>{{ t('editProductIntro') }}</p>
      </div>
      <RouterLink to="/admin/products" class="button button-secondary">{{ t('backToList') }}</RouterLink>
    </header>

    <div v-if="loading" class="status-state">{{ t('loadingProduct') }}</div>
    <div v-else-if="error && !title" class="status-state is-error">{{ error }}</div>
    <form v-else class="admin-form-card" @submit.prevent="submit">
      <div class="field">
        <label for="title">{{ t('productTitle') }}</label>
        <input id="title" v-model.trim="title" name="title" required />
      </div>
      <div class="field">
        <label for="image">{{ t('imageUrl') }}</label>
        <input id="image" v-model.trim="image" name="image" type="url" required />
      </div>
      <button class="button" type="submit" :disabled="saving">{{ saving ? t('saving') : t('saveChanges') }}</button>
      <p v-if="error" class="form-error" role="alert">{{ error }}</p>
    </form>
  </section>
</template>

<script setup lang="ts">
import { t } from '@/i18n'
import { onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'

const title = ref('')
const image = ref('')
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const route = useRoute()
const router = useRouter()

onMounted(async () => {
  try {
    const response = await fetch(`http://localhost:8004/api/products/${route.params.id}`)
    if (!response.ok) throw new Error(t('loadProductError'))
    const product = await response.json()
    title.value = product.title
    image.value = product.image
  } catch (cause: unknown) {
    error.value = cause instanceof Error ? cause.message : t('loadProductError')
  } finally {
    loading.value = false
  }
})

async function submit() {
  saving.value = true
  error.value = ''
  try {
    const response = await fetch(`http://localhost:8004/api/products/${route.params.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ title: title.value, image: image.value }),
    })
    if (!response.ok) throw new Error(t('saveChangesError'))
    await router.push('/admin/products')
  } catch (cause: unknown) {
    error.value = cause instanceof Error ? cause.message : t('saveChangesError')
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.form-error { margin: 16px 0 0; color: var(--danger); font-size: 13px; }
</style>
