<?php
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
require_once "../../src/functions/upload.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php?status=erro");
    exit;
}

if (empty($_POST["id_referencia"]) || !filter_var($_POST["id_referencia"], FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=erro");
    exit;
}

$referencia_id = $_POST["id_referencia"];
$parte_id = $_POST["parte_id"] ?? null;

try {
    $sql = "SELECT * FROM referencias WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":id" => $referencia_id
    ]);

    $referencia = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$referencia) {
        $_SESSION["erro"] = "Referência não encontrada.";
        header("Location: index.php?status=erro");
        exit;
    }

    $imagemAntiga = $referencia->imagem;
    $imagemNova = null;
    $imagemFinal = $imagemAntiga;

    if (!empty($_FILES["imagem"]["name"])) {
        $imagemNova = salvar_imagem(
            "imagem",
            "referencias",
            $_POST["titulo"]
        );

        $imagemFinal = $imagemNova;
    }

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
        ":imagem" => $imagemFinal,
        ":tipo" => $_POST["tipo"],
        ":id" => $referencia_id
    ]);

    if ($imagemNova !== null && !empty($imagemAntiga)) {
        deletar_arquivo($imagemAntiga);
    }

    if ($parte_id) {
        header("Location: index.php?parte_id=" . urlencode($parte_id) . "&status=editado");
    } else {
        header("Location: ../index.php?status=editado");
    }

    exit;

} catch (Exception $e) {
    if (!empty($imagemNova)) {
        deletar_arquivo($imagemNova);
    }

    $_SESSION["erro"] = $e->getMessage();

    voltar_para_editar($referencia_id, $parte_id);
}