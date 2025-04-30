<?php

$dbFile = __DIR__ . '/../database/database.sqlite';

// Sprawdź połączenie z SQLite
try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>PHP + SQLite działa!</h1>";
} catch (PDOException $e) {
    echo "<h1>Błąd połączenia z bazą danych:</h1>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
