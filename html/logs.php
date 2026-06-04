<?php
date_default_timezone_set('Europe/Paris');

$log_file = '/tmp/access.log';
$error_file = '/tmp/error.log';
$nb_lignes = 50;

// Lecture des logs d'accès
function lire_logs($fichier, $nb) {
    if (!file_exists($fichier)) return [];
    $lignes = file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return array_slice(array_reverse($lignes), 0, $nb);
}

// Parse une ligne de log Nginx
function parse_log($ligne) {
    $pattern = '/^(\S+) - \S+ \[([^\]]+)\] "(\S+) (\S+)[^"]*" (\d+) (\d+)/';
    if (preg_match($pattern, $ligne, $m)) {
        return [
            'ip'     => $m[1],
            'date'   => $m[2],
            'method' => $m[3],
            'url'    => $m[4],
            'code'   => $m[5],
            'taille' => $m[6]
        ];
    }
    return null;
}

$access_logs = lire_logs($log_file, $nb_lignes);
$error_logs = lire_logs($error_file, 20);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta http-equiv="refresh" content="15">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>POWERiti — Logs</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'IBM Plex Sans', sans-serif;
    background: #f5f4f0;
    color: #1a1a1a;
    min-height: 100vh;
  }

  nav {
    background: #1a1a1a;
    padding: 0 2rem;
    display: flex;
    align-items: center;
    height: 52px;
    position: sticky;
    top: 0;
    z-index: 100;
  }

  .nav-brand {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.85rem;
    font-weight: 600;
    color: #fff;
    margin-right: 2rem;
    letter-spacing: 0.05em;
  }

  .nav-brand span { color: #f0a500; }

  nav a {
    display: flex;
    align-items: center;
    padding: 0 1.25rem;
    height: 100%;
    font-size: 0.82rem;
    font-weight: 500;
    color: #888;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    transition: color 0.2s, border-color 0.2s;
    white-space: nowrap;
  }

  nav a:hover { color: #fff; }
  nav a.active { color: #fff; border-bottom-color: #f0a500; }

  .page-header {
    background: #1a1a1a;
    color: #fff;
    padding: 3rem 2rem 2.5rem;
    border-bottom: 3px solid #f0a500;
  }

  .header-inner {
    max-width: 1100px;
    margin: 0 auto;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
  }

  .page-header h1 {
    font-size: 2rem;
    font-weight: 600;
    letter-spacing: -0.02em;
    margin-bottom: 0.4rem;
  }

  .page-header p {
    color: #888;
    font-size: 0.9rem;
    font-family: 'IBM Plex Mono', monospace;
  }

  .refresh-info {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.75rem;
    color: #888;
  }

  main {
    max-width: 1100px;
    margin: 2.5rem auto;
    padding: 0 2rem;
  }

  .section-title {
    font-size: 0.7rem;
    font-family: 'IBM Plex Mono', monospace;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #888;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #ddd;
  }

  /* TABLE LOGS */
  .logs-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 2.5rem;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8rem;
  }

  thead {
    background: #1a1a1a;
    color: #fff;
  }

  th {
    padding: 0.75rem 1rem;
    text-align: left;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.7rem;
    letter-spacing: 0.05em;
    font-weight: 600;
  }

  td {
    padding: 0.6rem 1rem;
    border-bottom: 1px solid #f0efeb;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.75rem;
  }

  tr:last-child td { border-bottom: none; }
  tr:hover td { background: #fafaf8; }

  /* CODES HTTP */
  .code {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.72rem;
  }

  .code-200 { background: #f0fdf4; color: #166534; }
  .code-301, .code-302 { background: #eff6ff; color: #1d4ed8; }
  .code-404 { background: #fff7ed; color: #9a3412; }
  .code-500 { background: #fef2f2; color: #991b1b; }
  .code-other { background: #f5f4f0; color: #888; }

  /* ERREURS */
  .error-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 2.5rem;
  }

  .error-line {
    padding: 0.6rem 1.25rem;
    border-bottom: 1px solid #f0efeb;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.72rem;
    color: #ef4444;
    word-break: break-all;
  }

  .error-line:last-child { border-bottom: none; }

  .empty {
    padding: 2rem;
    text-align: center;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.8rem;
    color: #888;
  }

  footer {
    border-top: 1px solid #ddd;
    padding: 1.5rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    max-width: 1100px;
    margin: 0 auto;
  }

  .footer-text {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.72rem;
    color: #aaa;
  }

  .stack { display: flex; gap: 0.4rem; }

  .stack span {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.65rem;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    border: 1px solid #ddd;
    color: #888;
  }
</style>
</head>
<body>

<nav>
  <div class="nav-brand">POWER<span>iti</span></div>
  <a href="index.html">Réseau</a>
  <a href="equipe.html">Équipe</a>
  <a href="monitoring.php">Monitoring</a>
  <a href="contact.php">Contact</a>
  <a href="logs.php" class="active">Logs</a>
  <a href="index.php">PHP Info</a>
  <a href="db.php">Base de données</a>
  <a href="http://poweriti.local:8080">phpMyAdmin</a>
</nav>

<div class="page-header">
  <div class="header-inner">
    <div>
      <h1>Logs Nginx</h1>
      <p>50 dernières requêtes — actualisation toutes les 15 secondes</p>
    </div>
    <div class="refresh-info">Dernière MAJ : <?= date('H:i:s') ?></div>
  </div>
</div>

<main>

  <!-- LOGS ACCÈS -->
  <p class="section-title">Logs d'accès (<?= count($access_logs) ?> entrées)</p>
  <div class="logs-card">
    <?php if (empty($access_logs)): ?>
      <div class="empty">Aucun log disponible.</div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>IP</th>
            <th>Date</th>
            <th>Méthode</th>
            <th>URL</th>
            <th>Code</th>
            <th>Taille</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($access_logs as $ligne): ?>
            <?php $log = parse_log($ligne); if (!$log) continue; ?>
            <tr>
              <td><?= htmlspecialchars($log['ip']) ?></td>
              <td><?= htmlspecialchars($log['date']) ?></td>
              <td><?= htmlspecialchars($log['method']) ?></td>
              <td><?= htmlspecialchars($log['url']) ?></td>
              <td>
                <span class="code code-<?= in_array($log['code'], ['200','301','302','404','500']) ? $log['code'] : 'other' ?>">
                  <?= $log['code'] ?>
                </span>
              </td>
              <td><?= number_format($log['taille']) ?> o</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <!-- LOGS ERREURS -->
  <p class="section-title">Logs d'erreurs</p>
  <div class="error-card">
    <?php if (empty($error_logs)): ?>
      <div class="empty">Aucune erreur enregistrée.</div>
    <?php else: ?>
      <?php foreach ($error_logs as $ligne): ?>
        <div class="error-line"><?= htmlspecialchars($ligne) ?></div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</main>

<footer>
  <span class="footer-text">POWERiti — Logs Nginx · Usage confidentiel</span>
  <div class="stack">
    <span>Nginx</span>
    <span>PHP-FPM</span>
    <span>Docker</span>
  </div>
</footer>

</body>
</html>
