<template>
  <transition
    enter-active-class="transition transform duration-300"
    enter-from-class="opacity-0 scale-95"
    enter-to-class="opacity-100 scale-100"
    leave-active-class="transition transform duration-300"
    leave-from-class="opacity-100 scale-100"
    leave-to-class="opacity-0 scale-95"
  >
    <div v-if="mostrarResultados" class="modal-overlay" @click="cerrarModal">
      <div class="modal-content" @click.stop>
        <button class="modal-close-btn" @click="cerrarModal">X</button>
        <h3 class="text-lg font-semibold mb-4">Resultados de Búsqueda</h3>
        <table
          v-if="paginatedInvoices.length"
          class="w-full border-collapse text-sm"
        >
          <thead class="bg-gray-200">
            <tr>
              <th class="border px-2 py-1">Fecha</th>
              <th class="border px-2 py-1">Emisor</th>
              <th class="border px-2 py-1">Receptor</th>
              <th class="border px-2 py-1">Folio</th>
              <th class="border px-2 py-1">Total</th>
              <th class="border px-2 py-1">Código Análisis</th>
              <th class="border px-2 py-1">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr
              v-for="(factura, index) in paginatedInvoices"
              :key="index"
              class="hover:bg-gray-100"
            >
              <td :title="formatDate(factura.fecha)">
                {{ formatDate(factura.fecha) }}
              </td>
              <td class="border px-2 py-1" :title="factura.emisor">
                {{ factura.emisor }}
              </td>
              <td class="border px-2 py-1" :title="factura.receptor">
                {{ factura.receptor }}
              </td>
              <td class="border px-2 py-1" :title="factura.folio">
                {{ factura.folio }}
              </td>
              <td class="border px-2 py-1" :title="factura.total">
                {{ factura.total }}
              </td>
              <td class="border px-2 py-1" :title="factura.iecodanalisis">
                {{ factura.iecodanalisis }}
              </td>
              <td>
                <button
                  class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition"
                  @click="$emit('editar-factura', index)"
                  :title="'Editar factura ' + factura.folio"
                >
                  Editar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <p v-else class="flex justify-center gap-4 mt-4">
          No se encontraron resultados.
        </p>

        <!-- Paginación -->
        <div
          v-if="totalPages > 1"
          class="pagination flex justify-center gap-4 mt-4"
        >
          <button
            @click="$emit('cambiar-pagina', currentPage - 1)"
            :disabled="currentPage === 1"
            class="bg-blue-500 text-white px-3 py-1 rounded "
          >
            Anterior
          </button>
          <span class="flex items-center text-base"
            >{{ currentPage }} de {{ totalPages }}</span
          >
          <button
            @click="$emit('cambiar-pagina', currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="text-white px-3 py-1 rounded"
          >
            Siguiente
          </button>
        </div>

        <!-- BOTONES UNIFORMES -->
        <div class="modal-buttons flex flex-wrap justify-center gap-4 mt-6">
          <ExportarFacturas :all-invoices="allInvoices" />
          <button
            @click="$emit('guardar-cambios')"
            class="text-white px-4 py-2 rounded hover:bg-blue-600 transition"
          >
            Guardar Cambios
          </button>
          <button
            @click="actualizarCodigosDesdeApi"
            class="text-white px-4 py-2 rounded hover:bg-blue-600 transition"
          >
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
          "http://localhost:8081/api/actualizarcodigos",
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

/* Contenido modal */
.modal-content {
  position: relative;
  background: white;
  padding: 20px;
  border-radius: 12px;
  width: 90%;
  height: auto;
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

th,
td {
  border: 1px solid #ddd;
  padding: 8px;
}
th {
  background-color: #cecaca;
}
td button {
  background-color: #0070c9;
  font-size: 14px;
}
td button:hover {
  background-color: #5347c2;
}

/* Paginación */
.pagination {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-top: 20px;
}
.pagination button {
  background-color: #0070c9;
  border: none;
}
.pagination button:hover {
  background-color: #5347c2;
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
  background-color: #0070c9 ;
}
.modal-buttons button:hover,
.modal-buttons :deep(button:hover) {
  background-color: #5347c2 ;
}
.modal-buttons button:active,
.modal-buttons :deep(button:active) {
  background-color: #5347c2;
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
  border-top: 6px solid #6cc1f1;
  border-radius: 50%;
  width: 120px;
  height: 120px;
  animation: spin 1s linear infinite;
  margin: 20px auto 0;
  z-index: 99999;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
