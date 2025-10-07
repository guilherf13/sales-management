<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-lg shadow-md">
      <div>
        <h2 class="text-3xl font-extrabold text-center text-gray-900">
          {{ isRegisterMode ? 'Criar nova conta' : 'Acessar sua conta' }}
        </h2>
        <p class="mt-2 text-sm text-center text-gray-600">
          {{ isRegisterMode ? 'Cadastre-se para começar a usar o sistema' : 'Entre com suas credenciais' }}
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div class="space-y-4 rounded-md shadow-sm">
          <BaseInput
            v-if="isRegisterMode"
            v-model="name"
            label="Nome completo"
            type="text"
            placeholder="Seu nome completo"
            required
            :error="errors.name"
          />
          <BaseInput
            v-model="email"
            label="E-mail"
            type="email"
            placeholder="seu@email.com"
            required
            :error="errors.email"
          />
          <BaseInput
            v-model="password"
            label="Senha"
            type="password"
            placeholder="Sua senha"
            required
            :error="errors.password"
          />
          <BaseInput
            v-if="isRegisterMode"
            v-model="passwordConfirmation"
            label="Confirmar senha"
            type="password"
            placeholder="Confirme sua senha"
            required
            :error="errors.password_confirmation"
          />
        </div>

        <div v-if="errors.api" class="text-sm text-red-600">
          {{ errors.api }}
        </div>

        <div>
          <BaseButton
            type="submit"
            class="w-full"
            :loading="loading"
          >
            {{ isRegisterMode ? 'Cadastrar' : 'Entrar' }}
          </BaseButton>
        </div>
      </form>

      <div class="text-center">
        <button
          @click="toggleMode"
          class="text-sm text-blue-600 hover:text-blue-500"
        >
          {{ isRegisterMode ? 'Já tem uma conta? Faça login' : 'Não tem uma conta? Cadastre-se' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import BaseInput from '@/components/BaseInput.vue';
import BaseButton from '@/components/BaseButton.vue';
import { useAuthStore } from '@/stores/auth';

const isRegisterMode = ref(false);
const name = ref('');
const email = ref('vendedor@test.com');
const password = ref('password');
const passwordConfirmation = ref('');
const loading = ref(false);
const errors = ref({ 
  name: '', 
  email: '', 
  password: '', 
  password_confirmation: '', 
  api: '' 
});

const authStore = useAuthStore();
const router = useRouter();

const toggleMode = () => {
  isRegisterMode.value = !isRegisterMode.value;
  clearForm();
};

const clearForm = () => {
  name.value = '';
  email.value = '';
  password.value = '';
  passwordConfirmation.value = '';
  errors.value = { name: '', email: '', password: '', password_confirmation: '', api: '' };
};

const handleSubmit = async () => {
  loading.value = true;
  errors.value.api = '';
  
  try {
    if (isRegisterMode.value) {
      await authStore.register({
        name: name.value,
        email: email.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value
      });
    } else {
      await authStore.login({ 
        email: email.value, 
        password: password.value 
      });
    }
    router.push('/');
  } catch (error: any) {
    if (error.response?.data?.errors) {
      // Handle validation errors
      const validationErrors = error.response.data.errors;
      errors.value = {
        name: validationErrors.name?.[0] || '',
        email: validationErrors.email?.[0] || '',
        password: validationErrors.password?.[0] || '',
        password_confirmation: validationErrors.password_confirmation?.[0] || '',
        api: ''
      };
    } else {
      errors.value.api = error.response?.data?.message || 'Ocorreu um erro. Tente novamente.';
    }
  } finally {
    loading.value = false;
  }
};
</script> 