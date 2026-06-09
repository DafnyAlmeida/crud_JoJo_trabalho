<?php
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
require_once "../../src/functions/upload.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

if (empty($_POST["id_referencia"]) || !filter_var($_POST["id_referencia"], FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=id_invalido");
    exit;
}

$referencia_id = $_POST["id_referencia"];
$parte_id = $_POST["parte_id"] ?? null;

$sql = "SELECT * FROM referencias WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $referencia_id
]);

$referencia = $stmt->fetch(PDO::FETCH_OBJ);

if (!$referencia) {
    header("Location: index.php?status=id_invalido");
    exit;
}

try {
    // Apagando fotos antigas da tabela stand e setando novas
    $imagem = $referencia->imagem;

    if (!empty($_FILES["imagem"]["name"])) {
        deletar_arquivo($referencia->imagem);
        $imagem = salvar_imagem(
            "imagem",
            "referencias",
            $_POST["titulo"]
        );
    }

    // Setando novas informações na tabela personagens
    $sql = "UPDATE referencias SET
        titulo = :titulo,
        descricao = :descricao,
        imagem = :imagem,
        tipo = :tipo
        WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":titulo" => $_POST["titulo"],
        ":descricao" => $_POST["descricao"],
        ":imagem" => $imagem,
        ":tipo" => $_POST["tipo"],
        ":id" => $referencia_id
    ]);

    if ($parte_id) {
        header("Location: index.php?parte_id=" . $parte_id . "&status=update_ok");
    } else {
        header("Location: referencias/index.php?status=update_ok");
    }

    exit;

} catch (Exception $e) {
    echo "Erro ao editar: " . $e->getMessage();
}