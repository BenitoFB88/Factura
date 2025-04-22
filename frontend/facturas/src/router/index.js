import { createRouter, createWebHistory } from 'vue-router';
import LoginPage from '../components/LoginPage.vue';
import UserDashboard from '../components/UserDashboard.vue';
import ExcelUpload from '../components/ExcelUpload.vue'; // Asegúrate de que el componente esté correctamente importado

const routes = [
  {
    path: '/',
    name: 'LoginPage',
    component: LoginPage
  },
  {
    path: '/dashboard',
    name: 'UserDashboard',
    component: UserDashboard // Asegúrate de que el nombre del componente sea correcto
  },
  {
    path: '/upload-excel', // Ruta para cargar archivos Excel
    name: 'ExcelUpload',
    component: ExcelUpload,
  }
];

const router = createRouter({
  history: createWebHistory(), // Correcto para Vue 3
  routes
});

export default router;
