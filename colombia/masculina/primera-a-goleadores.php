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

<table id="tabla">
<thead>
<tr>
    <th>Jugador</th>
    <th>Equipo</th>
    <th>Goles</th>
    <th>PJ</th>
    <th>Media</th>
</tr>
</thead>
<tbody></tbody>
</table>

<div class="footer">Fuente: WinSports / Dimayor</div>

<script>
fetch("https://www.winsports.co/api/rankings/player?tournamentId=5l22b8pqde1bdxk6377auk3ro&stat=Goles&competitionId=2ty8ihceabty8yddmu31iuuej")
.then(r => r.json())
.then(json => {
    const tbody = document.querySelector("#tabla tbody");
    json.data.slice(0,10).forEach((p, i) => {
        const media = (p.value / p.matchesPlayed).toFixed(2);
        const tr = document.createElement("tr");
        if (i === 0) tr.className = "top1";
        else if (i === 1) tr.className = "top2";
        else if (i === 2) tr.className = "top3";

        tr.innerHTML = `
            <td><strong>${p.player.name}</strong></td>
            <td>${p.team.name}</td>
            <td>${p.value}</td>
            <td>${p.matchesPlayed}</td>
            <td>${media}</td>
        `;
        tbody.appendChild(tr);
    });
});
</script>

</body>
</html>
