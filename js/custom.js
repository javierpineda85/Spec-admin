
$(function () {
  $("#example1").DataTable({
    "responsive": true,
    "lengthChange": false,
    "autoWidth": false,
    "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
  }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
  $('#example2').DataTable({
    "paging": true,
    "lengthChange": false,
    "searching": false,
    "ordering": true,
    "info": true,
    "autoWidth": false,
    "responsive": true,
  });
});
/////////////////////////////////////////////////////////////////////////////////////////////////////
//                             funcion para mostrar botones en data table
/////////////////////////////////////////////////////////////////////////////////////////////////////
/*$(function () {
  // 1) Inicialización de DataTable con Buttons
  var table = $("#example1").DataTable({
    responsive: true,
    lengthChange: false,
    autoWidth: false,

    // 2) Para que aparezcan los botones en la tabla
    dom: 'Bfrtip',

    // 3) Traducción general + textos de botones
    language: {
      url: "//cdn.datatables.net/plug-ins/1.11.4/i18n/es-ES.json",
      buttons: {
        copy:   'Copiar',
        csv:    'CSV',
        excel:  'Excel',
        pdf:    'PDF',
        print:  'Imprimir',
        colvis: 'Ver columnas'
      }
    },

    // 4) Definición de botones (TODO en un solo array)
    buttons: [
      {
        extend: 'copy',
        text:   null // ya toma "Copiar" de language.buttons
      },
      {
        extend: 'csv',
        text:   null // "CSV"
      },
      {
        extend: 'excel',
        text:   null, // "Excel"
        exportOptions: {
          columns: ':visible',
          footer:  true   // incluye tu <tfoot>
        }
      },
      {
        extend: 'pdf',
        text:   null,   // "PDF"
        exportOptions: {
          columns: ':visible',
          footer:  true   // incluye tu <tfoot>
        }
      },
      {
        extend: 'print',
        text:   null,   // "Imprimir"
        footer: true     // activa tu <tfoot> en la vista de impresión
      },
      {
        extend: 'colvis',
        text:   null    // "Ver columnas"
      }
    ]
  });

  // 5) Mover la barra de botones justo donde la quieres
  table.buttons()
       .container()
       .appendTo('#example1_wrapper .col-md-6:eq(0)');
});
*/
/////////////////////////////////////////////////////////////////////////////////////////////////////
//                             funcion para ocultar secciones
/////////////////////////////////////////////////////////////////////////////////////////////////////
function ocultar() {
  $e = document.getElementById('ocultar');
  $e.classList.toggle('d-none');
}

/////////////////////////////////////////////////////////////////////////////////////////////////////
//funcion para completar el DNI y contraseña al mismo tiempo en  paginas/usuarios/crear-usuario.php
/////////////////////////////////////////////////////////////////////////////////////////////////////
$(document).ready(function () {
  $('#inputDNI').on('input', function () {
    var valor = $(this).val();
    var input2 = $('#inputPass');
    var caracteresRestantes = $('#caracteresRestantes');

    // Limitar la longitud a 8 caracteres
    if (valor.length > 8) {
      valor = valor.slice(0, 8);
    }

    // Actualizar el valor del segundo input
    input2.val(valor);

    // Calcular y mostrar los caracteres restantes
    var restantes = 8 - valor.length;
    caracteresRestantes.text('Caracteres restantes: ' + restantes);
  });
});

/////////////////////////////////////////////////////////////////////////////////////////////////////
//funcion para agrandar las imagenes en un click
/////////////////////////////////////////////////////////////////////////////////////////////////////
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll("img[data-toggle='modal']").forEach(img => {
    img.addEventListener("click", function () {
      let modalId = this.getAttribute("data-target"); // Obtiene el modal específico
      let modalImg = document.querySelector(`${modalId} img`);

      if (modalImg) {
        modalImg.setAttribute("src", this.getAttribute("src"));
      } else {
        console.error(`No se encontró la imagen en el modal ${modalId}`);
      }
    });
  });
});

