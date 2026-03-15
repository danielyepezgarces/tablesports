<?php
require_once __DIR__ . '/config.php';

session_name(SESSION_NAME);
session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

/* ───────── helpers ───────── */

function openDb(): SQLite3 {
    $db = new SQLite3(DB_PATH);
    $db->enableExceptions(true);

    $db->exec("CREATE TABLE IF NOT EXISTS clubs (
        id     INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL UNIQUE
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS jugadores (
        id           INTEGER PRIMARY KEY AUTOINCREMENT,
        equipo       TEXT NOT NULL,
        nombre       TEXT NOT NULL,
        wiki_link    TEXT NOT NULL DEFAULT '',
        nacionalidad TEXT NOT NULL DEFAULT '',
        posicion     TEXT NOT NULL DEFAULT '',
        UNIQUE(equipo, nombre)
    )");

    // Schema migration: add new columns if coming from older schema
    $existing = [];
    $pragma = $db->query("PRAGMA table_info(jugadores)");
    while ($r = $pragma->fetchArray(SQLITE3_ASSOC)) {
        $existing[] = $r['name'];
    }
    // Each migration is a fully-formed literal statement to avoid interpolation.
    $migrations = [
        'nacionalidad' => "ALTER TABLE jugadores ADD COLUMN nacionalidad TEXT NOT NULL DEFAULT ''",
        'posicion'     => "ALTER TABLE jugadores ADD COLUMN posicion TEXT NOT NULL DEFAULT ''",
    ];
    foreach ($migrations as $col => $sql) {
        if (!in_array($col, $existing, true)) {
            $db->exec($sql);
        }
    }

    // Auto-populate clubs from existing player records (idempotent)
    $db->exec("INSERT OR IGNORE INTO clubs (nombre)
               SELECT DISTINCT equipo FROM jugadores WHERE equipo != ''");

    return $db;
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Token CSRF inválido.');
    }
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function getPositions(): array {
    return [
        'Portero',
        'Defensa Central',
        'Lateral Derecho',
        'Lateral Izquierdo',
        'Mediocampista Defensivo',
        'Mediocampista Central',
        'Mediocampista Ofensivo',
        'Extremo Derecho',
        'Extremo Izquierdo',
        'Segundo Delantero',
        'Delantero',
    ];
}

function getCountries(): array {
    return [
        'ALG' => 'Argelia',         'ARG' => 'Argentina',       'AUS' => 'Australia',
        'AUT' => 'Austria',         'BEL' => 'Bélgica',         'BOL' => 'Bolivia',
        'BRA' => 'Brasil',          'CAN' => 'Canadá',          'CHI' => 'Chile',
        'CHN' => 'China',           'CIV' => 'Costa de Marfil', 'CMR' => 'Camerún',
        'COL' => 'Colombia',        'CRC' => 'Costa Rica',      'CRO' => 'Croacia',
        'CUB' => 'Cuba',            'CZE' => 'Rep. Checa',      'DEN' => 'Dinamarca',
        'DOM' => 'Rep. Dominicana', 'ECU' => 'Ecuador',         'EGY' => 'Egipto',
        'ENG' => 'Inglaterra',      'ESP' => 'España',          'FRA' => 'Francia',
        'GER' => 'Alemania',        'GHA' => 'Ghana',           'GRE' => 'Grecia',
        'GTM' => 'Guatemala',       'HON' => 'Honduras',        'HUN' => 'Hungría',
        'IRL' => 'Irlanda',         'IRN' => 'Irán',            'ISL' => 'Islandia',
        'ITA' => 'Italia',          'JAM' => 'Jamaica',         'JPN' => 'Japón',
        'KEN' => 'Kenia',           'KOR' => 'Corea del Sur',   'MAR' => 'Marruecos',
        'MEX' => 'México',          'NED' => 'Países Bajos',    'NGA' => 'Nigeria',
        'NOR' => 'Noruega',         'PAN' => 'Panamá',          'PAR' => 'Paraguay',
        'PER' => 'Perú',            'POL' => 'Polonia',         'POR' => 'Portugal',
        'RSA' => 'Sudáfrica',       'ROU' => 'Rumanía',         'RUS' => 'Rusia',
        'SAU' => 'Arabia Saudí',    'SCO' => 'Escocia',         'SEN' => 'Senegal',
        'SRB' => 'Serbia',          'SUI' => 'Suiza',           'SWE' => 'Suecia',
        'TRI' => 'Trinidad y Tobago', 'TUN' => 'Túnez',         'TUR' => 'Turquía',
        'UKR' => 'Ucrania',         'URU' => 'Uruguay',         'USA' => 'Estados Unidos',
        'VEN' => 'Venezuela',       'WAL' => 'Gales',
    ];
}

/* ───────── action handling ───────── */

$db      = openDb();
$msg     = '';
$msgType = 'success';
$tab     = $_GET['tab'] ?? 'players';
if (!in_array($tab, ['players', 'clubs', 'import'], true)) {
    $tab = 'players';
}
$action  = $_GET['action'] ?? 'list';
$editRow = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $postAction = $_POST['action'] ?? '';

    /* ── add player ── */
    if ($postAction === 'add') {
        $equipo       = trim($_POST['equipo']       ?? '');
        $nombre       = trim($_POST['nombre']       ?? '');
        $wikiLink     = trim($_POST['wiki_link']    ?? '');
        $nacionalidad = trim($_POST['nacionalidad'] ?? '');
        $posicion     = trim($_POST['posicion']     ?? '');

        // Auto-generate wiki_link if omitted
        if ($wikiLink === '' && $nombre !== '') {
            $prefix   = $nacionalidad !== '' ? "{{bandera|{$nacionalidad}}} " : '';
            $wikiLink = $prefix . "[[{$nombre}]]";
        }
        if ($equipo === '' || $nombre === '') {
            $msg     = 'Equipo y nombre son obligatorios.';
            $msgType = 'error';
        } else {
            try {
                $stmt = $db->prepare(
                    "INSERT INTO jugadores (equipo, nombre, wiki_link, nacionalidad, posicion)
                     VALUES (?,?,?,?,?)"
                );
                $stmt->bindValue(1, $equipo,       SQLITE3_TEXT);
                $stmt->bindValue(2, $nombre,       SQLITE3_TEXT);
                $stmt->bindValue(3, $wikiLink,     SQLITE3_TEXT);
                $stmt->bindValue(4, $nacionalidad, SQLITE3_TEXT);
                $stmt->bindValue(5, $posicion,     SQLITE3_TEXT);
                $stmt->execute();
                $stmtC = $db->prepare("INSERT OR IGNORE INTO clubs (nombre) VALUES (?)");
                $stmtC->bindValue(1, $equipo, SQLITE3_TEXT);
                $stmtC->execute();
                $msg = 'Jugador añadido correctamente.';
            } catch (Exception $e) {
                $msg     = 'Error: ya existe un jugador con ese nombre en ese equipo.';
                $msgType = 'error';
            }
        }

    /* ── edit player ── */
    } elseif ($postAction === 'edit') {
        $id           = (int)($_POST['id']           ?? 0);
        $equipo       = trim($_POST['equipo']        ?? '');
        $nombre       = trim($_POST['nombre']        ?? '');
        $wikiLink     = trim($_POST['wiki_link']     ?? '');
        $nacionalidad = trim($_POST['nacionalidad']  ?? '');
        $posicion     = trim($_POST['posicion']      ?? '');

        if ($id <= 0 || $equipo === '' || $nombre === '') {
            $msg     = 'Equipo y nombre son obligatorios.';
            $msgType = 'error';
        } else {
            try {
                $stmt = $db->prepare(
                    "UPDATE jugadores
                     SET equipo=?, nombre=?, wiki_link=?, nacionalidad=?, posicion=?
                     WHERE id=?"
                );
                $stmt->bindValue(1, $equipo,       SQLITE3_TEXT);
                $stmt->bindValue(2, $nombre,       SQLITE3_TEXT);
                $stmt->bindValue(3, $wikiLink,     SQLITE3_TEXT);
                $stmt->bindValue(4, $nacionalidad, SQLITE3_TEXT);
                $stmt->bindValue(5, $posicion,     SQLITE3_TEXT);
                $stmt->bindValue(6, $id,           SQLITE3_INTEGER);
                $stmt->execute();
                $stmtC = $db->prepare("INSERT OR IGNORE INTO clubs (nombre) VALUES (?)");
                $stmtC->bindValue(1, $equipo, SQLITE3_TEXT);
                $stmtC->execute();
                $msg = 'Jugador actualizado correctamente.';
            } catch (Exception $e) {
                $msg     = 'Error al actualizar: ya existe un jugador con ese nombre en ese equipo.';
                $msgType = 'error';
            }
        }
        $action = 'list';

    /* ── delete player ── */
    } elseif ($postAction === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare("DELETE FROM jugadores WHERE id=?");
            $stmt->bindValue(1, $id, SQLITE3_INTEGER);
            $stmt->execute();
            $msg = 'Jugador eliminado.';
        }
        $action = 'list';

    /* ── transfer player ── */
    } elseif ($postAction === 'transfer') {
        $id     = (int)($_POST['id']    ?? 0);
        $equipo = trim($_POST['equipo'] ?? '');
        if ($id > 0 && $equipo !== '') {
            $stmt = $db->prepare("UPDATE jugadores SET equipo=? WHERE id=?");
            $stmt->bindValue(1, $equipo,   SQLITE3_TEXT);
            $stmt->bindValue(2, $id,       SQLITE3_INTEGER);
            $stmt->execute();
            $stmtC = $db->prepare("INSERT OR IGNORE INTO clubs (nombre) VALUES (?)");
            $stmtC->bindValue(1, $equipo, SQLITE3_TEXT);
            $stmtC->execute();
            $msg = 'Jugador transferido correctamente.';
        }
        $action = 'list';

    /* ── add club ── */
    } elseif ($postAction === 'add_club') {
        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '') {
            $msg     = 'El nombre del club es obligatorio.';
            $msgType = 'error';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO clubs (nombre) VALUES (?)");
                $stmt->bindValue(1, $nombre, SQLITE3_TEXT);
                $stmt->execute();
                $msg = "Club \"{$nombre}\" añadido.";
            } catch (Exception $e) {
                $msg     = 'Error: ya existe un club con ese nombre.';
                $msgType = 'error';
            }
        }
        $tab = 'clubs';

    /* ── delete club ── */
    } elseif ($postAction === 'delete_club') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmtN = $db->prepare("SELECT nombre FROM clubs WHERE id=?");
            $stmtN->bindValue(1, $id, SQLITE3_INTEGER);
            $clubName = ($stmtN->execute()->fetchArray(SQLITE3_ASSOC) ?: [])['nombre'] ?? '';
            if ($clubName !== '') {
                $stmtCnt = $db->prepare("SELECT COUNT(*) FROM jugadores WHERE equipo=?");
                $stmtCnt->bindValue(1, $clubName, SQLITE3_TEXT);
                $cnt = (int)$stmtCnt->execute()->fetchArray(SQLITE3_NUM)[0];
                if ($cnt > 0) {
                    $msg     = "No se puede eliminar \"{$clubName}\": tiene {$cnt} jugador(es). Transfiere o elimina los jugadores primero.";
                    $msgType = 'error';
                } else {
                    $stmtD = $db->prepare("DELETE FROM clubs WHERE id=?");
                    $stmtD->bindValue(1, $id, SQLITE3_INTEGER);
                    $stmtD->execute();
                    $msg = "Club \"{$clubName}\" eliminado.";
                }
            }
        }
        $tab = 'clubs';

    /* ── import CSV ── */
    } elseif ($postAction === 'import_csv') {
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $msg     = 'Error al subir el archivo. Verifica que sea un CSV válido.';
            $msgType = 'error';
        } else {
            $handle  = fopen($_FILES['csv_file']['tmp_name'], 'r');
            $headers = $handle ? fgetcsv($handle) : false;
            if (!$headers) {
                $msg     = 'El archivo CSV está vacío o es inválido.';
                $msgType = 'error';
            } else {
                $hdrs = array_map(fn($h) => strtolower(trim($h)), $headers);
                $col  = [];
                foreach (['equipo', 'nombre', 'wiki_link', 'nacionalidad', 'posicion'] as $f) {
                    $idx    = array_search($f, $hdrs, true);
                    $col[$f] = ($idx !== false) ? $idx : null;
                }
                if ($col['equipo'] === null || $col['nombre'] === null) {
                    $msg     = 'El CSV debe tener al menos las columnas: equipo, nombre.';
                    $msgType = 'error';
                } else {
                    $inserted = $skipped = 0;
                    $stmt  = $db->prepare(
                        "INSERT OR IGNORE INTO jugadores (equipo, nombre, wiki_link, nacionalidad, posicion)
                         VALUES (?,?,?,?,?)"
                    );
                    $stmtC = $db->prepare("INSERT OR IGNORE INTO clubs (nombre) VALUES (?)");
                    $db->exec('BEGIN');
                    while (($row = fgetcsv($handle)) !== false) {
                        $eq = trim($row[$col['equipo']] ?? '');
                        $nm = trim($row[$col['nombre']] ?? '');
                        if ($eq === '' || $nm === '') { $skipped++; continue; }
                        $wl = $col['wiki_link']    !== null ? trim($row[$col['wiki_link']]    ?? '') : '';
                        $na = $col['nacionalidad'] !== null ? trim($row[$col['nacionalidad']] ?? '') : '';
                        $po = $col['posicion']     !== null ? trim($row[$col['posicion']]     ?? '') : '';
                        if ($wl === '') {
                            $wl = ($na !== '' ? "{{bandera|{$na}}} " : '') . "[[{$nm}]]";
                        }
                        $stmt->bindValue(1, $eq, SQLITE3_TEXT);
                        $stmt->bindValue(2, $nm, SQLITE3_TEXT);
                        $stmt->bindValue(3, $wl, SQLITE3_TEXT);
                        $stmt->bindValue(4, $na, SQLITE3_TEXT);
                        $stmt->bindValue(5, $po, SQLITE3_TEXT);
                        $stmt->execute();
                        if ($db->changes() > 0) {
                            $stmtC->bindValue(1, $eq, SQLITE3_TEXT);
                            $stmtC->execute();
                            $inserted++;
                        } else {
                            $skipped++;
                        }
                    }
                    $db->exec('COMMIT');
                    fclose($handle);
                    $msg = "Importación completada: {$inserted} jugadores añadidos, {$skipped} omitidos.";
                }
            }
        }
        $tab = 'import';
    }
}

