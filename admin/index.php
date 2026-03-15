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
    $db->exec("CREATE TABLE IF NOT EXISTS jugadores (
        id        INTEGER PRIMARY KEY AUTOINCREMENT,
        equipo    TEXT NOT NULL,
        nombre    TEXT NOT NULL,
        wiki_link TEXT NOT NULL,
        UNIQUE(equipo, nombre)
    )");
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

/* ───────── action handling ───────── */

$db     = openDb();
$msg    = '';
$msgType = 'success';
$action = $_GET['action'] ?? 'list';
$editRow = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'add') {
        $equipo   = trim($_POST['equipo']   ?? '');
        $nombre   = trim($_POST['nombre']   ?? '');
        $wikiLink = trim($_POST['wiki_link'] ?? '');

        if ($equipo === '' || $nombre === '' || $wikiLink === '') {
            $msg     = 'Todos los campos son obligatorios.';
            $msgType = 'error';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO jugadores (equipo, nombre, wiki_link) VALUES (?, ?, ?)");
                $stmt->bindValue(1, $equipo,   SQLITE3_TEXT);
                $stmt->bindValue(2, $nombre,   SQLITE3_TEXT);
                $stmt->bindValue(3, $wikiLink, SQLITE3_TEXT);
                $stmt->execute();
                $msg = 'Jugador añadido correctamente.';
            } catch (Exception $e) {
                $msg     = 'Error: ya existe un jugador con ese nombre en ese equipo.';
                $msgType = 'error';
            }
        }

    } elseif ($postAction === 'edit') {
        $id       = (int)($_POST['id']       ?? 0);
        $equipo   = trim($_POST['equipo']   ?? '');
        $nombre   = trim($_POST['nombre']   ?? '');
        $wikiLink = trim($_POST['wiki_link'] ?? '');

        if ($id <= 0 || $equipo === '' || $nombre === '' || $wikiLink === '') {
            $msg     = 'Todos los campos son obligatorios.';
            $msgType = 'error';
        } else {
            try {
                $stmt = $db->prepare("UPDATE jugadores SET equipo=?, nombre=?, wiki_link=? WHERE id=?");
                $stmt->bindValue(1, $equipo,   SQLITE3_TEXT);
                $stmt->bindValue(2, $nombre,   SQLITE3_TEXT);
                $stmt->bindValue(3, $wikiLink, SQLITE3_TEXT);
                $stmt->bindValue(4, $id,       SQLITE3_INTEGER);
                $stmt->execute();
                $msg = 'Jugador actualizado correctamente.';
            } catch (Exception $e) {
                $msg     = 'Error al actualizar: ya existe un jugador con ese nombre en ese equipo.';
                $msgType = 'error';
            }
        }
        $action = 'list';

    } elseif ($postAction === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare("DELETE FROM jugadores WHERE id=?");
            $stmt->bindValue(1, $id, SQLITE3_INTEGER);
            $stmt->execute();
            $msg = 'Jugador eliminado.';
        }
        $action = 'list';
    }
}

// Load row for editing
if ($action === 'edit' && isset($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM jugadores WHERE id=?");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $res  = $stmt->execute();
    $editRow = $res->fetchArray(SQLITE3_ASSOC) ?: null;
    if (!$editRow) {
        $action = 'list';
    }
}

// Load players grouped by team for listing
$players = [];
$teams   = [];
if ($action === 'list') {
    $res = $db->query("SELECT * FROM jugadores ORDER BY equipo COLLATE NOCASE, nombre COLLATE NOCASE");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $players[$row['equipo']][] = $row;
        $teams[$row['equipo']]     = true;
    }
    $teams = array_keys($teams);
    sort($teams);
}

// Also load team list for add/edit dropdowns
$teamOptions = $db->query("SELECT DISTINCT equipo FROM jugadores ORDER BY equipo COLLATE NOCASE");
$allTeams    = [];
while ($row = $teamOptions->fetchArray(SQLITE3_ASSOC)) {
    $allTeams[] = $row['equipo'];
}

