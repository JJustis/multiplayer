<?php
$file = 'players.txt';
$afkThreshold = 10 * 1000; // 10 seconds in milliseconds
$currentTime = round(microtime(true) * 1000); // Current time in milliseconds

$afkPlayers = [];
$lines = file($file, FILE_IGNORE_NEW_LINES);

foreach ($lines as $key => $line) {
    if (!empty($line)) {
        list($playerId, $x, $y, $color, $lastActivity) = explode(',', $line);

        // Calculate the time difference between now and the player's last activity
        $timeDifference = $currentTime - (int)$lastActivity;

        // If the player has been inactive for more than the threshold, remove them
        if ($timeDifference > $afkThreshold) {
            $afkPlayers[] = $playerId; // Add player to the AFK list
            unset($lines[$key]); // Remove the player from the players.txt array
        }
    }
}

// Write the updated list back to players.txt
file_put_contents($file, implode(PHP_EOL, $lines));

// Return a response indicating which players are AFK
echo json_encode(['status' => 'success', 'afkPlayers' => $afkPlayers]);
?>
