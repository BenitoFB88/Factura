import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

export default defineConfig({
  plugins: [vue(), vueDevTools()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  server: {
    host: true, // <- importante para exponer correctamente dentro del contenedor
    proxy: {
      '/api': {
        target: 'http://nginx_server', // o 'http://laravel_app:9000' si usas Laravel sin Nginx
        changeOrigin: true,
      }
    }
  }
})
