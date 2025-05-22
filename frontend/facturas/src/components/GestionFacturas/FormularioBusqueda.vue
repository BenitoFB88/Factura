<template>
  <div class="form-container">
    <form
      @submit.prevent="$emit('buscar')"
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
};
</script>
