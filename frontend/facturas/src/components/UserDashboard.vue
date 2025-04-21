<template>
  <div class="container">
    <!-- Formulario de búsqueda de facturas -->
    <form @submit.prevent="searchInvoices" class="search-form">
      <!-- Campo: Fecha (con clase de error si hay error) -->
      <div class="form-group">
        <label for="fecha">Fecha:</label>
        <input
          type="date"
          v-model="searchParams.fecha"
          :class="{ 'is-invalid': errors.fecha }"
          id="fecha"
          class="form-control"
        />
      </div>

      <!-- Campo: Cliente (con clase de error si hay error) -->
      <div class="form-group">
        <label for="cliente">Cliente:</label>
        <input
          type="text"
          v-model="searchParams.cliente"
          :class="{ 'is-invalid': errors.cliente }"
          id="cliente"
          class="form-control"
        />
      </div>

      <!-- Campo: Número de Factura (con clase de error si hay error) -->
      <div class="form-group">
        <label for="numero_factura">Número de Factura:</label>
        <input
          type="number"
          v-model="searchParams.numero_factura"
          :class="{ 'is-invalid': errors.numero_factura }"
          id="numero_factura"
          class="form-control"
        />
      </div>

      <!-- Botón de búsqueda -->
      <button type="submit" class="btn btn-primary">Buscar</button>
    </form>

    <!-- Spinner de carga mientras se espera la respuesta del backend -->
    <div v-if="loading" class="loading-spinner">
      <i class="fas fa-spinner fa-spin"></i> Cargando...
    </div>

    <!-- Sección de resultados con animación -->
    <transition name="fade">
      <div v-if="!loading">
        <!-- Tabla de resultados si hay facturas -->
        <table v-if="paginatedInvoices.length > 0" class="table table-striped mt-4">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Emisor</th>
              <th>Receptor</th>
              <th>Folio</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="invoice in paginatedInvoices" :key="invoice.id">
              <td>{{ invoice.fecha || 'Fecha no disponible' }}</td>
              <td>{{ invoice.emisor || 'Emisor no disponible' }}</td>
              <td>{{ invoice.receptor || 'Receptor no disponible' }}</td>
              <td>{{ invoice.folio || 'Folio no disponible' }}</td>
              <td>{{ invoice.total || 'Total no disponible' }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Mensaje si no se encontraron facturas -->
        <div v-else class="alert alert-warning mt-4">
          No se encontraron facturas.
        </div>

        <!-- Mensaje de error si ocurre un problema -->
        <div v-if="errorMessage" class="alert alert-danger mt-4">
          {{ errorMessage }}
        </div>

        <!-- Botones de paginación -->
        <div v-if="totalPages > 1" class="pagination mt-4">
          <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" class="btn btn-secondary">
            Anterior
          </button>
          <span>Página {{ currentPage }} de {{ totalPages }}</span>
          <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" class="btn btn-secondary">
            Siguiente
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      // Datos del formulario de búsqueda
      searchParams: {
        fecha: '',
        cliente: '',
        numero_factura: ''
      },
      allInvoices: [],      // Lista de todas las facturas
      paginatedInvoices: [],// Facturas paginadas
      errorMessage: '',     // Mensaje de error
      loading: false,       // Estado de carga
      errors: {},           // Errores de validación por campo
      currentPage: 1,       // Página actual
      resultsPerPage: 10,   // Resultados por página
      totalResults: 0,      // Total de resultados
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.totalResults / this.resultsPerPage);
    }
  },
  methods: {
    // Realiza la búsqueda y obtiene todos los resultados de la API
    async searchInvoices() {
  this.errorMessage = '';

  // Validación previa al envío
  if (!this.validateForm()) return;

  try {
    this.loading = true;

    // Obtener el token desde el objeto auth
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

    console.log('Respuesta del backend:', response.data);

    // Verificar que la respuesta contiene un array válido
    if (Array.isArray(response.data)) {
      this.allInvoices = response.data;
      this.totalResults = this.allInvoices.length;
      this.currentPage = 1;

      // Paginamos los resultados obtenidos
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
}
,

    // Pagina los resultados guardados en `allInvoices`
    paginateInvoices() {
      const start = (this.currentPage - 1) * this.resultsPerPage;
      const end = start + this.resultsPerPage;
      
      // Verificar que `this.allInvoices` es un array
      if (Array.isArray(this.allInvoices)) {
        // Paginamos los resultados de `allInvoices` y los asignamos a `paginatedInvoices`
        this.paginatedInvoices = this.allInvoices.slice(start, end);
      }
    },

    // Cambia de página sin realizar nueva consulta al backend
    goToPage(pageNumber) {
      if (pageNumber < 1 || pageNumber > this.totalPages) return;

      this.currentPage = pageNumber;
      
      // Solo paginamos localmente, no hacemos más consultas
      this.paginateInvoices();
    },

    // Valida el formulario de búsqueda
    validateForm() {
      this.errors = {};

      const { fecha, cliente, numero_factura } = this.searchParams;

      if (!fecha && !cliente && !numero_factura) {
        this.errorMessage = 'Debe ingresar al menos un parámetro de búsqueda.';
        return false;
      }

      this.errorMessage = '';
      return true;
    }
  }
};
</script>


<style scoped>
/* Contenedor principal */
.container {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
}

/* Estilo del formulario */
.search-form {
  background-color: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.form-group {
  margin-bottom: 1.5rem;
}

/* Inputs con estilo base */
input[type="date"],
input[type="text"],
input[type="number"] {
  width: 100%;
  padding: 8px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  margin-top: 0.5rem;
}

/* Botón de envío */
button {
  background-color: #007bff;
  color: white;
  padding: 10px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #0056b3;
}

/* Tabla de resultados */
.table {
  width: 100%;
  margin-top: 2rem;
  border-collapse: collapse;
  background-color: #fff;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

th,
td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

th {
  background-color: #f2f2f2;
  font-weight: bold;
}

.table-striped tbody tr:nth-child(odd) {
  background-color: #f9f9f9;
}

/* Alertas */
.alert {
  border-radius: 5px;
  padding: 15px;
}

.alert-warning {
  background-color: #ffeeba;
  color: #856404;
}

.alert-danger {
  background-color: #f8d7da;
  color: #721c24;
}

/* Estilo del spinner */
.loading-spinner {
  text-align: center;
  margin-top: 20px;
  font-size: 24px;
  color: #007bff;
}

/* Estilo para inputs con errores */
.is-invalid {
  border-color: #dc3545;
  background-color: #f8d7da;
}

/* Transiciones */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Estilos de paginación */
.pagination {
  display: flex;
  justify-content: center;
  gap: 1rem;
}

.pagination button {
  padding: 8px 16px;
  background-color: #f1f1f1;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
}

.pagination button:disabled {
  background-color: #e0e0e0;
  cursor: not-allowed;
}
</style>
