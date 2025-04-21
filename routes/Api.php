<?php

include_once 'controllers/MesasController.php';
include_once 'controllers/PedidosController.php';
include_once 'controllers/UsuariosController.php';
include_once 'controllers/CategoriaController.php';
include_once 'controllers/ProdutosController.php';

// Protegendo rotas com JWT
include_once 'AuthMiddleware.php';

//Desbloqueando CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

// Inicializando os controladores
$mesasController = new MesasController();
$pedidosController = new PedidosController();
$usuariosController = new UsuariosController();
$categoriasController = new CategoriaController();
$produtosController = new ProdutosController();

header('Content-Type: application/json');

//PEGANDO REQUISIÇÔES GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET['action'])) {
        echo json_encode(["error" => "Nenhuma ação especificada."]);
        exit;
    }

    switch ($_GET['action']) {
        case 'get_all_mesas':
            // AuthMiddleware::validarToken();
            $mesasController->getAllMesas();
            break;

        case 'get_pedido_by_churras':
            //           AuthMiddleware::validarToken();
            $pedidosController->getChurrasqueira();
            break;
        case 'get_mesa_by_id':
            AuthMiddleware::validarToken();
            if (isset($_GET['id'])) {
                $mesasController->getMesaById($_GET['id']);
            } else {
                echo json_encode(["error" => "ID da mesa é obrigatório."]);
            }
            break;
        case 'get_pedidos_by_mesa':
            //  AuthMiddleware::validarToken();
            if (isset($_GET['id_mesa'])) {
                $pedidosController->getPedidosByMesa($_GET['id_mesa']);
            } else {
                echo json_encode(["error" => "ID da mesa é obrigatório."]);
            }
            break;
        case 'get_user_by_id':
            AuthMiddleware::validarToken();
            $usuariosController->obterDadosUsuarios();
            break;
        default:
            echo json_encode(["error" => "Rota inválida."]);
            break;
    }
    exit;
}

//PEGANDO REQUISIÇÔES POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_GET['action'])) {
        echo json_encode(["error" => "Nenhuma ação especificada."]);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    switch ($_GET['action']) {
        case 'login':
            if (!isset($data['email']) || !isset($data['senha'])) {
                echo json_encode(["error" => "E-mail e senha são obrigatórios."]);
                exit;
            }
            $usuariosController->login($data['email'], $data['senha']);
            break;
        case 'logout':
            AuthMiddleware::logout(); // Método que invalidará o token.
            echo json_encode(["success" => true, "message" => "Logout realizado com sucesso."]);
            break;
        case 'fechar_conta':
            //           AuthMiddleware::validarToken();
            if (isset($data['id_mesa'])) { // Pegando do JSON enviado no POST
                $pedidosController->fecharConta($data['id_mesa']);
            } else {
                echo json_encode(["error" => "ID da mesa é obrigatório."]);
            }
            break;

        case 'get_all_user':
            AuthMiddleware::validarToken();
            $usuariosController->getAllUsuarios();
            break;
        case 'update_user':
            AuthMiddleware::validarToken();
            $usuariosController->updateUsuario($data['id_usuario'], $data['nome'], $data['email'], $data['senha']);
            break;
        case 'create_user':
            $usuariosController->createUsuario($data['nome'], $data['email'], $data['senha'], $data['telefone'], $data['tipo']);
            break;
        case 'delete_user':
            AuthMiddleware::validarToken();
            if (isset($data['id_usuario'])) {
                $usuariosController->deleteUsuario($data['id_usuario']);
            } else {
                echo json_encode(["error" => "Erro ao deletar usuário"]);
            }
            break;
        case 'create_pedido':
            $usuarioAutenticado = AuthMiddleware::validarToken();
            $pedidosController->createPedido($data['id_mesa'], $usuarioAutenticado->sub, $data['itens']);
            break;
        case 'update_mesa':
            AuthMiddleware::validarToken();
            $mesasController->updateMesaStatus($data['id_mesa'], $data['status']);
            break;
        case 'categorias':
            AuthMiddleware::validarToken();
            $categoriasController->getCategorias();
            break;
        case 'produtos':
            AuthMiddleware::validarToken();

            // Verifica se o campo 'categoria' foi enviado
            if (isset($data['categoria']) && is_numeric($data['categoria'])) {
                $categoriaId = intval($data['categoria']); // Pegando o ID da categoria
                $produtosController->getProdutosByCategoria($categoriaId);
            } else {
                // Caso o ID da categoria seja inválido, retorna um erro JSON
                echo json_encode(["error" => "ID da categoria inválido"]);
            }
            break;

        case 'create_produto':
            AuthMiddleware::validarToken(); // Protege a rota, se necessário

            if (
                    isset($data['nome']) &&
                    isset($data['descricao']) &&
                    isset($data['preco']) &&
                    isset($data['id_categoria']) &&
                    isset($data['qtde'])
            ) {
                $produtosController->createProduto(
                        $data['nome'],
                        $data['descricao'],
                        $data['preco'],
                        $data['id_categoria'],
                        $data['qtde']
                );
            } else {
                echo json_encode(["success" => false, "error" => "Dados incompletos para criar produto."]);
            }
            break;

        // Novo case para atualizar o status do pedido
        case 'update_status_pedido':
            if (isset($data['id_pedido']) && isset($data['status'])) {
                $statusValido = in_array($data['status'], ['Concluído', 'Em andamento', 'Cancelado']);

                if ($statusValido) {
                    $resultado = $pedidosController->updateStatusPedido($data['id_pedido'], $data['status']);

                    if ($resultado) {
                        echo json_encode([
                            "success" => true,
                            "message" => "Status do pedido atualizado com sucesso."
                        ]);
                    } else {
                        echo json_encode([
                            "success" => false,
                            "message" => "Erro ao atualizar o pedido."
                        ]);
                    }
                } else {
                    echo json_encode(["error" => "Status inválido."]);
                }
            } else {
                echo json_encode(["error" => "ID do pedido e status são obrigatórios."]);
            }
            exit; // <-- Garante que nada mais seja enviado
            break;

        case 'list_produtos':
            AuthMiddleware::validarToken(); // protege a rota com token JWT, se necessário
            $produtosController->getAllProdutos(); // você precisa implementar esse método no controller
            break;
        case 'list_usuarios':
            AuthMiddleware::validarToken(); // protege a rota com token JWT, se necessário
            $usuariosController->getAllUsuarios(); // você precisa implementar esse método no controller
            break;
        case 'delete_produto':
            AuthMiddleware::validarToken(); // protege a rota com token JWT, se necessário
            $produtosController->delete();
            break;
        case 'marcarPedidoComoFeito':
//            AuthMiddleware::validarToken(); // protege a rota com token JWT, se necessário
//            $produtosController->delete();
            echo json_encode("status", "success");
            break;

        default:
            echo json_encode(["error" => "Rota inválida."]);
            break;
    }
    exit;
}

echo json_encode(["error" => "Método HTTP não permitido."]);
exit;
?>
