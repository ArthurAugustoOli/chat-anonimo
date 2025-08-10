<?php
$host = '';
$db = '';
$user = '';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // ✅ Define o fuso horário para -03:00 (horário de Brasília)
    $pdo->exec("SET time_zone = '-03:00'");

} catch (\PDOException $e) {
    die('Erro na conexão: ' . $e->getMessage());
}
