
<?php

include_once '/var/www/html/food_order_backend/config/database.php';

class Pedidos {

    private $db;

    public function __construct() {
        $this->db = conecta(); // Aqui a conexão é inicializada corretamente
        if (!$this->db) {
            die("Erro ao conectar ao banco de dados.");
        }
    }

    public function getPedidosByMesa($id_mesa) {
        $stmt = $this->db->prepare("
        SELECT 
            p.id_pedido,
            p.id_mesa,
            p.data_pedido,
            ip.id_item,
            ip.id_produto,
            ip.quantidade,
            ip.preco_unitario,
            ip.status AS status_item,  -- Agora pegamos o status do item
            pr.nome AS nome_produto
        FROM pedidos p
        INNER JOIN itens_pedido ip ON p.id_pedido = ip.id_pedido
        INNER JOIN produtos pr ON ip.id_produto = pr.id_produto
        WHERE p.id_mesa = :id_mesa AND p.conta_fechada = 0
    ");

        $stmt->execute(['id_mesa' => $id_mesa]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createPedido($id_mesa, $id_garcom, $itens) {
        try {
            // Inicia a transação
            $this->db->beginTransaction();

            // Insere o pedido na tabela pedidos
            $stmt = $this->db->prepare("INSERT INTO pedidos (id_mesa, id_garcom, status) VALUES (:id_mesa, :id_garcom, 'Em andamento')");
            $stmt->execute(['id_mesa' => $id_mesa, 'id_garcom' => $id_garcom]);

            // Obtém o ID do pedido recém-criado
            $id_pedido = $this->db->lastInsertId();

            // Query para obter o preço do produto
            $stmtPreco = $this->db->prepare("SELECT preco FROM produtos WHERE id_produto = :id_produto");

            // Query para inserir itens no pedido
            $stmtItem = $this->db->prepare("INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario) 
                                        VALUES (:id_pedido, :id_produto, :quantidade, :preco_unitario)");

            foreach ($itens as $item) {
                // Obtém o preço do produto do banco de dados
                $stmtPreco->execute(['id_produto' => $item['id_produto']]);
                $produto = $stmtPreco->fetch(PDO::FETCH_ASSOC);

                if (!$produto) {
                    throw new Exception("Produto ID " . $item['id_produto'] . " não encontrado.");
                }

                $preco_unitario = $produto['preco'];

                // Insere o item no pedido
                $stmtItem->execute([
                    'id_pedido' => $id_pedido,
                    'id_produto' => $item['id_produto'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $preco_unitario
                ]);
            }

            // Confirma a transação
            $this->db->commit();

            // 🔥 Verifica se tem itens de churrasco e imprime
            //  $this->imprimirPedidoChurrasqueira($id_pedido, $itens);

            return json_encode(["success" => true, "id_pedido" => $id_pedido]);
        } catch (Exception $e) {
            // Em caso de erro, reverte a transação
            $this->db->rollBack();
            return json_encode(["error" => "Erro ao criar pedido: " . $e->getMessage()]);
        }
    }

    // FECHAR CONTA
    public function fecharConta($idMesa) {
        $stmt = $this->db->prepare("UPDATE pedidos SET conta_fechada = 1 WHERE id_mesa = :id_mesa");
        $stmt->execute(['id_mesa' => $idMesa]);
        $stmt->fetch(PDO::FETCH_ASSOC);

        // Atualiza o status da mesa para 'Livre'
        // Segunda atualização (status da mesa)
        $query = "UPDATE mesas SET status = 'Livre' WHERE id_mesa = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$idMesa]);

        echo json_encode(["success" => "success"]);
        exit;
    }

    // PEGAR PEDIDOS CHURRASQUEIRA
    public function getChurrasqueira() {
        // Executa a consulta ao banco de dados
        $stmt = $this->db->prepare("
        SELECT 
    ip.id_pedido,
    ip.status AS status_item,
    p.id_mesa,
    p.data_pedido,
    ip.quantidade,
    ip.preco_unitario,
    pr.nome AS nome_produto,
    cp.setor_destino
FROM itens_pedido ip
INNER JOIN pedidos p ON ip.id_pedido = p.id_pedido
INNER JOIN produtos pr ON ip.id_produto = pr.id_produto
INNER JOIN categorias_produtos cp ON pr.id_categoria = cp.id_categoria
WHERE p.conta_fechada = 0
  AND cp.setor_destino = 'churrasqueira';
;
    ");

        $stmt->execute();

        // Retorna todos os resultados encontrados
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //FUNÇÂO PARA IMPRIMIR PEDIDOS NA CHURRASQUEIRA
    private function imprimirPedidoChurrasqueira($id_pedido, $itens) {
        $printerIp = '192.168.100.250';
        $printerPort = 9100;

        // Conecta no socket da impressora
        $socket = fsockopen($printerIp, $printerPort, $errno, $errstr, 10);

        if (!$socket) {
            error_log("Erro na impressão do pedido $id_pedido: $errstr ($errno)");
            return;
        }

        // Começa a montar o texto do pedido
        $texto = "=== CHURRASQUEIRA ===\n";
        $texto .= "PEDIDO Nº $id_pedido\n";
        $texto .= date("d/m/Y H:i") . "\n";
        $texto .= "-----------------------\n";

        // Buscar nome do produto e categoria
        $stmtProduto = $this->db->prepare("
    SELECT c.nome AS categoria, p.nome AS produto 
    FROM produtos p
    JOIN categorias_produtos c ON p.id_categoria = c.id_categoria
    WHERE p.id_produto = :id_produto
        ");

        foreach ($itens as $item) {
            $stmtProduto->execute(['id_produto' => $item['id_produto']]);
            $produto = $stmtProduto->fetch(PDO::FETCH_ASSOC);

            if ($produto && strtolower($produto['categoria']) === 'churrasqueira') {
                $texto .= strtoupper($produto['produto']) . " x" . $item['quantidade'] . "\n";
            }
        }


        $texto .= "\n\n\n";

        // Envia o texto pra impressora
        fwrite($socket, $texto);
        fclose($socket);
    }

    // Este método será chamado no case 'update_status_pedido'
  public function updateStatusPedidoChurras($idPedido, $status) {
    $statusValido = in_array($status, ['Em andamento', 'Concluído', 'Cancelado']);

    if (!$statusValido) {
        return false;
    }

    $sql = "
        UPDATE itens_pedido ip
        INNER JOIN produtos pr ON ip.id_produto = pr.id_produto
        INNER JOIN categorias_produtos cp ON pr.id_categoria = cp.id_categoria
        SET ip.status = :status
        WHERE ip.id_pedido = :id_pedido
        AND cp.setor_destino = 'churrasqueira';
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id_pedido', $idPedido, PDO::PARAM_INT);

    return $stmt->execute();
}


}
?>  

