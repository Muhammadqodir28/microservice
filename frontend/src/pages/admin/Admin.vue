<template>
  <div class="admin-app">
    <Navbar />
    <div class="admin-layout">
      <Menu />
      <main class="admin-main">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { RouterView } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import Menu from '@/components/Menu.vue'
</script>
