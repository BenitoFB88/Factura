<template>
  <!-- Contenedor principal de toda la aplicación -->
  <div
    id="app"
    class="flex flex-col min-h-screen bg-gradient-to-br from-blue-100 via-blue-50 to-blue-100 box-border"
  >
    <!-- Barra de navegación superior -->
    <nav
      class="flex justify-between items-center bg-[#41a9ee] text-white px-8 py-4 shadow-md"
    >
      <div class="flex items-center font-semibold text-[22px]">
        <!-- Logo de la empresa -->
        <img
          src="@/assets/Ihosting.png"
          alt="iHosting Logo"
          class="logo-img h-10 w-auto mr-3"
        />
        IHosting
      </div>

      <!-- Enlaces de navegación -->
      <ul class="flex space-x-8 m-0 p-0 list-none items-center">
        <li>
          <a
            href="#"
            class="no-underline px-3 py-2 rounded-md font-semibold text-white hover:bg-white hover:text-[#41a9ee] transition-colors duration-300"
            >Inicio</a
          >
        </li>
        <li>
          <a
            href="#"
            class="no-underline px-3 py-2 rounded-md font-semibold text-white hover:bg-white hover:text-[#41a9ee] transition-colors duration-300"
            >Hostings</a
          >
        </li>
        <li>
          <a
            href="#"
            class="no-underline px-3 py-2 rounded-md font-semibold text-white hover:bg-white hover:text-[#41a9ee] transition-colors duration-300"
            >Facturas</a
          >
        </li>
        <li>
          <a
            href="#"
            class="no-underline px-3 py-2 rounded-md font-semibold text-white hover:bg-white hover:text-[#41a9ee] transition-colors duration-300"
            >Contacto</a
          >
        </li>
      </ul>
    </nav>

    <!-- Contenido dinámico cargado desde las rutas de Vue Router -->
    <main class="flex-grow">
      <router-view />
    </main>

    <!-- Pie de página de la aplicación -->
    <footer
      class="bg-[#41a9ee] text-white text-center p-2 text-base font-sans shadow-inner"
    >
      <p>&copy; 2025 iHosting | Proyecto Universitario Duoc UC</p>
    </footer>
  </div>
</template>

<script>
export default {
  name: "App",
};
</script>

<style>
.logo-img {
  height: 60px;
  width: auto;
  padding: 5%;
 
}
</style>
