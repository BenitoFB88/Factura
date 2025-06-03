<template>
  <div>
    <button @click="exportarResultados" class="exportar-btn">
      Exportar a Excel
    </button>
  </div>
  <p v-if="mensajeExportacion" :class="{ fadeOut: isFadingOut }">
    {{ mensajeExportacion }}
  </p>
</template>

<script>
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";

export default {
  name: "ExportarFacturas",
  props: {
    allInvoices: {
      type: Array,
      required: true,
      default: () => [], // evita errores si no llega nada
    },
  },
  data() {
    return {
      mensajeExportacion: "",
      isFadingOut: false,
      iva: 0.19, // parece que lo usas pero no estaba definido
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
    formatXML(xmlString) {
      const PADDING = "  ";
      const reg = /(>)(<)(\/*)/g;
      let formatted = "";
      let pad = 0;

      xmlString = xmlString.replace(reg, "$1\r\n$2$3");
      xmlString.split("\r\n").forEach((node) => {
        let indent = 0;
        if (node.match(/.+<\/\w[^>]*>$/)) {
          indent = 0;
        } else if (node.match(/^<\/\w/)) {
          if (pad !== 0) pad -= 1;
        } else if (node.match(/^<\w[^>]*[^/]>.*$/)) {
          indent = 1;
        } else {
          indent = 0;
        }

        formatted += PADDING.repeat(pad) + node + "\r\n";
        pad += indent;
      });

      return formatted.trim();
    },
    exportarResultados() {
      const headers = [
        "Fecha Docto", //Fecha Emision
        "Tipo Docto", //33 Factura
        "Nro Docto", //Folio
        "Rut", //Receptor
        "Nombre", //Receptor
        "CTA Neto", //Total sin IVA
        "CA Neto",
        "Monto Neto",
        "CTA Exento",
        "CA Exento",
        "CC Exento",
        "COD SII Otro",
        "CTA Otro",
        "CC Otro",
        "Monto Otro",
        "% IVA", //19
        "IVA", //Monto de IVA
        "Total",
        "Glosa", // Esta es la columna problemática
      ];

      const workbook = new ExcelJS.Workbook();
      const sheet = workbook.addWorksheet("Facturas");

      // Agregar encabezados con estilo
      const headerRow = sheet.addRow(headers);
      let jsonStr;
      let parsedJson;
      let rutReceptor;
      headerRow.eachCell((cell) => {
        cell.font = { name: "Calibri", size: 12, bold: true };
        cell.fill = {
          type: "pattern",
          pattern: "solid",
          fgColor: { argb: "7caff1" },
        };
        cell.alignment = {
          horizontal: "center",
          vertical: "middle",
          wrapText: true,
        };
        cell.border = {
          top: { style: "thin" },
          left: { style: "thin" },
          bottom: { style: "thin" },
          right: { style: "thin" },
        };
      });

      // Agregar datos con formato
      this.allInvoices.forEach((f) => {
        jsonStr = f.xml;
        parsedJson = JSON.parse(jsonStr);
        // Extraer el RUT del receptor
        rutReceptor = parsedJson.Encabezado.Receptor.RUTRecep;
        const row = sheet.addRow([
          this.formatDate(f.fecha), // Puedes ajustar esto si es string o Date
          f.tipo_dte,
          f.folio,
          rutReceptor,
          f.receptor,
          f.neto,
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          this.iva * 100,
          f.neto * this.iva,
          f.neto * (1 + this.iva),
          this.formatXML(f.xml),
        ]);
        row.eachCell((cell, colNumber) => {
          cell.font = { name: "Calibri", size: 12 };
          cell.alignment = {
            horizontal: colNumber === 19 ? "left" : "center",
            vertical: "middle",
            wrapText: colNumber === 19, // Puedes dejar true si quieres scroll vertical interno
          };
          cell.border = {
            top: { style: "thin" },
            left: { style: "thin" },
            bottom: { style: "thin" },
            right: { style: "thin" },
          };

          // Formato de fecha y moneda
          if (colNumber === 1) {
            cell.numFmt = "dd-mm-yyyy"; // Fecha
          }
          if (colNumber === 5) {
            cell.numFmt = "#,##0"; // Total (moneda sin decimales)
          }

          // Ajustar el tamaño solo para la columna de "Glosa"
          if (colNumber === 19) {
            cell.alignment = {
              horizontal: "left",
              vertical: "top",
              wrapText: false,
              shrinkToFit: true,
            };
            cell.note = f.xml; // tooltip con todo el XML
            let glosa = this.formatXML(f.xml);
            if (glosa.length > 300) glosa = glosa.slice(0, 300) + " ...";
          }
        });
      });

      // Ajustar el ancho de las demás columnas, pero dejar la columna "Glosa" con un tamaño fijo
      sheet.columns.forEach((column, colIndex) => {
        if (colIndex !== 19) {
          // Ignorar la columna de "Glosa" (19 es su índice)
          let maxLength = 10;
          column.eachCell({ includeEmpty: true }, (cell) => {
            const value = cell.value ? cell.value.toString() : "";
            maxLength = Math.max(maxLength, value.length);
          });
          column.width = maxLength + 2;
        } else {
          // Asignar un tamaño fijo para la columna de "Glosa"
          column.width = 30; // Ajusta el número a lo que sea adecuado
        }
      });

      // Generar y descargar el archivo
      workbook.xlsx
        .writeBuffer()
        .then((buffer) => {
          saveAs(new Blob([buffer]), "facturas_exportadas.xlsx");

          this.mensajeExportacion =
            "Exportación exitosa. El archivo Excel ha sido generado correctamente.";
          setTimeout(() => {
            this.isFadingOut = true;
            setTimeout(() => {
              this.mensajeExportacion = "";
              this.isFadingOut = false;
            }, 1000);
          }, 2000);
        })
        .catch((error) => {
          console.error("Error al generar el archivo Excel", error);
          this.mensajeExportacion =
            "Error al generar el archivo Excel. Por favor, intente nuevamente.";
        });
    },
  },
};
</script>

<style scoped>
p {
  font-size: 16px;
  font-weight: bold;
  color: #5ace8e;
  margin-top: 10px;
  position: absolute;
  top: 20px;
  right: 120px;
}

.fadeOut {
  color: #5ace8e;
  animation: fadeOut 1s forwards;
}

.exportar-btn {
  padding: 8px 16px;
  color: white;
  border-radius: 4px;
  cursor: pointer;
  height: 60px;
}

@keyframes fadeOut {
  to {
    opacity: 0;
  }
}
</style>
