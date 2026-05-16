<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
require_once "../../src/functions/upload.php";

if (!isset($_GET["id_stand"])) {
    header("Location: index.php?status=id_vazio");
    exit;
}

$stand_id = $_GET["id_stand"];

if (!filter_var($stand_id, FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=id_invalido");
    exit;
}

$sql = "SELECT id, foto_anime FROM stands WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $stand_id
]);

if (!$stmt->fetch()) {
    header("Location: ../index.php?status=id_invalido");
    exit;
}

$stand = $stmt->fetch(PDO::FETCH_OBJ);

$pasta = dirname($stand->foto_anime); 
deletar_pasta("../" . $pasta);

$sql = "DELETE FROM stands WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $stand_id
]);

header("Location: index.php?status=delete_ok");
exit;
?>