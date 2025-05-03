<template>
  <div class="container py-4">
    <h2 class="text-center mb-4">Buscar Facturas</h2>
    <!-- Mensaje de éxito con transición fade-out -->
    <div
      v-if="mensajeExportacion"
      class="fade-message"
      :class="{ 'fade-out': isFadingOut }"
    >
      {{ mensajeExportacion }}
    </div>

    <!-- FORMULARIO -->
    <div class="form-container">
      <form
        @submit.prevent="buscarFacturas"
        class="bg-white p-4 rounded shadow-sm"
      >
        <div class="row g-3">
          <div class="col-md-4">
            <label for="fecha_inicio" class="form-label">Fecha Inicio:</label>
            <input
              id="fecha_inicio"
              type="date"
              v-model="searchParams.fecha_inicio"
              class="form-control"
            />
          </div>
          <div class="col-md-4">
            <label for="fecha_fin" class="form-label">Fecha Fin:</label>
            <input
              id="fecha_fin"
              type="date"
              v-model="searchParams.fecha_fin"
              class="form-control"
            />
          </div>
          <div class="col-md-4">
            <label for="numero_factura" class="form-label"
              >Número de Factura:</label
            >
            <input
              id="numero_factura"
              type="text"
              v-model="searchParams.numero_factura"
              class="form-control"
            />
          </div>
          <div class="col-md-6">
            <label for="cliente" class="form-label">Cliente:</label>
            <input
              id="cliente"
              type="text"
              v-model="searchParams.cliente"
              class="form-control"
            />
          </div>
          <div class="col-md-6">
            <label for="codigo_analisis" class="form-label"
              >Código de Análisis:</label
            >
            <input
              id="codigo_analisis"
              type="text"
              v-model="searchParams.codigo_analisis"
              class="form-control"
            />
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

          <button @click="exportarResultados" class="exportar-btn">
            Exportar Resultados
          </button>

          <table v-if="paginatedInvoices.length">
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
                <td>{{ formatDate(factura.fecha) }}</td>
                <td>{{ factura.emisor }}</td>
                <td>{{ factura.receptor }}</td>
                <td>{{ factura.folio }}</td>
                <td>{{ factura.total }}</td>
                <td>{{ factura.codigo_analisis }}</td>
                <td>
                  <button @click="seleccionarFactura(index)">Editar</button>
                </td>
              </tr>
            </tbody>
          </table>
          <p v-else>No se encontraron resultados.</p>

          <div v-if="totalPages > 1" class="pagination">
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
            >
              Anterior
            </button>
            <span>{{ currentPage }} de {{ totalPages }}</span>
            <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === totalPages"
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
            <label>Total:</label>
            <input type="text" v-model="facturaEditando.total" />
          </div>
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
import * as XLSX from "xlsx";
export default {
  name: "BuscarFacturas",
  data() {
    return {
      searchParams: {
        fecha_inicio: "",
        fecha_fin: "",
        numero_factura: "",
        cliente: "",
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
        const response = await axios.get(
          "http://localhost/api/invoices/search",
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
        "Fecha",
        "Emisor",
        "Receptor",
        "Folio",
        "Total",
        "Código Análisis",
      ];

      const ws_data = this.allInvoices.map((f) => [
        new Date(f.fecha), // mantener como Date
        f.emisor,
        f.receptor,
        f.folio,
        f.total,
        f.codigo_analisis,
      ]);

      // Crear la hoja de trabajo
      const ws = XLSX.utils.aoa_to_sheet([headers, ...ws_data]);

      // Aplicar estilo a los encabezados
      for (let col = 0; col < headers.length; col++) {
        const cell_address = XLSX.utils.encode_cell({ r: 0, c: col });
        if (!ws[cell_address]) ws[cell_address] = {};

        // Estilo para los encabezados
        ws[cell_address].s = {
          font: { name: "Calibri", sz: 12, bold: true }, // Negrita
          fill: { fgColor: { rgb: "FFFF00" } }, // Fondo amarillo
          alignment: {
            horizontal: "center",
            vertical: "center",
            wrapText: true,
          }, // Alineación y ajuste de texto
          border: {
            // Bordes negros
            top: { style: "thin", color: { rgb: "000000" } },
            left: { style: "thin", color: { rgb: "000000" } },
            bottom: { style: "thin", color: { rgb: "000000" } },
            right: { style: "thin", color: { rgb: "000000" } },
          },
        };
      }

      // Aplicar bordes a todas las celdas
      const range = XLSX.utils.decode_range(ws["!ref"]);
      for (let R = range.s.r; R <= range.e.r; ++R) {
        for (let C = range.s.c; C <= range.e.c; ++C) {
          const cell_address = XLSX.utils.encode_cell({ r: R, c: C });
          if (!ws[cell_address]) continue;

          if (!ws[cell_address].s) ws[cell_address].s = {};

          // Estilo normal para celdas
          ws[cell_address].s.font = { name: "Calibri", sz: 12 };
          ws[cell_address].s.alignment = {
            horizontal: "center",
            vertical: "center",
            wrapText: true,
          };

          // Bordes negros para todas las celdas
          ws[cell_address].s.border = {
            top: { style: "thin", color: { rgb: "000000" } },
            left: { style: "thin", color: { rgb: "000000" } },
            bottom: { style: "thin", color: { rgb: "000000" } },
            right: { style: "thin", color: { rgb: "000000" } },
          };

          // Formato especial para columnas específicas
          if (R !== 0) {
            if (C === 0) {
              ws[cell_address].z = "dd-mm-yyyy"; // Fecha
            } else if (C === 4) {
              ws[cell_address].z = "#,##0"; // Total en CLP
            }
          }
        }
      }

      // Ajustar ancho de columnas (Fecha fija, resto automático)
      ws["!cols"] = headers.map((_, i) => {
        if (i === 0) return { wch: 12 }; // Fecha fija
        const maxLength = ws_data.reduce((max, row) => {
          const val = row[i];
          const str = val ? val.toString() : "";
          return Math.max(max, str.length);
        }, headers[i].length);
        return { wch: maxLength + 2 };
      });

      try {
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Facturas");
        XLSX.writeFile(wb, "facturas_exportadas.xlsx");

        // Mensaje de éxito
        this.mensajeExportacion =
          "Exportación exitosa. El archivo Excel ha sido generado correctamente.";

        // Iniciar el fade-out después de 2 segundos
        setTimeout(() => {
          this.isFadingOut = true;

          // Esperar a que termine la transición para borrar el mensaje
          setTimeout(() => {
            this.mensajeExportacion = "";
            this.isFadingOut = false;
          }, 1000); // Tiempo de la transición de fade-out (1s)
        }, 2000); // El mensaje se mantendrá visible durante 2 segundos antes de empezar a desvanecerse
      } catch (error) {
        console.error("Error al generar el archivo Excel", error);
        // Mensaje de error
        this.mensajeExportacion =
          "Error al generar el archivo Excel. Por favor, intente nuevamente.";
      }
    },
  },
};
</script>

<style scoped>
/* Todos tus estilos anteriores se mantienen (omito aquí para no hacerte demasiado largo esto) */
</style>

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

.fade-message {
  transition: opacity 1s ease-out;
  opacity: 1;
  background-color: #4caf50; /* Verde de éxito */
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
</style>
