<template>
  <div class="upload-container">
    <h2>Cargar Facturas desde un archivo Excel</h2>

    <form @submit.prevent="submitFile" class="file-upload-form">
      <label for="file">Seleccione el archivo Excel (.xlsx o .xls):</label>
      <input
        type="file"
        @change="handleFileChange"
        class="file-input"
        accept=".xlsx,.xls"
        ref="fileInput"
      />

      <button type="submit" class="btn">Subir</button>
    </form>

    <div v-if="message" class="alert" :class="messageType">
      {{ message }}
    </div>

    <!-- Vista previa -->
    <div v-if="excelData.length" class="preview">
      <h3>Vista previa del archivo</h3>
      <table>
        <thead>
          <tr>
            <th v-for="(value, key) in excelData[0]" :key="key">{{ key }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in excelData.slice(0, 5)" :key="index">
            <td v-for="(value, key) in row" :key="key">{{ value }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import * as XLSX from "xlsx";

export default {
  data() {
    return {
      file: null,
      message: "",
      messageType: "",
      excelData: [],
    };
  },
  methods: {
    handleFileChange(event) {
      const file = event.target.files[0];
      const allowedExtensions = ["xlsx", "xls"];
      const fileExtension = file?.name.split(".").pop().toLowerCase();

      if (!file || !allowedExtensions.includes(fileExtension)) {
        this.message =
          "Formato de archivo no válido. Solo se permiten .xlsx o .xls.";
        this.messageType = "alert-danger";
        this.file = null;
        this.excelData = [];
        this.$refs.fileInput.value = "";
        return;
      }

      this.file = file;
      this.message = "";
      this.messageType = "";

      const reader = new FileReader();
      reader.onload = (e) => {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: "array" });
        const sheet = workbook.Sheets[workbook.SheetNames[0]];
        this.excelData = XLSX.utils.sheet_to_json(sheet);
      };
      reader.readAsArrayBuffer(file);
    },

    async submitFile() {
      if (!this.file) {
        this.message = "Por favor, seleccione un archivo Excel.";
        this.messageType = "alert-danger";
        return;
      }

      const formData = new FormData();
      formData.append("file", this.file);

      try {
        await axios.post("/api/factura/import", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
            Authorization: `Bearer ${localStorage.getItem("access_token")}`,
          },
        });

        this.message = "Archivo cargado y procesado exitosamente.";
        this.messageType = "alert-success";
      } catch (error) {
        console.error("Error al cargar el archivo:", error);
        this.message = "Error al cargar el archivo. Intenta nuevamente.";
        this.messageType = "alert-danger";
      }
    },
  },
};
</script>

<style scoped>
.upload-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 30px;
  text-align: center;
}

.file-upload-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-top: 20px;
  align-items: center;
}

.file-input {
  padding: 10px;
  border: 1px solid #ccc;
  width: 60%;
}

.btn {
  background-color: #007bff;
  color: white;
  padding: 10px 25px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.btn:hover {
  background-color: #0056b3;
}

.alert {
  margin-top: 20px;
  padding: 10px;
  border-radius: 5px;
  width: 60%;
  margin-left: auto;
  margin-right: auto;
}

.alert-success {
  background-color: #d4edda;
  color: #155724;
}

.alert-danger {
  background-color: #f8d7da;
  color: #721c24;
}

.preview {
  margin-top: 30px;
  text-align: left;
  overflow-x: auto;
}

.preview h3 {
  margin-bottom: 10px;
  text-align: center;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin: 0 auto;
}

th,
td {
  padding: 8px;
  border: 1px solid #ddd;
}
</style>
