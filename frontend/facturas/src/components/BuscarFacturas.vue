<template>
    <div class="busqueda-facturas">
      <h2>Buscar Facturas</h2>
  
      <!-- FORMULARIO -->
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
  
      <!-- MODAL RESULTADOS -->
      <transition name="fade-modal">
        <div v-if="mostrarResultados" class="modal-overlay">
          <div class="modal-content">
            <h3>Resultados de Búsqueda</h3>
  
            <table v-if="resultados.length">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Número</th>
                  <th>Cliente</th>
                  <th>Monto</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(factura, index) in resultados" :key="index">
                  <td>{{ factura.fecha }}</td>
                  <td>{{ factura.numero }}</td>
                  <td>{{ factura.cliente }}</td>
                  <td>{{ factura.monto }}</td>
                  <td>
                    <button @click="seleccionarFactura(index)">Editar</button>
                  </td>
                </tr>
              </tbody>
            </table>
  
            <p v-else>No se encontraron resultados.</p>
  
            <div class="modal-buttons">
              <button @click="mostrarResultados = false">Cerrar</button>
            </div>
          </div>
        </div>
      </transition>
  
      <!-- MODAL EDITAR -->
      <transition name="fade-modal">
        <div v-if="facturaEditando" class="modal-overlay">
          <div class="modal-content">
            <h3>Editar Factura</h3>
  
            <label>Fecha:</label>
            <input type="date" v-model="facturaEditando.fecha" />
  
            <label>Número:</label>
            <input type="text" v-model="facturaEditando.numero" />
  
            <label>Cliente:</label>
            <input type="text" v-model="facturaEditando.cliente" />
  
            <label>Monto:</label>
            <input type="text" v-model="facturaEditando.monto" />
  
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
  export default {
    name: 'BuscarFacturas',
    data() {
      return {
        filtros: {
          fecha: '',
          numero: '',
          cliente: ''
        },
        resultados: [],
        facturaEditando: null,
        mostrarResultados: false
      };
    },
    methods: {
      buscarFacturas() {
        this.resultados = [
          {
            fecha: '2025-04-10',
            numero: 'F001',
            cliente: 'Juan Pérez',
            monto: '$120.000'
          },
          {
            fecha: '2025-04-12',
            numero: 'F002',
            cliente: 'Ana Gómez',
            monto: '$85.500'
          }
        ];
        this.mostrarResultados = true;
      },
      seleccionarFactura(index) {
        this.facturaEditando = { ...this.resultados[index], index };
      },
      guardarCambios() {
        const i = this.facturaEditando.index;
        this.resultados[i] = { ...this.facturaEditando };
        this.facturaEditando = null;
      },
      cancelarEdicion() {
        this.facturaEditando = null;
      }
    }
  };
  </script>
  
  <style scoped>
  :global(body) {
    margin: 0;
    padding: 0;
    background: linear-gradient(135deg, #dbeeff, #f1f9ff);
    background-attachment: fixed;
    font-family: 'Segoe UI', sans-serif;
  }
  
  .busqueda-facturas {
    max-width: 600px;
    margin: 40px auto;
    padding: 20px;
    background-color: white;
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
  }
  
  h2 {
    color: #1e3a5f;
    margin-bottom: 30px;
    text-align: center;
  }
  
  .formulario div {
    margin-bottom: 15px;
  }
  
  label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: #444;
  }
  
  input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
  }
  
  button {
    background-color: #0070c9;
    color: white;
    border: none;
    padding: 10px 18px;
    font-size: 14px;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  
  button:hover {
    background-color: #005ea8;
  }
  
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }
  
  th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
  }
  
  th {
    background-color: #0070c9;
    color: white;
  }
  
  td button {
    background-color: #ffc107;
    color: black;
    padding: 6px 12px;
    font-size: 13px;
    border-radius: 6px;
    cursor: pointer;
  }
  
  td button:hover {
    background-color: #e0a800;
  }
  
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
    background: white;
    padding: 30px;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  }
  
  .modal-buttons {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
    gap: 10px;
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
  