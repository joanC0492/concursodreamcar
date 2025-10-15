<?php
/**
 * Funciones de Cifrado para Datos Sensibles - Toyota Dream Car
 * 
 * Implementa cifrado AES-256-GCM para proteger números de DNI
 * Cumple con requerimientos de auditoría de seguridad Toyota
 */

// Clave de cifrado - DEBE estar en variables de entorno
function obtenerClaveCifrado() {
    $clave = getenv('ENCRYPTION_KEY');
    if (empty($clave)) {
        // Para desarrollo - CAMBIAR en producción
        $clave = 'toyota_dreamcar_encryption_key_2025_secure_dni_protection';
    }
    
    // Derivar clave de 32 bytes usando PBKDF2
    return hash_pbkdf2('sha256', $clave, 'toyota_salt_2025', 10000, 32, true);
}

/**
 * Cifra un número de DNI usando AES-256-GCM
 * @param string $dni Número de DNI a cifrar
 * @return string DNI cifrado en formato base64
 */
function cifrarDNI($dni) {
    if (empty($dni)) {
        return '';
    }
    
    $clave = obtenerClaveCifrado();
    $iv = random_bytes(12); // IV de 12 bytes para GCM
    
    $cifrado = openssl_encrypt(
        $dni, 
        'aes-256-gcm', 
        $clave, 
        OPENSSL_RAW_DATA, 
        $iv,
        $tag
    );
    
    if ($cifrado === false) {
        throw new Exception('Error al cifrar DNI');
    }
    
    // Combinar IV + tag + datos cifrados
    $resultado = $iv . $tag . $cifrado;
    
    return base64_encode($resultado);
}

/**
 * Descifra un número de DNI cifrado
 * @param string $dni_cifrado DNI cifrado en formato base64
 * @return string DNI descifrado
 */
function descifrarDNI($dni_cifrado) {
    if (empty($dni_cifrado)) {
        return '';
    }
    
    try {
        $datos = base64_decode($dni_cifrado);
        if ($datos === false) {
            throw new Exception('Formato de DNI cifrado inválido');
        }
        
        $clave = obtenerClaveCifrado();
        
        // Extraer IV (12 bytes), tag (16 bytes) y datos cifrados
        $iv = substr($datos, 0, 12);
        $tag = substr($datos, 12, 16);
        $cifrado = substr($datos, 28);
        
        $dni = openssl_decrypt(
            $cifrado,
            'aes-256-gcm',
            $clave,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        if ($dni === false) {
            throw new Exception('Error al descifrar DNI');
        }
        
        return $dni;
        
    } catch (Exception $e) {
        // Log error pero no exponer detalles
        error_log('Error descifrado DNI: ' . $e->getMessage());
        return '[DNI_CIFRADO]'; // Placeholder para mostrar en reportes
    }
}

/**
 * Valida formato de DNI peruano
 * @param string $dni DNI a validar
 * @return bool True si es válido
 */
function validarFormatoDNI($dni) {
    // DNI peruano: 8 dígitos
    return preg_match('/^\d{8}$/', $dni);
}

/**
 * Cifra DNI con validación previa
 * @param string $dni DNI a cifrar
 * @return string DNI cifrado o excepción
 */
function cifrarDNIValidado($dni) {
    if (!validarFormatoDNI($dni)) {
        throw new Exception('Formato de DNI inválido. Debe tener 8 dígitos.');
    }
    
    return cifrarDNI($dni);
}
?>