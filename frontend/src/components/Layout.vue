<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div class="flex items-center">
            <h1 class="text-3xl font-bold text-gray-900">Gestão de Vendas</h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-500">Bem-vindo, {{ authStore.user?.name }}</span>
            <button
              @click="logout"
              class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium"
            >
              Sair
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-white border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex space-x-4">
          <router-link
            to="/"
            class="border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm rounded-t-md transition-all"
            :class="{ 'border-blue-500 text-blue-600 bg-blue-50': $route.name === 'dashboard' }"
          >
            Dashboard
          </router-link>
          <router-link
            to="/sellers"
            class="border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm rounded-t-md transition-all"
            :class="{ 'border-blue-500 text-blue-600 bg-blue-50': $route.name === 'sellers' }"
          >
            Vendedores
          </router-link>
          <router-link
            to="/sales"
            class="border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm rounded-t-md transition-all"
            :class="{ 'border-blue-500 text-blue-600 bg-blue-50': $route.name === 'sales' }"
          >
            Vendas
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <router-view />
    </main>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const logout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>
