<?php
// save_message.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the message data from the request body
    $data = file_get_contents('php://input');
    $message = json_decode($data, true);

    if (isset($message['playerId'], $message['content'], $message['timestamp'])) {
        // Read existing messages from chat.json
        $chatFile = 'chat.json';
        $messages = [];

        if (file_exists($chatFile)) {
            $messages = json_decode(file_get_contents($chatFile), true);
        }

        // Add the new message to the messages array
        $messages[] = $message;

        // Save the updated messages back to chat.json
        file_put_contents($chatFile, json_encode($messages, JSON_PRETTY_PRINT));

        echo json_encode(['status' => 'success', 'message' => 'Message saved successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid message data']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
