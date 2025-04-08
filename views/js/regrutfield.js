// Función para calcular el dígito verificador (algoritmo Módulo 11)
function calcularDV(rutNum) {
    let M = 0, S = 1;
    while (rutNum > 0) {
        S = (S + rutNum % 10 * (9 - M++ % 6)) % 11;
        rutNum = Math.floor(rutNum / 10);
    }
    return S ? S - 1 : 'k';  // Si S == 0, retorna 'k', en caso contrario retorna S-1
}

// Función para validar el RUT completo "XXXXXXXX-X"
function validarRUT(rutCompleto) {
    // Eliminar puntos del RUT por si el usuario los ingresó
    rutCompleto = rutCompleto.replace(/\./g, '').trim();

    // Verificar formato: sólo dígitos, un guion y dígito verificador (número o K)
    const regex = /^[0-9]+[-‐]{1}[0-9Kk]{1}$/;
    if (!regex.test(rutCompleto)) {
        return false; // formato no válido&#8203;:contentReference[oaicite:0]{index=0}
    }
    // Separar cuerpo y dígito verificador
    const [cuerpo, dv] = rutCompleto.split('-');
    // Calcular dígito verificador esperado
    let dvCalculado = calcularDV(parseInt(cuerpo, 10));
    // Estandarizar a minúscula para comparar 'k'
    dvCalculado = dvCalculado === 'k' ? 'k' : dvCalculado.toString();
    const dvUsuario = dv.toLowerCase();
    return dvCalculado === dvUsuario; // true si el dígito verificador coincide
}

// Esperar a que el DOM esté listo para asociar eventos
document.addEventListener('DOMContentLoaded', function() {
    // Obtener referencias al formulario y al campo de RUT
    console.log("Ejecutando script de validación de RUT");

    const formRegistro = document.querySelector('#customer-form');  // formulario de registro
    const inputRUT = document.querySelector('#field-rut');   // campo de RUT

    if (!formRegistro || !inputRUT) return; // Si no existe el formulario o campo, no continuar

    // Crear elemento para mostrar mensaje de error de RUT (oculto inicialmente)
    const msgError = document.createElement('div');
    msgError.className = 'invalid-feedback';  // clase de Bootstrap para mensaje de error
    msgError.textContent = 'Ingrese un RUT válido (formato 12345678-5)'; 
    msgError.style.display = 'none';
    inputRUT.parentNode.appendChild(msgError);

    // Función para mostrar/ocultar mensaje de error según validez del RUT
    function revisarRUT() {
        const rutVal = inputRUT.value;
        if (rutVal === '') {
            // Campo vacío: quitar mensaje de error
            inputRUT.classList.remove('is-invalid');
            msgError.style.display = 'none';
            return true;
        }
        if (validarRUT(rutVal)) {
            // RUT válido
            inputRUT.classList.remove('is-invalid');
            msgError.style.display = 'none';
            return true;
        } else {
            // RUT inválido: mostrar error
            inputRUT.classList.add('is-invalid');
            msgError.style.display = 'block';
            return false;
        }
    }

    // Validar al perder foco (blur) y en cada cambio (input) para feedback inmediato
    inputRUT.addEventListener('blur', revisarRUT);
    inputRUT.addEventListener('input', function() {
        if (inputRUT.classList.contains('is-invalid')) {
            // Revalidar en vivo sólo si estaba marcado inválido
            revisarRUT();
        }
    });

    // Interceptar el envío del formulario para impedir envío si RUT inválido
    formRegistro.addEventListener('submit', function(e) {
        if (!revisarRUT()) {
            e.preventDefault();  // detiene el envío si el RUT no es válido&#8203;:contentReference[oaicite:1]{index=1}
            // (El mensaje de error ya se muestra junto al campo RUT)
        }
    });
});
