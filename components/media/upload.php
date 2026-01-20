<?php
    require("classes/Media.php");

    if (!empty($_FILES['image'])) {
        $response = Media::uploadImage($_FILES['image']);
        echo json_encode($response);
    } else {
        echo json_encode(array("error" => "No se recibió ninguna imagen", "code" => "NO_IMAGE"));
    }
?>
