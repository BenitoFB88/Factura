<template>
  <div
    class="flex flex-col min-h-[80vh] bg-gradient-to-br from-[#dbeeff] via-[#e6f3ff] to-[#f1f9ff] font-sans"
  >
    <main class="flex-1 flex justify-center items-center">
      <div
        class="bg-white p-10 rounded-[16px] shadow-lg w-full max-w-[400px] text-center"
        style="padding: 40px"
      >
        <h2>Iniciar Sesión</h2>
        <form @submit.prevent="login">
          <div class="mb-5 text-left">
            <label for="usuario">Usuario</label>
            <input
              type="email"
              id="usuario"
              v-model="email"
              placeholder="Ingrese su usuario"
            />
          </div>

          <div class="mb-5 text-left">
            <label for="clave">Clave</label>
            <input
              type="password"
              id="clave"
              v-model="password"
              placeholder="Ingrese su clave"
            />
          </div>

          <div class="flex justify-between gap-2.5 mt-2.5">
            <button type="submit" :disabled="cargando">
              {{ cargando ? "Ingresando..." : "Iniciar" }}
            </button>
            <button type="button" class="cancelar" @click="cancelar">
              Cancelar
            </button>
          </div>

          <div v-if="cargando" class="spinner"></div>
          <p class="respuesta" v-if="mensaje">{{ mensaje }}</p>
        </form>
      </div>
    </main>

    <div v-if="mostrarPopup" class="popup">¡Inicio de sesión exitoso!</div>
  </div>
</template>

<script>
import axios from "@/axios";

export default {
  name: "LoginPage",
  data() {
    return {
      email: "",
      password: "",
      mensaje: "",
      cargando: false,
      mostrarPopup: false,
    };
  },
  mounted() {
    axios
      .get("/api/hola")
      .then((response) => {
        this.mensaje = response.data.mensaje;
      })
      .catch((error) => {
        console.error("Error al obtener el mensaje:", error);
      });
  },
  methods: {
    sanitizeInput(input) {
      return input.replace(/<[^>]*>?/gm, "");
    },
    async login() {
      // Valida que ningún campo esté vacío
      if (!this.email.trim() || !this.password.trim()) {
        this.mensaje = "Por favor, complete todos los campos.";
        return;
      }

      this.cargando = true;
      this.mensaje = "";
      const sanitizedEmail = this.sanitizeInput(this.email);
      const sanitizedPassword = this.sanitizeInput(this.password);

      try {
        const response = await axios.post("/api/login", {
          email: sanitizedEmail,
          password: sanitizedPassword,
        });

        localStorage.setItem(
          "auth",
          JSON.stringify({
            access_token: response.data.access_token,
            refresh_token: response.data.refresh_token,
            token_type: response.data.token_type,
            expires_in: response.data.expires_in,
          })
        );

        this.mostrarPopup = true;

        setTimeout(() => {
          this.$router.push("/dashboard");
        }, 1000);
      } catch (error) {
        this.mensaje = "Credenciales incorrectas o error al iniciar sesión.";
        console.error(error);
      } finally {
        this.cargando = false;
      }
    },
    cancelar() {
      this.email = "";
      this.password = "";
    },
  },
};
</script>

<style scoped>
h2 {
  color: #1e3a5f;
  margin-bottom: 30px;
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
  border-top: 6px solid #aad5ee;
  border-radius: 50%;
  width: 120px;
  height: 120px;
  animation: spin 1s linear infinite;
  margin: 20px auto 0;
  z-index: 99999;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
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
  0% {
    opacity: 0;
    transform: translate(-50%, -60%);
  }
  10% {
    opacity: 1;
    transform: translate(-50%, -50%);
  }
  90% {
    opacity: 1;
    transform: translate(-50%, -50%);
  }
  100% {
    opacity: 0;
    transform: translate(-50%, -40%);
  }
}
</style>
