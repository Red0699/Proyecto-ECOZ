'use strict';

// Importamos jQuery, que es un requisito para DataTables
import $ from 'jquery';

// Importamos las librerías de DataTables
import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import 'datatables.net-buttons-bs5';
import JSZip from 'jszip';
import pdfmake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';
pdfMake.vfs = pdfFonts.pdfMake.vfs;

// Cuando el documento esté listo, inicializamos la tabla
$(document).ready(function () {
  $('#dataTable').DataTable({
    // Activa el diseño responsivo
    responsive: true,
    // Configuración del lenguaje (opcional, pero recomendado)
    language: {
      search: "Buscar:",
      lengthMenu: "Mostrar _MENU_ registros",
      info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
      paginate: {
        first: "Primero",
        last: "Último",
        next: "Siguiente",
        previous: "Anterior"
      }
    },
    // Opcional: Configuración de botones de exportación
    dom: 'Bfrtip',
    buttons: [
      'copy', 'csv', 'excel', 'pdf', 'print'
    ]
  });
});