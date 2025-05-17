

  $(function() {
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
  //                             funcion para ocultar secciones
  /////////////////////////////////////////////////////////////////////////////////////////////////////
  function ocultar() {
    $e = document.getElementById('ocultar');
    $e.classList.toggle('d-none');
  }


  /////////////////////////////////////////////////////////////////////////////////////////////////////
  //       no permite enviar el formulario de editar-perfil sin que no se presione submit
  /////////////////////////////////////////////////////////////////////////////////////////////////////

  $("#editar-perfil").submit(function(event) {
    var fnac = $('#fnac').val();
    var domicilioPerfil = $('#domicilioPerfil').val();
    var errores = [];

    if ($.trim(fnac) === '') {
      errores.push("Debe completar la fecha de nacimiento");
    }
    if ($.trim(domicilioPerfil) === '') {
      errores.push("Debe completar el domiclio");
    }


    if (errores.length > 0) {
      // Muestra los errores como toasts en lugar de un alert
      Toastify({
        text: errores.join(" / "),
        duration: 3000, // Duración en milisegundos
        close: true, // Agregar un botón para cerrar el toast
        gravity: "top", // Posición del toast 
      }).showToast();

      event.preventDefault(); // Evita que el formulario se envíe
    }

  });

  /////////////////////////////////////////////////////////////////////////////////////////////////////
  //funcion para completar el DNI y contraseña al mismo tiempo en  paginas/usuarios/crear-usuario.php
  /////////////////////////////////////////////////////////////////////////////////////////////////////
  $(document).ready(function() {
    $('#inputDNI').on('input', function() {
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
  document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll("img[data-toggle='modal']").forEach(img => {
      img.addEventListener("click", function() {
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
  // Obtener el elemento select
  var selectProvincia = document.getElementById("provincia");

  // JSON con las provincias argentinas
  var provinciasJSON = {
    "provincias": [{
        "nombre": "Buenos Aires"
      }, {
        "nombre": "Catamarca"
      }, {
        "nombre": "Chaco"
      }, {
        "nombre": "Chubut"
      }, {
        "nombre": "Ciudad Autónoma de Buenos Aires"
      }, {
        "nombre": "Córdoba"
      }, {
        "nombre": "Corrientes"
      }, {
        "nombre": "Entre Ríos"
      }, {
        "nombre": "Formosa"
      }, {
        "nombre": "Jujuy"
      }, {
        "nombre": "La Pampa"
      },
      {
        "nombre": "La Rioja"
      }, {
        "nombre": "Mendoza"
      }, {
        "nombre": "Misiones"
      }, {
        "nombre": "Neuquén"
      },
      {
        "nombre": "Río Negro"
      }, {
        "nombre": "Salta"
      }, {
        "nombre": "San Juan"
      }, {
        "nombre": "San Luis"
      },
      {
        "nombre": "Santa Cruz"
      }, {
        "nombre": "Santa Fe"
      }, {
        "nombre": "Santiago del Estero"
      },
      {
        "nombre": "Tierra del Fuego, Antártida e Islas del Atlántico Sur"
      }, {
        "nombre": "Tucumán"
      }
    ]
  };


  // Agregar opciones al select
  provinciasJSON.provincias.forEach(function(provincia) {
    var option = document.createElement("option");
    option.value = provincia.nombre;
    option.text = provincia.nombre;
    selectProvincia.add(option);
  });

  /////////////////////////////////////////////////////////////////////////////////////////////////////
  //                Quitar los botones de BROWSE en los input type file
  /////////////////////////////////////////////////////////////////////////////////////////////////////
  document.querySelectorAll('.custom-file-input').forEach(input => {
    input.addEventListener('change', function(e) {
      let fileName = e.target.files[0] ? e.target.files[0].name : "Selecciona un archivo";
      let label = e.target.nextElementSibling;

      // Cambia el texto del label al nombre del archivo
      label.textContent = fileName;

      // Agrega color verde cuando hay un archivo seleccionado
      if (e.target.files.length > 0) {
        label.classList.add("file-selected");
      } else {
        label.classList.remove("file-selected");
      }
    });
  });

  // Hacer que el botón "Buscar" abra el input file
  document.querySelectorAll('.custom-file-button').forEach((button, index) => {
    button.addEventListener('click', function() {
      document.querySelectorAll('.custom-file-input')[index].click();
    });
  });

  /////////////////////////////////////////////////////////////////////////////////////////////////////
  //                Funcion para no enviar formularios vacios
  /////////////////////////////////////////////////////////////////////////////////////////////////////

  document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll("form").forEach(function(form) {
      form.addEventListener("submit", function(event) {
        let isValid = true;
        let mensajeError = "";

        form.querySelectorAll("input, select, textarea").forEach(function(campo) {
          // Omitir botones y campos opcionales
          if (campo.type === "submit" || campo.type === "reset" || campo.disabled) return;

          // Verificar si el campo está vacío
          if (campo.value.trim() === "") {
            isValid = false;
            mensajeError = "⚠️ Todos los campos son obligatorios.";
            campo.style.border = "2px solid red";
          } else {
            campo.style.border = ""; // Restablecer el borde si es válido
          }
        });

        if (!isValid) {
          event.preventDefault(); // Detener el envío del formulario

          // Mostrar alerta con SweetAlert
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
  const switchInput = document.getElementById('entradaSalidaSwitch');
  const switchLabel = document.getElementById('switchLabel');
  const switchText = document.getElementById('switchText');

  switchInput.addEventListener('change', function() {
    switchLabel.textContent = this.checked ? 'Registrar Salida' : 'Registrar Entrada';
    switchText.textContent = this.checked ? 'Ahora se registrará la salida' : 'Se registrará la entrada';
  });

  /////////////////////////////////////////////////////////////////////////////////////////////////////
  //                Funcion para buscar rondas por objetivos
  /////////////////////////////////////////////////////////////////////////////////////////////////////

  // Cuando cambie el objetivo, recargo los puestos
  document.getElementById('objetivo').addEventListener('change', function() {
    const objetivoId = this.value;
    const puestoSelect = document.getElementById('puesto');

    // Limpio opciones
    puestoSelect.innerHTML = '<option value="" disabled selected>Selecciona un puesto</option>';

    // Agrego solo las rondas que coinciden
    rondas.forEach(r => {
      if (String(r.objetivo_id) === String(objetivoId)) {
        const opt = document.createElement('option');
        opt.value = r.idRonda;
        opt.textContent = r.puesto;
        puestoSelect.appendChild(opt);
      }
    });
  });

  $(function() {
    // Cuando cambie el objetivo, recargo los puestos
    $('#objetivo').on('change', function() {
      const id = this.value;
      const $puesto = $('#puesto').empty()
        .append('<option value="" disabled selected>Selecciona un puesto</option>');

      rondas.forEach(r => {
        if (String(r.objetivo_id) === String(id)) {
          $puesto.append(
            `<option value="${r.idRonda}">${r.puesto}</option>`
          );
        }
      });
    });

    // Inicializo Select2 para buscar Vigiladores
    $('#vigilador').select2({
      placeholder: 'Escribí el apellido…',
      allowClear: true,
      width: '100%'
    });
  });
