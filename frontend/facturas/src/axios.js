import axios from 'axios';

const instance = axios.create({
  baseURL: 'http://localhost:8081', // Ajusta el host/puerto según tu entorno
  // timeout: 5000, // opcional
});

// Interceptor para agregar el token Bearer automáticamente
instance.interceptors.request.use(
  (config) => {
    const auth = localStorage.getItem('auth');
    if (auth) {
      try {
        const token = JSON.parse(auth).access_token;
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
      } catch {
        // Si falla el parseo, no hace nada
      }
    }
    return config;
  },
  (error) => Promise.reject(error)
);

export default instance;
