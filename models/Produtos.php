
<?php

include_once '/var/www/html/food_order_backend/config/database.php';

class Produtos {

    private $db;

    public function __construct() {
        $this->db = conecta(); // Aqui a conexão é inicializada corretamente
        if (!$this->db) {
            die("Erro ao conectar ao banco de dados.");
        }
    }

    //Pegando Produtos pelo id Categoria
    public function getProdutosByCategoria($id) {
        // Executa a consulta ao banco de dados
        $stmt = $this->db->prepare("SELECT * FROM produtos WHERE id_categoria = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    //CREATE PRODUTO
    public function createProduto($dados) {
        $sql = "INSERT INTO produtos (nome, descricao, preco, id_categoria, qtde) 
            VALUES (:nome, :descricao, :preco, :id_categoria, :qtde)";

        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':descricao', $dados['descricao']);
        $stmt->bindParam(':preco', $dados['preco']);
        $stmt->bindParam(':id_categoria', $dados['id_categoria'], PDO::PARAM_INT);
        $stmt->bindParam(':qtde', $dados['qtde'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Produto cadastrado com sucesso'];
        } else {
            return ['success' => false, 'error' => 'Erro ao cadastrar produto'];
        }
    }
    
    //LISTAR TODOS OS PRODUTOS
    public function buscarTodos($limit = 10, $offset = 0) {
        // Consulta os produtos com paginação
        $sql = "SELECT p.*, c.nome AS categoria_nome
            FROM produtos p
            JOIN categorias_produtos c ON p.id_categoria = c.id_categoria
            ORDER BY p.nome ASC
            LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Consulta o total de produtos sem paginação
        $countSql = "SELECT COUNT(*) FROM produtos";
        $countStmt = $this->db->query($countSql);
        $total = $countStmt->fetchColumn();

        // Retorna os dados paginados e o total
        return [
            'produtos' => $produtos,
            'total' => $total
        ];
    }
    
      public function delete($id_produto) {
        $stmt = $this->db->prepare("DELETE FROM produtos WHERE id_produto = :id");
        return $stmt->execute([':id' => $id_produto]);
    }

}
?>  

