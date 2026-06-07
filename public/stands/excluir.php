<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
include_once "../../src/functions/upload.php";

if (empty($_GET["id_stand"]) || !filter_var($_GET["id_stand"], FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=id_invalido");
    exit;
}

$stand_id = $_GET["id_stand"];
$parte_id = $_GET["parte_id"];

if (!$parte_id) {
    header("Location: index.php");
    exit;
}

$sql = "SELECT foto_anime
        FROM stands
        WHERE id = :id
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $stand_id
]);

$stand = $stmt->fetch(PDO::FETCH_OBJ);

if (!$stand) {
    header("Location: index.php?status=id_invalido");
    exit;
}

$sql = "SELECT imagem, forca
        FROM stand_habilidades
        WHERE stand_id = :stand_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":stand_id" => $stand_id
]);

$habilidades = $stmt->fetchAll(PDO::FETCH_OBJ);

try {

    if (!empty($stand->foto_anime)) {
        // Apaga a pasta so stand do upload
        deletar_pasta($stand->foto_anime);
    }

    foreach ($habilidades as $habilidade) {

        // Apaga a pasta habilidades do uploads
        if (!empty($habilidade->imagem)) {

            deletar_pasta($habilidade->imagem);
        }

        // Apaga a pasta diagramas do uploads
        if (!empty($habilidade->forca)) {

            deletar_pasta($habilidade->forca);
        }
    }

    // Deleta as habilidades e o stand do banco
    $sql = "DELETE FROM stand_habilidades
            WHERE stand_id = :stand_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":stand_id" => $stand_id
    ]);

    $sql = "DELETE FROM stands
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":id" => $stand_id
    ]);

    header("Location: index.php?parte_id=" . $parte_id . "&status=delete_ok");
    exit;

} catch (Exception $e) {
    echo $e->getMessage();
}