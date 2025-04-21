
<?php
include_once '/var/www/html/food_order_backend/models/Mesas.php';

class MesasController {
    
    private $modelMesas;

    public function __construct() {
        $this->modelMesas = new Mesas(); // Se a classe Mesas requer um parâmetro, pode estar errado aqui
    }

    public function getAllMesas() {
       $mesas = $this->modelMesas->getAllMesas();
       echo json_encode($mesas);
    }

    public function getMesaById($id) {
        $mesa = $this->modelMesas->getMesaById($id);  // Chama o método de instância
        echo json_encode($mesa);
    }

    public function updateMesaStatus($id, $status) {
        $this->modelMesas->updateMesaStatus($id, $status);  // Chama o método de instância
        echo json_encode(['status' => 'success']);
    }
    
    // Função para atualizar o status da mesa
function atualizarStatusMesa($idMesa, $novoStatus) {
    // Código para atualizar o status da mesa no banco de dados
    
    $this->updateMesaStatus($id, $novoStatus);
    
    // Após atualizar o banco, enviar atualização aos clientes WebSocket
    $mensagem = json_encode(['idMesa' => $idMesa, 'novoStatus' => $novoStatus]);

    // Código para enviar a mensagem ao servidor WebSocket
    $this->enviarMensagemWebSocket($mensagem);
}

// Função para enviar mensagem ao servidor WebSocket
function enviarMensagemWebSocket($mensagem) {
    $url = 'ws://localhost:8080';
    $client = new WebSocket\Client($url);
    $client->send($mensagem);
}




}
?>
