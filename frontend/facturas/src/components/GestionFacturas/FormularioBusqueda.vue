<template>
  <div class="form-container">
    <!-- Contenedor para agrupar el formulario y el botón limpiar -->
    <div class="form-wrapper">
      <form
        @submit.prevent="handleBuscar"
        class="bg-white p-4 rounded shadow-sm"
      >
        <div class="row g-3">
          <div class="col-md-4">
            <label for="fecha_inicio" class="form-label">Fecha Inicio:</label>
            <input
              id="fecha_inicio"
              type="date"
              v-model="localSearchParams.fecha_inicio"
              class="form-control"
            />
          </div>
          <div class="col-md-4">
            <label for="fecha_fin" class="form-label">Fecha Fin:</label>
            <input
              id="fecha_fin"
              type="date"
              v-model="localSearchParams.fecha_fin"
              class="form-control"
            />
          </div>
          <div class="col-md-4">
            <label for="folio" class="form-label">Número de Factura:</label>
            <input
              id="folio"
              type="text"
              v-model="localSearchParams.folio"
              class="form-control"
            />
          </div>
          <div class="col-md-6">
            <label for="emisor" class="form-label">Cliente:</label>
            <input
              id="emisor"
              type="text"
              v-model="localSearchParams.emisor"
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
              v-model="localSearchParams.codigo_analisis"
              class="form-control"
            />
          </div>
        </div>
        
        <div class="mt-4 d-flex justify-content-end">
          <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
      </form>

      <!-- Botón "Limpiar" posicionado visualmente dentro del contenedor -->
      <button
        type="button"
        class="btn btn-primary btn-limpiar"
        @click="$emit('limpiar')"
      >
        Limpiar
      </button>
    </div>
    
  </div>
  <!-- Muestra el mensaje de error si no hay información en ningún campo -->
        <div
          v-if="mensajeError"
          class="alert alert-danger mt-3"
          :class="{ 'fade-out': isFadingOut }"
        >
          {{ mensajeError }}
        </div>
</template>

<script>
export default {
  name: "FormularioBusqueda",
  props: {
    modelValue: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      mensajeError: "",
      isFadingOut: false,
    };
  },
  computed: {
    localSearchParams: {
      get() {
        return this.modelValue;
      },
      set(val) {
        this.$emit("update:modelValue", val);
      },
    },
  },
  methods: {
    handleBuscar() {
      const params = this.localSearchParams;
      // Comprueba si todos los campos están vacíos
      if (
        !params.fecha_inicio &&
        !params.fecha_fin &&
        !params.folio &&
        !params.emisor &&
        !params.codigo_analisis
      ) {
        this.mensajeError = "Al menos un campo debe tener información.";
        this.isFadingOut = false;
        // Inicia el fade-out después de 3 segundos
        setTimeout(() => {
          this.isFadingOut = true;
        }, 2000);
        // Limpia el mensaje luego de la transición (0.5s de fade-out)
        setTimeout(() => {
          this.mensajeError = "";
          this.isFadingOut = false;
        }, 2500);
      } else {
        this.mensajeError = "";
        this.$emit("buscar");
      }
    },
  },
};
</script>
<style scoped>
.form-wrapper {
  position: relative;
  /* Puedes ajustar el padding o margin para que se vea agrupado */
}

/* Posicionamiento absoluto del botón limpiar dentro del contenedor */
.btn-limpiar {
  position: absolute;
  bottom: 1.45rem;
  right: 8rem;
}
.btn:hover {
  background-color: #5347c2;

}

.btn-limpiar:hover {
  position: absolute;
  bottom: 1.45rem;
  right: 8rem;
  background-color: #5347c2;
}
/* Clase para la animación de fade-out */
.fade-out {
  opacity: 0;
  transition: opacity 0.5s ease-out;
}
</style>