$token = csrfToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Jugadores – TableSports</title>
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
.btn {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
}
.btn-primary   { background: #38bdf8; color: #0f172a; }
.btn-primary:hover { background: #0ea5e9; text-decoration: none; }
.btn-danger    { background: #ef4444; color: #fff; }
.btn-danger:hover  { background: #dc2626; }
.btn-secondary { background: #334155; color: #e5e7eb; }
.btn-secondary:hover { background: #475569; text-decoration: none; }
.btn-sm { padding: 4px 10px; font-size: 0.8rem; }
.msg {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: 500;
}
.msg.success { background: #14532d; color: #86efac; }
.msg.error   { background: #450a0a; color: #fca5a5; }
/* Form card */
.card {
    background: #1e293b;
    border-radius: 10px;
    padding: 24px;
    margin-bottom: 28px;
    max-width: 680px;
}
.card h2 { margin: 0 0 20px; color: #94a3b8; font-size: 1.1rem; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; margin-bottom: 5px; color: #94a3b8; font-size: 0.85rem; }
.form-group input,
.form-group select {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #334155;
    border-radius: 6px;
    background: #0f172a;
    color: #e5e7eb;
    font-size: 0.95rem;
}
.form-group input:focus,
.form-group select:focus { outline: none; border-color: #38bdf8; }
.form-row { display: flex; gap: 14px; flex-wrap: wrap; }
.form-row .form-group { flex: 1; min-width: 180px; }
.form-actions { display: flex; gap: 10px; margin-top: 6px; }
/* Team accordion */
.team-block { margin-bottom: 16px; }
.team-header {
    background: #1e293b;
    padding: 12px 16px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
}
.team-header:hover { background: #263348; }
.team-header span.count {
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
.wiki-cell { font-family: monospace; font-size: 0.8rem; color: #94a3b8; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
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
    width: 260px;
}
.search-bar input:focus { outline: none; border-color: #38bdf8; }
.stats { color: #64748b; font-size: 0.85rem; }
</style>
</head>
<body>

<header>
    <h1>⚙️ Admin – Jugadores</h1>
    <div style="display:flex;gap:10px;align-items:center;">
        <a href="../index.php" class="btn btn-secondary">← Inicio</a>
        <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
    </div>
</header>

<?php if ($msg): ?>
    <div class="msg <?= $msgType ?>"><?= h($msg) ?></div>
<?php endif; ?>

<?php if ($action === 'edit' && $editRow): ?>
<!-- ═══ EDIT FORM ═══ -->
<div class="card">
    <h2>✏️ Editar jugador</h2>
    <form method="post" action="index.php">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="edit_equipo">Equipo</label>
                <input type="text" id="edit_equipo" name="equipo"
                       value="<?= h($editRow['equipo']) ?>"
                       list="teams-list" required>
                <datalist id="teams-list">
                    <?php foreach ($allTeams as $t): ?>
                        <option value="<?= h($t) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="form-group">
                <label for="edit_nombre">Nombre del jugador</label>
                <input type="text" id="edit_nombre" name="nombre"
                       value="<?= h($editRow['nombre']) ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label for="edit_wiki">Wiki link</label>
            <input type="text" id="edit_wiki" name="wiki_link"
                   value="<?= h($editRow['wiki_link']) ?>" required
                   placeholder="{{bandera|COL}} [[Nombre Apellido]]">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php else: ?>
<!-- ═══ ADD FORM ═══ -->
<div class="card">
    <h2>➕ Añadir jugador</h2>
    <form method="post" action="index.php">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="add_equipo">Equipo</label>
                <input type="text" id="add_equipo" name="equipo"
                       list="teams-list-add" required placeholder="Nombre del equipo">
                <datalist id="teams-list-add">
                    <?php foreach ($allTeams as $t): ?>
                        <option value="<?= h($t) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="form-group">
                <label for="add_nombre">Nombre del jugador</label>
                <input type="text" id="add_nombre" name="nombre" required placeholder="Nombre Apellido">
            </div>
        </div>
        <div class="form-group">
            <label for="add_wiki">Wiki link</label>
            <input type="text" id="add_wiki" name="wiki_link" required
                   placeholder="{{bandera|COL}} [[Nombre Apellido]]">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Añadir jugador</button>
        </div>
    </form>
</div>

<!-- ═══ PLAYER LIST ═══ -->
<div class="search-bar">
    <input type="text" id="search" placeholder="🔍 Buscar jugador o equipo…" oninput="filterPlayers(this.value)">
    <span class="stats" id="stats"></span>
</div>

<?php
$totalPlayers = 0;
foreach ($players as $rows) { $totalPlayers += count($rows); }
?>
<script>
// Counts for stats display
const totalPlayers = <?= $totalPlayers ?>;
document.getElementById('stats').textContent =
    totalPlayers + ' jugadores, <?= count($teams) ?> equipos';
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
                    <th>Wiki link</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($players[$team] as $row): ?>
                <tr>
                    <td><?= h($row['nombre']) ?></td>
                    <td class="wiki-cell" title="<?= h($row['wiki_link']) ?>"><?= h($row['wiki_link']) ?></td>
                    <td class="actions-cell">
                        <a href="index.php?action=edit&id=<?= (int)$row['id'] ?>"
                           class="btn btn-primary btn-sm">Editar</a>
                        <form method="post" action="index.php" style="display:inline"
                              onsubmit="return confirm('¿Eliminar a <?= h(addslashes($row['nombre'])) ?>?')">
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

<?php endif; ?>

<script>
function toggleTeam(header) {
    const body = header.nextElementSibling;
    body.classList.toggle('open');
}

function filterPlayers(query) {
    query = query.trim().toLowerCase();
    let visible = 0;
    document.querySelectorAll('.team-block').forEach(block => {
        const teamName = block.dataset.team.toLowerCase();
        const rows = block.querySelectorAll('tbody tr');
        let teamVisible = 0;

        rows.forEach(row => {
            const name = row.cells[0].textContent.toLowerCase();
            const show = !query || name.includes(query) || teamName.includes(query);
            row.style.display = show ? '' : 'none';
            if (show) teamVisible++;
        });

        block.style.display = teamVisible > 0 ? '' : 'none';
        if (teamVisible > 0) {
            block.querySelector('.team-body').classList.add('open');
        }
        visible += teamVisible;
    });
    document.getElementById('stats').textContent =
        visible + ' jugador' + (visible !== 1 ? 'es' : '') + ' encontrado' + (visible !== 1 ? 's' : '');
    if (!query) {
        document.getElementById('stats').textContent =
            totalPlayers + ' jugadores, <?= count($teams) ?> equipos';
        document.querySelectorAll('.team-body').forEach(b => b.classList.remove('open'));
        document.querySelectorAll('.team-block').forEach(b => b.style.display = '');
    }
}
</script>

</body>
</html>
