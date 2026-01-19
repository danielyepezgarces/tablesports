<?php
$url = "https://www.winsports.co/api/rankings/player?tournamentId=5l22b8pqde1bdxk6377auk3ro&stat=Goles&competitionId=2ty8ihceabty8yddmu31iuuej";

/* =============================
   0. MAPA DE JUGADORES POR EQUIPO
   ============================= */
$LinksPorEquipo = [
   
    // AMÉRICA DE CALI
    "América de Cali" => [

        // PORTEROS
        "Jean Fernandes"        => "{{bandera|BRA}} [[Jean Paulo Fernandes Filho|Jean Fernandes]]",
        "Jorge Soto"            => "{{bandera|COL}} [[Jorge Iván Soto|Jorge Soto]]",
        "Alexis Sinisterra"     => "{{bandera|COL}} [[Alexis Sinisterra]]",

        // DEFENSAS
        "Marlon Torres"         => "{{bandera|COL}} [[Marlon Torres]]",
        "Dany Rosero"           => "{{bandera|COL}} [[Dany Rosero]]",
        "Andrés Mosquera"       => "{{bandera|COL}} [[Andrés Mosquera Guardia|Andrés Mosquera]]",
        "Mateo Castillo"        => "{{bandera|COL}} [[Mateo Castillo]]",
        "Marcos Mina"           => "{{bandera|COL}} [[Marcos Mina]]",
        "Brayan Correa"         => "{{bandera|COL}} [[Brayan Correa (futbolista)|Brayan Correa]]",
        "Nicolás Hernández"     => "{{bandera|COL}} [[Nicolás Hernández Rodríguez|Nicolás Hernández]]",
        "Ómar Bertel"           => "{{bandera|COL}} [[Ómar Bertel]]",

        // CENTROCAMPISTAS
        "Josen Escobar"         => "{{bandera|COL}} [[Josen Escobar]]",
        "Dylan Borrero"         => "{{bandera|COL}} [[Dylan Borrero]]",
        "Yeison Guzmán"         => "{{bandera|COL}} [[Yeison Guzmán]]",
        "Darwin Machís"         => "{{bandera|VEN}} [[Darwin Machís]]",
        "Rafael Carrascal"      => "{{bandera|COL}} [[Rafael Carrascal]]",
        "Jan Lucumí"            => "{{bandera|COL}} [[Jan Lucumí]]",
        "Carlos Sierra"         => "{{bandera|COL}} [[Carlos José Sierra|Carlos Sierra]]",
        "Joel Romero"           => "{{bandera|COL}} [[Joel Romero]]",
        "José Cavadía"          => "{{bandera|COL}} [[José Cavadía]]",
        "Jhon Murillo"          => "{{bandera|VEN}} [[Jhon Murillo]]",

        // DELANTEROS
        "Yojan Garcés"          => "{{bandera|COL}} [[Yojan Garcés]]",
        "Jhon Tilman Palacios"  => "{{bandera|COL}} [[Jhon Tilman Palacios]]",
        "Adrián Ramos"          => "{{bandera|COL}} [[Adrián Ramos]]",
        "Kevin Angulo"          => "{{bandera|COL}} [[Kevin Angulo]]",
        "Rodrigo Holgado"       => "{{bandera|MYS}} [[Rodrigo Holgado]]",

    ],
    // ATLÉTICO BUCARAMANGA
    "Atlético Bucaramanga" => [

        // PORTEROS
        "Aldair Quintana"     => "{{bandera|COL}} [[Aldair Quintana]]",
        "Luis Vásquez"       => "{{bandera|COL}} [[Luis Erney Vásquez|Luis Vásquez]]",

        // DEFENSAS
        "Jefferson Mena"     => "{{bandera|COL}} [[Jefferson Mena]]",
        "Martín Rea"         => "{{bandera|URU}} [[Martín Rea]]",
        "José García"        => "{{bandera|COL}} [[José García Aragón|José García]]",
        "Fredy Hinestroza"   => "{{bandera|COL}} [[Fredy Hinestroza]]",
        "Israel Alba"        => "{{bandera|COL}} [[Israel Alba]]",
        "Aldair Gutiérrez"   => "{{bandera|COL}} [[Aldair Gutiérrez]]",
        "Carlos Romaña"      => "{{bandera|COL}} [[Carlos Romaña]]",
        "Carlos de las Salas"=> "{{bandera|COL}} [[Carlos de las Salas]]",

        // MEDIOCAMPISTAS
        "Gustavo Charrupí"   => "{{bandera|COL}} [[Gustavo Charrupí]]",
        "Kevin Londoño"      => "{{bandera|COL}} [[Kevin Londoño]]",
        "Fabián Sambueza"    => "{{bandera|ARG}} [[Fabián Sambueza]]",
        "Juan Camilo Mosquera"=> "{{bandera|COL}} [[Juan Camilo Mosquera]]",
        "Aldair Zárate"      => "{{bandera|COL}} [[Aldair Zárate]]",
        "Félix Charrupí"     => "{{bandera|COL}} [[Félix Charrupí]]",
        "Fabry Castro"       => "{{bandera|COL}} [[Fabry Castro]]",
        "Leonardo Flores"    => "{{bandera|VEN}} [[Leonardo Flores (futbolista venezolano)|Leonardo Flores]]",

        // DELANTEROS
        "Faber Gil"          => "{{bandera|COL}} [[Faber Gil]]",
        "Jhon Fredy Salazar" => "{{bandera|COL}} [[Jhon Fredy Salazar]]",
        "Luciano Pons"       => "{{bandera|ARG}} [[Luciano Pons]]",
        "Brandon Caicedo"    => "{{bandera|COL}} [[Brandon Caicedo]]",
        "Gleyfer Medina"     => "{{bandera|COL}} [[Gleyfer Medina]]",

    ],
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
       // LLANEROS FÚTBOL CLUB
    "Llaneros FC" => [

        // PORTEROS
        "Miguel Ortega"        => "{{bandera|MEX}} [[Miguel Ortega]]",
        "Juan Loaiza"          => "{{bandera|COL}} [[Juan Camilo Loaiza|Juan Loaiza]]",
        "Roameth Romaña"       => "{{bandera|COL}} [[Roameth Romaña]]",

        // DEFENSAS
        "Howell Mena"          => "{{bandera|COL}} [[Howell Mena]]",
        "Jhojan Escobar"       => "{{bandera|COL}} [[Jhojan Escobar]]",
        "Francisco Meza"       => "{{bandera|COL}} [[Francisco Meza]]",
        "Dennis Quintero"      => "{{bandera|ECU}} [[Dennis Quintero]]",
        "Leider Riascos"       => "{{bandera|COL}} [[Leider Riascos]]",
        "Alejandro Moralez"    => "{{bandera|COL}} [[Alejandro Moralez]]",
        "Juan Pertuz"          => "{{bandera|COL}} [[Juan David Pertuz|Juan Pertuz]]",
        "Jimmy Medranda"       => "{{bandera|COL}} [[Jimmy Medranda]]",

        // MEDIOCAMPISTAS
        "Eyder Restrepo"       => "{{bandera|COL}} [[Eyder Restrepo]]",
        "Marlon Sierra"        => "{{bandera|COL}} [[Marlon Ricardo Sierra|Marlon Sierra]]",
        "Luis Miranda"         => "{{bandera|COL}} [[Luis Fernando Miranda|Luis Miranda]]",
        "Juan José Ramírez"    => "{{bandera|COL}} [[Juan José Ramírez]]",
        "Néider Ospina"        => "{{bandera|COL}} [[Néider Ospina]]",
        "Edwin Laszo"          => "{{bandera|COL}} [[Edwin Laszo]]",
        "Brian Benítez"        => "{{bandera|ARG}} [[Brian Benítez]]",
        "Daniel Mantilla"      => "{{bandera|COL}} [[Daniel Mantilla]]",
        "Kelvin Osorio"        => "{{bandera|COL}} [[Kelvin Osorio]]",
        "Kevin Caicedo"        => "{{bandera|COL}} [[Kevin Caicedo]]",
        "Jhon Vásquez"         => "{{bandera|COL}} [[Jhon Vásquez]]",
        "Andrés Domingo López" => "{{bandera|COL}} [[Andrés Domingo López]]",

        // DELANTEROS
        "Manuel Barreiro"      => "{{bandera|COL}} [[Carlos Manuel Cortés Barreiro|Manuel Barreiro]]",
        "Jhonier Blanco"       => "{{bandera|COL}} [[Jhonier Blanco]]",
        "Érik Bodencer"        => "{{bandera|ARG}} [[Érik Bodencer]]",
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
