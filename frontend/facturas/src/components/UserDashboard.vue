<template>
  <div class="container">
    <!-- Formulario de Búsqueda -->
    <form @submit.prevent="searchInvoices" class="search-form">
      <div class="form-group">
        <label for="fecha">Fecha:</label>
        <input type="date" v-model="searchParams.fecha" id="fecha" class="form-control">
      </div>
      <div class="form-group">
        <label for="cliente">Cliente:</label>
        <input type="text" v-model="searchParams.cliente" id="cliente" class="form-control">
      </div>
      <div class="form-group">
        <label for="numero_factura">Número de Factura:</label>
        <input type="number" v-model="searchParams.numero_factura" id="numero_factura" class="form-control">
      </div>
      <button type="submit" class="btn btn-primary">Buscar</button>
    </form>

    <!-- Icono de carga -->
    <div v-if="loading" class="loading-spinner">
      <i class="fas fa-spinner fa-spin"></i> Cargando...
    </div>

    <!-- Tabla de resultados -->
    <table v-if="invoices.length > 0" class="table table-striped mt-4">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Emisor</th>
          <th>Receptor</th>
          <th>Número de Factura</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="invoice in invoices" :key="invoice.id">
          <td>{{ invoice.fecha }}</td>
          <td>{{ invoice.emisor }}</td>
          <td>{{ invoice.receptor }}</td>
          <td>{{ invoice.folio }}</td>
          <td>{{ invoice.total }}</td>
        </tr>
      </tbody>
    </table>

    <!-- Mensaje si no hay resultados -->
    <div v-else class="alert alert-warning mt-4">No se encontraron facturas.</div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="alert alert-danger mt-4">{{ errorMessage }}</div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      searchParams: {
        fecha: '',
        cliente: '',
        numero_factura: ''
      },
      invoices: [],
      errorMessage: '',
      loading: false  // Nueva propiedad para manejar el estado de carga
    };
  },
  methods: {
    // Función para buscar facturas
    async searchInvoices() {
      try {
        // Activar el indicador de carga
        this.loading = true;

        // Verificar que el token esté presente en el localStorage
        const token = localStorage.getItem('access_token');
        
        if (!token) {
          this.errorMessage = 'Token de autenticación no encontrado.';
          return;
        }

        // Convertir numero_factura a número, si tiene valor
        if (this.searchParams.numero_factura) {
          this.searchParams.numero_factura = parseInt(this.searchParams.numero_factura);
        }

        // Hacer la petición GET con los parámetros de búsqueda
        const response = await axios.get('http://localhost/api/invoices/search', {
          params: this.searchParams,
          headers: {
            'Authorization': `Bearer ${token}` // Agregar el token en los encabezados
          }
        });

        // Mostrar la respuesta en la consola para ver lo que recibe desde el backend
        console.log('Respuesta del backend:', response.data);

        // Asignar las facturas recibidas a la variable invoices
        this.invoices = response.data;

      } catch (error) {
        console.error('Error al realizar la búsqueda:', error);
        this.errorMessage = 'Error al realizar la búsqueda. Intenta nuevamente.';
      } finally {
        // Desactivar el indicador de carga una vez finalizada la búsqueda
        this.loading = false;
      }
    }
  }
};
</script>

<style scoped>
/* Estilos personalizados para el formulario y tabla */

.container {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
}

.search-form {
  background-color: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.form-group {
  margin-bottom: 1.5rem;
}

input[type="date"], input[type="text"], input[type="number"] {
  width: 100%;
  padding: 8px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  margin-top: 0.5rem;
}

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

.table {
  width: 100%;
  margin-top: 2rem;
  border-collapse: collapse;
  background-color: #fff;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

th, td {
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

/* Estilos para el spinner de carga */
.loading-spinner {
  text-align: center;
  margin-top: 20px;
  font-size: 24px;
  color: #007bff;
}
</style>
