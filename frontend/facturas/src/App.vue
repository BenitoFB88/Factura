<template>
  <div id="app">
    <AppNavbar />
    <BuscarFacturas />
    <AppFooter />
  </div>
</template>

<script>
import AppNavbar from './components/Navbar.vue'
import AppFooter from './components/Footer.vue'
import BuscarFacturas from './components/BuscarFacturas.vue'

export default {
  name: 'App',
  components: {
    AppNavbar,
    BuscarFacturas,
    AppFooter
  }
}
</script>

<style>
/* Puedes dejar el estilo como está o vacío */
</style>
