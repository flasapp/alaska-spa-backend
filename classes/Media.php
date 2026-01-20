<?php

class Media {
    public static function uploadImage($image, $destination = null) {
        $limite_kb = 1000;
        $allowedTypes = ["image/jpeg", "image/jpg", "image/gif", "image/png", "image/webp"];
        
        if (in_array($image['type'], $allowedTypes) && $image['size'] <= $limite_kb * 1024) {
            
            $carpetaDestino = UPLOAD_DIR;
            
            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0777, true);
            }

            $newName = time() . "_" . basename($image['name']);
            $uploadfile = $carpetaDestino . $newName;

            if (move_uploaded_file($image['tmp_name'], $uploadfile)) {
                return array("success" => true, "filename" => $newName, "url" => UPLOAD_URL . $newName);
            } else {
                return array("error" => "Error al subir el archivo físico al servidor", "path" => $uploadfile);
            }
        } else {
            return array("error" => "Tipo de imagen incorrecta o excede el peso permitido (" . $limite_kb . "KB)");
        }
    }
}
?>
