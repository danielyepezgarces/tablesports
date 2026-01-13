<?php
$countries = [
    'CO' => [
        'name' => 'Colombia',
        'flag' => '🇨🇴'
    ]
];
// Tablas por país usando código ISO
$tables = [
    'CO' => [
        [
            'name' => 'Liga BetPlay Masculina – Primera A',
            'link' => 'colombia/masculina/primera-a.php',
            'icon' => '⚽',
            'color' => '#3b82f6'
        ],
        [
            'name' => 'Liga BetPlay – Reclasificación',
            'link' => 'colombia/masculina/primera-a-reclasificacion.php',
            'icon' => '📊',
            'color' => '#22c55e'
        ],
        [
            'name' => 'Liga BetPlay Femenina – Primera',
            'link' => 'colombia/femenina/primera.php',
            'icon' => '🏆',
            'color' => '#ec4899'
        ],
        [
            'name' => 'Torneo Dimayor',
            'link' => 'colombia/dimayor/torneo.php',
            'icon' => '🥇',
            'color' => '#facc15'
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TableSports</title>
<style>
body {
    font-family: system-ui, sans-serif;
    background:#0f172a;
    color:#e5e7eb;
    margin:0;
    padding:20px;
}
h1 {
    text-align:center;
    margin-bottom:30px;
    font-size:2rem;
    color:#38bdf8;
}
.accordion {
    width:100%;
    max-width:800px;
    margin:0 auto;
}
.accordion-item {
    border-radius:8px;
    margin-bottom:15px;
    background:#020617;
    overflow:hidden;
}
.accordion-header {
    padding:15px 20px;
    cursor:pointer;
    font-weight:bold;
    display:flex;
    justify-content:space-between;
    align-items:center;
    transition: background 0.2s;
}
.accordion-header:hover {
    background:#1e293b;
}
.accordion-header::after {
    content: '+';
    font-size:1.2rem;
}
.accordion-header.active::after {
    content: '−';
}
.accordion-content {
    display:none;
    padding:15px 20px;
}
.card {
    display:flex;
    align-items:center;
    gap:15px;
    background:#0f172a;
    padding:10px 15px;
    margin-bottom:10px;
    border-radius:6px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.5);
}
.card .icon {
    font-size:1.8rem;
    padding:8px;
    border-radius:50%;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
}
.card-title {
    font-weight:bold;
}
a { text-decoration:none; color:inherit; }
.credits {
    margin-top:40px;
    text-align:center;
    font-size:0.85rem;
    color:#94a3b8;
}
.credits a { color:#38bdf8; text-decoration:none; }
.credits a:hover { text-decoration:underline; }
</style>
</head>
<body>

<h1>TableSports 🏟️</h1>

<div class="accordion">
    <?php foreach($tables as $iso => $list): ?>
        <div class="accordion-item">
            <div class="accordion-header">
               <?php echo $countries[$iso]['flag']; ?>
               <?php echo $countries[$iso]['name']; ?>
            </div>
            <div class="accordion-content">
                <?php foreach($list as $t): ?>
                    <a href="<?php echo $t['link']; ?>">
                        <div class="card">
                            <div class="icon" style="background: <?php echo $t['color']; ?>;"><?php echo $t['icon']; ?></div>
                            <div class="card-title"><?php echo $t['name']; ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="credits">
    Hecho por <a href="https://es.wikipedia.org/wiki/Usuario:Danielyepezgarces" target="_blank">Daniel Yepez Garces</a>.<br>
    <strong>Licencia:</strong> Código bajo <a href="https://www.gnu.org/licenses/gpl-3.0.html" target="_blank">GPL v3</a>, contenido libre para usar.
</div>

<script>
// Accordion simple
document.querySelectorAll('.accordion-header').forEach(header => {
    header.addEventListener('click', () => {
        const active = header.classList.contains('active');
        // Cerrar todos
        document.querySelectorAll('.accordion-header').forEach(h => h.classList.remove('active'));
        document.querySelectorAll('.accordion-content').forEach(c => c.style.display='none');
        // Abrir el seleccionado si no estaba activo
        if (!active) {
            header.classList.add('active');
            header.nextElementSibling.style.display='block';
        }
    });
});
</script>

</body>
</html>
