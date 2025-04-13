<template>
  <div id="app">
    <header class="banner">Gestor Facturas iHosting</header>

    <main class="main-content">
      <div class="login-box">
        <h2>Iniciar Sesión</h2>
        <input type="text" placeholder="Usuario" />

        <!-- Campo de contraseña con el ícono al lado derecho -->
        <div class="password-container">
          <input
            placeholder="Contraseña"
          />
         
        </div>

        <button @click="irADashboard">Ingresar</button>
        <p class="respuesta" v-if="mensaje">{{ mensaje }}</p>
      </div>
    </main>

    <footer class="footer">© 2025 iHosting</footer>
  </div>
</template>

<script>
import axios from '../axios';

export default {
  name: 'LoginPage',
  data() {
    return {
      mensaje: '',
      passwordVisible: false, // Controla la visibilidad de la contraseña
    };
  },
  mounted() {
    // Realiza la solicitud a la API cuando el componente se monta
    axios.get('/api/hola')
      .then(response => {
        this.mensaje = response.data.mensaje;
      })
      .catch(error => {
        console.error('Error al obtener el mensaje:', error);
      });
  },
  methods: {
    irADashboard() {
      this.$router.push('/dashboard');
    },
    togglePasswordVisibility() {
      this.passwordVisible = !this.passwordVisible; // Alterna la visibilidad de la contraseña
    },
  },
};
</script>

<style scoped>
body {
  margin: 0;
  font-family: Avenir, Helvetica, Arial, sans-serif;
  background-color: #e0f7fa; /* Celeste claro */
}

#app {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.banner {
  background-color: #00bcd4; /* Celeste fuerte */
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

.respuesta {
  margin-top: 1rem;
  font-size: 0.9rem;
  color: #4caf50;
}
</style>
