jQuery(document).ready(function () {
  jQuery
    .ajax({
      type: "POST",
      url: "controller/cargar_departamentos.php",
    })
    .done(function (departamentos) {
      jQuery("#departamento").html(departamentos);
    })
    .fail(function (e) {
      console.log(e);
      alert("Hubo un error");
    });

  jQuery("#departamento").on("change", function () {
    jQuery("#distrito")
      .find("option")
      .remove()
      .end()
      .append('<option value="0">Distrito</option>')
      .val("0");

    const id = jQuery("#departamento").val();

    jQuery
      .ajax({
        type: "POST",
        url: "controller/cargar_provincias.php",
        data: { id: id },
      })
      .done(function (provincias) {
        jQuery("#provincia").html(provincias);
      })
      .fail(function () {
        alert("Hubo un error");
      });
  });

  jQuery("#provincia").on("change", function () {
    const id = jQuery("#provincia").val();
    jQuery
      .ajax({
        type: "POST",
        url: "controller/cargar_distritos.php",
        data: { id: id },
      })
      .done(function (distritos) {
        jQuery("#distrito").html(distritos);
      })
      .fail(function () {
        alert("Hubo un error");
      });
  });
});
