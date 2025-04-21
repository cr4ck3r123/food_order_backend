<?php
include_once '/var/www/html/food_order_backend/models/Produtos.php';

class ProdutosController {
    
    private  $modelProdutos;
    
     public function __construct() {
        $this->modelProdutos = new Produtos(); // Se a classe Mesas requer um parâmetro, pode estar errado aqui
    }

    public function getProdutosByCategoria($id) {
      $categorias = $this->modelProdutos->getProdutosByCategoria($id);
       echo json_encode($categorias);
    }

    public function createProduto($nome, $descricao, $preco, $id_categoria, $qtde) {
        $resultado = $this->modelProdutos->createProduto([
            'nome' => $nome,
            'descricao' => $descricao,
            'preco' => $preco,
            'id_categoria' => $id_categoria,
            'qtde' => $qtde
        ]);
        echo json_encode($resultado);
    }
  
    //PEGAR TODOS OS PRODUTOS
    public function getAllProdutos() {
        try {
            // Lê os dados do corpo da requisição (JSON)
            $dados = json_decode(file_get_contents("php://input"), true);
            $limit = isset($dados['limit']) ? intval($dados['limit']) : 10;
            $offset = isset($dados['offset']) ? intval($dados['offset']) : 0;

            // Chama o método com os parâmetros de paginação
            $resultado = $this->modelProdutos->buscarTodos($limit, $offset);

            if ($resultado['produtos']) {
                echo json_encode([
                    "success" => true,
                    "produtos" => $resultado['produtos'],
                    "total" => $resultado['total'] // 🔢 Total de produtos (sem paginação)
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "error" => "Nenhum produto encontrado."
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "error" => "Erro ao buscar produtos: " . $e->getMessage()
            ]);
        }
    }
    
    
    //METODO DELETE 
    public function delete() {
        header("Content-Type: application/json");

        // Lê o corpo da requisição
        $input = json_decode(file_get_contents("php://input"), true);
        $id_produto = $input['id_produto'] ?? null;
        
        if (!$id_produto) {
            echo json_encode(['success' => false, 'error' => 'ID do produto inválido']);
            exit;
        }

        $result = $this->modelProdutos->delete($id_produto);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Falha ao deletar produto']);
        }
    }

}
?>


