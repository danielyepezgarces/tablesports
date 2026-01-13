<?php
/**
 * Liga BetPlay Masculina - Primera A
 * Obtiene tabla de posiciones mediante POST y elimina logos de equipos
 */

$ajaxUrl = 'https://dimayor.com.co/wp-admin/admin-ajax.php';
$pageUrl = 'https://dimayor.com.co/';

// 1️⃣ Obtener la página para capturar nonce dinámico
$pageHtml = @file_get_contents($pageUrl, false, stream_context_create([
    'http' => [
        'method' => "GET",
        'header' => implode("\r\n", [
            "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/143.0.0.0 Safari/537.36",
            "Accept: text/html",
            "Origin: https://dimayor.com.co",
            "Referer: https://dimayor.com.co/liga-betplay"
        ]),
        'timeout' => 15
    ]
]));
if (!$pageHtml) die('Error: No se pudo cargar la página de Dimayor.');

// 2️⃣ Extraer nonce dinámicamente desde el script
preg_match('/const nonce\s*=\s*"([a-z0-9]+)"/', $pageHtml, $matches);
$nonce = $matches[1] ?? '';
if (!$nonce) die('Error: No se pudo obtener el nonce dinámicamente.');

// 3️⃣ Preparar POST multipart/form-data
$postData = [
    'action' => 'get_dynamic_content_v5',
    'nonce' => $nonce,
    'type' => 'posiciones',
    'competition_id' => '169679',
    'competition_type' => 'Liga BetPlay',
    'phase_id' => '169782',
    'matchday' => '0'
];

// 4️⃣ Ejecutar POST con cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $ajaxUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Origin: https://dimayor.com.co",
    "Referer: https://dimayor.com.co/",
    "User-Agent: Mozilla/5.0",
    "Accept: */*",
]);
$response = curl_exec($ch);
if (curl_errno($ch)) die('Error cURL: ' . curl_error($ch));
curl_close($ch);

// 5️⃣ Decodificar JSON
$json = json_decode($response, true);
if (!($json['success'] ?? false)) die('Error: El AJAX no devolvió éxito.');

// 6️⃣ Obtener HTML de la tabla
$html = $json['data']['html'] ?? '';
if (!$html) die('Error: No se pudo extraer la tabla de posiciones.');

// 7️⃣ Procesar HTML para eliminar imágenes dentro de team-name-cell y limpiar nombres
$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
libxml_clear_errors();

$xpath = new DOMXPath($dom);
$nodes = $xpath->query('//td[contains(@class,"team-name-cell")]');

foreach ($nodes as $td) {
    // 7a️⃣ Eliminar imágenes
    $imgs = $td->getElementsByTagName('img');
    for ($i = $imgs->length - 1; $i >= 0; $i--) {
        $img = $imgs->item($i);
        $img->parentNode->removeChild($img);
    }

    // 7b️⃣ Limpiar el texto del equipo
    $text = trim($td->textContent);              // Quitar espacios al inicio y final
    $text = str_replace('F.C.', 'F.C', $text); // Reemplazar F.C.
    $td->nodeValue = $text;                      // Actualizar el contenido del <td>
}

// 8️⃣ Guardar HTML modificado (solo la primera tabla)
$tableHtml = '';
$tables = $dom->getElementsByTagName('table');
if ($tables->length > 0) {
    $tableHtml = $dom->saveHTML($tables->item(0));
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Liga BetPlay Masculina - Primera A</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { font-family: system-ui; background:#0f172a; color:#e5e7eb; padding:20px; }
h1 { text-align:center; margin-bottom:20px; }
.table-wrapper { overflow-x:auto; }
table { width:100%; border-collapse:collapse; background:#020617; }
th, td { padding:10px; text-align:center; border-bottom:1px solid #1e293b; }
th { background:#020617; color:#38bdf8; text-transform:uppercase; font-size:12px; }
tr:hover { background:#020617; }
</style>
</head>
<body>

<h1>🏆 Liga BetPlay Masculina – Primera A</h1>

<div class="table-wrapper">
    <?php echo $tableHtml; ?>
</div>

</body>
</html>
