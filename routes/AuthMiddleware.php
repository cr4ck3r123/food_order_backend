<?php
include 'vendor/autoload.php';
include_once '/var/www/html/food_order_backend/config/config.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {
    public static function validarToken() {
        $headers = apache_request_headers();
        if (!isset($headers['Authorization'])) {
            echo json_encode(["error" => "Token não fornecido"]);
            exit;
        }

        $token = str_replace("Bearer ", "", $headers['Authorization']);

        try {
            $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            echo json_encode(["error" => "Token inválido: " . $e->getMessage()]);
              header("Location: login.php");
            exit;
        }
    }
    
     public static function logout() {
        // Se você estiver utilizando sessões para armazenar o token, pode destruí-las:
        if (isset($_SESSION['token'])) {
            unset($_SESSION['token']); // Remover o token da sessão
        }
        
        // Caso use algum tipo de blacklist ou banco de dados, você também pode invalidar o token aqui.

        // Se você usar JWT sem sessão, pode registrar o token como inválido ou armazená-lo em um banco de dados
        // para impedir seu uso novamente.

        // Por fim, você pode fazer outras ações, caso necessário, para garantir que o token não seja mais válido
    }
}
?>

