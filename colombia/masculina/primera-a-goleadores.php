<?php
$url = "https://www.winsports.co/api/rankings/player?tournamentId=5l22b8pqde1bdxk6377auk3ro&stat=Goles&competitionId=2ty8ihceabty8yddmu31iuuej";

/* =============================
   1. PETICIÓN CON cURL (GZIP)
   ============================= */
$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_ENCODING       => "gzip, deflate",
    CURLOPT_USERAGENT      => "Mozilla/5.0",
    CURLOPT_TIMEOUT        => 15,
]);

$response = curl_exec($ch);

if ($response === false) {
    die("Error cURL: " . curl_error($ch));
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die("Error HTTP: " . $httpCode);
}

/* =============================
   2. DECODIFICAR JSON
   ============================= */
$data = json_decode($response, true);

if (!is_array($data)) {
    die("Error: JSON inválido o vacío");
}

/* =============================
   3. TOMAR SOLO TOP 10
   ============================= */
$top10 = array_slice($data, 0, 10);

/* =============================
   4. GENERAR TABLA HTML
   ============================= */
echo '<table style="width:50%; margin:auto; border-collapse:collapse; font-size:90%">';
echo '<tr style="background:#98A1B2; text-align:center">';
echo '<th>Jugador</th>';
echo '<th>Equipo</th>';
echo '<th>Goles</th>';
echo '<th>PJ</th>';
echo '<th>Media</th>';
echo '</tr>';

foreach ($top10 as $player) {
    $jugador = htmlspecialchars($player['name']);
    $equipo  = htmlspecialchars($player['contestantName']);
    $goles   = (int)$player['value'];
    $pj      = (int)$player['appearances'];
    $media   = $pj > 0 ? round($goles / $pj, 2) : 0;

    echo '<tr style="background:#F5F5F5; text-align:center">';
    echo "<td>$jugador</td>";
    echo "<td>$equipo</td>";
    echo "<td><strong>$goles</strong></td>";
    echo "<td>$pj</td>";
    echo "<td>$media</td>";
    echo '</tr>';
}

echo '</table>';
echo '<p style="text-align:center; font-size:80%">Fuente: Win Sports / Dimayor</p>';
?>
