<template>
  <div class="container">
    <h2 class="title">Buscar Facturas</h2>
    <!-- FORMULARIO -->
    <div class="form-container">
      <form @submit.prevent="buscarFacturas" class="formulario">
        <div class="form-group">
          <label for="fecha">Fecha:</label>
          <input id="fecha" type="date" v-model="searchParams.fecha" />
        </div>
        <div class="form-group">
          <label for="numero">Número de Factura:</label>
          <input id="numero" type="text" v-model="searchParams.numero_factura" />
        </div>
        <div class="form-group">
          <label for="cliente">Cliente:</label>
          <input id="cliente" type="text" v-model="searchParams.cliente" />
        </div>
        <button type="submit">Buscar</button>
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
          <button class="modal-close-btn" @click="cerrarModal" style="width: auto; padding: auto; background-color: red;">X</button>
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
                  {{ formatDate(factura.fecha) || 'Fecha no disponible' }}
                </td>
                <td :title="factura.emisor || 'Emisor no disponible'">
                  {{ factura.emisor || 'Emisor no disponible' }}
                </td>
                <td :title="factura.receptor || 'Receptor no disponible'">
                  {{ factura.receptor || 'Receptor no disponible' }}
                </td>
                <td :title="factura.folio || 'Folio no disponible'">
                  {{ factura.folio || 'Folio no disponible' }}
                </td>
                <td :title="factura.total || 'Total no disponible'">
                  {{ factura.total || 'Total no disponible' }}
                </td>
                
                <td><button @click="seleccionarFactura(index)">Editar</button></td>
              </tr>
            </tbody>
          </table>

          <p v-else>No se encontraron resultados.</p>

          <!-- Paginación -->
          <div v-if="totalPages > 1" class="pagination">
            <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1">Anterior</button>
            <span>{{ currentPage }} de {{ totalPages }}</span>
            <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages">Siguiente</button>
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
            <input type="date" v-model="facturaEditando.fecha" />
          </div>

          <div class="form-group">
            <label>Número:</label>
            <input type="text" v-model="facturaEditando.numero" />
          </div>

          <div class="form-group">
            <label>Cliente:</label>
            <input type="text" v-model="facturaEditando.cliente" />
          </div>

          <div class="form-group">
            <label>Monto:</label>
            <input type="text" v-model="facturaEditando.monto" />
          </div>

          <div class="modal-buttons">
            <button @click="guardarCambios">Guardar</button>
            <button @click="cancelarEdicion">Cancelar</button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'BuscarFacturas',
  data() {
    return {
      searchParams: {
        fecha: '',
        cliente: '',
        numero_factura: ''
      },
      allInvoices: [],
      paginatedInvoices: [],
      errorMessage: '',
      loading: false,
      errors: {},
      currentPage: 1,
      resultsPerPage: 10,
      totalResults: 0,
      facturaEditando: null,
      mostrarResultados: false
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.totalResults / this.resultsPerPage);
    }
  },
  methods: {
    // Función para formatear la fecha sin la hora
    formatDate(date) {
      if (!date) return '';
      const parsedDate = new Date(date);
      return parsedDate.toLocaleDateString('es-CL'); // Usa el formato de fecha para Chile
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
      this.errorMessage = '';
      if (!this.validateForm()) return;

      try {
        this.loading = true;
        const authData = JSON.parse(localStorage.getItem('auth'));
        const token = authData?.access_token;

        if (!token) {
          this.errorMessage = 'Token de autenticación no encontrado.';
          this.loading = false;
          return;
        }

        const params = { ...this.searchParams };
        const response = await axios.get('http://localhost/api/invoices/search', {
          params,
          headers: {
            Authorization: `Bearer ${token}`
          }
        });

        if (Array.isArray(response.data)) {
          this.allInvoices = response.data;
          this.totalResults = this.allInvoices.length;
          this.currentPage = 1;
          this.paginateInvoices();
        } else {
          this.errorMessage = 'Formato de respuesta inesperado de la API.';
        }
      } catch (error) {
        console.error('Error al realizar la búsqueda:', error);
        this.errorMessage = 'Error al realizar la búsqueda. Intenta nuevamente.';
      } finally {
        this.loading = false;
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
      const { fecha, cliente, numero_factura } = this.searchParams;

      if (!fecha && !cliente && !numero_factura) {
        this.errorMessage = 'Debe ingresar al menos un parámetro de búsqueda.';
        return false;
      }

      this.errorMessage = '';
      return true;
    },

    cerrarModal() {
      this.mostrarResultados = false;
      this.facturaEditando = null;
    }
  }
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
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Estilos para la tabla de resultados */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  table-layout: fixed; /* Asegura que las columnas tengan el mismo ancho */
}

th, td {
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

/* Paginación */
.pagination {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-top: 20px;
}

.pagination button {
  padding: 8px 12px;
  font-size: 14px;
  background-color: #0070c9;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.pagination button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.pagination span {
  display: flex;
  align-items: center;
  font-size: 16px;
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
  position: relative; /* Importante para posicionar la X en la esquina */
  background: white;
  padding: 20px;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  font-size: 14px;
}

.modal-content button {
  padding: 8px 16px;
  font-size: 10px; /* Cambia el tamaño de la fuente */
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
</style>
