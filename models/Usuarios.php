<?php

include_once '/var/www/html/food_order_backend/config/database.php';
include 'vendor/autoload.php';
include_once '/var/www/html/food_order_backend/config/config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Usuarios {

    private $db;

    public function __construct() {
        $this->db = conecta(); // Aqui a conexão é inicializada corretamente
        if (!$this->db) {
            die("Erro ao conectar ao banco de dados.");
        }
    }

    // Método para logar o usuário e gerar o token JWT
    public function login($email, $senha) {
        try {
            $stmt = $this->db->prepare("SELECT id_usuario, nome, senha, telefone, tipo  FROM usuario WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                echo json_encode(["error" => "Usuário não encontrado."]);
                return;
            }

            // Verifica se a senha informada bate com a armazenada (SHA-256)
            $senha_sha256 = hash("sha256", $senha);
            if ($senha_sha256 !== $usuario['senha']) {
                echo json_encode(["error" => "E-mail ou senha inválidos."]);
                return;
            }

            // Gerar token JWT
            $payload = [
                "iss" => "food_order_backend", // Emissor do token
                "iat" => time(), // Timestamp de criação
                "exp" => time() + (60 * 60), // Expira em 1 hora
                "sub" => $usuario['id_usuario'], // ID do usuário
                "nome" => $usuario['nome']
            ];

            $token = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');

            // Resposta com token JWT
            echo json_encode([
                "success" => true,
                "Tipo" => $usuario['tipo'],
                "Telefone" => $usuario['telefone'],
                "token" => $token
            ]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Erro ao realizar login: " . $e->getMessage()]);
        }
    }

    // Método para obter todos os usuários (READ)
    public function getAllUsuarios() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuario");
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($usuarios);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Erro ao obter usuários: " . $e->getMessage()]);
        }
    }

    // Método para cadastrar um novo usuário (CREATE)
    public function createUsuario($nome, $email, $senha, $telefone, $tipo) {
        try {
            // Verifica se o e-mail já está cadastrado
            $stmt = $this->db->prepare("SELECT id_usuario FROM usuario WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                echo json_encode(["error" => "E-mail já cadastrado."]);
                return;
            }

            // Cadastra o novo usuário
            $senha_sha256 = hash("sha256", $senha); // Senha convertida para SHA-256
            $stmt = $this->db->prepare("INSERT INTO usuario (nome, email, senha, telefone, tipo) VALUES (:nome, :email, :senha, :telefone, :tipo)");
            $stmt->execute([
                'nome' => $nome,
                'email' => $email,
                'senha' => $senha_sha256,
                'telefone' => $telefone, // Corrigido
                'tipo' => $tipo           // Corrigido
            ]);
            echo json_encode(["success" => "Usuário cadastrado com sucesso."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Erro ao cadastrar usuário: " . $e->getMessage()]);
        }
    }

    // Método para atualizar os dados de um usuário (UPDATE)
    public function updateUsuario($id_usuario, $nome, $email, $senha) {
        try {
            // Atualiza os dados do usuário
            $senha_sha256 = hash("sha256", $senha); // Senha convertida para SHA-256
            $stmt = $this->db->prepare("UPDATE usuario SET nome = :nome, email = :email, senha = :senha WHERE id_usuario = :id_usuario");
            $stmt->execute(['nome' => $nome, 'email' => $email, 'senha' => $senha_sha256, 'id_usuario' => $id_usuario]);

            echo json_encode(["success" => "Usuário atualizado com sucesso."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Erro ao atualizar usuário: " . $e->getMessage()]);
        }
    }

    // Método para deletar um usuário (DELETE)
    public function deleteUsuario($id_usuario) {
        try {
            // Deleta o usuário
            $stmt = $this->db->prepare("DELETE FROM usuario WHERE id_usuario = :id_usuario");
            $stmt->execute(['id_usuario' => $id_usuario]);

            echo json_encode(["success" => "Usuário deletado com sucesso."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Erro ao deletar usuário: " . $e->getMessage()]);
        }
    }

    // Função para obter dados do usuário autenticado
    function obterDadosUsuario() {
        
        // Verifica se o cabeçalho Authorization foi enviado
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            echo json_encode(['success' => false, 'error' => 'Token não fornecido']);
            exit;
        }

        // Extrai o token JWT
        $token = str_replace('Bearer ', '', $headers['Authorization']);

        // Verifica se o token é válido
        $payload = $this->verificarJWT($token);
        if (!$payload) {
            echo json_encode(['success' => false, 'error' => 'Token inválido ou expirado']);
            exit;
        }

        // Obtém o ID do usuário do token
        $usuario_id = $payload->sub;

        try {
            // Consulta o banco de dados para obter os dados do usuário
            $stmt = $this->db->prepare("SELECT id_usuario, nome, email, telefone, tipo FROM usuario WHERE id_usuario = :id_usuario");
            $stmt->bindParam(':id_usuario', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                echo json_encode(['success' => true, 'usuario' => $usuario]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Usuário não encontrado']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Erro ao buscar usuário: ' . $e->getMessage()]);
        }
    }
    
    function verificarJWT($token) {
    try {
        return JWT::decode($token, new Key(JWT_SECRET_KEY, 'HS256'));
    } catch (Exception $e) {
        return false;
    }
}

}
?>
