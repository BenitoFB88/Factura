<template>
  <FacturaSidebar @navigate="vistaActiva = $event">
    <div v-if="vistaActiva === 'clientes'">
      <h2>Clientes</h2>
      <p>Aquí irá la vista de clientes.</p>
    </div>

    <div v-else-if="vistaActiva === 'item1'">
      <h2>Item 1</h2>
      <p>Contenido de Item 1.</p>
    </div>

    <div v-else-if="vistaActiva === 'item2'">
      <h2>Item 2</h2>
      <p>Contenido de Item 2.</p>
    </div>

    <div v-else-if="vistaActiva === 'item3'">
      <h2>Item 3</h2>
      <p>Contenido de Item 3.</p>
    </div>

    <div v-else-if="vistaActiva === 'facturacion'">
      <BuscarFacturas />
    </div>

    <div v-else-if="vistaActiva === 'descarga'">
      <h2>Descarga</h2>
      <p>Vista de descarga de archivos.</p>
    </div>
  </FacturaSidebar>
</template>

<script>
import FacturaSidebar from './FacturaSidebar.vue';
  import BuscarFacturas from './BuscarFacturas.vue';
  export default {
    name: 'FacturaDashboard',
    components: {
      FacturaSidebar,
      BuscarFacturas
    },
    data() {
      return {
        vistaActiva: 'clientes' // vista por defecto
      };
    }
  };
</script>
