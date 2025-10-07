<template>
  <div class="bg-white shadow rounded-lg p-6">
    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">Comparação Mensal</h3>
    
    <!-- Gráfico de Barras Agrupadas -->
    <div class="mb-8">
      <div class="relative" style="height: 400px;">
        <Bar :data="chartData" :options="chartOptions" />
      </div>
    </div>

    <!-- Legenda com valores exatos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div v-for="metric in metricsLegend" :key="metric.label" class="text-center p-4 bg-gray-50 rounded-lg">
        <p class="text-xs text-gray-500 mb-2">{{ metric.label }}</p>
        <div class="space-y-1">
          <div class="flex items-center justify-center text-sm">
            <span class="w-3 h-3 rounded-full mr-2" :style="{ backgroundColor: metric.currentColor }"></span>
            <span class="font-medium text-gray-900">{{ metric.currentValue }}</span>
          </div>
          <div class="flex items-center justify-center text-sm">
            <span class="w-3 h-3 rounded-full mr-2" :style="{ backgroundColor: metric.previousColor }"></span>
            <span class="text-gray-600">{{ metric.previousValue }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Comparação Percentual -->
    <div class="pt-6 border-t border-gray-200">
      <h4 class="text-sm font-medium text-gray-900 mb-4">Variação em relação ao mês anterior</h4>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="comparison in comparisons" :key="comparison.label" class="text-center">
          <p class="text-xs text-gray-500 mb-1">{{ comparison.label }}</p>
          <p class="text-lg font-semibold" :class="comparison.isPositive ? 'text-green-600' : 'text-red-600'">
            <span v-if="comparison.isPositive">+</span>{{ comparison.percentage }}%
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
  ChartOptions
} from 'chart.js';

// Registrar componentes do Chart.js
ChartJS.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend);

interface MonthStats {
  sellers: number;
  sales: number;
  revenue: number;
  commission: number;
}

interface Props {
  currentMonth: MonthStats;
  previousMonth: MonthStats;
}

const props = defineProps<Props>();

// Cores para o gráfico (mês atual e mês anterior)
const currentMonthColor = 'rgb(59, 130, 246)';   // Azul vibrante
const previousMonthColor = 'rgba(156, 163, 175, 0.7)';  // Cinza

// Configurações do gráfico
const chartOptions: ChartOptions<'bar'> = {
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y', // Barras horizontais
  scales: {
    x: {
      beginAtZero: true,
      grid: {
        display: true,
        color: 'rgba(0, 0, 0, 0.05)'
      },
      ticks: {
        callback: function(value) {
          // Formatar valores grandes com 'k' para milhares
          const num = Number(value);
          if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'k';
          }
          return num.toString();
        }
      }
    },
    y: {
      grid: {
        display: false
      }
    }
  },
  plugins: {
    legend: {
      display: true,
      position: 'top',
      labels: {
        boxWidth: 12,
        padding: 15,
        font: {
          size: 12
        }
      }
    },
    tooltip: {
      callbacks: {
        label: function(context) {
          const label = context.dataset.label || '';
          const value = context.parsed.x;
          // Formatar valor dependendo da categoria
          const categoryIndex = context.dataIndex;
          if (categoryIndex >= 2) { // Revenue e Commission
            return `${label}: ${formatCurrency(value)}`;
          }
          return `${label}: ${value}`;
        }
      }
    }
  }
};

// Formatar moeda
const formatCurrency = (value: number) => {
  return value.toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

// Converter valores para números
const toNumber = (value: number | string): number => {
  return typeof value === 'string' ? parseFloat(value) : value;
};

// Dados do gráfico de barras agrupadas
const chartData = computed(() => ({
  labels: ['Vendedores', 'Vendas', 'Receita', 'Comissão'],
  datasets: [
    {
      label: 'Mês Atual',
      data: [
        props.currentMonth.sellers,
        props.currentMonth.sales,
        toNumber(props.currentMonth.revenue),
        toNumber(props.currentMonth.commission)
      ],
      backgroundColor: currentMonthColor,
      borderColor: currentMonthColor,
      borderWidth: 0,
      borderRadius: 4,
      maxBarThickness: 40
    },
    {
      label: 'Mês Anterior',
      data: [
        props.previousMonth.sellers,
        props.previousMonth.sales,
        toNumber(props.previousMonth.revenue),
        toNumber(props.previousMonth.commission)
      ],
      backgroundColor: previousMonthColor,
      borderColor: previousMonthColor,
      borderWidth: 0,
      borderRadius: 4,
      maxBarThickness: 40
    }
  ]
}));

// Legenda com valores para todas as métricas
const metricsLegend = computed(() => [
  {
    label: 'Vendedores',
    currentValue: props.currentMonth.sellers.toString(),
    previousValue: props.previousMonth.sellers.toString(),
    currentColor: currentMonthColor,
    previousColor: previousMonthColor
  },
  {
    label: 'Vendas',
    currentValue: props.currentMonth.sales.toString(),
    previousValue: props.previousMonth.sales.toString(),
    currentColor: currentMonthColor,
    previousColor: previousMonthColor
  },
  {
    label: 'Receita',
    currentValue: formatCurrency(toNumber(props.currentMonth.revenue)),
    previousValue: formatCurrency(toNumber(props.previousMonth.revenue)),
    currentColor: currentMonthColor,
    previousColor: previousMonthColor
  },
  {
    label: 'Comissão',
    currentValue: formatCurrency(toNumber(props.currentMonth.commission)),
    previousValue: formatCurrency(toNumber(props.previousMonth.commission)),
    currentColor: currentMonthColor,
    previousColor: previousMonthColor
  }
]);

// Calcular variações percentuais
const calculatePercentageChange = (current: number, previous: number): string => {
  if (previous === 0) return current > 0 ? '100' : '0';
  const change = ((current - previous) / previous) * 100;
  return change.toFixed(1);
};

// Comparações
const comparisons = computed(() => [
  {
    label: 'Vendedores',
    percentage: calculatePercentageChange(props.currentMonth.sellers, props.previousMonth.sellers),
    isPositive: props.currentMonth.sellers >= props.previousMonth.sellers
  },
  {
    label: 'Vendas',
    percentage: calculatePercentageChange(props.currentMonth.sales, props.previousMonth.sales),
    isPositive: props.currentMonth.sales >= props.previousMonth.sales
  },
  {
    label: 'Receita',
    percentage: calculatePercentageChange(toNumber(props.currentMonth.revenue), toNumber(props.previousMonth.revenue)),
    isPositive: toNumber(props.currentMonth.revenue) >= toNumber(props.previousMonth.revenue)
  },
  {
    label: 'Comissão',
    percentage: calculatePercentageChange(toNumber(props.currentMonth.commission), toNumber(props.previousMonth.commission)),
    isPositive: toNumber(props.currentMonth.commission) >= toNumber(props.previousMonth.commission)
  }
]);
</script>

