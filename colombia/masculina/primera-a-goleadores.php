<?php
/**
 * Liga BetPlay Masculina – Goleadores
 * Fuente: WinSports
 */

$apiUrl = 'https://www.winsports.co/api/rankings/player?tournamentId=5l22b8pqde1bdxk6377auk3ro&stat=Goles&competitionId=2ty8ihceabty8yddmu31iuuej';

// 1️⃣ Obtener datos vía cURL
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_HTTPHEADER => [
        "User-Agent: Mozilla/5.0 (X11; Linux x86_64)",
        "Accept: application/json",
        "Referer: https://www.winsports.co/"
    ],
]);

$response = curl_exec($ch);

if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);
    die("Error cURL: $error");
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die("Error HTTP al obtener datos: $httpCode");
}

// 2️⃣ Decodificar JSON
$data = json_decode($response, true);
if (!isset($data['data']) || !is_array($data['data'])) {
    die('Error: JSON inválido o vacío.');
}

// 3️⃣ Procesar Top 10
$rows = [];
$pos = 0;

foreach ($data['data'] as $item) {
    if ($pos >= 10) break;

    $goles = (int) ($item['value'] ?? 0);
    $pj    = (int) ($item['matchesPlayed'] ?? 0);
    $media = $pj > 0 ? number_format($goles / $pj, 2) : '0.00';

    $rows[] = [
        'jugador' => $item['player']['name'] ?? '—',
        'equipo'  => $item['team']['name'] ?? '—',
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
    color: #38bdf8;
    margin-bottom: 20px;
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
    color: #7dd3fc;
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
.footer {
    text-align: center;
    font-size: 12px;
    margin-top: 15px;
    color: #94a3b8;
}
</style>
</head>
<body>

<h1>🥅 Goleadores – Liga BetPlay</h1>

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
<?php foreach ($rows as $r): 
    $class = '';
    if ($r['pos'] === 0) $class = 'top1';
    elseif ($r['pos'] === 1) $class = 'top2';
    elseif ($r['pos'] === 2) $class = 'top3';
?>
<tr class="<?= $class ?>">
    <td><strong><?= htmlspecialchars($r['jugador']) ?></strong></td>
    <td><?= htmlspecialchars($r['equipo']) ?></td>
    <td><?= $r['goles'] ?></td>
    <td><?= $r['pj'] ?></td>
    <td><?= $r['media'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div class="footer">
    Fuente: WinSports / Dimayor
</div>

</body>
</html>
