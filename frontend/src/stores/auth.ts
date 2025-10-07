import { defineStore } from 'pinia';
import { login, logout, getUser, setAuthHeader, removeAuthHeader, register } from '@/services/authService';
import type { User, RegisterData } from '@/services/authService';

interface AuthState {
  user: User | null;
  token: string | null;
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    token: localStorage.getItem('auth_token'),
  }),
  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    userProfile: (state) => state.user?.perfil,
  },
  actions: {
    async login(credentials: { email: string; password: string }) {
      const response = await login(credentials);
      const { access_token, user } = response.data;
      
      this.token = access_token;
      this.user = user;

      localStorage.setItem('auth_token', access_token);
      localStorage.setItem('user', JSON.stringify(user));

      setAuthHeader(access_token);
    },
    async register(data: RegisterData) {
      const response = await register(data);
      const { access_token, user } = response.data;
      
      this.token = access_token;
      this.user = user;

      localStorage.setItem('auth_token', access_token);
      localStorage.setItem('user', JSON.stringify(user));

      setAuthHeader(access_token);
    },
    async logout() {
      try {
        await logout();
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        removeAuthHeader();
      }
    },
    async initializeAuth() {
      if (this.token) {
        setAuthHeader(this.token);
        try {
          const response = await getUser();
          this.user = response.data;
        } catch (error) {
          console.error('Auth initialization error:', error);
          this.logout();
        }
      }
    }
  },
}); 