<?php
$url = "https://www.winsports.co/api/rankings/player?tournamentId=5l22b8pqde1bdxk6377auk3ro&stat=Tarjetas+Amarillas&competitionId=2ty8ihceabty8yddmu31iuuej";

/* =============================
   0. MAPA DE JUGADORES POR EQUIPO
   ============================= */
$LinksPorEquipo = include 'jugadores.php';

/* =============================
   FUNCIÓN DE REPLACE
   ============================= */
function wikiJugador(string $equipo, string $jugador, array $mapa): string {
    return $mapa[$equipo][$jugador] ?? htmlspecialchars($jugador, ENT_QUOTES, 'UTF-8');
}

/* =============================
   1. PETICIÓN cURL
   ============================= */
$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_ENCODING       => "",
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
    die("JSON inválido");
}

/* =============================
   3. TOP 10
   ============================= */
$top10 = array_slice($data, 0, 10);

/* =============================
   4. TABLA HTML
   ============================= */
echo '<table style="width:50%; margin:auto; border-collapse:collapse; font-size:90%">';
echo '<tr style="background:#98A1B2; text-align:center">';
echo '<th>Jugador</th><th>Equipo</th><th>Tarjetas recibidas</th><th>PJ</th>';
echo '</tr>';

foreach ($top10 as $player) {

    $nombreJugador = $player['name'];
    $nombreEquipo  = $player['contestantName'];

    $jugador = wikiJugador($nombreEquipo, $nombreJugador, $LinksPorEquipo);
    $equipo  = htmlspecialchars($nombreEquipo, ENT_QUOTES, 'UTF-8');

    $goles = (int)$player['value'];
    $pj    = (int)$player['appearances'];

    if ($pj > 0) {
        $raw = $goles / $pj;
        $media = ($raw == floor($raw)) ? (int)$raw : number_format($raw, 2, '.', '');
    } else {
        $media = 0;
    }

    echo '<tr style="background:#F5F5F5; text-align:center">';
    echo "<td>$jugador</td>";
    echo "<td>$equipo</td>";
    echo "<td><strong>$goles</strong></td>";
    echo "<td>$pj</td>";
    echo '</tr>';
}

echo '</table>';
echo '<p style="text-align:center; font-size:80%">Fuente: Win Sports / Dimayor</p>';
?>
