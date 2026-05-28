<?php
try {
    $pdo = new PDO(
        'mysql:host=mariadb;dbname=monsite',
        'root',
        'secret'
    );

    echo "<h1>Connexion à MariaDB réussie !</h1>";

    $pdo->exec("CREATE TABLE IF NOT EXISTS utilisateurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100),
        email VARCHAR(100)
    )");

$count = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
if ($count ==0) {
    $pdo->exec("INSERT INTO utilisateurs (nom, email) 
        VALUES ('Alice', 'alice@mail.com'),
               ('Bob', 'bob@mail.com')");
}

    $stmt = $pdo->query("SELECT * FROM utilisateurs");
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nom</th><th>Email</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['nom']}</td>
            <td>{$row['email']}</td>
        </tr>";
    }
    echo "</table>";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
