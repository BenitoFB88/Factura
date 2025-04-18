<template>
  <div id="app">
    <header class="banner">Gestor Facturas iHosting</header>

    <main class="main-content">
      <div class="login-box">
        <h2>Iniciar Sesión</h2>
        <input v-model="email" type="text" placeholder="Usuario" />
        <div class="password-container">
          <input v-model="password" type="password" placeholder="Contraseña" />
        </div>

        <button @click="login" :disabled="cargando">
          {{ cargando ? 'Ingresando...' : 'Ingresar' }}
        </button>

        <!-- Spinner debajo del botón -->
        <div v-if="cargando" class="spinner"></div>

        <p class="respuesta" v-if="mensaje">{{ mensaje }}</p>
      </div>
    </main>

    <footer class="footer">© 2025 iHosting</footer>

    <!-- Popup de éxito -->
    <div v-if="mostrarPopup" class="popup">
      ¡Inicio de sesión exitoso!
    </div>
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
    }
  },
};
</script>

<style scoped>
body {
  margin: 0;
  font-family: Avenir, Helvetica, Arial, sans-serif;
  background-color: #e0f7fa;
}

#app {
  display: flex;
  flex-direction: column;
  height: 100vh;
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
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
  width: 300px;
  text-align: center;
  position: relative; /* Necesario para el popup */
}

.login-box h2 {
  margin-bottom: 1rem;
  color: #00acc1;
}

.login-box input {
  width: 100%;
  padding: 0.5rem;
  margin-bottom: 1rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.password-container {
  position: relative;
  width: 100%;
}

.password-container input {
  width: 100%;
}

.login-box button {
  width: 100%;
  padding: 0.5rem;
  background-color: #00bcd4;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}

.login-box button:hover {
  background-color: #0097a7;
}

.login-box button:disabled {
  background-color: #80deea;
  cursor: not-allowed;
}

.respuesta {
  margin-top: 1rem;
  font-size: 0.9rem;
  color: #f44336;
}

/* Spinner */
.spinner {
  position: absolute;
  top: 100%; /* Posicionar justo debajo del botón */
  left: 50%;
  transform: translateX(-50%);
  border: 4px solid rgba(0, 188, 212, 0.3); /* Celeste claro */
  border-top: 4px solid #00bcd4; /* Celeste más oscuro */
  border-radius: 50%;
  width: 24px;
  height: 24px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Popup */
.popup {
  position: absolute;
  top: -50px; /* Ajusta para que se posicione sobre el formulario */
  left: 50%;
  transform: translateX(-50%);
  background-color: #4caf50;
  color: white;
  padding: 1rem 1.5rem;
  border-radius: 5px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  z-index: 999;
  animation: fadeInOut 2s ease-in-out forwards;
}

@keyframes fadeInOut {
  0% {
    opacity: 0;
    transform: translateY(-10px);
  }
  10% {
    opacity: 1;
    transform: translateY(0);
  }
  90% {
    opacity: 1;
    transform: translateY(0);
  }
  100% {
    opacity: 0;
    transform: translateY(-10px);
  }
}
</style>
