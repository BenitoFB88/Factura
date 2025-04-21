<template>
  <div id="app" class="layout">
    <LoginView v-if="!logueado" @login-success="logueado = true"/>
  <div v-else>
    
    <AppNavbar />
    <FacturaDashboard/>
    <AppFooter />
 </div>
  </div>
</template>

<script>
import AppNavbar from './components/Navbar.vue'
import AppFooter from './components/Footer.vue'
import FacturaDashboard from './components/FacturaDashboard.vue';
import LoginView from './components/LoginView.vue';


export default {
  name: 'App',
  components: {
    AppNavbar,
    AppFooter,
    FacturaDashboard,
    LoginView
  },
  data() {
    return {
      logueado: false
    }
  }
};
</script>

<style>
.layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.layout > * {
  flex-shrink: 0;
}

.layout > main {
  flex: 1;
}

#app {
  margin: 0;
  padding: 0;
}
</style>
