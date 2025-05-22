<template>
  <div class="container py-4">
    <h2 class="text-center mb-4">Buscar Facturas</h2>
    <!-- Mensaje de éxito con transición fade-out -->
    <div
      v-if="mensaje"
      class="fade-message"
      :class="{ 'fade-out': isFadingOut }"
    >
      {{ mensaje }}
    </div>

    <!-- FORMULARIO -->
    <FormularioBusqueda v-model="searchParams" @buscar="buscarFacturas" />

    <!-- Indicador de carga -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner"></div>
    </div>

    <!-- MODAL RESULTADOS -->
    <ModalResultados
      :mostrarResultados="mostrarResultados"
      :paginatedInvoices="paginatedInvoices"
      :allInvoices="this.allInvoices"
      :currentPage="currentPage"
      :totalPages="totalPages"
      :loading="loading"
      @cerrar-modal="mostrarResultados = false"
      @editar-factura="seleccionarFactura"
      @cambiar-pagina="goToPage"
      @guardar-cambios="guardarCambiosEnBD"
      @codigos-actualizados="actualizarCodigosEnPadre"
    />

    <!-- MODAL EDITAR -->
    <transition name="fade-modal">
      <div v-if="facturaEditando" class="modal-overlay" @click="cerrarModal">
        <div class="modal-content" @click.stop>
          <h3>Editar Factura</h3>
          <div class="form-group">
            <label>Código de Análisis:</label>
            <select
              v-model="facturaEditando.iecodanalisis"
              class="form-control"
            >
              <option disabled value="">Selecciona un código</option>
              <option
                v-for="codigo in codigosAnalisis"
                :key="codigo.id_iecuentas"
                :value="codigo.id_iecuentas"
              >
                {{ codigo.nombre }} - {{ codigo.id_iecuentas }}
              </option>
            </select>
          </div>
          <div class="modal-buttons">
            <button @click="guardarCambios">Guardar</button>
            <button @click="cancelarEdicion" class="cancelar">Cancelar</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from "axios";
import ModalResultados from "./ModalResultados.vue";
import FormularioBusqueda from "./FormularioBusqueda.vue";

export default {
  name: "BuscarFacturas",
  data() {
    return {
      searchParams: {
        fecha_inicio: "",
        fecha_fin: "",
        folio: "",
        emisor: "",
        codigo_analisis: "",
        isFadingOut: false,
      },
      codigosAnalisis: [],
      allInvoices: [],
      paginatedInvoices: [],
      errorMessage: "",
      loading: false,
      currentPage: 1,
      resultsPerPage: 10,
      totalResults: 0,
      facturaEditando: null,
      mostrarResultados: false,
      mensaje: "",
      iva: 0.19,
    };
  },
  components: {
    ModalResultados,
    FormularioBusqueda,
  },
  computed: {
    totalPages() {
      return Math.ceil(this.totalResults / this.resultsPerPage);
    },
  },
  methods: {
    editarCodigoAnalisis() {
      this.allInvoices.forEach((factura) => {
        factura.codigo_analisis = this.searchParams.codigo_analisis;
        factura.iecodanalisis = this.searchParams.codigo_analisis;
      });
      this.paginateInvoices();
    },
    actualizarCodigosEnPadre(nuevosCodigos) {
      this.codigosAnalisis = nuevosCodigos;
    },
    seleccionarFactura(index) {
      this.facturaEditando = { ...this.paginatedInvoices[index], index };
    },
    guardarCambios() {
      const i = this.facturaEditando.index;
      if (
        i !== undefined &&
        this.facturaEditando?.iecodanalisis !== undefined
      ) {
        this.paginatedInvoices[i].iecodanalisis =
          this.facturaEditando.iecodanalisis;
      }
      this.facturaEditando = null;
    },
    cancelarEdicion() {
      this.facturaEditando = null;
    },
    async buscarFacturas() {
      this.errorMessage = "";
      try {
        this.loading = true;
        const authData = JSON.parse(localStorage.getItem("auth"));
        const token = authData?.access_token;
        if (!token) {
          this.errorMessage = "Token de autenticación no encontrado.";
          return;
        }

        const params = { ...this.searchParams };
        const response = await axios.get("http://localhost/api/buscar-dte", {
          params,
          headers: { Authorization: `Bearer ${token}` },
        });

        if (Array.isArray(response.data)) {
          this.allInvoices = response.data.map((factura) => ({
            ...factura,
            iecodanalisis: factura.iecodanalisis || "",
            codigo_analisis: factura.iecodanalisis || "",
          }));
          this.totalResults = this.allInvoices.length;
          this.currentPage = 1;
          this.paginateInvoices();
        } else {
          this.errorMessage = "Formato inesperado de respuesta.";
        }
      } catch (error) {
        console.error("Error buscando facturas", error);
      } finally {
        this.loading = false;
      }
    },
    async guardarCambiosEnBD() {
      this.loading = true; // Mostrar spinner

      try {
        const datosAGuardar = this.paginatedInvoices.map((factura) => ({
          emisor: factura.emisor,
          folio: factura.folio,
          iecuenta: factura.iecuenta,
          iecodanalisis: factura.iecodanalisis,
        }));

        const token = JSON.parse(localStorage.getItem("auth"))?.access_token;

        if (!token) {
          this.loading = false; // Ocultar spinner
          alert("Token de autenticación no disponible.");
          return;
        }

        const response = await fetch("http://localhost/api/actualizar-dtes", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({ datos: datosAGuardar }),
        });

        if (response.ok) {
          this.enviarMensaje(
            "✅ Cambios guardados exitosamente. Se han reflejado en la base de datos."
          );
        } else {
          this.enviarMensaje("❌ Hubo un problema al guardar los datos.");
        }
      } catch (error) {
        console.error("Error al guardar cambios:", error);
        this.enviarMensaje(
          "❌ Error al guardar cambios. Por favor, intenta nuevamente."
        );
      } finally {
        this.loading = false; // Ocultar spinner pase lo que pase
      }
    },
    paginateInvoices() {
      const start = (this.currentPage - 1) * this.resultsPerPage;
      const end = start + this.resultsPerPage;
      this.paginatedInvoices = this.allInvoices.slice(start, end);
      this.mostrarResultados = true;
    },
    goToPage(pageNumber) {
      if (pageNumber < 1 || pageNumber > this.totalPages) return;
      this.currentPage = pageNumber;
      this.paginateInvoices();
    },
    enviarMensaje(mensajeNuevo) {
      this.mensaje = mensajeNuevo;
      setTimeout(() => {
        this.isFadingOut = true;
        setTimeout(() => {
          this.mensaje = "";
          this.isFadingOut = false;
        }, 1000);
      }, 2000);
    },
  },
};
</script>

