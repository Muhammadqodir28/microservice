import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import AboutView from '../views/AboutView.vue';
import Main from '@/pages/Main.vue';
import Admin from '@/pages/admin/Admin.vue';
import Products from '@/pages/admin/Products.vue';
import ProductCreate from '@/pages/admin/ProductCreate.vue';
import ProductEdit from '@/pages/admin/ProductEdit.vue';
import LoginView from '@/views/LoginView.vue';
import RegisterView from '@/views/RegisterView.vue';
import ProfileView from '@/views/ProfileView.vue';
import ChatView from '@/views/ChatView.vue';
import { useAuthStore } from '@/stores/auth.ts'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'main',
      component: Main,
    },
    {
      path: '/admin',
      component: Admin,
      meta: { requiresAuth: true },
      children: [
        {path: 'products', component: Products},
        {path: 'products/create', component: ProductCreate},
        {path: 'products/:id/edit', component: ProductEdit},
      ]
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
    },
    {
      path: '/profile',
      name: 'profile',
      component: ProfileView,
      meta: { requiresAuth: true },
    },
    {
      path: '/chat',
      name: 'chat',
      component: ChatView,
      meta: { requiresAuth: true },
    },
  ],
})

router.beforeEach((to) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  return true
})

export default router
