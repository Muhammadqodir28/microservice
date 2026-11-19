<template>
  <section>
    <header class="page-heading">
      <div>
        <p class="eyebrow">{{ t('catalog') }}</p>
        <h1>{{ t('newProduct') }}</h1>
        <p>{{ t('newProductIntro') }}</p>
      </div>
      <RouterLink to="/admin/products" class="button button-secondary">{{ t('backToList') }}</RouterLink>
    </header>

    <form class="admin-form-card" @submit.prevent="submit">
      <div class="field">
        <label for="title">{{ t('productTitle') }}</label>
        <input id="title" v-model.trim="title" name="title" :placeholder="t('exampleProduct')" required />
      </div>
      <div class="field">
        <label for="image">{{ t('imageUrl') }}</label>
        <input id="image" v-model.trim="image" name="image" type="url" placeholder="https://example.com/image.jpg" required />
      </div>
      <button class="button" type="submit" :disabled="saving">{{ saving ? t('saving') : t('saveProduct') }}</button>
      <p v-if="error" class="form-error" role="alert">{{ error }}</p>
    </form>
  </section>
</template>

<script setup lang="ts">
import { t } from '@/i18n'
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'

const title = ref('')
const image = ref('')
const saving = ref(false)
const error = ref('')
const router = useRouter()

async function submit() {
  saving.value = true
  error.value = ''
  try {
    const response = await fetch('http://localhost:8004/api/products', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ title: title.value, image: image.value }),
    })
    if (!response.ok) throw new Error(t('saveProductError'))
    await router.push('/admin/products')
  } catch (cause: unknown) {
    error.value = cause instanceof Error ? cause.message : t('saveProductError')
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.form-error { margin: 16px 0 0; color: var(--danger); font-size: 13px; }
</style>
