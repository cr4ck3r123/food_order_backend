
<?php

include_once '/var/www/html/food_order_backend/config/database.php';

class Categoria {

    private $db;

    public function __construct() {
        $this->db = conecta(); // Aqui a conexão é inicializada corretamente
        if (!$this->db) {
            die("Erro ao conectar ao banco de dados.");
        }
    }

    public function getCategorias() {
        // Executa a consulta ao banco de dados
        $stmt = $this->db->prepare("SELECT * FROM categorias_produtos");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }  

}
?>  

