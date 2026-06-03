<?php
date_default_timezone_set('Europe/Paris');

$pdo = new PDO('mysql:host=mariadb;dbname=monsite', 'root', 'secret');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$success = false;
$erreur = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($nom) || empty($email) || empty($message)) {
        $erreur = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Adresse email invalide.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO contacts (nom, email, message) VALUES (?, ?, ?)');
        $stmt->execute([$nom, $email, $message]);
        $success = true;
    }
}

// Récupère tous les messages
$messages = $pdo->query('SELECT * FROM contacts ORDER BY date_envoi DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>POWERiti — Contact</title>
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

  .grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
  }

  @media (max-width: 700px) { .grid { grid-template-columns: 1fr; } }

  /* FORMULAIRE */
  .form-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 1.5rem;
  }

  .form-group {
    margin-bottom: 1.25rem;
  }

  label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    margin-bottom: 0.4rem;
    color: #1a1a1a;
  }

  input, textarea {
    width: 100%;
    padding: 0.65rem 0.9rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 0.85rem;
    background: #f5f4f0;
    transition: border-color 0.2s;
    color: #1a1a1a;
  }

  input:focus, textarea:focus {
    outline: none;
    border-color: #f0a500;
    background: #fff;
  }

  textarea { height: 120px; resize: vertical; }

  button {
    width: 100%;
    padding: 0.75rem;
    background: #1a1a1a;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
  }

  button:hover { background: #333; }

  .alert {
    padding: 0.75rem 1rem;
    border-radius: 6px;
    font-size: 0.82rem;
    margin-bottom: 1.25rem;
    font-family: 'IBM Plex Mono', monospace;
  }

  .alert-success {
    background: #f0fdf4;
    border: 1px solid #86efac;
    color: #166534;
  }

  .alert-error {
    background: #fef2f2;
    border: 1px solid #fca5a5;
    color: #991b1b;
  }

  /* MESSAGES */
  .messages-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
  }

  .message-item {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f0efeb;
  }

  .message-item:last-child { border-bottom: none; }

  .message-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 0.4rem;
  }

  .message-nom {
    font-weight: 600;
    font-size: 0.9rem;
  }

  .message-date {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.68rem;
    color: #888;
  }

  .message-email {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.72rem;
    color: #888;
    margin-bottom: 0.4rem;
  }

  .message-texte {
    font-size: 0.82rem;
    color: #444;
    line-height: 1.5;
  }

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
  <a href="contact.php" class="active">Contact</a>
  <a href="index.php">PHP Info</a>
  <a href="db.php">Base de données</a>
  <a href="http://poweriti.local:8080">phpMyAdmin</a>
</nav>

<div class="page-header">
  <div class="header-inner">
    <h1>Contact</h1>
    <p>Envoyer un message — stocké en base de données</p>
  </div>
</div>

<main>

  <div class="grid">

    <!-- FORMULAIRE -->
    <div>
      <p class="section-title">Formulaire</p>
      <div class="form-card">

        <?php if ($success): ?>
          <div class="alert alert-success">Message envoyé et enregistré en base de données.</div>
        <?php endif; ?>

        <?php if ($erreur): ?>
          <div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="POST" action="contact.php">
          <div class="form-group">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" placeholder="Votre nom" required>
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="votre@email.com" required>
          </div>
          <div class="form-group">
            <label for="message">Message</label>
            <textarea id="message" name="message" placeholder="Votre message..." required></textarea>
          </div>
          <button type="submit">Envoyer le message</button>
        </form>

      </div>
    </div>

    <!-- MESSAGES REÇUS -->
    <div>
      <p class="section-title">Messages reçus (<?= count($messages) ?>)</p>
      <div class="messages-card">
        <?php if (empty($messages)): ?>
          <div class="empty">Aucun message pour le moment.</div>
        <?php else: ?>
          <?php foreach ($messages as $msg): ?>
            <div class="message-item">
              <div class="message-header">
                <div class="message-nom"><?= htmlspecialchars($msg['nom']) ?></div>
                <div class="message-date"><?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?></div>
              </div>
              <div class="message-email"><?= htmlspecialchars($msg['email']) ?></div>
              <div class="message-texte"><?= htmlspecialchars($msg['message']) ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>

</main>

<footer>
  <span class="footer-text">POWERiti — Formulaire de contact</span>
  <div class="stack">
    <span>Nginx</span>
    <span>PHP-FPM</span>
    <span>MariaDB</span>
    <span>Docker</span>
  </div>
</footer>

</body>
</html>
