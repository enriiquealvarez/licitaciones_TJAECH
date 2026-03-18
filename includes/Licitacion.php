<?php
// includes/Licitacion.php

class Licitacion {
    private $conn;
    private $table_name = "licitaciones";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Insertar nueva licitación
    public function crear($datos) {
        $query = "INSERT INTO " . $this->table_name . "
                  SET numero_licitacion=:num, titulo=:tit, descripcion=:desc, 
                      tipo_procedimiento=:tipo, anio=:anio, fecha_publicacion=:f_pub, 
                      fecha_limite=:f_lim, area_responsable=:area, pdf_principal=:pdf,
                      slug=:slug, estatus=:est, creado_por=:creador";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':num', $datos['numero_licitacion']);
        $stmt->bindParam(':tit', $datos['titulo']);
        $stmt->bindParam(':desc', $datos['descripcion']);
        $stmt->bindParam(':tipo', $datos['tipo_procedimiento']);
        $stmt->bindParam(':anio', $datos['anio']);
        $stmt->bindParam(':f_pub', $datos['fecha_publicacion']);
        $stmt->bindParam(':f_lim', $datos['fecha_limite']);
        $stmt->bindParam(':area', $datos['area_responsable']);
        $stmt->bindParam(':pdf', $datos['pdf_principal']);
        $stmt->bindParam(':slug', $datos['slug']);
        $stmt->bindParam(':est', $datos['estatus']);
        $stmt->bindParam(':creador', $datos['creado_por']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Actualizar licitacion existente
    public function actualizar($id, $datos) {
        $query = "UPDATE " . $this->table_name . "
                  SET titulo=:tit, descripcion=:desc, tipo_procedimiento=:tipo,
                      fecha_publicacion=:f_pub, fecha_limite=:f_lim, 
                      area_responsable=:area, estatus=:est, actualizado_por=:actualizador";
        
        // Solo actualizar el numero, año y slug si son pasados
        if (isset($datos['numero_licitacion'])) $query .= ", numero_licitacion=:num";
        if (isset($datos['anio'])) $query .= ", anio=:anio";
        if (isset($datos['slug'])) $query .= ", slug=:slug";
        if (isset($datos['pdf_principal'])) $query .= ", pdf_principal=:pdf";

        $query .= " WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':tit', $datos['titulo']);
        $stmt->bindParam(':desc', $datos['descripcion']);
        $stmt->bindParam(':tipo', $datos['tipo_procedimiento']);
        $stmt->bindParam(':f_pub', $datos['fecha_publicacion']);
        $stmt->bindParam(':f_lim', $datos['fecha_limite']);
        $stmt->bindParam(':area', $datos['area_responsable']);
        $stmt->bindParam(':est', $datos['estatus']);
        $stmt->bindParam(':actualizador', $datos['actualizado_por']);
        $stmt->bindParam(':id', $id);

        if (isset($datos['numero_licitacion'])) $stmt->bindParam(':num', $datos['numero_licitacion']);
        if (isset($datos['anio'])) $stmt->bindParam(':anio', $datos['anio']);
        if (isset($datos['slug'])) $stmt->bindParam(':slug', $datos['slug']);
        if (isset($datos['pdf_principal'])) $stmt->bindParam(':pdf', $datos['pdf_principal']);

        return $stmt->execute();
    }

    // Eliminar
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }

    // Obtener todas para panel admin
    public function leerTodosAdmin() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener publicadas para portal público
    public function leerPublicas($search = "", $anio = "", $procedimiento = "") {
        $query = "SELECT * FROM " . $this->table_name . " WHERE estatus = 'publicado'";
        
        $params = [];
        
        if (!empty($search)) {
            $query .= " AND (titulo LIKE ? OR numero_licitacion LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if (!empty($anio)) {
            $query .= " AND anio = ?";
            $params[] = $anio;
        }
        if (!empty($procedimiento)) {
            $query .= " AND tipo_procedimiento = ?";
            $params[] = $procedimiento;
        }

        $query .= " ORDER BY fecha_publicacion DESC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        
        $stmt->execute();
        return $stmt;
    }

    // Leer una por ID
    public function leerUnico($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Leer por Slug (Para página pública)
    public function leerPorSlug($slug) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE slug = ? AND estatus = 'publicado' LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $slug);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Comprobar existencia de número de licitación para evitar duplicados
    public function existeNumero($numero, $exclude_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE numero_licitacion = ?";
        if ($exclude_id !== null) {
            $query .= " AND id != ?";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $numero);
        if ($exclude_id !== null) {
            $stmt->bindParam(2, $exclude_id);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
