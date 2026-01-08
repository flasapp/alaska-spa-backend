<?php
require dirname(__DIR__) . '/config/env.php';

class Task {
    private $model = "tareas";

    // Get All Tasks
    public function getAll($conn) {
        // Based on the example provided:
        // SELECT tareas.* , usuarios.nomUsuario, usuarios.apellido FROM tareas LEFT OUTER JOIN usuarios ON tareas.idUsuario = usuarios.idUsuario WHERE tareas.estado != 2 ORDER BY fechaRealizado DESC
        $sql = "SELECT t.idTarea as id, t.titulo as title, t.descripcion as description, t.fechaRealizado as date, t.estado as status, t.idUsuario as userId, u.nomUsuario as userName, u.apellido as userLastName 
                FROM ".$this->model." t 
                LEFT OUTER JOIN usuarios u ON t.idUsuario = u.idUsuario 
                WHERE t.estado != 2 
                ORDER BY t.fechaRealizado DESC";
        
        $d = $conn->query($sql);

        if (empty($d)) {
            return array();
        } else {
            return $d;
        }
    }

    // Create Task
    public function create($conn, $item) {
        $title = isset($item['title']) ? $item['title'] : '';
        $description = isset($item['description']) ? $item['description'] : '';
        $userId = isset($item['userId']) ? $item['userId'] : 0;
        
        // Validation could be added here
        if(empty($title) || empty($userId)){
             return array("error" => "Create Task: Missing required fields (title, userId)", "code" => "MISSING_DATA");
        }

        $sql = "INSERT INTO ".$this->model." (titulo, descripcion, fechaRealizado, idUsuario, estado)
                VALUES ('$title', '$description', NOW(), '$userId', 0)";
        
        $d = $conn->query($sql);
        
        if (empty($d)) { // "if($datos == "")" check from example means success in this context usually? 
            // Wait, looking at User.php: 
            // $d = $conn->query($sql); if($datos == ""){ success } 
            
            // However, typically $conn->query returns data on SELECT and something else on INSERT.
            // Let's stick to the pattern I saw in User.php:
            // if($d == ""){ return OK }
            // The provided example says:
            // if($datos == ""){ return OK }
            
            return array("response" => "Ok", "success" => true);
        } else {
             return array("error" => "Error creating task", "sql" => $sql);
        }
    }

    // Update Task
    public function update($conn, $id, $item) {
        // We only update fields that are passed
        $updates = [];
        if(isset($item['title'])) $updates[] = "titulo = '".$item['title']."'";
        if(isset($item['description'])) $updates[] = "descripcion = '".$item['description']."'";
        if(isset($item['status'])) $updates[] = "estado = '".$item['status']."'";

        if(empty($updates)){
             return array("response" => "OK", "message" => "No changes");
        }

        $sql = "UPDATE ".$this->model." SET ".implode(', ', $updates)." WHERE idTarea ='$id'";
        
        $d = $conn->query($sql);
        
        if (empty($d)) {
            return array("response" => "Ok", "success" => true);
        } else {
            return array("error" => "Error updating task", "sql" => $sql);
        }
    }

    // Delete Task (Soft Delete)
    public function delete($conn, $id) {
        $sql = "UPDATE ".$this->model." SET estado = 2 WHERE idTarea ='$id'";
        $d = $conn->query($sql);
        
        if (empty($d)) {
            return array("response" => "Ok", "success" => true);
        } else {
            return array("error" => "Error deleting task", "sql" => $sql);
        }
    }
}
?>
