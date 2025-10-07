<template>
  <div id="app">
    <router-view />
    <NotificationToast />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import NotificationToast from '@/components/NotificationToast.vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

onMounted(() => {
  authStore.initializeAuth();
});
</script>

<style>
#app {
  min-height: 100vh;
}
</style>