<?php

include_once '/var/www/html/food_order_backend/config/database.php';

class Mesas {

    private $db;

    public function __construct() {
        $this->db = conecta(); // Aqui a conexão é inicializada corretamente
          if (!$this->db) {
        die("Erro ao conectar ao banco de dados.");
    }
    
    }

    // Método de instância
    public function getAllMesas() {
        // Executa a consulta ao banco de dados
        $stmt = $this->db->prepare("SELECT * FROM mesas");
        $stmt->execute();  // Você esqueceu de executar a query
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Para debug, imprima o resultado
    //    var_dump($result);

        return $result; // Retorna os resultados // Retorna os resultados
    }

    public function getMesaById($id) {
        // Consulta para buscar uma mesa pelo ID
        $stmt = $this->db->prepare("SELECT * FROM mesas WHERE id_mesa = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateMesaStatus($id, $status) {
        // Atualiza o status da mesa
        $stmt = $this->db->prepare("UPDATE mesas SET status = :status WHERE id_mesa = :id");
        $stmt->execute(['status' => $status, 'id' => $id]);
    }

}

?>
