import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { useAuthStore } from '@/stores/auth.ts'
import { useThemeStore } from '@/stores/theme'

const app = createApp(App)

app.use(createPinia())

const authStore = useAuthStore()
authStore.initializeAuth()
useThemeStore().initialize()

app.use(router)

app.mount('#app')
