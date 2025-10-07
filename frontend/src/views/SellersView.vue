<template>
  <div class="px-4 py-6 sm:px-0">
    <div class="mb-8 flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Vendedores</h2>
        <p class="text-gray-600">Gerencie vendedores e suas informações</p>
      </div>
      <div>
        <button
          @click="showCreateForm = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
        >
          Adicionar Vendedor
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
      <div class="flex gap-4">
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar por nome ou email..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @input="applyFilters"
          />
        </div>
        <div class="flex items-end">
          <button
            v-if="hasActiveFilters"
            @click="clearFilters"
            class="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md"
          >
            Limpar
          </button>
        </div>
      </div>
      <div v-if="hasActiveFilters" class="mt-4 text-sm text-gray-600">
        Mostrando resultados filtrados
      </div>
    </div>

    <!-- Sellers Table -->
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <div v-if="sellers.length === 0" class="text-center py-8 text-gray-500">
          Nenhum vendedor encontrado
        </div>
        <div v-else class="overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total de Vendas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comissão Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="seller in sellers" :key="seller.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ seller.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ seller.email }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ seller.sales_count || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(seller.total_commission || 0) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center space-x-3">
                    <button
                      @click="editSeller(seller)"
                      title="Editar"
                      class="text-blue-600 hover:text-blue-900 transition-colors"
                    >
                      <PencilSquareIcon class="h-5 w-5" />
                    </button>
                    <button
                      @click="resendCommissionEmail(seller)"
                      title="Reenviar Email"
                      class="text-purple-600 hover:text-purple-900 transition-colors"
                    >
                      <EnvelopeIcon class="h-5 w-5" />
                    </button>
                    <button
                      @click="deleteSeller(seller)"
                      title="Excluir"
                      class="text-red-600 hover:text-red-900 transition-colors"
                    >
                      <TrashIcon class="h-5 w-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div v-if="pagination.total > pagination.perPage" class="mt-6 flex items-center justify-between border-t border-gray-200 pt-4">
          <div class="flex items-center text-sm text-gray-700">
            <span>
              Mostrando 
              <span class="font-medium">{{ pagination.from }}</span>
              a
              <span class="font-medium">{{ pagination.to }}</span>
              de
              <span class="font-medium">{{ pagination.total }}</span>
              resultados
            </span>
          </div>
          <div class="flex space-x-2">
            <button
              @click="goToPage(1)"
              :disabled="pagination.currentPage === 1"
              :class="[
                'px-3 py-2 text-sm font-medium rounded-md',
                pagination.currentPage === 1
                  ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                  : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
              ]"
            >
              Primeira
            </button>
            <button
              @click="goToPage(pagination.currentPage - 1)"
              :disabled="pagination.currentPage === 1"
              :class="[
                'px-3 py-2 text-sm font-medium rounded-md',
                pagination.currentPage === 1
                  ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                  : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
              ]"
            >
              Anterior
            </button>
            
            <div class="flex space-x-1">
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="goToPage(page)"
                :class="[
                  'px-4 py-2 text-sm font-medium rounded-md',
                  page === pagination.currentPage
                    ? 'bg-blue-600 text-white'
                    : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                ]"
              >
                {{ page }}
              </button>
            </div>
            
            <button
              @click="goToPage(pagination.currentPage + 1)"
              :disabled="pagination.currentPage === pagination.lastPage"
              :class="[
                'px-3 py-2 text-sm font-medium rounded-md',
                pagination.currentPage === pagination.lastPage
                  ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                  : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
              ]"
            >
              Próxima
            </button>
            <button
              @click="goToPage(pagination.lastPage)"
              :disabled="pagination.currentPage === pagination.lastPage"
              :class="[
                'px-3 py-2 text-sm font-medium rounded-md',
                pagination.currentPage === pagination.lastPage
                  ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                  : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
              ]"
            >
              Última
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Seller Modal -->
    <div v-if="showCreateForm || editingSeller" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ editingSeller ? 'Editar Vendedor' : 'Criar Novo Vendedor' }}
          </h3>
          <form @submit.prevent="saveSeller">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
              <input
                v-model="sellerForm.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Nome do vendedor"
              />
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input
                v-model="sellerForm.email"
                type="email"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="vendedor@exemplo.com"
              />
            </div>
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="cancelForm"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md"
              >
                Cancelar
              </button>
              <button
                type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md"
              >
                {{ editingSeller ? 'Atualizar' : 'Criar' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { PencilSquareIcon, EnvelopeIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { sellerService } from '@/services/sellerService';
import { saleService } from '@/services/saleService';
import type { Seller } from '@/services/sellerService';
import { useToast } from 'vue-toastification';

const toast = useToast();
const sellers = ref<Seller[]>([]);
const showCreateForm = ref(false);
const editingSeller = ref<Seller | null>(null);

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  perPage: 20,
  total: 0,
  from: 0,
  to: 0
});

const sellerForm = ref({
  name: '',
  email: ''
});

const searchQuery = ref('');

let filterTimeout: ReturnType<typeof setTimeout> | null = null;

const hasActiveFilters = computed(() => {
  return searchQuery.value !== '';
});

const visiblePages = computed(() => {
  const pages = [];
  const current = pagination.value.currentPage;
  const last = pagination.value.lastPage;
  
  // Mostra até 5 páginas ao redor da página atual
  let start = Math.max(1, current - 2);
  let end = Math.min(last, current + 2);
  
  // Ajusta para sempre mostrar 5 páginas quando possível
  if (end - start < 4) {
    if (start === 1) {
      end = Math.min(last, start + 4);
    } else if (end === last) {
      start = Math.max(1, end - 4);
    }
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
});

const loadSellers = async (page: number = 1) => {
  try {
    // Envia o parâmetro de busca apenas se não estiver vazio
    const search = searchQuery.value.trim() || undefined;
    
    const response = await sellerService.getAll(page, pagination.value.perPage, search);
    
    // Laravel retorna dados paginados em response.data.data
    sellers.value = response.data.data || [];
    
    // Atualiza informações de paginação
    // Laravel pode retornar em dois formatos diferentes
    if (response.data.meta) {
      // Formato com meta
      pagination.value = {
        currentPage: response.data.meta.current_page,
        lastPage: response.data.meta.last_page,
        perPage: response.data.meta.per_page,
        total: response.data.meta.total,
        from: response.data.meta.from || 0,
        to: response.data.meta.to || 0
      };
    } else {
      // Formato direto
      pagination.value = {
        currentPage: response.data.current_page || 1,
        lastPage: response.data.last_page || 1,
        perPage: response.data.per_page || 20,
        total: response.data.total || 0,
        from: response.data.from || 0,
        to: response.data.to || 0
      };
    }
  } catch (error: any) {
    // Error handling
  }
};

const goToPage = (page: number) => {
  if (page >= 1 && page <= pagination.value.lastPage) {
    loadSellers(page);
  }
};

const applyFilters = () => {
  // Debounce - aguarda 500ms após o usuário parar de digitar
  if (filterTimeout) {
    clearTimeout(filterTimeout);
  }
  filterTimeout = setTimeout(() => {
    loadSellers(1); // Volta para a primeira página ao aplicar filtros
  }, 500);
};

const clearFilters = () => {
  searchQuery.value = '';
  loadSellers(1);
};

const saveSeller = async () => {
  try {
    if (editingSeller.value) {
      await sellerService.update(editingSeller.value.id, sellerForm.value);
    } else {
      await sellerService.create(sellerForm.value);
    }
    
    await loadSellers(pagination.value.currentPage);
    cancelForm();
  } catch (error: any) {
    // Error handling
  }
};

const editSeller = (seller: Seller) => {
  editingSeller.value = seller;
  sellerForm.value = {
    name: seller.name,
    email: seller.email
  };
};

const cancelForm = () => {
  showCreateForm.value = false;
  editingSeller.value = null;
  sellerForm.value = {
    name: '',
    email: ''
  };
};

const deleteSeller = async (seller: Seller) => {
  if (confirm(`Are you sure you want to delete ${seller.name}?`)) {
    try {
      await sellerService.delete(seller.id);
      
      // Se deletou o último item da página e não é a primeira página, volta uma página
      if (sellers.value.length === 1 && pagination.value.currentPage > 1) {
        await loadSellers(pagination.value.currentPage - 1);
      } else {
        await loadSellers(pagination.value.currentPage);
      }
    } catch (error: any) {
      // Error handling
    }
  }
};

const resendCommissionEmail = async (seller: Seller) => {
  try {
    await saleService.resendCommissionEmail(seller.id);
    toast.success('Email enviado', {
      timeout: 5000
    });
  } catch (error: any) {
    toast.error('Erro ao agendar e-mail. Tente novamente.', {
      timeout: 5000
    });
  }
};

const formatCurrency = (value: number | string) => {
  const numValue = typeof value === 'string' ? parseFloat(value) : value;
  return numValue.toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

onMounted(() => {
  loadSellers();
});
</script>
