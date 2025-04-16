<template>
    <div class="busqueda-facturas">
      <h2>Buscar Facturas</h2>
  
      <!-- Formulario de búsqueda -->
      <form @submit.prevent="buscarFacturas" class="formulario">
        <div>
          <label for="fecha">Fecha:</label>
          <input id="fecha" type="date" v-model="filtros.fecha" />
        </div>
  
        <div>
          <label for="numero">Número de Factura:</label>
          <input id="numero" type="text" v-model="filtros.numero" />
        </div>
  
        <div>
          <label for="cliente">Cliente:</label>
          <input id="cliente" type="text" v-model="filtros.cliente" />
        </div>
  
        <button type="submit">Buscar</button>
      </form>
      <!-- Tabla de resultados -->
<table v-if="resultados.length">
  <thead>
    <tr>
      <th>Fecha</th>
      <th>Número</th>
      <th>Cliente</th>
      <th>Monto</th>
      
    </tr>
  </thead>
  <tbody>
    <tr v-for="(factura, index) in resultados" :key="index">
      <td>{{ factura.fecha }}</td>
      <td>{{ factura.numero }}</td>
      <td>{{ factura.cliente }}</td>
      <td>{{ factura.monto }}</td>
      <td>
        <button @click="editarFactura(index)">Editar</button>
      </td>
    </tr>
  </tbody>
</table>

<p v-else>No hay resultados aún.</p>

    </div>
  </template>
  
  <script>
  export default {
    name: 'BuscarFacturas',
    data() {
      return {
        filtros: {
          fecha: '',
          numero: '',
          cliente: ''
        },
        resultados: []
      }
    },
    methods: {
      buscarFacturas() {
        console.log("Buscando facturas con:", this.filtros);
        // Aquí iría la lógica de búsqueda real
        
      }
    }
  }
  </script>
  
  <style scoped>
/* Fondo de página animado */
:global(body) {
  margin: 0;
  padding: 0;
  background: linear-gradient(135deg, #dbeeff, #f1f9ff);
  background-attachment: fixed;
  font-family: 'Segoe UI', sans-serif;
}

/* Tarjeta central */
.busqueda-facturas {
    max-width: 600px;
  margin: 40px auto;
  padding: 20px;
  background-color: white;
  border-radius: 16px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.busqueda-facturas:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 32px rgba(0, 0, 0, 0.2);
}

/* Título */
h2 {
  color: #1e3a5f;
  margin-bottom: 30px;
  text-align: center;
  font-size: 28px;
}

/* Formulario */
.formulario div {
    display: flex;
  flex-wrap: wrap;
  gap: 15px;
  justify-content: space-between;
}

label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: #444;
}

input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 15px;
}

/* Botón */
button {
  background-color: #0070c9;
  color: white;
  border: none;
  padding: 12px 24px;
  font-size: 15px;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #005ea8;
}

/* Tabla */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 30px;
  background-color: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

th, td {
  padding: 14px;
  text-align: left;
  border-bottom: 1px solid #f0f0f0;
}

th {
  background-color: #0070c9;
  color: white;
}
</style>
