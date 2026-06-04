<?php
date_default_timezone_set('Europe/Paris');
// Uptime du serveur
$uptime = shell_exec('uptime -p');

// RAM
$ram_total = shell_exec('grep MemTotal /proc/meminfo | awk \'{print $2}\'');
$ram_libre = shell_exec('grep MemAvailable /proc/meminfo | awk \'{print $2}\'');
$ram_total = round($ram_total / 1024);
$ram_libre = round($ram_libre / 1024);
$ram_utilisee = $ram_total - $ram_libre;
$ram_pourcent = round(($ram_utilisee / $ram_total) * 100);

// Disque
$disque_total = disk_total_space('/');
$disque_libre = disk_free_space('/');
$disque_utilisee = $disque_total - $disque_libre;
$disque_pourcent = round(($disque_utilisee / $disque_total) * 100);
$disque_total_go = round($disque_total / 1073741824);
$disque_utilisee_go = round($disque_utilisee / 1073741824);

// Charge CPU
$cpu = sys_getloadavg();
$cpu_charge = round($cpu[0] * 100);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta http-equiv="refresh" content="10">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>POWERiti — Monitoring</title>
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

  /* STATS GRID */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1px;
    background: #ddd;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 2.5rem;
  }

  .stat {
    background: #fff;
    padding: 1.25rem 1.5rem;
  }

  .stat-value {
    font-size: 1.8rem;
    font-weight: 600;
    line-height: 1;
    margin-bottom: 0.3rem;
  }

  .stat-label {
    font-size: 0.78rem;
    color: #888;
    font-family: 'IBM Plex Mono', monospace;
  }

  .ok { color: #10b981; }
  .warn { color: #f0a500; }
  .danger { color: #ef4444; }

  /* BARRES */
  .metrics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2.5rem;
  }

  @media (max-width: 700px) {
    .metrics-grid { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
  }

  .metric-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 1.5rem;
  }

  .metric-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 1rem;
  }

  .metric-title {
    font-weight: 600;
    font-size: 0.9rem;
  }

  .metric-value {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.85rem;
    color: #888;
  }

  .progress-bar {
    height: 8px;
    background: #f0efeb;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.75rem;
  }

  .progress-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
  }

  .fill-ok { background: #10b981; }
  .fill-warn { background: #f0a500; }
  .fill-danger { background: #ef4444; }

  .metric-detail {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.72rem;
    color: #888;
  }

  /* INFOS SYSTÈME */
  .info-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 2.5rem;
  }

  .info-row {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    border-bottom: 1px solid #f0efeb;
  }

  .info-row:last-child { border-bottom: none; }

  .info-key {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.75rem;
    color: #888;
    width: 200px;
    flex-shrink: 0;
  }

  .info-val {
    font-size: 0.85rem;
    font-weight: 500;
  }

  /* FOOTER */
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
  <a href="monitoring.php" class="active">Monitoring</a>
  <a href="contact.php">Contact</a>
  <a href="logs.php">Logs</a>
  <a href="index.php">PHP Info</a>
  <a href="db.php">Base de données</a>
  <a href="http://poweriti.local:8080">phpMyAdmin</a>
</nav>

<div class="page-header">
  <div class="header-inner">
    <div>
      <h1>Monitoring</h1>
      <p>État du serveur en temps réel</p>
    </div>
    <div class="refresh-info">Actualisation toutes les 10 secondes</div>
  </div>
</div>

<main>

  <!-- STATS -->
  <p class="section-title">Résumé</p>
  <div class="stats-grid">
    <div class="stat">
      <div class="stat-value <?= $ram_pourcent > 80 ? 'danger' : ($ram_pourcent > 60 ? 'warn' : 'ok') ?>">
        <?= $ram_pourcent ?>%
      </div>
      <div class="stat-label">RAM utilisée</div>
    </div>
    <div class="stat">
      <div class="stat-value <?= $disque_pourcent > 80 ? 'danger' : ($disque_pourcent > 60 ? 'warn' : 'ok') ?>">
        <?= $disque_pourcent ?>%
      </div>
      <div class="stat-label">Disque utilisé</div>
    </div>
    <div class="stat">
      <div class="stat-value <?= $cpu_charge > 80 ? 'danger' : ($cpu_charge > 60 ? 'warn' : 'ok') ?>">
        <?= $cpu_charge ?>%
      </div>
      <div class="stat-label">Charge CPU</div>
    </div>
    <div class="stat">
      <div class="stat-value ok">OK</div>
      <div class="stat-label">Statut serveur</div>
    </div>
  </div>

  <!-- MÉTRIQUES -->
  <p class="section-title">Métriques détaillées</p>
  <div class="metrics-grid">

    <!-- RAM -->
    <div class="metric-card">
      <div class="metric-header">
        <div class="metric-title">Mémoire RAM</div>
        <div class="metric-value"><?= $ram_pourcent ?>%</div>
      </div>
      <div class="progress-bar">
        <div class="progress-fill <?= $ram_pourcent > 80 ? 'fill-danger' : ($ram_pourcent > 60 ? 'fill-warn' : 'fill-ok') ?>"
             style="width: <?= $ram_pourcent ?>%"></div>
      </div>
      <div class="metric-detail"><?= $ram_utilisee ?> Mo utilisés / <?= $ram_total ?> Mo total</div>
    </div>

    <!-- DISQUE -->
    <div class="metric-card">
      <div class="metric-header">
        <div class="metric-title">Espace disque</div>
        <div class="metric-value"><?= $disque_pourcent ?>%</div>
      </div>
      <div class="progress-bar">
        <div class="progress-fill <?= $disque_pourcent > 80 ? 'fill-danger' : ($disque_pourcent > 60 ? 'fill-warn' : 'fill-ok') ?>"
             style="width: <?= $disque_pourcent ?>%"></div>
      </div>
      <div class="metric-detail"><?= $disque_utilisee_go ?> Go utilisés / <?= $disque_total_go ?> Go total</div>
    </div>

    <!-- CPU -->
    <div class="metric-card">
      <div class="metric-header">
        <div class="metric-title">Charge CPU</div>
        <div class="metric-value"><?= $cpu_charge ?>%</div>
      </div>
      <div class="progress-bar">
        <div class="progress-fill <?= $cpu_charge > 80 ? 'fill-danger' : ($cpu_charge > 60 ? 'fill-warn' : 'fill-ok') ?>"
             style="width: <?= min($cpu_charge, 100) ?>%"></div>
      </div>
      <div class="metric-detail">Charge moyenne sur 1 minute</div>
    </div>

  </div>

  <!-- INFOS SYSTÈME -->
  <p class="section-title">Informations système</p>
  <div class="info-card">
    <div class="info-row">
      <div class="info-key">Uptime</div>
      <div class="info-val"><?= trim($uptime) ?></div>
    </div>
    <div class="info-row">
      <div class="info-key">Système</div>
      <div class="info-val"><?= php_uname('s') ?> <?= php_uname('r') ?></div>
    </div>
    <div class="info-row">
      <div class="info-key">Hostname</div>
      <div class="info-val"><?= gethostname() ?></div>
    </div>
    <div class="info-row">
      <div class="info-key">Version PHP</div>
      <div class="info-val"><?= phpversion() ?></div>
    </div>
    <div class="info-row">
      <div class="info-key">Dernière actualisation</div>
      <div class="info-val"><?= date('d/m/Y H:i:s') ?></div>
    </div>
  </div>

</main>

<footer>
  <span class="footer-text">POWERiti — Monitoring interne</span>
  <div class="stack">
    <span>Nginx</span>
    <span>PHP-FPM</span>
    <span>Docker</span>
  </div>
</footer>

</body>
</html>
