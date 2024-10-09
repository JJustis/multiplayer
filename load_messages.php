<?php
// load_messages.php
header('Content-Type: application/json');

// Read the chat.json file and return the messages
$chatFile = 'chat.json';

if (file_exists($chatFile)) {
    $messages = json_decode(file_get_contents($chatFile), true);
    echo json_encode(['status' => 'success', 'messages' => $messages]);
} else {
    echo json_encode(['status' => 'success', 'messages' => []]);
}
?>
