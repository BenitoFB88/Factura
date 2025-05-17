import { createRouter, createWebHistory } from 'vue-router';
import LoginPage from '@/components/Login/LoginPage.vue';
import FacturaDashboard from '@/components/GestionFacturas/FacturaDashboard.vue';
const routes = [
  {
    path: '/',
    redirect: '/login' // 👈 Redirige automáticamente a login al cargar la app
  },
  {
    path: '/login',
    name: 'LoginPage',
    component: LoginPage
  },
  {
    path: '/dashboard',
    name: 'FacturaDashboard',
    component: FacturaDashboard
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
