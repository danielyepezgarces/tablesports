<?php
$url = "https://www.winsports.co/api/rankings/player?tournamentId=5l22b8pqde1bdxk6377auk3ro&stat=Goles&competitionId=2ty8ihceabty8yddmu31iuuej";

/* =============================
   0. MAPA DE JUGADORES POR EQUIPO
   ============================= */
$LinksPorEquipo = [

    // ATLÉTICO NACIONAL
    "Atlético Nacional" => [

        // PORTEROS
        "David Ospina"      => "{{bandera|COL}} [[David Ospina]]",
        "Harlen Castillo"   => "{{bandera|COL}} [[Harlen Castillo]]",
        "Luis Marquinez"    => "{{bandera|COL}} [[Luis Marquinez]]",
        "Mateo Valencia"    => "{{bandera|COL}} [[Mateo Valencia]]",
        "Kevin Cataño"      => "{{bandera|COL}} [[Kevin Cataño Jiménez|Kevin Cataño]]",

        // DEFENSAS
        "César Haydar"      => "{{bandera|COL}} [[César Haydar]]",
        "Andrés Román"      => "{{bandera|COL}} [[Andrés Román]]",
        "William Tesillo"   => "{{bandera|COL}} [[William Tesillo]]",
        "Andrés Salazar"    => "{{bandera|COL}} [[Andrés Salazar Osorio|Andrés Salazar]]",
        "Juan José Arias"   => "{{bandera|COL}} [[Juan José Arias]]",
        "Simón García"      => "{{bandera|COL}} [[Simón García Valero|Simón García]]",
        "Royer Caicedo"     => "{{bandera|COL}} [[Royer Caicedo]]",
        "Cristian Uribe"    => "{{bandera|COL}} [[Cristian Uribe Uribe|Cristian Uribe]]",
        "Samuel Velásquez"  => "{{bandera|COL}} [[Samuel Velásquez]]",
        "Milton Casco"      => "{{bandera|ARG}} [[Milton Casco]]",

        // CENTROCAMPISTAS
        "Mateus Uribe"      => "{{bandera|COL}} [[Mateus Uribe]]",
        "Edwin Cardona"     => "{{bandera|COL}} [[Edwin Cardona]]",
        "Jorman Campuzano"  => "{{bandera|COL}} [[Jorman Campuzano]]",
        "Elkin Rivero"      => "{{bandera|COL}} [[Elkin Rivero]]",
        "Juan Bauza"        => "{{bandera|ARG}} [[Juan Bauza]]",
        "Luis Landázuri"    => "{{bandera|COL}} [[Luis Miguel Landázuri|Luis Landázuri]]",
        "Juan Rengifo"      => "{{bandera|COL}} [[Juan Manuel Rengifo|Juan Rengifo]]",
        "Juan Zapata"       => "{{bandera|COL}} [[Juan Zapata Zumaque|Juan Zapata]]",

        // DELANTEROS
        "Marlos Moreno"     => "{{bandera|COL}} [[Marlos Moreno]]",
        "Alfredo Morelos"   => "{{bandera|COL}} [[Alfredo Morelos]]",
        "Marino Hinestroza" => "{{bandera|COL}} [[Marino Hinestroza]]",
        "Dairon Asprilla"   => "{{bandera|COL}} [[Dairon Asprilla]]",
        "Andrés Sarmiento"  => "{{bandera|COL}} [[Andrés de Jesús Sarmiento|Andrés Sarmiento]]",
        "Juan Rosa"         => "{{bandera|COL}} [[Juan José Rosa|Juan Rosa]]",
        "Nico Rodríguez"    => "{{bandera|COL}} [[Nico Rodríguez]]",
        "Eduard Bello"      => "{{bandera|VEN}} [[Eduard Bello]]",
        "Emilio Aristizábal"=> "{{bandera|COL}} [[Emilio Aristizábal]]",
        "Jayder Asprilla"   => "{{bandera|COL}} [[Jayder Asprilla]]",
    ],
];

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
echo '<th>Jugador</th><th>Equipo</th><th>Goles</th><th>PJ</th><th>Media</th>';
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
    echo "<td>$media</td>";
    echo '</tr>';
}

echo '</table>';
echo '<p style="text-align:center; font-size:80%">Fuente: Win Sports / Dimayor</p>';
?>
