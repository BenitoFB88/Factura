import { createRouter, createWebHistory } from 'vue-router';
import LoginPage from '../components/LoginPage.vue';
import UserDashboard from '../components/UserDashboard.vue'; // Asegúrate de que sea UserDashboard

const routes = [
  {
    path: '/',
    name: 'LoginPage',
    component: LoginPage
  },
  {
    path: '/dashboard',
    name: 'UserDashboard',
    component: UserDashboard // Usa 'UserDashboard' correctamente
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
