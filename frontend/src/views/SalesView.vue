<template>
  <div class="px-4 py-6 sm:px-0">
    <div class="mb-8 flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Vendas</h2>
        <p class="text-gray-600">Gerencie registros de vendas e comissões</p>
      </div>
      <div>
        <button
          @click="showCreateForm = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
        >
          Adicionar Venda
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg mb-6">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Vendedor</label>
            <div class="relative filter-seller-search-container">
              <input
                v-model="filterSellerSearch"
                @input="searchFilterSellers(filterSellerSearch)"
                @focus="searchFilterSellers(filterSellerSearch)"
                type="text"
                placeholder="Digite pelo menos 3 letras do nome..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <button
                v-if="selectedFilterSeller"
                @click="clearFilterSellerSelection"
                type="button"
                class="absolute right-2 top-2 text-gray-400 hover:text-gray-600"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
              
              <!-- Dropdown de resultados -->
              <div
                v-if="showFilterSellerDropdown"
                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
              >
                <div
                  v-for="seller in filteredFilterSellers"
                  :key="seller.id"
                  @click="selectFilterSeller(seller)"
                  class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0"
                >
                  <div class="font-medium text-gray-900">{{ seller.name }}</div>
                  <div class="text-sm text-gray-500">{{ seller.email }}</div>
                </div>
                <div v-if="filteredFilterSellers.length === 0 && filterSellerSearch.length >= 3" class="px-3 py-2 text-gray-500 text-sm">
                  Nenhum vendedor encontrado
                </div>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Data Inicial</label>
            <input
              v-model="filters.date_from"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Data Final</label>
            <input
              v-model="filters.date_to"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div class="flex items-end">
            <button
              @click="applyFilters"
              class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium"
            >
              Aplicar Filtros
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Sales Table -->
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <div v-if="sales.length === 0" class="text-center py-8 text-gray-500">
          Nenhuma venda encontrada
        </div>
        <div v-else class="overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendedor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comissão</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="sale in sales" :key="sale.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ sale.seller?.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(sale.amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(sale.commission) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(sale.sale_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                  <button
                    @click="editSale(sale)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Editar
                  </button>
                  <button
                    @click="deleteSale(sale)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Excluir
                  </button>
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

    <!-- Create/Edit Sale Modal -->
    <div v-if="showCreateForm || editingSale" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              {{ editingSale ? 'Editar Venda' : 'Criar Nova Venda' }}
            </h3>
            <button
              @click="cancelForm"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="saveSale">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Vendedor <span class="text-red-500">*</span>
              </label>
              <div class="relative seller-search-container">
                <input
                  v-model="sellerSearch"
                  @input="searchSellers(sellerSearch)"
                  @focus="searchSellers(sellerSearch)"
                  type="text"
                  placeholder="Digite pelo menos 3 letras do nome do vendedor..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{ 'border-red-500': !saleForm.seller_id }"
                />
                <button
                  v-if="selectedSeller"
                  @click="clearSellerSelection"
                  type="button"
                  class="absolute right-2 top-2 text-gray-400 hover:text-gray-600"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
                
                <!-- Dropdown de resultados -->
                <div
                  v-if="showSellerDropdown"
                  class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                >
                  <div
                    v-for="seller in filteredSellers"
                    :key="seller.id"
                    @click="selectSeller(seller)"
                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0"
                  >
                    <div class="font-medium text-gray-900">{{ seller.name }}</div>
                    <div class="text-sm text-gray-500">{{ seller.email }}</div>
                  </div>
                  <div v-if="filteredSellers.length === 0" class="px-3 py-2 text-gray-500 text-sm">
                    Nenhum vendedor encontrado
                  </div>
                </div>
              </div>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Valor <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-gray-500">R$</span>
                <input
                  v-model="formattedAmount"
                  @input="formattedAmount = formatAmount(formattedAmount)"
                  type="text"
                  required
                  inputmode="decimal"
                  class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="0,00"
                  :class="{ 'border-red-500': !formattedAmount || parseAmount(formattedAmount) <= 0 }"
                />
              </div>
              <p class="text-xs text-gray-500 mt-1">Digite apenas números. Os 2 últimos dígitos são os centavos (Ex: 12345 = 123,45)</p>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Data da Venda <span class="text-red-500">*</span>
              </label>
              <input
                v-model="saleForm.sale_date"
                type="date"
                required
                :max="new Date().toISOString().split('T')[0]"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': !saleForm.sale_date }"
              />
              <p class="text-xs text-gray-500 mt-1">Não pode ser uma data futura</p>
            </div>
            
            <div v-if="formattedAmount && saleForm.seller_id" class="mb-4 p-3 bg-blue-50 rounded-md">
              <p class="text-sm text-blue-800">
                <strong>Prévia da Comissão:</strong> 
                R$ {{ (parseAmount(formattedAmount) * 0.085).toFixed(2).replace('.', ',') }} 
                (8.5% de R$ {{ parseAmount(formattedAmount).toFixed(2).replace('.', ',') }})
              </p>
            </div>
            
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="cancelForm"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="!saleForm.seller_id || !formattedAmount || !saleForm.sale_date || parseAmount(formattedAmount) <= 0"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
              >
                {{ editingSale ? 'Atualizar Venda' : 'Criar Venda' }}
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
import { saleService } from '@/services/saleService';
import { sellerService } from '@/services/sellerService';
import type { Sale } from '@/services/saleService';

interface Seller {
  id: number;
  name: string;
  email: string;
}

const sales = ref<Sale[]>([]);
const sellers = ref<Seller[]>([]);
const showCreateForm = ref(false);
const editingSale = ref<Sale | null>(null);

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  perPage: 20,
  total: 0,
  from: 0,
  to: 0
});

