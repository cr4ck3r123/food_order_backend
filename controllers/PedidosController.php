<?php

include_once '/var/www/html/food_order_backend/models/Pedidos.php';

class PedidosController {

    private $modelPedidos;

    public function __construct() {
        $this->modelPedidos = new Pedidos(); // Se a classe Mesas requer um parâmetro, pode estar errado aqui
    }

    public function getPedidosByMesa($id_mesa) {
        $pedidos = $this->modelPedidos->getPedidosByMesa($id_mesa);
        echo json_encode($pedidos);
    }

    //Controller para criar pedido
    public function createPedido($id_mesa, $id_garcom, $itens) {
        if (isset($id_mesa) && isset($id_garcom)) {
            $pedidoId = $this->modelPedidos->createPedido($id_mesa, $id_garcom, $itens);

            echo json_encode(['pedido_id' => $pedidoId]);
        } else {
            echo json_encode(["error" => "Algo deu errado"]);
        }
    }

    //CONTROLLER PARA FECHAR A CONTA
    public function fecharConta($idMesa) {
        if (isset($idMesa)) {
            $this->modelPedidos->fecharConta($idMesa);
        } else {
            echo json_encode(["error" => "Algo deu errado"]);
        }
    }

    //CONTROLLER PARA PEGAR SOMENTES PEDIDOS DA CHURRAQUEIRA
    public function getChurrasqueira() {
        $result = $this->modelPedidos->getChurrasqueira();
        echo json_encode($result);
    }

    // Este método será chamado no case 'update_status_pedido'
    public function updateStatusPedido($idPedido, $status) {
        // Chama o método da model
        return $this->modelPedidos->updateStatusPedidoChurras($idPedido, $status);
    }

}
?>


