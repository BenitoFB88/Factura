<template>
  <div>
    <h1>Vue + Laravel</h1>
    <p>Respuesta desde Laravel: {{ respuesta }}</p>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'

const respuesta = ref('')

onMounted(async () => {
  try {
    const res = await fetch('/api/ping')
    const data = await res.json()
    respuesta.value = data.message
  } catch (e) {
    respuesta.value = 'Error al conectar con la API'
  }
})
</script>