// Load edit row
if ($action === 'edit' && isset($_GET['id'])) {
    $id      = (int)$_GET['id'];
    $stmt    = $db->prepare("SELECT * FROM jugadores WHERE id=?");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $editRow = $stmt->execute()->fetchArray(SQLITE3_ASSOC) ?: null;
    if (!$editRow) {
        $action = 'list';
    }
    $tab = 'players';
}

// Load players for the players tab
$players = [];
$teams   = [];
if ($tab === 'players') {
    $res = $db->query(
        "SELECT * FROM jugadores ORDER BY equipo COLLATE NOCASE, nombre COLLATE NOCASE"
    );
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $players[$row['equipo']][] = $row;
        $teams[$row['equipo']]     = true;
    }
    $teams = array_keys($teams);
    sort($teams);
}

// All clubs (for dropdowns and clubs tab)
$clubsRes = $db->query("SELECT id, nombre FROM clubs ORDER BY nombre COLLATE NOCASE");
$allClubs = [];
while ($row = $clubsRes->fetchArray(SQLITE3_ASSOC)) {
    $allClubs[] = $row;
}

// Clubs with player counts (clubs tab)
$clubsWithCounts = [];
if ($tab === 'clubs') {
    foreach ($allClubs as $club) {
        $stmtCnt = $db->prepare("SELECT COUNT(*) FROM jugadores WHERE equipo=?");
        $stmtCnt->bindValue(1, $club['nombre'], SQLITE3_TEXT);
        $cnt               = (int)$stmtCnt->execute()->fetchArray(SQLITE3_NUM)[0];
        $clubsWithCounts[] = ['id' => $club['id'], 'nombre' => $club['nombre'], 'count' => $cnt];
    }
}

