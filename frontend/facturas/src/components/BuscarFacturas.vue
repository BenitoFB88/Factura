<template>
  <div class="container">
    <h2 class="title">Buscar Facturas</h2>

    <!-- FORMULARIO -->
    <div class="form-container">
      <form @submit.prevent="buscarFacturas" class="formulario">
        <div class="form-group">
          <label for="fecha_inicio">Fecha de inicio:</label>
          <input
            id="fecha_inicio"
            type="date"
            v-model="searchParams.fecha_inicio"
            v-tooltip="'Seleccione la fecha de inicio'"
          />
        </div>
        <div class="form-group">
          <label for="fecha_fin">Fecha de fin:</label>
          <input
            id="fecha_fin"
            type="date"
            v-model="searchParams.fecha_fin"
            v-tooltip="'Seleccione la fecha de fin'"
          />
        </div>
        <div class="form-group">
          <label for="numero">Número de Factura:</label>
          <input
            id="numero"
            type="text"
            v-model="searchParams.numero_factura"
            v-tooltip="'Ingrese el número de factura'"
          />
        </div>
        <div class="form-group">
          <label for="cliente">Cliente:</label>
          <input
            id="cliente"
            type="text"
            v-model="searchParams.cliente"
            v-tooltip="'Ingrese el nombre del cliente'"
          />
        </div>
        <button type="submit" v-tooltip="'Haz clic para buscar las facturas'">
          Buscar
        </button>
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>
      </form>
    </div>

    <!-- Indicador de carga -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner"></div>
    </div>

    <!-- MODAL RESULTADOS -->
    <transition name="fade-modal">
      <div v-if="mostrarResultados" class="modal-overlay" @click="cerrarModal">
        <div class="modal-content" @click.stop>
          <button
            class="modal-close-btn"
            @click="cerrarModal"
            v-tooltip="'Cerrar ventana de resultados'"
          >
            X
          </button>
          <h3>Resultados de Búsqueda</h3>

          <table v-if="paginatedInvoices.length">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Emisor</th>
                <th>Receptor</th>
                <th>Folio</th>
                <th>Monto</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(factura, index) in paginatedInvoices" :key="index">
                <td :title="formatDate(factura.fecha) || 'Fecha no disponible'">
                  {{ formatDate(factura.fecha) || "Fecha no disponible" }}
                </td>
                <td :title="factura.emisor || 'Emisor no disponible'">
                  {{ factura.emisor || "Emisor no disponible" }}
                </td>
                <td :title="factura.receptor || 'Receptor no disponible'">
                  {{ factura.receptor || "Receptor no disponible" }}
                </td>
                <td :title="factura.folio || 'Folio no disponible'">
                  {{ factura.folio || "Folio no disponible" }}
                </td>
                <td :title="factura.total || 'Total no disponible'">
                  {{ factura.total || "Total no disponible" }}
                </td>
                <td>
                  <button
                    @click="seleccionarFactura(index)"
                    v-tooltip="'Editar factura'"
                  >
                    Editar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <p v-else>No se encontraron resultados.</p>

          <!-- Paginación -->
          <div v-if="totalPages > 1" class="pagination">
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
              v-tooltip="'Ir a la página anterior'"
            >
              Anterior
            </button>
            <span>{{ currentPage }} de {{ totalPages }}</span>
            <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              v-tooltip="'Ir a la siguiente página'"
            >
              Siguiente
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- MODAL EDITAR -->
    <transition name="fade-modal">
      <div v-if="facturaEditando" class="modal-overlay" @click="cerrarModal">
        <div class="modal-content" @click.stop>
          <h3>Editar Factura</h3>

          <div class="form-group">
            <label>Fecha:</label>
            <input
              type="date"
              v-model="facturaEditando.fecha"
              v-tooltip="'Seleccione la nueva fecha'"
            />
          </div>

          <div class="form-group">
            <label>Número:</label>
            <input
              type="text"
              v-model="facturaEditando.numero"
              v-tooltip="'Ingrese el nuevo número de factura'"
            />
          </div>

          <div class="form-group">
            <label>Cliente:</label>
            <input
              type="text"
              v-model="facturaEditando.cliente"
              v-tooltip="'Ingrese el nombre del cliente'"
            />
          </div>

          <div class="form-group">
            <label>Monto:</label>
            <input
              type="text"
              v-model="facturaEditando.monto"
              v-tooltip="'Ingrese el monto de la factura'"
            />
          </div>

          <div class="modal-buttons">
            <button
              @click="guardarCambios"
              v-tooltip="'Guardar los cambios realizados'"
            >
              Guardar
            </button>
            <button @click="cancelarEdicion" v-tooltip="'Cancelar la edición'">
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from "axios";
import VTooltip from "v-tooltip"; // Asegúrate de que lo hayas importado

