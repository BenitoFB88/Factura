<template>
  <div>
    <button @click="exportarResultados">Exportar a Excel</button>
    <p v-if="mensajeExportacion" :class="{ fadeOut: isFadingOut }">{{ mensajeExportacion }}</p>
  </div>
</template>

<script>
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";

export default {
  name: "ExportarFacturas",
  data() {
    return {
      allInvoices: [
        // Ejemplo de datos, reemplázalos con tus datos reales
        { fecha: new Date(), emisor: "Empresa A", receptor: "Cliente B", folio: 123, total: 10000, codigo_analisis: "ABC123" },
      ],
      mensajeExportacion: "",
      isFadingOut: false,
    };
  },
  methods: {
    formatDate(date) {
      const d = new Date(date);
      const day = String(d.getDate()).padStart(2, "0");
      const month = String(d.getMonth() + 1).padStart(2, "0");
      const year = d.getFullYear();
      return `${day}-${month}-${year}`;
    },
    exportarResultados() {
      const headers = ["Fecha", "Emisor", "Receptor", "Folio", "Total", "Código Análisis"];
      const workbook = new ExcelJS.Workbook();
      const sheet = workbook.addWorksheet("Facturas");

      // Encabezados
      const headerRow = sheet.addRow(headers);
      headerRow.eachCell((cell) => {
        cell.font = { name: "Calibri", size: 12, bold: true };
        cell.fill = {
          type: "pattern",
          pattern: "solid",
          fgColor: { argb: "FFFF00" },
        };
        cell.alignment = { horizontal: "center", vertical: "middle", wrapText: true };
        cell.border = {
          top: { style: "thin" },
          left: { style: "thin" },
          bottom: { style: "thin" },
          right: { style: "thin" },
        };
      });

      // Filas de datos
      this.allInvoices.forEach((f) => {
        const row = sheet.addRow([
          this.formatDate(f.fecha),
          f.emisor,
          f.receptor,
          f.folio,
          f.total,
          f.codigo_analisis,
        ]);
        row.eachCell((cell, colNumber) => {
          cell.font = { name: "Calibri", size: 12 };
          cell.alignment = { horizontal: "center", vertical: "middle", wrapText: true };
          cell.border = {
            top: { style: "thin" },
            left: { style: "thin" },
            bottom: { style: "thin" },
            right: { style: "thin" },
          };
          if (colNumber === 1) cell.numFmt = "dd-mm-yyyy";
          if (colNumber === 5) cell.numFmt = "#,##0";
        });
      });

      // Ajustar ancho de columnas
      sheet.columns.forEach((column) => {
        let maxLength = 10;
        column.eachCell({ includeEmpty: true }, (cell) => {
          const value = cell.value ? cell.value.toString() : "";
          maxLength = Math.max(maxLength, value.length);
        });
        column.width = maxLength + 2;
      });

      // Exportar
      workbook.xlsx.writeBuffer().then((buffer) => {
        saveAs(new Blob([buffer]), "facturas_exportadas.xlsx");
        this.mensajeExportacion = "Exportación exitosa. El archivo Excel ha sido generado correctamente.";
        setTimeout(() => {
          this.isFadingOut = true;
          setTimeout(() => {
            this.mensajeExportacion = "";
            this.isFadingOut = false;
          }, 1000);
        }, 2000);
      }).catch((error) => {
        console.error("Error al generar el archivo Excel", error);
        this.mensajeExportacion = "Error al generar el archivo Excel. Por favor, intente nuevamente.";
      });
    },
  },
};
</script>

<style scoped>
.fadeOut {
  animation: fadeOut 1s forwards;
}

@keyframes fadeOut {
  to {
    opacity: 0;
  }
}
</style>