/////////////////////////////////////////////////////////////////////////////////////////////////////
//funcion para agregar provincias
/////////////////////////////////////////////////////////////////////////////////////////////////////
document.addEventListener('DOMContentLoaded', () => {
  // Obtener el elemento select
  const selectProvincia = document.getElementById("provincia");
  if (!selectProvincia) return;  // Si no existe sale y no hace nada

  // JSON con las provincias argentinas
  const provinciasJSON = {
    "provincias": [
      { "nombre": "Buenos Aires" },
      { "nombre": "Catamarca" },
      { "nombre": "Chaco" },
      { "nombre": "Chubut" },
      { "nombre": "Ciudad Autónoma de Buenos Aires" },
      { "nombre": "Córdoba" },
      { "nombre": "Corrientes" },
      { "nombre": "Entre Ríos" },
      { "nombre": "Formosa" },
      { "nombre": "Jujuy" },
      { "nombre": "La Pampa" },
      { "nombre": "La Rioja" },
      { "nombre": "Mendoza" },
      { "nombre": "Misiones" },
      { "nombre": "Neuquén" },
      { "nombre": "Río Negro" },
      { "nombre": "Salta" },
      { "nombre": "San Juan" },
      { "nombre": "San Luis" },
      { "nombre": "Santa Cruz" },
      { "nombre": "Santa Fe" },
      { "nombre": "Santiago del Estero" },
      { "nombre": "Tierra del Fuego, Antártida e Islas del Atlántico Sur" },
      { "nombre": "Tucumán" }
    ]
  };

  // Agregar opciones al select solo si existe
  provinciasJSON.provincias.forEach(provincia => {
    const option = document.createElement("option");
    option.value = provincia.nombre;
    option.text = provincia.nombre;
    selectProvincia.add(option);
  });
});

/////////////////////////////////////////////////////////////////////////////////////////////////////
//                Quitar los botones de BROWSE en los input type file
/////////////////////////////////////////////////////////////////////////////////////////////////////
document.addEventListener('DOMContentLoaded', () => {
  // Selector de inputs de archivo
  const fileInputs = document.querySelectorAll('.custom-file-input');
  if (fileInputs.length) {
    fileInputs.forEach(input => {
      input.addEventListener('change', function (e) {
        const fileName = e.target.files[0]?.name || "Selecciona un archivo";

        // Intento primero con nextElementSibling
        let label = e.target.nextElementSibling;
        // Si no existe o no tiene classList, intento buscarlo por clase
        if (!label || !label.classList) {
          label = e.target.closest('.custom-file')?.querySelector('.custom-file-label');
        }
        // Si sigue sin existir, salgo
        if (!label || !label.classList) return;

        // Actualizo texto del label
        label.textContent = fileName;

        // Agrego o quito la clase según si hay archivo
        if (e.target.files.length > 0) {
          label.classList.add("file-selected");
        } else {
          label.classList.remove("file-selected");
        }
      });
    });
  }

  // Selector de botones que disparan el input file
  const fileButtons = document.querySelectorAll('.custom-file-button');
  if (fileButtons.length && fileInputs.length) {
    fileButtons.forEach((button, index) => {
      button.addEventListener('click', () => {
        const input = fileInputs[index];
        if (input && typeof input.click === 'function') {
          input.click();
        }
      });
    });
  }
});


/////////////////////////////////////////////////////////////////////////////////////////////////////
//                Funcion para no enviar formularios vacios
/////////////////////////////////////////////////////////////////////////////////////////////////////

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll("form").forEach(function (form) {
    form.addEventListener("submit", function (event) {

      if (form.id === "perfilForm" || form.id === "entradaSalidaForm") {
        console.log("↪ Saltando validación genérica para este form:", form.id);
        return;
      }

      let isValid = true;
      let mensajeError = "";

      form.querySelectorAll(".form-control").forEach(function (campo) {
        // Omitir botones, ocultos, deshabilitados o explícitamente opcionales
        const isOptional = campo.hasAttribute("data-optional");

        if (
          campo.type === "submit" ||
          campo.type === "reset" ||
          campo.type === "file" ||
          campo.type === "hidden" ||
          campo.disabled ||
          isOptional
        ) {
          return;
        }
        let vacio = false;
        // Validar múltiple select
        if (campo.tagName === "SELECT" && campo.multiple) {
          const valoresSeleccionados = Array.from(campo.options).filter(op => op.selected && op.value !== "");
          vacio = valoresSeleccionados.length === 0;
        } else {
          vacio = campo.value.trim() === "";
        }
        console.log("Campo:", campo.name || campo.id, "| Optional:", campo.dataset.optional);

        if (vacio) {
          isValid = false;
          mensajeError = "⚠️ Todos los campos obligatorios deben estar completos.";
          console.log("Campo vacío:", campo.name || campo.id);
          campo.style.border = "2px solid red";
        } else {
          campo.style.border = "";
        }
      });

      if (!isValid) {
        event.preventDefault(); // Detener el envío del formulario
        Swal.fire({
          icon: "error",
          title: "Error",
          text: mensajeError,
          confirmButtonColor: "#d33"
        });
      }
    });
  });
});

