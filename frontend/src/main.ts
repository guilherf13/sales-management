import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import { useAuthStore } from './stores/auth';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import './style.css';

const pinia = createPinia();

const app = createApp(App);

app.use(pinia);
app.use(Toast, {
  position: "top-center",
  timeout: 5000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  draggablePercent: 0.6,
  showCloseButtonOnHover: false,
  hideProgressBar: false,
  closeButton: "button",
  icon: true,
  rtl: false,
  transition: "Vue-Toastification__bounce",
  maxToasts: 3,
  newestOnTop: true
});

// Inicializa o auth store para verificar o token do localStorage
const authStore = useAuthStore();

// Envolve a montagem do app em uma função assíncrona
async function initializeAndMount() {
  try {
    // Espera a inicialização da autenticação terminar
    await authStore.initializeAuth();
  } catch (error) {
    console.error('Falha ao inicializar a autenticação:', error);
    // Mesmo em caso de erro, continuamos para montar o app, 
    // o router guard vai redirecionar para o login se necessário.
  }

  // Só depois da inicialização, montamos o app
  app.use(router);
  app.mount('#app');
}

// Chama a função para iniciar o processo
initializeAndMount();