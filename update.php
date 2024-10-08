<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON data from the POST request
    $data = file_get_contents('php://input');
    $player = json_decode($data, true);

    // Retrieve the player's ID and position
    $playerId = $player['playerId'];
    $x = $player['x'];
    $y = $player['y'];

    // Read the players.txt file
    $playersData = file_exists('players.txt') ? file('players.txt', FILE_IGNORE_NEW_LINES) : [];

    // Update the player's position in the file or add it if not present
    $updatedData = [];
    $playerFound = false;
    foreach ($playersData as $line) {
        if (strpos($line, $playerId) === 0) {
            $updatedData[] = "$playerId,$x,$y";
            $playerFound = true;
        } else {
            $updatedData[] = $line;
        }
    }
    if (!$playerFound) {
        $updatedData[] = "$playerId,$x,$y";
    }

    // Write the updated data back to the file
    file_put_contents('players.txt', implode("\n", $updatedData));
}
?>
