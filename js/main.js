$(document).ajaxError(function (event, jqxhr, settings, thrownError) {
    console.error("🚨 ERROR en AJAX 🚨");
    console.error("URL:", settings.url);
    console.error("Estado:", jqxhr.status);
    console.error("Texto del estado:", jqxhr.statusText);
    console.error("Respuesta:", jqxhr.responseText);
    console.error("Error lanzado:", thrownError);

    alert("Se ha producido un error en la solicitud AJAX. Revisa la consola para más detalles.");
});
