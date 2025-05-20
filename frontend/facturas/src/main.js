
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import './index.css'; // o './tailwind.css' si usaste ese nombre




const app = createApp(App);
app.use(router); // 👈 esto es importante
app.mount('#app');
