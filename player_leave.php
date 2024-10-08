<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = file_get_contents('php://input');
    $player = json_decode($data, true);

    $playerId = $player['playerId'];

    $playersData = file_exists('players.txt') ? file('players.txt', FILE_IGNORE_NEW_LINES) : [];
    $updatedData = [];

    foreach ($playersData as $line) {
        if (strpos($line, $playerId) === 0) {
            $lineParts = explode(',', $line);
            $updatedData[] = "$playerId,{$lineParts[1]},{$lineParts[2]},{$lineParts[3]},left"; // Set status to 'left'
        } else {
            $updatedData[] = $line;
        }
    }

    file_put_contents('players.txt', implode("\n", $updatedData));

    // Update player's online status in their individual file
    $playerFile = "user_$playerId.txt";
    if (file_exists($playerFile)) {
        $playerData = json_decode(file_get_contents($playerFile), true);
        $playerData['online_status'] = false;
        file_put_contents($playerFile, json_encode($playerData));
    }
}
?>
