<template>
  <!-- Contenedor principal de toda la aplicación -->
  <div id="app">
    
    <!-- Barra de navegación superior -->
    <nav class="navbar">
      <div class="logo">
        <!-- Logo de la empresa -->
        <img src="@/assets/Ihosting.png" alt="iHosting Logo" class="logo-img" />
        IHosting
      </div>

      <!-- Enlaces de navegación -->
      <ul class="nav-links">
        <li><a href="#">Inicio</a></li>
        <li><a href="#">Hostings</a></li>
        <li><a href="#">Facturas</a></li>
        <li><a href="#">Contacto</a></li>
      </ul>
    </nav>

    <!-- Contenido dinámico cargado desde las rutas de Vue Router -->
    <main class="content">
      <router-view />
    </main>

    <!-- Pie de página de la aplicación -->
    <footer class="footer">
      <p>&copy; 2025 iHosting | Proyecto Universitario Duoc UC</p>
    </footer>
  </div>
</template>

<script>
// Componente principal que se carga al iniciar la app
export default {
  name: "App",
};
</script>

<style>
/* Elimina márgenes por defecto y ocupa el alto completo de la ventana */
html,
body {
  height: 100%;
  margin: 0;
  background: linear-gradient(135deg, #dbeeff, #f1f9ff);
}

/* Estilo del contenedor principal (#app) */
#app {
  display: flex;              /* Organiza hijos en columna */
  flex-direction: column;
  min-height: 100vh;          /* Asegura que al menos cubra todo el alto de la ventana */
}

/* Contenedor principal del contenido de cada ruta */
.content {
  flex: 1;                    /* Ocupa todo el espacio restante entre navbar y footer */
}

/* Estilo de la barra de navegación */
.navbar {
  display: flex;
  justify-content: space-between; /* Distribuye logo y enlaces a los extremos */
  align-items: center;
  background-color: #41a9ee;      /* Color de fondo */
  color: white;
  padding: 15px 30px;
  font-family: "Inter", sans-serif;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Sombra inferior */
}

/* Estilo del logo y texto de marca */
.logo {
  font-size: 22px;
  font-weight: 600;
}

/* Lista de enlaces del menú de navegación */
.nav-links {
  list-style: none;           /* Quita los bullets */
  display: flex;              /* Muestra los enlaces en línea */
  gap: 25px;                  /* Espacio entre enlaces */
  margin: 10px;               /* Margen externo */
}

/* Enlaces del menú */
.nav-links li a {
  color: white;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s ease-in-out; /* Animación al pasar el mouse */
}

/* Efecto hover para enlaces */
.nav-links li a:hover {
  color: #dbeeff;
}

/* Estilo del footer o pie de página */
.footer {
  background-color: #41a9ee;
  height: auto;
  color: white;
  text-align: center;
  padding: 20px;
  font-size: 16px;
  font-family: "Inter", sans-serif;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1); /* Sombra superior */
}

/* Tamaño del logo */
.logo-img {
  height: 40px;
  width: auto;
}
</style>
