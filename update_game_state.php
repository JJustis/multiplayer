<?php
// update_game_state.php

// Read current game state
$gameState = json_decode(file_get_contents('game_state.json'), true);

// Get player update data from request
$data = json_decode(file_get_contents('php://input'), true);
$playerId = $data['playerId'];
$x = $data['x'];
$y = $data['y'];
$health = $data['health'];
$mana = $data['mana'];
$color = $data['color'];

// Update the player's state
$gameState['players'][$playerId] = [
    'x' => $x,
    'y' => $y,
    'health' => $health,
    'mana' => $mana,
    'inventory' => $data['inventory'] ?? $gameState['players'][$playerId]['inventory'],
    'color' => $color
];

// Save the updated state back to file
file_put_contents('game_state.json', json_encode($gameState));

echo json_encode(['status' => 'success']);
?>
