// src/auth.js
export function logout() {
    localStorage.removeItem('auth');
    window.location.href = '/'; // Ajusta si es necesario
  }
  