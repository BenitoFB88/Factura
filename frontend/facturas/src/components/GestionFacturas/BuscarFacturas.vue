<template>
  <div class="container py-4">
    <h2 class="text-center mb-4">Buscar Facturas</h2>
    <!-- Mensaje de éxito con transición fade-out -->
    <div v-if="mensajeExportacion" class="fade-message" :class="{ 'fade-out': isFadingOut }">
      {{ mensajeExportacion }}
    </div>

    <!-- FORMULARIO -->
    <div class="form-container">
      <form @submit.prevent="buscarFacturas" class="bg-white p-4 rounded shadow-sm">
        <div class="row g-3">
          <div class="col-md-4">
            <label for="fecha_inicio" class="form-label">Fecha Inicio:</label>
            <input id="fecha_inicio" type="date" v-model="searchParams.fecha_inicio" class="form-control" />
          </div>
          <div class="col-md-4">
            <label for="fecha_fin" class="form-label">Fecha Fin:</label>
            <input id="fecha_fin" type="date" v-model="searchParams.fecha_fin" class="form-control" />
          </div>
          <div class="col-md-4">
            <label for="folio" class="form-label">Número de Factura:</label>
            <input id="folio" type="text" v-model="searchParams.folio" class="form-control" />
          </div>
          <div class="col-md-6">
            <label for="emisor" class="form-label">Cliente:</label>
            <input id="emisor" type="text" v-model="searchParams.emisor" class="form-control" />
          </div>
          <div class="col-md-6">
            <label for="codigo_analisis" class="form-label">Código de Análisis:</label>
            <input id="codigo_analisis" type="text" v-model="searchParams.codigo_analisis" class="form-control" />
          </div>
        </div>
        <div class="mt-4 d-flex justify-content-end">
          <button type="submit" class="btn btn-primary">Buscar</button>
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
          <button class="modal-close-btn" @click="cerrarModal">X</button>
          <h3>Resultados de Búsqueda</h3>
          <table v-if="paginatedInvoices.length" class="table table-striped table-bordered text-small">

            <thead>
              <tr>
                <th>Fecha</th>
                <th>Emisor</th>
                <th>Receptor</th>
                <th>Folio</th>
                <th>Total</th>
                <th>Código Análisis</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(factura, index) in paginatedInvoices" :key="index">
                <td :title="formatDate(factura.fecha)">
                  {{ formatDate(factura.fecha) }}
                </td>
                <td :title="factura.emisor">{{ factura.emisor }}</td>
                <td :title="factura.receptor">{{ factura.receptor }}</td>
                <td :title="factura.folio">{{ factura.folio }}</td>
                <td :title="factura.total">{{ factura.total }}</td>
                <td :title="factura.iecodanalisis">
                  {{ factura.iecodanalisis }}
                </td>
                <td>
                  <button @click="seleccionarFactura(index)" :title="'Editar factura ' + factura.folio">
                    Editar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <p v-else>No se encontraron resultados.</p>

          <div v-if="totalPages > 1" class="pagination">
            <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1">
              Anterior
            </button>
            <span>{{ currentPage }} de {{ totalPages }}</span>
            <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages">
              Siguiente
            </button>
            <button @click="exportarResultados" class="exportar-btn">
              Exportar Resultados
            </button>
            <button @click="guardarCambiosEnBD" class="guardar-btn">
              Guardar Cambios
            </button>
            <button class="btn btn-success" @click="actualizarCodigosDesdeApi">Importar Código de Análisis</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- MODAL EDITAR -->

    <!-- MODAL EDITAR -->
    <transition name="fade-modal">
      <div v-if="facturaEditando" class="modal-overlay" @click="cerrarModal">
        <div class="modal-content" @click.stop>
          <h3>Editar Factura</h3>
          <div class="form-group">
            <label>Código de Análisis:</label>
            <select v-model="facturaEditando.codigo_analisis" class="form-control">
              <option disabled value="">Selecciona un código</option>
              <option v-for="codigo in codigosAnalisis" :key="codigo" :value="codigo">
                {{ codigo }}
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
  import ExcelJS from "exceljs";
  import { saveAs } from "file-saver";

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
        },
        codigosAnalisis: [
          'CA001', 'CA002', 'CA003', 'CA004', 'CA005',
          'CA006', 'CA007', 'CA008', 'CA009', 'CA010',
          'CA011', 'CA012', 'CA013', 'CA014', 'CA015',
          'CA016', 'CA017', 'CA018', 'CA019', 'CA020',
        ],
        allInvoices: [],
        paginatedInvoices: [],
        errorMessage: "",
        loading: false,
        currentPage: 1,
        resultsPerPage: 10,
        totalResults: 0,
        facturaEditando: null,
        mostrarResultados: false,
        mensajeExportacion: "",
        iva: 0.19,
        codigosNoUsados: [],
      };
    },
    computed: {
      totalPages() {
        return Math.ceil(this.totalResults / this.resultsPerPage);
      },
    },
    methods: {
      async actualizarCodigosDesdeApi() {
        try {
          const authData = JSON.parse(localStorage.getItem("auth"));
          const token = authData?.access_token;

          if (!token) {
            alert("Token de autenticación no disponible.");
            return;
          }

          const response = await axios.get("http://localhost/api/actualizarcodigos", {
            headers: {
              Authorization: `Bearer ${token}`,
              Accept: "application/json",
              "Content-Type": "application/json",
            },
          });

          if (response.data?.mensaje) {
            alert(`✅ ${response.data.mensaje}`);
          } else {
            alert("Actualización completada.");
          }

          console.log("Respuesta de actualización:", response.data);
        } catch (error) {
          console.error("❌ Error al actualizar códigos:", error.response?.data || error.message);
          alert("Error al actualizar los códigos. Revisa la consola.");
        }
      },

      formatDate(date) {
        if (!date) return "";
        return new Date(date).toLocaleDateString("es-CL");
      },
      editarCodigoAnalisis() {
        this.allInvoices.forEach((factura) => {
          factura.codigo_analisis = this.searchParams.codigo_analisis;
          factura.iecodanalisis = this.searchParams.codigo_analisis;
        });
        this.paginateInvoices();
      },
      seleccionarFactura(index) {
        this.facturaEditando = { ...this.paginatedInvoices[index], index };
      },
      guardarCambios() {
        const i = this.facturaEditando.index;
        this.facturaEditando.iecodanalisis = this.facturaEditando.codigo_analisis;
        this.paginatedInvoices[i] = {
          ...this.paginatedInvoices[i],
          ...this.facturaEditando,
        };
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
            this.allInvoices = response.data.map(factura => ({
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
        try {
          const datosAGuardar = this.paginatedInvoices.map((factura) => ({
            emisor: factura.emisor,
            folio: factura.folio,
            iecuenta: factura.iecuenta,
            iecodanalisis: factura.iecodanalisis,
          }));

          const token = JSON.parse(localStorage.getItem("auth"))?.access_token;

          if (!token) {
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

          const resultado = await response.json();

          if (response.ok) {
            alert("Cambios guardados exitosamente.");
          } else {
            alert("Error al guardar cambios. Revisa la consola.");
            console.error("Error en respuesta:", resultado);
          }
        } catch (error) {
          console.error("Error de red al guardar cambios:", error);
          alert("Error de red al intentar guardar los cambios.");
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
      async exportarResultados() {
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet("Facturas");

        worksheet.columns = [
          { header: "Emisor", key: "emisor", width: 20 },
          { header: "Folio", key: "folio", width: 10 },
          { header: "Código Análisis", key: "iecodanalisis", width: 20 },
        ];

        this.paginatedInvoices.forEach((factura) => {
          worksheet.addRow({
            emisor: factura.emisor,
            folio: factura.folio,
            iecodanalisis: factura.iecodanalisis,
          });
        });

        const blob = await workbook.xlsx.writeBuffer();
        saveAs(new Blob([blob]), "facturas.xlsx");
      },
      cerrarModal() {
        this.mostrarResultados = false;
        this.facturaEditando = null;
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
    table-layout: fixed;
    /* Asegura que las columnas tengan el mismo ancho */
  }


  th,
  td {
    border: 1px solid #ddd;
    padding: 8px;
    /* más compacto */
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


  .table>:not(caption)>*>* {
    padding: .2rem .2rem;
    text-align: center;
    color: var(--bs-table-color-state, var(--bs-table-color-type, var(--bs-table-color)));
    background-color: var(--bs-table-bg);
    border-bottom-width: var(--bs-border-width);
    box-shadow: inset 0 0 0 9999px var(--bs-table-bg-state, var(--bs-table-bg-type, var(--bs-table-accent-bg)));
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


  .exportar-btn {
    background-color: #28a745 !important;
    /* Verde */
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    padding: 8px 16px;
    font-size: 14px;
    margin-left: 90px;
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