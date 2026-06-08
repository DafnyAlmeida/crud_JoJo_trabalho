<?php

require_once __DIR__ . "/env.php";

carregar_env(__DIR__ . "/../../.env");

$host = $_ENV["DB_HOST"] ?? "banco";
$porta = $_ENV["DB_PORT"] ?? "3306";
$banco = $_ENV["DB_DATABASE"] ?? "jojo_crud";
$usuario = $_ENV["DB_USERNAME"] ?? "jojo";
$senha = $_ENV["DB_PASSWORD"] ?? "jojo123";

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$porta};dbname={$banco};charset=utf8mb4",
        $usuario,
        $senha
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $erro) {
    die("Erro ao conectar com o banco: " . $erro->getMessage());
}