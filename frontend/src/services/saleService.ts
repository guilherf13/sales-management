import axios from 'axios';

const apiService = axios.create({
  baseURL: 'http://localhost:8080/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// Add auth token to requests
apiService.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle responses
apiService.interceptors.response.use(
  (response) => response,
  (error) => {
    console.error('API Error:', error.response?.data || error.message);
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export interface Sale {
  id: number;
  seller_id: number;
  amount: number | string;
  commission: number | string;
  sale_date: string;
  created_at: string;
  updated_at: string;
  seller?: {
    id: number;
    name: string;
    email: string;
  };
}

export interface PaginationMeta {
  current_page: number;
  from: number;
  last_page: number;
  per_page: number;
  to: number;
  total: number;
}

export interface PaginationLinks {
  first: string;
  last: string;
  prev: string | null;
  next: string | null;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta?: PaginationMeta;
  links?: PaginationLinks;
  current_page?: number;
  last_page?: number;
  per_page?: number;
  total?: number;
  from?: number;
  to?: number;
}

export interface ApiResponse<T> {
  data: T;
  message?: string;
}

class SaleService {
  async getAll(page: number = 1, perPage: number = 20, filters?: string) {
    let url = `/sales?page=${page}&per_page=${perPage}`;
    if (filters) {
      url += `&${filters}`;
    }
    return apiService.get<PaginatedResponse<Sale>>(url);
  }

  async getById(id: number) {
    return apiService.get<ApiResponse<Sale>>(`/sales/${id}`);
  }

  async create(sale: { seller_id: number; amount: number; sale_date: string }) {
    return apiService.post<ApiResponse<Sale>>('/sales', sale);
  }

  async update(id: number, sale: Partial<Sale>) {
    return apiService.put<ApiResponse<Sale>>(`/sales/${id}`, sale);
  }

  async delete(id: number) {
    return apiService.delete(`/sales/${id}`);
  }

  async getDashboardStats() {
    return apiService.get('/dashboard/stats');
  }

  async getDailySummary(date?: string) {
    const url = date ? `/sales/daily-summary?date=${date}` : '/sales/daily-summary';
    return apiService.get(url);
  }

  async resendCommissionEmail(sellerId: number, date?: string) {
    const url = date ? `/sellers/${sellerId}/resend-commission?date=${date}` : `/sellers/${sellerId}/resend-commission`;
    return apiService.post(url);
  }
}

export const saleService = new SaleService();
