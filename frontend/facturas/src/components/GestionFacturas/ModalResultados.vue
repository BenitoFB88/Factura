<template>
  <transition name="fade-modal">
    <div v-if="mostrarResultados" class="modal-overlay" @click="cerrarModal">
      <div class="modal-content" @click.stop>
        <button class="modal-close-btn" @click="cerrarModal">X</button>
        <h3>Resultados de Búsqueda</h3>
        <table
          v-if="paginatedInvoices.length"
          class="table table-striped table-bordered text-small"
        >
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
                <button
                  @click="$emit('editar-factura', index)"
                  :title="'Editar factura ' + factura.folio"
                >
                  Editar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <p v-else>No se encontraron resultados.</p>

        <div v-if="totalPages > 1" class="pagination">
          <button
            @click="$emit('cambiar-pagina', currentPage - 1)"
            :disabled="currentPage === 1"
          >
            Anterior
          </button>
          <span>{{ currentPage }} de {{ totalPages }}</span>
          <button
            @click="$emit('cambiar-pagina', currentPage + 1)"
            :disabled="currentPage === totalPages"
          >
            Siguiente
          </button>
        </div>

        <!-- BOTONES UNIFORMES -->
        <div class="modal-buttons">
          <ExportarFacturas :all-invoices="this.allInvoices" />
          <button @click="$emit('guardar-cambios')">Guardar Cambios</button>
          <button @click="actualizarCodigosDesdeApi">
            Importar Código de Análisis
          </button>
        </div>
      </div>

      <div v-if="loading" class="loading-overlay">
        <div class="loading-spinner"></div>
      </div>
    </div>
  </transition>
</template>

<script>
import ExportarFacturas from "./ExportarFacturas.vue";
import axios from "axios";

export default {
  name: "ModalResultados",
  components: {
    ExportarFacturas,
  },
  data() {
    return {
      loading: false,
      isFadingOut: false, // o el valor inicial que necesites
      codigos: [], // Propiedad reactiva para almacenar los códigos
    };
  },
  props: {
    mostrarResultados: { type: Boolean, required: true },
    paginatedInvoices: { type: Array, default: () => [] },
    currentPage: { type: Number, default: 1 },
    totalPages: { type: Number, default: 1 },
    allInvoices: { type: Array, default: () => [] },
  },
  methods: {
    cerrarModal() {
      this.$emit("cerrar-modal");
    },
    formatDate(date) {
      const d = new Date(date);
      return d.toLocaleDateString();
    },
    async actualizarCodigosDesdeApi() {
      try {
        this.loading = true;
        const authData = JSON.parse(localStorage.getItem("auth"));
        const token = authData?.access_token;

        if (!token) {
          alert("Token de autenticación no disponible.");
          return;
        }

        const response = await axios.post(
          "http://localhost/api/actualizarcodigos",
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        if (response.data?.mensaje) {
          alert(`✅ ${response.data.mensaje}`);
        } else {
          alert("Actualización completada.");
        }

        // Guardar el array de códigos en una propiedad reactiva, por ejemplo: this.codigos
        this.codigos = response.data.codigos_actualizados || [];
        this.$emit("codigos-actualizados", this.codigos);

        console.log("Respuesta de actualización:", response.data);

      } catch (error) {
        console.error(
          "❌ Error al actualizar códigos:",
          error.response?.data || error.message
        );
        alert("Error al actualizar los códigos. Revisa la consola.");
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
/* Animación entrada/salida modal */
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

/* Fondo oscuro del modal */
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

/* Contenido modal */
.modal-content {
  position: relative;
  background: white;
  padding: 20px;
  border-radius: 12px;
  width: 90%;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  font-size: 14px;
}

/* Botón cerrar (X) */
.modal-close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  font-size: 18px;
  font-weight: bold;
  background-color: #f44336;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 6px 12px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}
.modal-close-btn:hover {
  background-color: #d32f2f;
}

/* Estilos tabla */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  table-layout: fixed;
}
th,
td {
  border: 1px solid #ddd;
  padding: 8px;
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

/* BOTONES PRINCIPALES UNIFICADOS */
.modal-buttons {
  display: flex;
  justify-content: center;
  gap: 12px;
  margin-top: 20px;
  flex-wrap: wrap;
}
.modal-buttons button,
.modal-buttons :deep(button) {
  min-width: 180px;
  padding: 10px 16px;
  font-size: 14px;
  border-radius: 6px;
  background-color: #0070c9;
  color: white;
  border: none;
  cursor: pointer;
  text-align: center;
}
.modal-buttons button:hover,
.modal-buttons :deep(button:hover) {
  background-color: #005ba1;
}
.modal-buttons button:active,
.modal-buttons :deep(button:active) {
  background-color: #003d7a;
}
.exportar-resultados {
  background-color: #07612a;
}

/* Cargando */
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
</style>