<style scoped>
/* Contenedor principal para centrar todo el contenido */
.container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  height: 100vh;
  background: linear-gradient(135deg, #dbeeff, #f1f9ff);
}

.title {
  text-align: center;
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 30px;
}

/* Estilos para el formulario */
.form-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
}

.formulario {
  background: white;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 500px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  font-size: 14px;
  color: #333;
  font-weight: 600;
  display: block;
  margin-bottom: 8px;
}

.form-group input {
  width: 100%;
  padding: 10px;
  font-size: 14px;
  border: 1px solid #ddd;
  border-radius: 4px;
  box-sizing: border-box;
}

.form-group input:focus {
  border-color: #0070c9;
  outline: none;
}

button {
  width: 100%;
  max-width: 80px;
  padding: 12px;
  font-size: 16px;
  background-color: #0070c9;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #005ba1;
}

button:active {
  background-color: #003d7a;
}

/* Estilos para la carga y modales */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10000;
}

.loading-spinner {
  border: 6px solid rgba(0, 188, 212, 0.3);
  border-top: 6px solid #00bcd4;
  border-radius: 50%;
  width: 50px;
  height: 50px;
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

/* Estilos de los modales */
.modal-buttons button {
  padding: 8px 16px;
  background-color: #0070c9;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 4px;
  width: auto;
  height: auto;
}

.modal-buttons button:hover {
  background-color: #005ba1;
}

.modal-buttons button:active {
  background-color: #003d7a;
}
/* Eliminar el foco en el modal al hacer clic fuera de él */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  position: relative;
  /* Importante para posicionar la X en la esquina */
  background: white;
  padding: 20px;
  border-radius: 12px;
  width: 90%;
  /* max-width: 500px; */
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  font-size: 14px;
}

.modal-content button {
  padding: 8px 16px;
  font-size: 10px;
  /* Cambia el tamaño de la fuente */
  background-color: #0070c9;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.modal-close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  font-size: 20px;
  font-weight: bold;
  background: none;
  border: none;
  color: #0070c9;
  cursor: pointer;
  transition: color 0.3s ease;
  width: auto;
}

.modal-close-btn:hover {
  color: #005ba1;
}

.modal-close-btn:active {
  color: #003d7a;
}

.modal-content button:hover {
  background-color: #005ba1;
}

.modal-content button:active {
  background-color: #003d7a;
}

.fade-modal-enter-active,
.fade-modal-leave-active {
  transition: all 0.3s ease;
}

.fade-modal-enter-from {
  opacity: 0;
  transform: scale(0.95);
}

.fade-modal-enter-to {
  opacity: 1;
  transform: scale(1);
}

.fade-modal-leave-from {
  opacity: 1;
  transform: scale(1);
}

.fade-modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

.fade-message {
  transition: opacity 1s ease-out;
  opacity: 1;
  background-color: #4caf50;
  /* Verde de éxito */
  color: white;
  padding: 10px;
  border-radius: 5px;
  margin-top: 10px;
  text-align: center;
  z-index: 9999;
}

.fade-message.fade-out {
  opacity: 0;
}

.btn-success {
  background-color: #28a745;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
}

.btn-success:hover {
  background-color: #218838;
}
</style>
