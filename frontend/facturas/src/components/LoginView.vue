<template>
    <div class="login-page">
      <div class="login-box">
        <h2>Iniciar Sesión</h2>
  
        <form @submit.prevent="login">
          <div class="form-group">
            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" v-model="usuario" placeholder="Ingrese su usuario" />
          </div>
  
          <div class="form-group">
            <label for="clave">Clave</label>
            <input type="password" id="clave" v-model="clave" placeholder="Ingrese su clave" />
          </div>
  
          <div class="buttons">
            <button type="submit">Iniciar</button>
            <button type="button" class="cancelar" @click="cancelar">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'LoginView',
    data() {
      return {
        usuario: '',
        clave: ''
      };
    },
    methods: {
      login() {
        alert(`Bienvenido, ${this.usuario}`);
        this.$emit('login-success');
    },
      cancelar() {
        this.usuario = '';
        this.clave = '';
      }
    }
  };
  </script>
  
  <style scoped>
  .login-page {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background: linear-gradient(135deg, #dbeeff, #f1f9ff);
    font-family: 'Segoe UI', sans-serif;
  }
  
  .login-box {
    background-color: white;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    text-align: center;
  }
  
  h2 {
    color: #1e3a5f;
    margin-bottom: 30px;
  }
  
  .form-group {
    margin-bottom: 20px;
    text-align: left;
  }
  
  label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
  }
  
  input {
    width: 100%;
    padding: 10px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 8px;
  }
  
  .buttons {
    display: flex;
    justify-content: space-between;
    gap: 10px;
  }
  
  button {
    padding: 10px 20px;
    border: none;
    font-size: 14px;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  button[type="submit"] {
    background-color: #0070c9;
    color: white;
  }
  
  button.cancelar {
    background-color: #ccc;
    color: #333;
  }
  
  button:hover {
    opacity: 0.9;
  }
  </style>
  