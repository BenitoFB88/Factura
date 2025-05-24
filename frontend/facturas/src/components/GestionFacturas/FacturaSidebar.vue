<template>
  <div class="layout">
    <aside class="sidebar">
      <h3 class="sidebar-title">Menú</h3>
      <ul>
        <li @click="$emit('navigate', 'clientes')">Clientes</li>
        <li @click="$emit('navigate', 'item1')">Item 1</li>
        <li @click="$emit('navigate', 'item2')">Item 2</li>
        <li @click="$emit('navigate', 'item3')">Item 3</li>
        <li @click="$emit('navigate', 'facturacion')">Facturación</li>
        <li @click="$emit('navigate', 'descarga')">Descarga</li>
      </ul>
    </aside>

    <main class="content">
      <slot></slot>
    </main>
  </div>
</template>

<script>
export default {
  name: "FacturaSidebar",
};
</script>

<style scoped>
.layout {
  display: flex;
  min-height: 80vh;
  background:linear-gradient(
    to bottom,
    #80bae0 0%,
    #46a5db 100%
  );
  
}

.sidebar {
  width: 200px;
  background-color: #1b5c88;
  color: white;
  padding: 20px;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
  min-height: 90vh;
}

.sidebar-title {
  font-size: 18px;
  margin-bottom: 15px;
}

.sidebar ul {
  list-style: none;
  padding: 0;
}

.sidebar li {
  padding: 10px;
  margin-bottom: 8px;
  cursor: pointer;
  border-radius: 5px;
  transition: background 0.2s;
}

.sidebar li:hover {
  background-color: #34577c;
}

.content {
  margin: auto;
  padding: 30px;
  background-color: transparent;
}
</style>
