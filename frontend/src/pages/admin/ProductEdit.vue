<template>
  <section>
    <header class="page-heading">
      <div>
        <p class="eyebrow">Каталог</p>
        <h1>Изменить товар</h1>
        <p>Обновите название или изображение товара.</p>
      </div>
      <RouterLink to="/admin/products" class="button button-secondary">← К списку</RouterLink>
    </header>

    <div v-if="loading" class="status-state">Загружаем товар…</div>
    <div v-else-if="error && !title" class="status-state is-error">{{ error }}</div>
    <form v-else class="admin-form-card" @submit.prevent="submit">
      <div class="field">
        <label for="title">Название товара</label>
        <input id="title" v-model.trim="title" name="title" required />
      </div>
      <div class="field">
        <label for="image">Ссылка на изображение</label>
        <input id="image" v-model.trim="image" name="image" type="url" required />
      </div>
      <button class="button" type="submit" :disabled="saving">{{ saving ? 'Сохраняем…' : 'Сохранить изменения' }}</button>
      <p v-if="error" class="form-error" role="alert">{{ error }}</p>
    </form>
  </section>
</template>

<script setup lang="ts">
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
    if (!response.ok) throw new Error('Не удалось загрузить товар.')
    const product = await response.json()
    title.value = product.title
    image.value = product.image
  } catch (cause: unknown) {
    error.value = cause instanceof Error ? cause.message : 'Не удалось загрузить товар.'
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
    if (!response.ok) throw new Error('Не удалось сохранить изменения.')
    await router.push('/admin/products')
  } catch (cause: unknown) {
    error.value = cause instanceof Error ? cause.message : 'Не удалось сохранить изменения.'
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.form-error { margin: 16px 0 0; color: var(--danger); font-size: 13px; }
</style>