/////////////////////////////////////////////////////////////////////////////////////////////////////
//                Funcion para cambiar los colores del switch en novedades/entradas-salidas
/////////////////////////////////////////////////////////////////////////////////////////////////////
document.addEventListener('DOMContentLoaded', () => {
  const switchInput = document.getElementById('entradaSalidaSwitch');
  const switchLabel = document.getElementById('switchLabel');
  const switchText = document.getElementById('switchText');

  // Solo si todos los elementos existen los vinculamos
  if (switchInput && switchLabel && switchText) {
    switchInput.addEventListener('change', function () {
      switchLabel.textContent = this.checked
        ? 'Registrar Salida'
        : 'Registrar Entrada';

      switchText.textContent = this.checked
        ? 'Ahora se registrará la salida'
        : 'Se registrará la entrada';
    });
  }
});

/////////////////////////////////////////////////////////////////////////////////////////////////////
//                Funcion para buscar rondas por objetivos
/////////////////////////////////////////////////////////////////////////////////////////////////////

// Cuando cambie el objetivo, recargo los puestos
document.addEventListener('DOMContentLoaded', () => {
  // Referencias a los selects
  const objetivo = document.getElementById('objetivo');
  const puesto = document.getElementById('puesto');

  if (objetivo && puesto) {
    // ==== Vanilla JS ====
    objetivo.addEventListener('change', function () {
      // reinicio opciones
      puesto.innerHTML = '<option value="" disabled selected>Selecciona un puesto</option>';
      // agrego sólo las rondas que coinciden
      rondas.forEach(r => {
        if (String(r.objetivo_id) === this.value) {
          const opt = document.createElement('option');
          opt.value = r.idPuesto;
          opt.textContent = r.puesto;
          puesto.appendChild(opt);
        }
      });
    });

    // ==== jQuery ====
    $('#objetivo').on('change', function () {
      const id = this.value;
      const $puesto = $('#puesto')
        .empty()
        .append('<option value="" disabled selected>Selecciona un puesto</option>');

      rondas.forEach(r => {
        if (String(r.objetivo_id) === id) {
          $puesto.append(`<option value="${r.idPuesto}">${r.puesto}</option>`);
        }
      });
    });
  }

  // ==== Select2 para vigiladores ====
  if (window.jQuery && $.fn.select2) {
    $('#vigilador').select2({
      placeholder: 'Escribí el apellido…',
      allowClear: true,
      width: '100%'
    });
  }
});

