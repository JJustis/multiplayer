<?php
$host = 'localhost';
$port = 8080;

// Create a TCP/IP socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, 0, $port);
socket_listen($socket);

$clients = [$socket];
$players = []; // Array to keep track of players and their data

// Function to handle the WebSocket handshake
function handshake($client, $headers) {
    if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $matches)) {
        $key = $matches[1];
        $acceptKey = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $upgrade = "HTTP/1.1 101 Switching Protocols\r\n" .
                   "Upgrade: websocket\r\n" .
                   "Connection: Upgrade\r\n" .
                   "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";
        socket_write($client, $upgrade);
    }
}

// Function to encode a message according to the WebSocket protocol
function encodeMessage($message) {
    $length = strlen($message);
    if ($length <= 125) {
        return chr(129) . chr($length) . $message;
    } elseif ($length >= 126 && $length <= 65535) {
        return chr(129) . chr(126) . pack('n', $length) . $message;
    } else {
        return chr(129) . chr(127) . pack('Q', $length) . $message;
    }
}

// Function to decode incoming WebSocket messages
function decodeMessage($data) {
    $length = ord($data[1]) & 127;
    if ($length == 126) {
        $masks = substr($data, 4, 4);
        $data = substr($data, 8);
    } elseif ($length == 127) {
        $masks = substr($data, 10, 4);
        $data = substr($data, 14);
    } else {
        $masks = substr($data, 2, 4);
        $data = substr($data, 6);
    }

    $decoded = '';
    for ($i = 0; $i < strlen($data); $i++) {
        $decoded .= $data[$i] ^ $masks[$i % 4];
    }
    return $decoded;
}

// Function to broadcast a message to all connected clients
function broadcast($message, $exclude = null) {
    global $clients, $socket;
    foreach ($clients as $client) {
        if ($client != $socket && $client != $exclude) {
            socket_write($client, encodeMessage($message));
        }
    }
}

// Function to handle battles between players
function initiateBattle($challengerId, $opponentId) {
    return [
        'type' => 'battle',
        'challengerId' => $challengerId,
        'opponentId' => $opponentId,
        'message' => "$challengerId has challenged $opponentId to a battle!"
    ];
}

// Main loop to handle incoming connections and messages
while (true) {
    $read = $clients;
    socket_select($read, $write, $except, 0, 10);

    if (in_array($socket, $read)) {
        $newClient = socket_accept($socket);
        $clients[] = $newClient;
        $header = socket_read($newClient, 1024);
        handshake($newClient, $header);
        echo "New client connected!\n";
    }

    foreach ($clients as $key => $client) {
        if ($client == $socket) continue;

        $data = @socket_read($client, 1024, PHP_BINARY_READ);
        if (!$data) {
            // Client disconnected
            unset($clients[$key]);
            socket_close($client);
            echo "Client disconnected.\n";
            continue;
        }

        $decodedMessage = decodeMessage($data);
        $message = json_decode($decodedMessage, true);

        switch ($message['type']) {
            case 'register':
                $playerId = $message['playerId'];
                $players[$playerId] = [
                    'playerId' => $playerId,
                    'x' => rand(0, 800),
                    'y' => rand(0, 600),
                    'color' => $message['color'],
                    'avatar' => $message['avatar'] // Store avatar as base64 string
                ];
                broadcast(json_encode(['type' => 'playerJoined', 'playerData' => $players[$playerId]]), $client);
                break;

            case 'move':
                $playerId = $message['playerId'];
                $players[$playerId]['x'] = $message['x'];
                $players[$playerId]['y'] = $message['y'];
                broadcast(json_encode(['type' => 'move', 'playerData' => $players[$playerId]]), $client);
                break;

            case 'battleRequest':
                $challengerId = $message['challengerId'];
                $opponentId = $message['opponentId'];
                $battleMessage = initiateBattle($challengerId, $opponentId);
                broadcast(json_encode($battleMessage), $client);
                break;
        }
    }
}

// Close the master socket
socket_close($socket);
?>
