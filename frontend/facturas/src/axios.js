import axios from 'axios';

const instance = axios.create({
  baseURL: 'http://localhost:', // Ajusta si es necesario
});

// Interceptor para agregar el token automáticamente
instance.interceptors.request.use(
  (config) => {
    const auth = localStorage.getItem('auth');
    if (auth) {
      const token = JSON.parse(auth).access_token;
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    }
    return config;
  },
  (error) => Promise.reject(error)
);

export default instance;