/////////////////////////////////////////////////////////////////////////////////////////////////////
//                  funcion para ver las notificaciones de los mensajes
/////////////////////////////////////////////////////////////////////////////////////////////////////
document.addEventListener('DOMContentLoaded', function () {
  function actualizarContadorMensajes() {
    fetch('ajax/ver_mensajes.php')
      .then(res => res.json())
      .then(mensajes => {
        //console.log('📬 Mensajes recibidos:', mensajes);
        const badge = document.getElementById('badge-mensajes');
        const contenedor = document.getElementById('dropdown-mensajes-preview');
        if (!badge || !contenedor) {
          console.warn('⚠️ No se encontró el badge o el contenedor de mensajes');
          return;
        }

        if (mensajes.length > 0) {
          badge.innerText = mensajes.length;
          badge.style.display = 'inline-block';

          const prev = parseInt(localStorage.getItem('mensajes_previos'), 10) || 0;
          if (
            mensajes.length > prev &&
            !window.location.search.includes('r=bandeja-entrada')
          ) {
            document
              .getElementById('sonido-alerta-global')
              .play()
              .catch(() => { });
          }
          localStorage.setItem('mensajes_previos', mensajes.length);

          contenedor.innerHTML = '';
          mensajes.slice(0, 3).forEach(m => {
            const nombre = m.nombre ?? 'Sin nombre';
            const apellido = m.apellido ?? '';
            const fecha = m.fMensaje ?? '';
            const hora = fecha.slice(11, 16);
            const dia = fecha.slice(0, 10);

            contenedor.insertAdjacentHTML(
              'beforeend',`
            <a href="index.php?r=bandeja-entrada" class="dropdown-item">
              <i class="fas fa-envelope mr-2"></i>
              ${nombre} ${apellido}
              <span class="float-right text-muted text-sm">${hora}</span>
              <div class="text-sm">Recibido el ${dia}</div>
            </a>
            <div class="dropdown-divider"></div>
          `
            );
          });
        } else {
          badge.style.display = 'none';
          contenedor.innerHTML =
            '<span class="dropdown-item text-muted">Sin mensajes nuevos</span>';
          localStorage.setItem('mensajes_previos', 0);
        }
      })
      .catch(e => console.error('❌ Error al obtener mensajes:', e));
  }


  $(document).on('click', '.ver-mensaje', function () {
    const idMensaje = $(this).data('id');
    $('#contenido-mensaje').html('<p class="text-muted">Cargando mensaje...</p>');

    $.ajax({
      url: 'ajax/ver_mensaje.php',
      type: 'POST',
      data: {
        idMensaje
      },
      dataType: 'json',
      success: function (respuesta) {
        if (respuesta && respuesta.exito) {
          const html = `
            <p><strong>De:</strong> ${respuesta.nombre} ${respuesta.apellido}</p>
            <p><strong>Fecha:</strong> ${respuesta.fecha} ${respuesta.hora}</p>
            <hr>
            <p>${respuesta.contenido}</p>
          `;
          $('#contenido-mensaje').html(html);
          $('#modalVerMensaje').modal('show');
        } else {
          $('#contenido-mensaje').html(`<p class="text-danger">${respuesta.error ?? 'Error al cargar el mensaje.'}</p>`);
        }
      },
      error: function (xhr, status, error) {
        console.error("❌ Error en AJAX:", status, error);
        $('#contenido-mensaje').html('<p class="text-danger">Error de conexión con el servidor.</p>');
      }

    });

    $.ajax({
      url: 'ajax/marcar_leido.php',
      type: 'POST',
      data: {
        idMensaje
      },
      success: function (respuesta) {
        //console.log('📬 Mensaje marcado como leído');
        $(`#mensaje-${idMensaje}`).removeClass('no-leido font-weight-bold bg-light').addClass('leido');
        $(`#icono-${idMensaje}`).removeClass('fa-envelope').addClass('fa-envelope-open');

        //Para simular que actualizó la cantidad desde la BD
        const badgePrincipal = $('#badge-mensajes');
        const badgeEntrada = $('#badge-mensajes-entrada');

        const cantidadPrincipal = parseInt(badgePrincipal.text(), 10);
        const cantidadEntrada = parseInt(badgeEntrada.text(), 10);

        if (cantidadPrincipal > 0) {
          badgePrincipal.text(cantidadPrincipal - 1);
          if (cantidadPrincipal - 1 === 0) {
            badgePrincipal.hide();
          }
        }

        if (cantidadEntrada > 0) {
          badgeEntrada.text(cantidadEntrada - 1);
          if (cantidadEntrada - 1 === 0) {
            badgeEntrada.hide();
          }
        }
        //location.reload();
      },
      error: function (xhr, status, error) {
        //console.error("❌ Error al marcar como leído:", status, error);
      }
    });

  });
  //Marcar como NO LEIDO
  $(document).on('click', '.marcar-no-leido', function () {
    const idMensaje = $(this).data('id');

    $.ajax({
      url: 'ajax/marcar_no_leido.php',
      type: 'POST',
      data: {
        'idMensaje': idMensaje
      },
      dataType: 'json',
      success: function (respuesta) {
        if (respuesta.exito) {
          //alert('📭 Mensaje marcado como no leído');
          $(`#mensaje-${idMensaje}`).removeClass('leido').addClass('no-leido font-weight-bold bg-light');
          $(`#icono-${idMensaje}`).removeClass('fa-envelope-open').addClass('fa-envelope');

          location.reload(); // o actualizar solo el ícono si querés evitar reload
        } else {
          alert('Error: ' + (respuesta.error ?? 'No se pudo marcar como no leído'));
        }
      },
      error: function (xhr, status, error) {
        console.error("❌ Error AJAX:", status, error);
        alert('Error de conexión');
      }
    });
  });

  actualizarContadorMensajes();
  setInterval(actualizarContadorMensajes, 30000);

});

