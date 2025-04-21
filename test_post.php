<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "sucesso",
        "mensagem" => "Requisição POST recebida!"
    ]);
} else {
    echo "Método não permitido. Use POST.";
}
?>
