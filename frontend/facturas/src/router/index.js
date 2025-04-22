import { createRouter, createWebHistory } from 'vue-router';
import LoginPage from '../components/LoginPage.vue';
import UserDashboard from '../components/UserDashboard.vue';
import ExcelUpload from '../components/ExcelUpload.vue';
import FacturaDashboard from '@/components/FacturaDashboard.vue';

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
  },
  {
    path: '/upload-excel',
    name: 'ExcelUpload',
    component: ExcelUpload,
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