$totalPlayers = 0;
foreach ($players as $rows) {
    $totalPlayers += count($rows);
}

$positions = getPositions();
$countries = getCountries();
$token     = csrfToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin – TableSports</title>
<style>
*, *::before, *::after { box-sizing: border-box; }
body {
    font-family: system-ui, sans-serif;
    background: #0f172a;
    color: #e5e7eb;
    margin: 0;
    padding: 20px;
}
a { color: #38bdf8; text-decoration: none; }
a:hover { text-decoration: underline; }
header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 10px;
}
h1 { margin: 0; color: #38bdf8; font-size: 1.6rem; }
/* Buttons */
.btn {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.15s;
}
.btn-primary   { background: #38bdf8; color: #0f172a; }
.btn-primary:hover { background: #0ea5e9; text-decoration: none; }
.btn-danger    { background: #ef4444; color: #fff; }
.btn-danger:hover  { background: #dc2626; text-decoration: none; }
.btn-secondary { background: #334155; color: #e5e7eb; }
.btn-secondary:hover { background: #475569; text-decoration: none; }
.btn-warning   { background: #f59e0b; color: #0f172a; }
.btn-warning:hover { background: #d97706; text-decoration: none; }
.btn-sm { padding: 4px 10px; font-size: 0.8rem; }
/* Messages */
.msg {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: 500;
}
.msg.success { background: #14532d; color: #86efac; }
.msg.error   { background: #450a0a; color: #fca5a5; }
/* Tabs */
.tabs {
    display: flex;
    gap: 4px;
    margin-bottom: 24px;
    border-bottom: 1px solid #334155;
    padding-bottom: 0;
}
.tab-btn {
    padding: 10px 20px;
    border-radius: 6px 6px 0 0;
    background: transparent;
    border: none;
    color: #94a3b8;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    margin-bottom: -1px;
    border-bottom: 2px solid transparent;
    transition: color 0.15s, border-color 0.15s;
}
.tab-btn:hover { color: #e5e7eb; text-decoration: none; }
.tab-btn.active { color: #38bdf8; border-bottom-color: #38bdf8; }
/* Form card */
.card {
    background: #1e293b;
    border-radius: 10px;
    padding: 24px;
    margin-bottom: 28px;
    max-width: 720px;
}
.card h2 { margin: 0 0 20px; color: #94a3b8; font-size: 1.1rem; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; margin-bottom: 5px; color: #94a3b8; font-size: 0.85rem; }
.form-group input[type="text"],
.form-group input[type="file"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #334155;
    border-radius: 6px;
    background: #0f172a;
    color: #e5e7eb;
    font-size: 0.95rem;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { outline: none; border-color: #38bdf8; }
.form-row { display: flex; gap: 14px; flex-wrap: wrap; }
.form-row .form-group { flex: 1; min-width: 180px; }
.form-actions { display: flex; gap: 10px; margin-top: 6px; flex-wrap: wrap; }
/* Searchable select */
.ss-wrap { position: relative; }
.ss-display {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #334155;
    border-radius: 6px;
    background: #0f172a;
    color: #e5e7eb;
    font-size: 0.95rem;
    cursor: pointer;
    user-select: none;
}
.ss-display::after {
    content: ' ▾';
    color: #64748b;
}
.ss-display.has-value { color: #e5e7eb; }
.ss-dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 2px);
    left: 0; right: 0;
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 6px;
    z-index: 200;
    max-height: 260px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0,0,0,0.5);
}
.ss-dropdown.open { display: flex; flex-direction: column; }
.ss-search-input {
    padding: 8px 12px;
    border: none;
    border-bottom: 1px solid #334155;
    background: #0f172a;
    color: #e5e7eb;
    font-size: 0.9rem;
    flex-shrink: 0;
}
.ss-search-input:focus { outline: none; }
.ss-options { overflow-y: auto; flex: 1; }
.ss-option {
    padding: 8px 14px;
    cursor: pointer;
    font-size: 0.9rem;
    color: #e5e7eb;
}
.ss-option:hover, .ss-option.highlighted { background: #334155; }
.ss-option.placeholder { color: #64748b; font-style: italic; }
/* Team accordion */
.team-block { margin-bottom: 12px; }
.team-header {
    background: #1e293b;
    padding: 12px 16px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    user-select: none;
}
.team-header:hover { background: #263348; }
.team-header .count {
    font-size: 0.8rem;
    background: #334155;
    padding: 2px 8px;
    border-radius: 20px;
    color: #94a3b8;
}
.team-body { display: none; overflow-x: auto; }
.team-body.open { display: block; }
table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
th {
    background: #0f172a;
    color: #94a3b8;
    padding: 8px 12px;
    text-align: left;
    font-weight: 600;
}
td { padding: 8px 12px; border-bottom: 1px solid #1e293b; vertical-align: middle; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: #1a2744; }
.wiki-cell {
    font-family: monospace;
    font-size: 0.8rem;
    color: #94a3b8;
    max-width: 240px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.actions-cell { white-space: nowrap; }
/* Search */
.search-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.search-bar input {
    padding: 9px 14px;
    border: 1px solid #334155;
    border-radius: 6px;
    background: #1e293b;
    color: #e5e7eb;
    font-size: 0.95rem;
    width: 280px;
}
.search-bar input:focus { outline: none; border-color: #38bdf8; }
.stats { color: #64748b; font-size: 0.85rem; }
/* Clubs table */
.clubs-table { max-width: 600px; }
.clubs-table td:last-child { text-align: right; }
/* Import */
.import-hint {
    background: #1e293b;
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 20px;
    max-width: 680px;
}
.import-hint pre {
    background: #0f172a;
    padding: 12px;
    border-radius: 6px;
    font-size: 0.82rem;
    overflow-x: auto;
    color: #94a3b8;
    margin: 10px 0 0;
}
/* Transfer dialog */
dialog {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 12px;
    padding: 28px;
    color: #e5e7eb;
    width: 380px;
    max-width: 95vw;
}
dialog::backdrop { background: rgba(0,0,0,0.65); }
dialog h3 { margin: 0 0 20px; color: #38bdf8; }
</style>
</head>
<body>

<header>
    <h1>⚙️ Admin – TableSports</h1>
    <div style="display:flex;gap:10px;align-items:center;">
        <a href="../index.php" class="btn btn-secondary">← Inicio</a>
        <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
    </div>
</header>

<?php if ($msg): ?>
    <div class="msg <?= h($msgType) ?>"><?= h($msg) ?></div>
<?php endif; ?>

<!-- ═══ TABS ═══ -->
<nav class="tabs">
    <a href="index.php?tab=players"
       class="tab-btn <?= $tab === 'players' ? 'active' : '' ?>">👤 Jugadores</a>
    <a href="index.php?tab=clubs"
       class="tab-btn <?= $tab === 'clubs'   ? 'active' : '' ?>">🏟️ Clubes</a>
    <a href="index.php?tab=import"
       class="tab-btn <?= $tab === 'import'  ? 'active' : '' ?>">📥 Importar CSV</a>
</nav>

<?php /* ══════════════════════════════════════
   TAB: PLAYERS
═══════════════════════════════════════════ */ if ($tab === 'players'): ?>

<?php if ($action === 'edit' && $editRow): ?>
<!-- ─── EDIT PLAYER FORM ─── -->
<div class="card">
    <h2>✏️ Editar jugador</h2>
    <form method="post" action="index.php?tab=players" id="edit-form">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Equipo</label>
                <div class="ss-wrap" id="ss-edit-club">
                    <div class="ss-display has-value" tabindex="0"><?= h($editRow['equipo']) ?></div>
                    <input type="hidden" name="equipo" value="<?= h($editRow['equipo']) ?>">
                    <div class="ss-dropdown">
                        <input type="text" class="ss-search-input" placeholder="Buscar club…">
                        <div class="ss-options">
                            <?php foreach ($allClubs as $club): ?>
                            <div class="ss-option" data-value="<?= h($club['nombre']) ?>"><?= h($club['nombre']) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="edit_nombre">Nombre del jugador</label>
                <input type="text" id="edit_nombre" name="nombre"
                       value="<?= h($editRow['nombre']) ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Nacionalidad</label>
                <?php $ec = $editRow['nacionalidad'] ?? ''; $cn = $countries[$ec] ?? ''; ?>
                <div class="ss-wrap" id="ss-edit-nac">
                    <div class="ss-display <?= $ec !== '' ? 'has-value' : '' ?>" tabindex="0">
                        <?= $ec !== '' ? h("{$cn} ({$ec})") : 'Sin especificar' ?>
                    </div>
                    <input type="hidden" name="nacionalidad" value="<?= h($ec) ?>">
                    <div class="ss-dropdown">
                        <input type="text" class="ss-search-input" placeholder="Buscar país…">
                        <div class="ss-options">
                            <div class="ss-option placeholder" data-value="">— Sin especificar —</div>
                            <?php foreach ($countries as $code => $name): ?>
                            <div class="ss-option" data-value="<?= h($code) ?>"><?= h($name) ?> (<?= h($code) ?>)</div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="edit_posicion">Posición</label>
                <select id="edit_posicion" name="posicion">
                    <option value="">— Sin especificar —</option>
                    <?php foreach ($positions as $pos): ?>
                    <option value="<?= h($pos) ?>"
                        <?= ($editRow['posicion'] ?? '') === $pos ? 'selected' : '' ?>>
                        <?= h($pos) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="edit_wiki">Wiki link <small style="color:#64748b">(auto-generado si está vacío)</small></label>
            <input type="text" id="edit_wiki" name="wiki_link"
                   value="<?= h($editRow['wiki_link']) ?>"
                   placeholder="{{bandera|COL}} [[Nombre Apellido]]">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="index.php?tab=players" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php else: ?>
<!-- ─── ADD PLAYER FORM ─── -->
<div class="card">
    <h2>➕ Añadir jugador</h2>
    <form method="post" action="index.php?tab=players" id="add-form">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Equipo</label>
                <div class="ss-wrap" id="ss-add-club">
                    <div class="ss-display" tabindex="0">Seleccionar equipo…</div>
                    <input type="hidden" name="equipo" value="" required>
                    <div class="ss-dropdown">
                        <input type="text" class="ss-search-input" placeholder="Buscar club…">
                        <div class="ss-options">
                            <?php foreach ($allClubs as $club): ?>
                            <div class="ss-option" data-value="<?= h($club['nombre']) ?>"><?= h($club['nombre']) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="add_nombre">Nombre del jugador</label>
                <input type="text" id="add_nombre" name="nombre" required placeholder="Nombre Apellido">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Nacionalidad</label>
                <div class="ss-wrap" id="ss-add-nac">
                    <div class="ss-display" tabindex="0">Sin especificar</div>
                    <input type="hidden" name="nacionalidad" value="">
                    <div class="ss-dropdown">
                        <input type="text" class="ss-search-input" placeholder="Buscar país…">
                        <div class="ss-options">
                            <div class="ss-option placeholder" data-value="">— Sin especificar —</div>
                            <?php foreach ($countries as $code => $name): ?>
                            <div class="ss-option" data-value="<?= h($code) ?>"><?= h($name) ?> (<?= h($code) ?>)</div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="add_posicion">Posición</label>
                <select id="add_posicion" name="posicion">
                    <option value="">— Sin especificar —</option>
                    <?php foreach ($positions as $pos): ?>
                    <option value="<?= h($pos) ?>"><?= h($pos) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="add_wiki">Wiki link <small style="color:#64748b">(auto-generado si está vacío)</small></label>
            <input type="text" id="add_wiki" name="wiki_link"
                   placeholder="{{bandera|COL}} [[Nombre Apellido]]">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="add-btn">Añadir jugador</button>
        </div>
    </form>
</div>

<!-- ─── PLAYER LIST ─── -->
<div class="search-bar">
    <input type="text" id="search" placeholder="🔍 Buscar jugador, equipo, posición…"
           oninput="filterPlayers(this.value)">
    <span class="stats" id="stats"></span>
</div>

<script>
const totalPlayers = <?= $totalPlayers ?>;
const totalTeams   = <?= count($teams) ?>;
document.getElementById('stats').textContent =
    totalPlayers + ' jugadores, ' + totalTeams + ' equipos';
</script>

<?php foreach ($teams as $team): ?>
<div class="team-block" data-team="<?= h($team) ?>">
    <div class="team-header" onclick="toggleTeam(this)">
        <span><?= h($team) ?></span>
        <span class="count"><?= count($players[$team]) ?> jugadores</span>
    </div>
    <div class="team-body">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Posición</th>
                    <th>Nac.</th>
                    <th>Wiki link</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($players[$team] as $row): ?>
                <tr data-nombre="<?= h(strtolower($row['nombre'])) ?>"
                    data-posicion="<?= h(strtolower($row['posicion'] ?? '')) ?>">
                    <td><?= h($row['nombre']) ?></td>
                    <td><?= h($row['posicion'] ?? '') ?></td>
                    <td><?= h($row['nacionalidad'] ?? '') ?></td>
                    <td class="wiki-cell" title="<?= h($row['wiki_link']) ?>"><?= h($row['wiki_link']) ?></td>
                    <td class="actions-cell">
                        <a href="index.php?tab=players&action=edit&id=<?= (int)$row['id'] ?>"
                           class="btn btn-primary btn-sm">Editar</a>
                        <button type="button" class="btn btn-warning btn-sm"
                                onclick="openTransfer(<?= (int)$row['id'] ?>, <?= json_encode($row['nombre']) ?>, <?= json_encode($row['equipo']) ?>)">
                            Transferir
                        </button>
                        <form method="post" action="index.php?tab=players" style="display:inline"
                              onsubmit="return confirm(<?= json_encode('¿Eliminar a ' . $row['nombre'] . '?') ?>)">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>

<?php endif; /* end edit/add branch */ ?>

<!-- ─── TRANSFER DIALOG ─── -->
<dialog id="transfer-dialog">
    <h3>🔄 Transferir jugador</h3>
    <p id="transfer-player-name" style="color:#94a3b8; margin: 0 0 16px;"></p>
    <form method="post" action="index.php?tab=players">
        <input type="hidden" name="action" value="transfer">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="id" id="transfer-id">
        <div class="form-group">
            <label>Nuevo equipo</label>
            <div class="ss-wrap" id="ss-transfer-club">
                <div class="ss-display" tabindex="0">Seleccionar equipo…</div>
                <input type="hidden" name="equipo" value="" id="transfer-equipo-hidden">
                <div class="ss-dropdown">
                    <input type="text" class="ss-search-input" placeholder="Buscar club…">
                    <div class="ss-options">
                        <?php foreach ($allClubs as $club): ?>
                        <div class="ss-option" data-value="<?= h($club['nombre']) ?>"><?= h($club['nombre']) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-warning" id="transfer-submit-btn">Transferir</button>
            <button type="button" class="btn btn-secondary"
                    onclick="document.getElementById('transfer-dialog').close()">Cancelar</button>
        </div>
    </form>
</dialog>

<?php /* ══════════════════════════════════════
   TAB: CLUBS
═══════════════════════════════════════════ */ elseif ($tab === 'clubs'): ?>

<div class="card" style="max-width:500px;">
    <h2>➕ Añadir club</h2>
    <form method="post" action="index.php?tab=clubs">
        <input type="hidden" name="action" value="add_club">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <div style="display:flex;gap:10px;">
            <div class="form-group" style="flex:1;margin:0;">
                <input type="text" name="nombre" required placeholder="Nombre del club">
            </div>
            <button type="submit" class="btn btn-primary">Añadir</button>
        </div>
    </form>
</div>

<div class="card" style="max-width:600px; padding:0; overflow:hidden;">
    <table class="clubs-table" style="max-width:100%; width:100%;">
        <thead>
            <tr>
                <th style="padding:14px 16px;">Club</th>
                <th style="padding:14px 16px;">Jugadores</th>
                <th style="padding:14px 16px; text-align:right;">Acción</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($clubsWithCounts)): ?>
            <tr><td colspan="3" style="padding:16px; color:#64748b; text-align:center;">Sin clubs registrados.</td></tr>
        <?php endif; ?>
        <?php foreach ($clubsWithCounts as $club): ?>
            <tr>
                <td style="padding:10px 16px; font-weight:600;"><?= h($club['nombre']) ?></td>
                <td style="padding:10px 16px; color:#94a3b8;">
                    <?php if ($club['count'] > 0): ?>
                        <a href="index.php?tab=players" style="color:#38bdf8;">
                            <?= $club['count'] ?> jugador<?= $club['count'] !== 1 ? 'es' : '' ?>
                        </a>
                    <?php else: ?>
                        <span style="color:#64748b;">0 jugadores</span>
                    <?php endif; ?>
                </td>
                <td style="padding:10px 16px; text-align:right;">
                    <?php if ($club['count'] === 0): ?>
                    <form method="post" action="index.php?tab=clubs" style="display:inline"
                          onsubmit="return confirm(<?= json_encode('¿Eliminar el club ' . $club['nombre'] . '?') ?>)">
                        <input type="hidden" name="action" value="delete_club">
                        <input type="hidden" name="id" value="<?= (int)$club['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                    <?php else: ?>
                        <span style="color:#64748b; font-size:0.8rem;">Transferir jugadores primero</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php /* ══════════════════════════════════════
   TAB: IMPORT CSV
═══════════════════════════════════════════ */ elseif ($tab === 'import'): ?>

<div class="import-hint">
    <strong style="color:#94a3b8;">Formato del CSV</strong>
    <p style="margin:8px 0; color:#64748b; font-size:0.9rem;">
        El archivo debe tener una fila de encabezados. Columnas requeridas: <code>equipo</code>, <code>nombre</code>.
        Columnas opcionales: <code>wiki_link</code>, <code>nacionalidad</code> (código de 3 letras), <code>posicion</code>.<br>
        Si <code>wiki_link</code> está vacío, se genera automáticamente desde la nacionalidad y el nombre.
    </p>
    <pre>equipo,nombre,wiki_link,nacionalidad,posicion
Alianza,Carlos Pérez,{{bandera|COL}} [[Carlos Pérez]],COL,Delantero
Alianza,Luis Gómez,,ARG,Portero</pre>
</div>

<div class="card" style="max-width:520px;">
    <h2>📥 Importar jugadores desde CSV</h2>
    <form method="post" action="index.php?tab=import" enctype="multipart/form-data">
        <input type="hidden" name="action" value="import_csv">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <div class="form-group">
            <label for="csv_file">Archivo CSV</label>
            <input type="file" id="csv_file" name="csv_file" accept=".csv,text/csv" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Importar</button>
        </div>
    </form>
</div>

<?php endif; /* end tab switch */ ?>

<script>
/* ─── Searchable Select ─── */
function initSearchableSelect(wrap) {
    const display = wrap.querySelector('.ss-display');
    const hidden  = wrap.querySelector('input[type="hidden"]');
    const dropdown = wrap.querySelector('.ss-dropdown');
    const searchIn = wrap.querySelector('.ss-search-input');
    const optList  = wrap.querySelectorAll('.ss-option');

    function openDrop() {
        document.querySelectorAll('.ss-dropdown.open').forEach(d => {
            if (d !== dropdown) d.classList.remove('open');
        });
        dropdown.classList.add('open');
        searchIn.value = '';
        optList.forEach(o => o.style.display = '');
        searchIn.focus();
    }
    function closeDrop() {
        dropdown.classList.remove('open');
    }
    function selectOpt(value, label) {
        hidden.value = value;
        display.textContent = label || '— Sin especificar —';
        display.classList.toggle('has-value', value !== '');
        closeDrop();
    }

    display.addEventListener('click', e => { e.stopPropagation(); openDrop(); });
    display.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openDrop(); }
        if (e.key === 'Escape') { closeDrop(); }
    });
    searchIn.addEventListener('input', () => {
        const q = searchIn.value.toLowerCase();
        optList.forEach(o => {
            o.style.display = o.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
    searchIn.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeDrop(); display.focus(); }
    });
    optList.forEach(opt => {
        opt.addEventListener('click', e => {
            e.stopPropagation();
            selectOpt(opt.dataset.value, opt.textContent.trim());
        });
    });
    document.addEventListener('click', () => closeDrop());
    dropdown.addEventListener('click', e => e.stopPropagation());
}

document.querySelectorAll('.ss-wrap').forEach(initSearchableSelect);

/* ─── Validate equipo on submit ─── */
['add-form', 'edit-form'].forEach(id => {
    const form = document.getElementById(id);
    if (!form) return;
    form.addEventListener('submit', e => {
        const clubHidden = form.querySelector('input[name="equipo"]');
        if (clubHidden && clubHidden.value.trim() === '') {
            e.preventDefault();
            let err = form.querySelector('.form-err-club');
            if (!err) {
                err = document.createElement('p');
                err.className = 'form-err-club';
                err.style.cssText = 'color:#f87171;font-size:0.85rem;margin:4px 0 0;';
                clubHidden.parentNode.appendChild(err);
            }
            err.textContent = 'Selecciona un equipo antes de guardar.';
        }
    });
});

/* ─── Transfer dialog ─── */
function openTransfer(id, name, currentTeam) {
    document.getElementById('transfer-id').value = id;
    document.getElementById('transfer-player-name').textContent =
        name + '  ←  ' + currentTeam;
    const wrap = document.getElementById('ss-transfer-club');
    const display = wrap.querySelector('.ss-display');
    const hidden  = document.getElementById('transfer-equipo-hidden');
    display.textContent = 'Seleccionar equipo…';
    display.classList.remove('has-value');
    hidden.value = '';
    document.getElementById('transfer-dialog').showModal();
}
document.getElementById('transfer-dialog').addEventListener('submit', e => {
    const hidden = document.getElementById('transfer-equipo-hidden');
    if (!hidden || hidden.value.trim() === '') {
        e.preventDefault();
        let err = document.getElementById('transfer-equipo-err');
        if (!err) {
            err = document.createElement('p');
            err.id = 'transfer-equipo-err';
            err.style.cssText = 'color:#f87171;font-size:0.85rem;margin:4px 0 0;';
            hidden.parentNode.appendChild(err);
        }
        err.textContent = 'Selecciona el nuevo equipo.';
    }
});

/* ─── Team accordion ─── */
function toggleTeam(header) {
    header.nextElementSibling.classList.toggle('open');
}

/* ─── Player search filter ─── */
function filterPlayers(query) {
    query = query.trim().toLowerCase();
    let visible = 0;
    document.querySelectorAll('.team-block').forEach(block => {
        const teamName = block.dataset.team.toLowerCase();
        const rows = block.querySelectorAll('tbody tr');
        let teamVisible = 0;
        rows.forEach(row => {
            const nombre   = row.dataset.nombre   || '';
            const posicion = row.dataset.posicion || '';
            const show = !query
                || nombre.includes(query)
                || teamName.includes(query)
                || posicion.includes(query);
            row.style.display = show ? '' : 'none';
            if (show) teamVisible++;
        });
        block.style.display = teamVisible > 0 ? '' : 'none';
        if (teamVisible > 0) block.querySelector('.team-body').classList.add('open');
        visible += teamVisible;
    });
    const statsEl = document.getElementById('stats');
    if (statsEl) {
        if (!query) {
            statsEl.textContent = totalPlayers + ' jugadores, ' + totalTeams + ' equipos';
            document.querySelectorAll('.team-body').forEach(b => b.classList.remove('open'));
            document.querySelectorAll('.team-block').forEach(b => b.style.display = '');
        } else {
            statsEl.textContent = visible + ' jugador' + (visible !== 1 ? 'es' : '') +
                ' encontrado' + (visible !== 1 ? 's' : '');
        }
    }
}
</script>

</body>
</html>
