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
                <td :title="formatDate(factura.fecha)">{{ formatDate(factura.fecha) }}</td>
                <td :title="factura.emisor">{{ factura.emisor }}</td>
                <td :title="factura.receptor">{{ factura.receptor }}</td>
                <td :title="factura.folio">{{ factura.folio }}</td>
                <td :title="factura.total">{{ factura.total }}</td>
                <td :title="factura.codigo_analisis">{{ factura.codigo_analisis }}</td>
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
            <label>Código de Análisis:</label>
            <input type="text" v-model="facturaEditando.codigo_analisis" />
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
  import { saveAs } from "file-saver"; export default {
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
        iva: 0.19
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
        return new Date(date).toLocaleDateString("es-CL");
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
        try {
          this.loading = true;
          const authData = JSON.parse(localStorage.getItem("auth"));
          const token = authData?.access_token;
          if (!token) {
            this.errorMessage = "Token de autenticación no encontrado.";
            return;
          }
          const params = { ...this.searchParams };
          console.log(params);
          const response = await axios.get(
            "http://localhost/api/buscar-dte",
            {
              params,
              headers: { Authorization: `Bearer ${token}` },
            }
          );
          if (Array.isArray(response.data)) {
            this.allInvoices = response.data;
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
      cerrarModal() {
        this.mostrarResultados = false;
        this.facturaEditando = null;
      },
      exportarResultados() {
        const headers = [
          "Fecha Docto",//Fecha Emision
          "Tipo Docto",//33 Factura
          "Nro Docto",//Folio
          "Rut",//Receptor
          "Nombre",//Receptor
          "CTA Neto",//Total sin IVA
          "CA Neto",
          "Monto Neto",
          "CTA Exento",
          "CA Exento",
          "CC Exento",
          "COD SII Otro",
          "CTA Otro",
          "CC Otro",
          "Monto Otro",
          "% IVA",//19
          "IVA",//Monto de IVA
          "Total",
          "Glosa"
        ];

        const workbook = new ExcelJS.Workbook();
        const sheet = workbook.addWorksheet("Facturas");

        // Agregar encabezados con estilo
        const headerRow = sheet.addRow(headers);
        headerRow.eachCell((cell) => {
          cell.font = { name: "Calibri", size: 12, bold: true };
          cell.fill = {
            type: "pattern",
            pattern: "solid",
            fgColor: { argb: "7caff1" },
          };
          cell.alignment = { horizontal: "center", vertical: "middle", wrapText: true };
          cell.border = {
            top: { style: "thin" },
            left: { style: "thin" },
            bottom: { style: "thin" },
            right: { style: "thin" },
          };
        });

        // Agregar datos con formato
        this.allInvoices.forEach((f) => {
          const row = sheet.addRow([
            this.formatDate(f.fecha), // Puedes ajustar esto si es string o Date
            f.tipo_dte,
            f.folio,
            f.receptor,
            f.receptor,
            f.neto,
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            this.iva * 100,
            (f.neto * this.iva),
            f.neto * (1 + this.iva),
            "Sin Glosa"
          ]);
          row.eachCell((cell, colNumber) => {
            cell.font = { name: "Calibri", size: 12 };
            cell.alignment = { horizontal: "center", vertical: "middle", wrapText: true };
            cell.border = {
              top: { style: "thin" },
              left: { style: "thin" },
              bottom: { style: "thin" },
              right: { style: "thin" },
            };

            // Formato de fecha y moneda
            if (colNumber === 1) {
              cell.numFmt = "dd-mm-yyyy"; // Fecha
            }
            if (colNumber === 5) {
              cell.numFmt = "#,##0"; // Total (moneda sin decimales)
            }
          });
        });

        // Ajustar ancho de columnas automáticamente
        sheet.columns.forEach((column) => {
          let maxLength = 10;
          column.eachCell({ includeEmpty: true }, (cell) => {
            const value = cell.value ? cell.value.toString() : "";
            maxLength = Math.max(maxLength, value.length);
          });
          column.width = maxLength + 2;
        });

        // Generar y descargar el archivo
        workbook.xlsx.writeBuffer().then((buffer) => {
          saveAs(new Blob([buffer]), "facturas_exportadas.xlsx");

          this.mensajeExportacion = "Exportación exitosa. El archivo Excel ha sido generado correctamente.";
          setTimeout(() => {
            this.isFadingOut = true;
            setTimeout(() => {
              this.mensajeExportacion = "";
              this.isFadingOut = false;
            }, 1000);
          }, 2000);
        }).catch((error) => {
          console.error("Error al generar el archivo Excel", error);
          this.mensajeExportacion = "Error al generar el archivo Excel. Por favor, intente nuevamente.";
        });
      }
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
</style>