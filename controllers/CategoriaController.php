<?php
include_once '/var/www/html/food_order_backend/models/Categoria.php';

class CategoriaController {
    
    private  $modelCategoria;
    
     public function __construct() {
        $this->modelCategoria = new Categoria(); // Se a classe Mesas requer um parâmetro, pode estar errado aqui
    }

    public function getCategorias() {
      $categorias = $this->modelCategoria->getCategorias();
       echo json_encode($categorias);
    }

    
}
?>


