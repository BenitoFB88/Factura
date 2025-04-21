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
  name: 'FacturaSidebar'
};
</script>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
}

.sidebar {
  width: 200px;
  background-color: #1e3a5f;
  color: white;
  padding: 20px;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
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
  flex: 1;
  padding: 30px;
  background-color: #f9fbff;
}
</style>
