<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
include_once "../../src/functions/upload.php";

if (empty($_GET["id_personagem"]) || !filter_var($_GET["id_personagem"], FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=id_invalido");
    exit;
}

$personagem_id = $_GET["id_personagem"];
$parte_id = $_GET["parte_id"];

if (!$parte_id) {
    header("Location: index.php");
    exit;
}

$sql = "SELECT foto_anime
        FROM personagens
        WHERE id = :id
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $personagem_id
]);

$personagem = $stmt->fetch(PDO::FETCH_OBJ);

if (!$personagem) {
    header("Location: index.php?status=id_invalido");
    exit;
}

try {
    if (!empty($personagem->foto_anime)) {
        
        deletar_pasta($personagem->foto_anime);
    }

    $sql = "DELETE FROM personagens WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":id" => $personagem_id
    ]);

    $sql = "DELETE FROM personagens_partes WHERE personagem_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":id" => $personagem_id
    ]);

    header("Location: index.php?parte_id=" . urlencode($parte_id) . "&status=delete_ok");
    exit;

} catch (Exception $e) {
    echo $e->getMessage();
}