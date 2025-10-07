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
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export interface User {
  id: number;
  name: string;
  email: string;
  perfil: 'Gestor' | 'Seller';
}

export interface LoginResponse {
  access_token: string;
  user: User;
}

export function setAuthHeader(token: string) {
  apiService.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

export function removeAuthHeader() {
  delete apiService.defaults.headers.common['Authorization'];
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export async function login(credentials: { email: string; password: string }) {
  return apiService.post<LoginResponse>('/login', credentials);
}

export async function register(data: RegisterData) {
  return apiService.post<LoginResponse>('/register', data);
}

export async function logout() {
  return apiService.post('/logout');
}

export async function getUser() {
  return apiService.get<User>('/user');
}
