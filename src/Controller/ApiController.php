<?php

namespace App\Controller;

use App\Database\Connection;
use PDO;

class ApiController
{
    /**
     * Retorna métricas desde base de datos.
     */
    public function getRawMetrics(): void
    {
        header('Content-Type: application/json');

        try {
            $db = Connection::get();

            $stmt = $db->prepare("SELECT month, ventas, usuarios FROM metrics ORDER BY id ASC");
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener métricas', 'details' => $e->getMessage()]);
        }
    }
}
