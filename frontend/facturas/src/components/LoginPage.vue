<template>
  <div class="login-page">
    <header class="banner">Gestor Facturas iHosting</header>

    <main class="main-content">
      <div class="login-box">
        <h2>Iniciar Sesión</h2>
        <form @submit.prevent="login">
          <div class="form-group">
            <label for="usuario">Usuario</label>
            <input
              type="text"
              id="usuario"
              v-model="email"
              placeholder="Ingrese su usuario"
            />
          </div>

          <div class="form-group">
            <label for="clave">Clave</label>
            <input
              type="password"
              id="clave"
              v-model="password"
              placeholder="Ingrese su clave"
            />
          </div>

          <div class="buttons">
            <button type="submit" :disabled="cargando">
              {{ cargando ? 'Ingresando...' : 'Iniciar' }}
            </button>
            <button type="button" class="cancelar" @click="cancelar">Cancelar</button>
          </div>

          <div v-if="cargando" class="spinner"></div>
          <p class="respuesta" v-if="mensaje">{{ mensaje }}</p>
        </form>
      </div>
    </main>

    <footer class="footer">© 2025 iHosting</footer>

    <div v-if="mostrarPopup" class="popup">¡Inicio de sesión exitoso!</div>
  </div>
</template>

<script>
import axios from '../axios';

export default {
  name: 'LoginPage',
  data() {
    return {
      email: '',
      password: '',
      mensaje: '',
      cargando: false,
      mostrarPopup: false,
    };
  },
  mounted() {
    axios.get('/api/hola')
      .then(response => {
        this.mensaje = response.data.mensaje;
      })
      .catch(error => {
        console.error('Error al obtener el mensaje:', error);
      });
  },
  methods: {
    async login() {
      this.cargando = true;
      this.mensaje = '';

      try {
        const response = await axios.post('/api/login', {
          email: this.email,
          password: this.password,
        });

        localStorage.setItem('auth', JSON.stringify({
          access_token: response.data.access_token,
          refresh_token: response.data.refresh_token,
          token_type: response.data.token_type,
          expires_in: response.data.expires_in
        }));

        this.mostrarPopup = true;

        setTimeout(() => {
          this.$router.push('/dashboard');
        }, 1000);

      } catch (error) {
        this.mensaje = 'Credenciales incorrectas o error al iniciar sesión.';
        console.error(error);
      } finally {
        this.cargando = false;
      }
    },
    cancelar() {
      this.email = '';
      this.password = '';
    }
  }
};
</script>

<style scoped>
.login-page {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: linear-gradient(135deg, #dbeeff, #f1f9ff);
  font-family: 'Segoe UI', sans-serif;
}

.banner {
  background-color: #00bcd4;
  color: white;
  text-align: center;
  padding: 1rem;
  font-size: 1.5rem;
}

.footer {
  background-color: #00bcd4;
  color: white;
  text-align: center;
  padding: 1rem;
  font-size: 1rem;
}

.main-content {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
}

.login-box {
  background-color: white;
  padding: 40px;
  border-radius: 16px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
  text-align: center;
}

h2 {
  color: #1e3a5f;
  margin-bottom: 30px;
}

.form-group {
  margin-bottom: 20px;
  text-align: left;
}

label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  color: #333;
}

input {
  width: 100%;
  padding: 10px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 8px;
}

.buttons {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  margin-top: 10px;
}

button {
  padding: 10px 20px;
  border: none;
  font-size: 14px;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s;
}

button[type="submit"] {
  background-color: #0070c9;
  color: white;
}

button.cancelar {
  background-color: #ccc;
  color: #333;
}

button:hover {
  opacity: 0.9;
}

button:disabled {
  background-color: #80deea;
  cursor: not-allowed;
}

.spinner {
  border: 6px solid rgba(0, 188, 212, 0.3);
  border-top: 6px solid #00bcd4;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin: 20px auto 0;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.respuesta {
  margin-top: 1rem;
  font-size: 0.9rem;
  color: #f44336;
}

.popup {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: #4caf50;
  color: white;
  padding: 1rem 1.5rem;
  border-radius: 5px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  z-index: 9999;
  animation: fadeInOut 2s ease-in-out forwards;
}

@keyframes fadeInOut {
  0% { opacity: 0; transform: translate(-50%, -60%); }
  10% { opacity: 1; transform: translate(-50%, -50%); }
  90% { opacity: 1; transform: translate(-50%, -50%); }
  100% { opacity: 0; transform: translate(-50%, -40%); }
}
</style>
