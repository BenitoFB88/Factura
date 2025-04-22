/* eslint-disable */

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
  </template>
  
  <script>
  export default {
    name: 'AppNavbar'
  }
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
  </style>
  