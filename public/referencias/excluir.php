<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
include_once "../../src/functions/upload.php";

if (empty($_GET["id_referencia"]) || !filter_var($_GET["id_referencia"], FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=id_invalido");
    exit;
}

$id_referencia = $_GET["id_referencia"];
$parte_id = $_GET["parte_id"];

if (!$parte_id) {
    header("Location: index.php");
    exit;
}

$sql = "SELECT imagem
        FROM referencias
        WHERE id = :id
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $id_referencia
]);

$referencia = $stmt->fetch(PDO::FETCH_OBJ);

if (!$referencia) {
    header("Location: index.php?status=id_invalido");
    exit;
}

try {
    if (!empty($referencia->imagem)) {
        // Apaga a pasta so stand do uploads
        $pasta_referencia =
            "../" . dirname($referencia->imagem);

        deletar_pasta($pasta_referencia);
    }

    $sql = "DELETE FROM referencias WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":id" => $id_referencia
    ]);

    header("Location: index.php?parte_id=" . urlencode($parte_id) . "&status=delete_ok");
    exit;

} catch (Exception $e) {
    echo $e->getMessage();
}