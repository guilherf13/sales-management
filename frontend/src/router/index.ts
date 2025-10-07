import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import DashboardView from '../views/DashboardView.vue';
import SellersView from '../views/SellersView.vue';
import SalesView from '../views/SalesView.vue';
import LoginView from '../views/LoginView.vue';
import Layout from '../components/Layout.vue';
import { useAuthStore } from '../stores/auth';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/login',
    name: 'login',
    component: LoginView
  },
  {
    path: '/',
    component: Layout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: DashboardView
      },
      {
        path: '/sellers',
        name: 'sellers',
        component: SellersView
      },
      {
        path: '/sales',
        name: 'sales',
        component: SalesView
      }
    ]
  }
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);

  if (requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' });
  } else {
    next();
  }
});

export default router;