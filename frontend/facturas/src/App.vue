<template>
  <nav class="navbar">
      <div class="logo">iHosting</div>
      <ul class="nav-links">
        <li><a href="#">Inicio</a></li>
        <li><a href="#">Hostings</a></li>
        <li><a href="#">Facturas</a></li>
        <li><a href="#">Contacto</a></li>
      </ul>
    </nav>
  <router-view />
  <footer class="footer">
      <p>&copy; 2025 iHosting | Proyecto Universitario Duoc UC</p>
    </footer>
</template>

<script>
export default {
  name: 'App'
};
</script>

<style scoped>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #0070c9;
  color: white;
  padding: 15px 30px;
  font-family: 'Inter', sans-serif;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.logo {
  font-size: 22px;
  font-weight: 600;
}

.nav-links {
  list-style: none;
  display: flex;
  gap: 25px;
  margin: 0;
  padding: 0;
}

.nav-links li a {
  color: white;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s ease-in-out;
}

.nav-links li a:hover {
  color: #dbeeff;
}
.footer {
    background-color: #042c5c;
    height: auto;
    color: white;
    text-align: center;
    padding: 20px;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
  }
</style>
