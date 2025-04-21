<?php

include_once '/var/www/html/food_order_backend/models/Usuarios.php';

class UsuariosController {

    private $modelUsuario;

    public function __construct() {
        $this->modelUsuario = new Usuarios(); // Inicializando o modelo de usuários
    }

    // Método para realizar o login do usuário
    public function login($email, $senha) {
        // Chama o método de login do modelo
        $this->modelUsuario->login($email, $senha);
    }

    // Método para criar um novo usuário
    public function createUsuario($nome, $email, $senha, $telefone, $tipo) {
        // Chama o método de criação de usuário do modelo
        $this->modelUsuario->createUsuario($nome, $email, $senha, $telefone, $tipo);
    }

    // Método para obter todos os usuários
    public function getAllUsuarios() {
        // Chama o método de obtenção de usuários do modelo
        $this->modelUsuario->getAllUsuarios();
    }

    // Método para atualizar os dados de um usuário
    public function updateUsuario($id_usuario, $nome, $email, $senha) {
        // Chama o método de atualização de usuário do modelo
        $this->modelUsuario->updateUsuario($id_usuario, $nome, $email, $senha);
    }

    // Método para deletar um usuário
    public function deleteUsuario($id_usuario) {
        // Chama o método de deleção de usuário do modelo
        $this->modelUsuario->deleteUsuario($id_usuario);
    }

    public function obterDadosUsuarios(){
        $this->modelUsuario->obterDadosUsuario();
    }
    
    
}

?>
