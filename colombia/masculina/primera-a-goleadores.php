<?php
/**
 * Liga BetPlay Masculina – Goleadores
 * Obtiene ranking de goleadores vía API WinSports
 */

$apiUrl = 'https://www.winsports.co/api/rankings/player?tournamentId=5l22b8pqde1bdxk6377auk3ro&stat=Goles&competitionId=2ty8ihceabty8yddmu31iuuej';

// 1️⃣ Obtener datos del API
$response = @file_get_contents($apiUrl, false, stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => implode("\r\n", [
            "User-Agent: Mozilla/5.0 (X11; Linux x86_64)",
            "Accept: application/json"
        ]),
        'timeout' => 15
    ]
]));

if (!$response) {
    die('Error: No se pudo obtener el ranking.');
}

// 2️⃣ Decodificar JSON
$json = json_decode($response, true);
if (!isset($json['data'])) {
    die('Error: Respuesta inválida del API.');
}

// 3️⃣ Procesar datos (Top 10)
$rows = [];
$pos = 0;

foreach ($json['data'] as $item) {
    if ($pos >= 10) {
        break;
    }

    $goles = (int) $item['value'];
    $pj = (int) $item['matchesPlayed'];
    $media = $pj > 0 ? number_format($goles / $pj, 2) : '0.00';

    $rows[] = [
        'jugador' => $item['player']['name'],
        'equipo'  => $item['team']['name'],
        'goles'   => $goles,
        'pj'      => $pj,
        'media'   => $media,
        'pos'     => $pos
    ];

    $pos++;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Goleadores – Liga BetPlay</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
    font-family: system-ui;
    background: #0f172a;
    color: #e5e7eb;
    padding: 20px;
}
h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #22d3ee;
}
.table-wrapper {
    overflow-x: auto;
}
table {
    width: 100%;
    max-width: 800px;
    margin: auto;
    border-collapse: collapse;
    background: #020617;
}
th, td {
    padding: 10px;
    border-bottom: 1px solid #1e293b;
}
th {
    text-align: center;
    color: #38bdf8;
    font-size: 12px;
    text-transform: uppercase;
}
td:nth-child(3),
td:nth-child(4),
td:nth-child(5) {
    text-align: center;
}
tr.top1 { background: #1e1b4b; }
tr.top2 { background: #1e293b; }
tr.top3 { background: #020617; }
tr:hover {
    background: #020617;
}
.footer {
    text-align: center;
    font-size: 12px;
    margin-top: 15px;
    color: #94a3b8;
}
</style>
</head>
<body>

<h1>⚽ Goleadores – Liga BetPlay</h1>

<div class="table-wrapper">
<table>
  <thead>
    <tr>
      <th>Jugador</th>
      <th>Equipo</th>
      <th>Goles</th>
      <th>PJ</th>
      <th>Media</th>
    </tr>
  </thead>
  <tbody>
<?php for
