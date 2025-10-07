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

export interface Seller {
  id: number;
  name: string;
  email: string;
  sales_count?: number;
  total_commission?: number;
  created_at: string;
  updated_at: string;
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
}

export interface ApiResponse<T> {
  data: T;
  message?: string;
}

class SellerService {
  async getAll(page: number = 1, perPage: number = 20, search?: string) {
    let url = `/sellers?page=${page}&per_page=${perPage}`;
    if (search) {
      url += `&search=${encodeURIComponent(search)}`;
    }
    return apiService.get<PaginatedResponse<Seller>>(url);
  }

  async getById(id: number) {
    return apiService.get<ApiResponse<Seller>>(`/sellers/${id}`);
  }

  async create(seller: Omit<Seller, 'id' | 'created_at' | 'updated_at'>) {
    return apiService.post<ApiResponse<Seller>>('/sellers', seller);
  }

  async update(id: number, seller: Partial<Seller>) {
    return apiService.put<ApiResponse<Seller>>(`/sellers/${id}`, seller);
  }

  async delete(id: number) {
    return apiService.delete(`/sellers/${id}`);
  }
}

export const sellerService = new SellerService();