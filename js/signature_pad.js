var canvas = document.getElementById('signature-pad');

var signaturePad = new SignaturePad(canvas, {
  backgroundColor: 'rgb(255, 255, 255)', // necessary for saving image as JPEG; can be removed is only saving as PNG or SVG
});

// Adjust canvas coordinate space taking into account pixel ratio,
// to make it look crisp on mobile devices.
// This also causes canvas to be cleared.
function resizeCanvas() {
  // When zoomed out to less than 100%, for some very strange reason,
  // some browsers report devicePixelRatio as less than 1
  // and only part of the canvas is cleared then.
  var ratio = Math.max(window.devicePixelRatio || 1, 1);
  canvas.width = canvas.offsetWidth * ratio || 400;
  canvas.height = canvas.offsetHeight * ratio || 200;
  canvas.getContext("2d").scale(ratio, ratio);
  signaturePad.clear();
}

window.addEventListener("resize", resizeCanvas);
resizeCanvas();

let fileSignaturePad;

document.getElementById('save-jpeg').addEventListener('click', function() {
  if (signaturePad.isEmpty()) {
    document.getElementById('alerta-firma').style.color = "red";
    document.getElementById('alerta-firma').innerHTML = "Debe agregar y guardar su firma";
    setTimeout(() => {
      document.getElementById('alerta-firma').innerHTML = "";
    }, 1500);
    return;
  } else {
    var data = signaturePad.toDataURL('image/jpeg');
    document.getElementById('alerta-firma').style.color = "#18ADDE";
    document.getElementById('alerta-firma').innerHTML = "Firma guardada correctamente";
    setTimeout(() => {
      document.getElementById('alerta-firma').innerHTML = "";
    }, 1500);
  
    fileSignaturePad = dataURLtoFile(data, 'SignaturePad');
  }

});

document.getElementById('clear').addEventListener('click', function() {
  signaturePad.clear();
  fileSignaturePad = undefined;
});

function dataURLtoFile(data, name) {
  var arr = data.split(","),
    mime = arr[0].match(/:(.*?);/)[1],
    bstr = atob(arr[1]),
    n = bstr.length,
    u8arr = new Uint8Array(n);

  while (n--) {
    u8arr[n] = bstr.charCodeAt(n);
  }

  return new File([u8arr], name, { type: mime });
}