const filters = ref({
  seller_id: '',
  date_from: '',
  date_to: ''
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

const saleForm = ref({
  seller_id: '',
  amount: '',
  sale_date: ''
});

// Seller search functionality (for create/edit modal)
const sellerSearch = ref('');
const filteredSellers = ref<Seller[]>([]);
const showSellerDropdown = ref(false);
const selectedSeller = ref<Seller | null>(null);

// Seller search functionality (for filters)
const filterSellerSearch = ref('');
const filteredFilterSellers = ref<Seller[]>([]);
const showFilterSellerDropdown = ref(false);
const selectedFilterSeller = ref<Seller | null>(null);

// Amount formatting
const formattedAmount = ref('');

const loadSales = async (page: number = 1) => {
  try {
    const params = new URLSearchParams();
    if (filters.value.seller_id) params.append('seller_id', filters.value.seller_id);
    if (filters.value.date_from) params.append('date_from', filters.value.date_from);
    if (filters.value.date_to) params.append('date_to', filters.value.date_to);

    const response = await saleService.getAll(page, pagination.value.perPage, params.toString());
    
    // Laravel retorna dados paginados em response.data.data
    sales.value = response.data.data || [];
    
    // Atualiza informações de paginação
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
    loadSales(page);
  }
};

const loadSellers = async () => {
  try {
    // Busca todos os sellers sem paginação para os filtros
    const response = await sellerService.getAll(1, 1000);
    sellers.value = response.data.data || response.data;
  } catch (error: any) {
    // Error handling
  }
};

const saveSale = async () => {
  try {
    const saleData = {
      seller_id: parseInt(saleForm.value.seller_id),
      amount: parseAmount(formattedAmount.value),
      sale_date: saleForm.value.sale_date
    };

    if (editingSale.value) {
      await saleService.update(editingSale.value.id, saleData);
    } else {
      await saleService.create(saleData);
    }
    
    await loadSales(pagination.value.currentPage);
    cancelForm();
  } catch (error: any) {
    // Error handling
  }
};

const editSale = (sale: Sale) => {
  editingSale.value = sale;
  const amount = typeof sale.amount === 'string' ? parseFloat(sale.amount) : sale.amount;
  saleForm.value = {
    seller_id: sale.seller_id.toString(),
    amount: amount.toString(),
    sale_date: sale.sale_date
  };
  formattedAmount.value = formatAmount(amount.toString());
  
  // Seleciona o seller no campo
  if (sale.seller) {
    selectedSeller.value = {
      id: sale.seller_id,
      name: sale.seller.name,
      email: sale.seller.email || ''
    };
    sellerSearch.value = sale.seller.name;
  }
};

const cancelForm = () => {
  showCreateForm.value = false;
  editingSale.value = null;
  saleForm.value = {
    seller_id: '',
    amount: '',
    sale_date: ''
  };
  formattedAmount.value = '';
  clearSellerSelection();
};

const deleteSale = async (sale: Sale) => {
  const sellerName = sale.seller?.name || 'Unknown';
  
  if (confirm(`Are you sure you want to delete this sale?\n\nSale ID: ${sale.id}\nSeller: ${sellerName}\nAmount: ${formatCurrency(sale.amount)}\n\nNote: Only the sale will be deleted, the seller will remain in the system.`)) {
    try {
      await saleService.delete(sale.id);
      
      // Se deletou o último item da página e não é a primeira página, volta uma página
      if (sales.value.length === 1 && pagination.value.currentPage > 1) {
        await loadSales(pagination.value.currentPage - 1);
      } else {
        await loadSales(pagination.value.currentPage);
      }
    } catch (error: any) {
      alert(`Error deleting sale: ${error.response?.data?.message || error.message}`);
    }
  }
};

const applyFilters = () => {
  // Volta para a primeira página ao aplicar filtros
  loadSales(1);
};

// Amount formatting functions - Estilo banco digital
const formatAmount = (value: string): string => {
  // Remove tudo exceto números
  const numbersOnly = value.replace(/\D/g, '');
  
  if (!numbersOnly) return '';
  
  // Converte para número e divide por 100 (últimos 2 dígitos = centavos)
  const numberValue = parseInt(numbersOnly, 10);
  const cents = numberValue % 100;
  const reais = Math.floor(numberValue / 100);
  
  // Formata a parte inteira com separador de milhares
  const formattedReais = reais.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  
  // Formata centavos com 2 dígitos
  const formattedCents = cents.toString().padStart(2, '0');
  
  // Retorna no formato brasileiro: 1.234,56
  return `${formattedReais},${formattedCents}`;
};

const parseAmount = (formattedValue: string): number => {
  if (!formattedValue) return 0;
  // Remove separador de milhares e converte vírgula para ponto
  const cleanValue = formattedValue.replace(/\./g, '').replace(',', '.');
  return parseFloat(cleanValue) || 0;
};

// Seller search functions
const searchSellers = (query: string) => {
  if (query.length < 3) {
    filteredSellers.value = [];
    showSellerDropdown.value = false;
    return;
  }
  
  filteredSellers.value = sellers.value.filter(seller => 
    seller.name.toLowerCase().includes(query.toLowerCase())
  );
  showSellerDropdown.value = filteredSellers.value.length > 0;
};

const selectSeller = (seller: Seller) => {
  selectedSeller.value = seller;
  sellerSearch.value = seller.name;
  saleForm.value.seller_id = seller.id.toString();
  showSellerDropdown.value = false;
};

const clearSellerSelection = () => {
  selectedSeller.value = null;
  sellerSearch.value = '';
  saleForm.value.seller_id = '';
  showSellerDropdown.value = false;
};

// Filter seller search functions
const searchFilterSellers = (query: string) => {
  if (query.length < 3) {
    filteredFilterSellers.value = [];
    showFilterSellerDropdown.value = false;
    return;
  }
  
  filteredFilterSellers.value = sellers.value.filter(seller => 
    seller.name.toLowerCase().includes(query.toLowerCase())
  );
  showFilterSellerDropdown.value = true;
};

const selectFilterSeller = (seller: Seller) => {
  selectedFilterSeller.value = seller;
  filterSellerSearch.value = seller.name;
  filters.value.seller_id = seller.id.toString();
  showFilterSellerDropdown.value = false;
};

const clearFilterSellerSelection = () => {
  selectedFilterSeller.value = null;
  filterSellerSearch.value = '';
  filters.value.seller_id = '';
  showFilterSellerDropdown.value = false;
};

const formatDate = (dateString: string) => {
  // Parse the date string as local date (not UTC) to avoid timezone issues
  const [year, month, day] = dateString.split('T')[0].split('-');
  return `${day.padStart(2, '0')}/${month.padStart(2, '0')}/${year}`;
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
  loadSales();
  loadSellers();
  
  // Close dropdowns when clicking outside
  document.addEventListener('click', (event) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.seller-search-container')) {
      showSellerDropdown.value = false;
    }
    if (!target.closest('.filter-seller-search-container')) {
      showFilterSellerDropdown.value = false;
    }
  });
});
</script>
