<template>
  <div id="dashboard">
    <header class="banner">
      <h1>Bienvenido al Dashboard</h1>
      <button @click="logout">Cerrar Sesión</button>
    </header>

    <main class="main-content">
      <section class="stats">
        <h2>Estadísticas</h2>
        <div class="stat-card">
          <h3>Total de Facturas</h3>
          <p>100</p>
        </div>
        <div class="stat-card">
          <h3>Facturas Pendientes</h3>
          <p>25</p>
        </div>
        <div class="stat-card">
          <h3>Facturas Pagadas</h3>
          <p>75</p>
        </div>
      </section>

      <section class="actions">
        <h2>Acciones Rápidas</h2>
        <div class="action-card">
          <button @click="createInvoice">Crear Factura</button>
        </div>
        <div class="action-card">
          <button @click="viewInvoices">Ver Facturas</button>
        </div>
      </section>
    </main>

    <footer class="footer">© 2025 iHosting</footer>
  </div>
</template>

<script>
export default {
  name: "UserDashboard", // Aquí debes asegurarte de que el nombre del componente sea 'UserDashboard'
  methods: {
    logout() {
      this.$router.push('/');
    },
    createInvoice() {
      console.log("Creando factura...");
    },
    viewInvoices() {
      console.log("Mostrando facturas...");
    }
  }
};
</script>

<style scoped>
#dashboard {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  background-color: #f4f4f4;
  height: 100vh;
}

.banner {
  background-color: #00bcd4; /* Celeste fuerte */
  color: white;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.main-content {
  padding: 2rem;
}

.stats {
  display: flex;
  justify-content: space-between;
  margin-bottom: 2rem;
}

.stat-card {
  background-color: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  width: 30%;
  text-align: center;
}

.stat-card h3 {
  color: #00acc1;
}

.actions {
  display: flex;
  justify-content: space-between;
}

.action-card {
  background-color: white;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  width: 45%;
  text-align: center;
}

button {
  padding: 0.8rem;
  background-color: #00bcd4;
  color: white;
  border: none;
  border-radius: 4px;
  font-weight: bold;
  cursor: pointer;
}

button:hover {
  background-color: #0097a7;
}

footer {
  background-color: #00bcd4;
  color: white;
  text-align: center;
  padding: 1rem;
  font-size: 1rem;
}
</style>
