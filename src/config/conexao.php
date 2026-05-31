<?php

// $pdo = new PDO("mysql:host=localhost;dbname=jojo_crud;charset=utf8", "root", ""); $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $pdo = new PDO(
        "mysql:host=banco;port=3306;dbname=jojo_crud;charset=utf8mb4",
        "jojo",
        "jojo123"
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Erro na conexão com o banco: " . $e->getMessage());
}