export default {
  name: "BuscarFacturas",
  data() {
    return {
      searchParams: {
        fecha_inicio: "",
        fecha_fin: "",
        cliente: "",
        numero_factura: "",
      },
      allInvoices: [],
      paginatedInvoices: [],
      errorMessage: "",
      loading: false,
      errors: {},
      currentPage: 1,
      resultsPerPage: 10,
      totalResults: 0,
      facturaEditando: null,
      mostrarResultados: false,
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.totalResults / this.resultsPerPage);
    },
  },
  methods: {
    formatDate(date) {
      if (!date) return "";
      const parsedDate = new Date(date);
      return parsedDate.toLocaleDateString("es-CL");
    },

    seleccionarFactura(index) {
      this.facturaEditando = { ...this.paginatedInvoices[index], index };
    },

    guardarCambios() {
      const i = this.facturaEditando.index;
      this.paginatedInvoices[i] = { ...this.facturaEditando };
      this.facturaEditando = null;
    },

    cancelarEdicion() {
      this.facturaEditando = null;
    },

    async buscarFacturas() {
      this.errorMessage = "";
      if (!this.validateForm()) return;

      try {
        this.loading = true;

        const authData = JSON.parse(localStorage.getItem("auth"));
        const token = authData?.access_token;

        if (!token) {
          this.setErrorMessage("Token de autenticación no encontrado.");
          this.loading = false;
          return;
        }

        const params = { ...this.searchParams };

        const response = await axios.get("http://localhost/api/buscar-dte", {
          params,
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        if (Array.isArray(response.data)) {
          this.allInvoices = response.data;
          this.totalResults = this.allInvoices.length;
          this.currentPage = 1;
          this.paginateInvoices();
        } else {
          this.setErrorMessage("Formato de respuesta inesperado de la API.");
        }
      } catch (error) {
        console.error("Error al realizar la búsqueda:", error);
        this.setErrorMessage(
          "Error al realizar la búsqueda. Intenta nuevamente."
        );
      } finally {
        this.loading = false;
      }
    },

    setErrorMessage(message) {
      if (this.errorMessage !== message) {
        this.errorMessage = message;
        clearTimeout(this.errorTimeout);

        this.$nextTick(() => {
          const errorElement = document.querySelector(".error-message");
          if (errorElement) {
            errorElement.classList.remove("fade-out");
            void errorElement.offsetWidth;
            errorElement.classList.add("fade-out");
          }
        });

        this.errorTimeout = setTimeout(() => {
          this.errorMessage = "";
        }, 5000);
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

    validateForm() {
      this.errors = {};
      const { fecha_inicio, fecha_fin, cliente, numero_factura } =
        this.searchParams;

      if (!fecha_inicio && !fecha_fin && !cliente && !numero_factura) {
        this.errorMessage = "Debe ingresar al menos un parámetro de búsqueda.";
        return false;
      }

      if (
        fecha_inicio &&
        fecha_fin &&
        new Date(fecha_inicio) > new Date(fecha_fin)
      ) {
        this.errorMessage =
          "La fecha de inicio no puede ser mayor que la fecha de fin.";
        return false;
      }

      const fechaFin = new Date(fecha_fin);
      const fechaActual = new Date();
      if (fecha_fin && fechaFin > fechaActual) {
        this.errorMessage =
          "La fecha de fin no puede ser mayor que la fecha actual.";
        return false;
      }

      this.errorMessage = "";
      return true;
    },

    cerrarModal() {
      this.mostrarResultados = false;
      this.facturaEditando = null;
    },
  },
  directives: {
    tooltip: VTooltip,
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
  background-color: #f4f7fc;
  padding: 20px;
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
  z-index: 1000;
}

.loading-spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #0070c9;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 2s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Estilos para la tabla de resultados */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  table-layout: fixed; /* Asegura que las columnas tengan el mismo ancho */
}

th,
td {
  border: 1px solid #ddd;
  padding: 8px; /* más compacto */
  text-align: left;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 150px;
}

th {
  background-color: #f1f1f1;
  font-weight: bold;
}

td button {
  padding: 6px 12px;
  background-color: #0070c9;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

td button:hover {
  background-color: #005ba1;
}

.pagination {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.pagination button {
  padding: 8px 16px;
  font-size: 14px;
  margin: 0 5px;
  border-radius: 4px;
}

.pagination button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  width: 80%;
  max-width: 600px;
}

.modal-buttons button {
  margin-right: 10px;
  padding: 10px 15px;
  background-color: #0070c9;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.modal-buttons button:hover {
  background-color: #005ba1;
}

.modal-close-btn {
  background-color: red;
  color: white;
  font-size: 20px;
  border: none;
  padding: 5px 10px;
  cursor: pointer;
}

.error-message {
  background-color: #ff4d4d; /* Rojo para indicar error */
  color: white;
  padding: 10px;
  border-radius: 5px;
  margin-top: 10px;
  text-align: center;
  font-size: 16px;
  opacity: 1; /* Asegurarnos de que el mensaje sea visible */
  transition: opacity 0.5s ease; /* Hacer la animación más fluida */
}

/* Animación para desvanecer el mensaje después de 5 segundos */
@keyframes fadeOut {
  0% {
    opacity: 1;
  }
  100% {
    opacity: 0;
  }
}

.error-message.fade-out {
  animation: fadeOut 5s forwards; /* Se aplica la animación solo si tiene la clase 'fade-out' */
}
</style>
