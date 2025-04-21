<?php
header("Access-Control-Allow-Origin: *"); // Permitir todas as origens (ajuste conforme necessário)
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

// Configurações do WebSocket
$host = "0.0.0.0";
$port = 8000;

// Criar o socket TCP
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($socket, $host, $port);
socket_listen($socket);

echo "Servidor WebSocket rodando em ws://$host:$port\n";

// Lista de clientes conectados
$clients = [$socket];

while (true) {
    $read = $clients;
    socket_select($read, $write, $except, 0, 10);

    // Verificar novas conexões
    if (in_array($socket, $read)) {
        $newClient = socket_accept($socket);
        $clients[] = $newClient;
        handshake($newClient);
        echo "✅ Novo cliente conectado!\n";
    }

    // Buscar dados da API
    $data = file_get_contents("http://localhost/food_order_backend/index.php?action=get_all_mesas");

    if ($data === false) {
        echo "❌ Erro ao buscar dados da API!\n";
        continue;
    }

    // Verifica se é um JSON válido
    $jsonData = json_decode($data);
    if ($jsonData === null) {
        echo "❌ API retornou um JSON inválido!\n";
        continue;
    }

    // Enviar mensagem a todos os clientes conectados
    foreach ($clients as $client) {
        if ($client != $socket) {
            sendMessage($client, json_encode($jsonData, JSON_UNESCAPED_UNICODE) . "\n"); // Adiciona um "\n" para indicar o fim
        }
    }

    sleep(2); // Atualizar a cada 2 segundos
}

// 🖐 Função para handshake WebSocket
function handshake($client)
{
    $request = socket_read($client, 5000);
    preg_match("#Sec-WebSocket-Key: (.*)\r\n#", $request, $matches);
    $key = trim($matches[1]);
    $acceptKey = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

    $headers = "HTTP/1.1 101 Switching Protocols\r\n";
    $headers .= "Upgrade: websocket\r\n";
    $headers .= "Connection: Upgrade\r\n";
    $headers .= "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";

    socket_write($client, $headers, strlen($headers));
}

// 📩 Função para enviar mensagem no formato WebSocket correto
function sendMessage($client, $message)
{
    $length = strlen($message);
    $header = "\x81";

    if ($length <= 125) {
        $header .= chr($length);
    } elseif ($length <= 65535) {
        $header .= chr(126) . pack("n", $length);
    } else {
        $header .= chr(127) . pack("J", $length);
    }

    $finalMessage = $header . $message;
    socket_write($client, $finalMessage, strlen($finalMessage));
}
