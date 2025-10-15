<div class="popup-wins" id="miPopup">
    <span class="cerrar" id="popupCerrar" onclick="cerrarPopup()">&times;</span>
    <div id="popupImgContent">
        <img src="" alt="Imagen Popup" id="imagenPopup">
    </div>
</div>
<script type='text/javascript' src='../js/vendor/jquery/jquery.js'></script>
<script type='text/javascript' src='../js/custom/custom.js'></script>
<script type='text/javascript' src='../js/vendor/superfish.js'></script>
<script type='text/javascript' src='../js/custom/jquery/core.utils.js'></script>
<script type='text/javascript' src='../js/custom/jquery/core.init.js'></script>
<script type='text/javascript' src='../js/custom/jquery/theme.init.js'></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var imagenes = document.querySelectorAll(".box-win-categoria-imagen");
        var popup = document.getElementById("miPopup");
        var imagenPopup = document.getElementById("imagenPopup");
        var popupImgContent = document.getElementById("popupImgContent");
        var cerrar = document.getElementById("popupCerrar");

        imagenes.forEach(function(imagen) {
            imagen.addEventListener("click", function() {
                imagenPopup.src = this.querySelector("img").src;
                popup.style.display = "block";
            });
        });

        cerrar.addEventListener("click", function() {
            popup.style.display = "none";
        });

        popup.addEventListener("click", function (event) {
            if (event.target === popup || event.target === cerrar || event.target === popupImgContent) {
                popup.style.display = "none";
            }
        });
    });
</script>

