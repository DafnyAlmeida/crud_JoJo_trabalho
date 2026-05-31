<?php
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
require_once "../../src/functions/upload.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

if (empty($_POST["id_personagem"]) || !filter_var($_POST["id_personagem"], FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=id_invalido");
    exit;
}

$personagem_id = $_POST["id_personagem"];
$parte_id = $_POST["parte_id"] ?? null;

$sql = "SELECT * FROM personagens WHERE id = :id LIMIT 1";
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
    // Apagando fotos antigas da tabela stand e setando novas
    $foto_anime = $personagem->foto_anime;
    $foto_manga = $personagem->foto_manga;
    $foto_catalogo = $personagem->foto_catalogo;
    $foto_biografia = $personagem->foto_catalogo;

    if (!empty($_FILES["foto_anime"]["name"])) {
        deletar_arquivo($personagem->foto_anime);
        $foto_anime = salvar_imagem(
            "foto_anime",
            "stands",
            $_POST["nome"]
        );
    }

    if (!empty($_FILES["foto_manga"]["name"])) {
        deletar_arquivo($personagem->foto_manga);
        $foto_manga = salvar_imagem(
            "foto_manga",
            "stands",
            $_POST["nome"]
        );
    }

    if (!empty($_FILES["foto_catalogo"]["name"])) {
        deletar_arquivo($personagem->foto_catalogo);
        $foto_catalogo = salvar_imagem(
            "foto_catalogo",
            "stands",
            $_POST["nome"]
        );
    }

    if (!empty($_FILES["foto_biografia"]["name"])) {
        deletar_arquivo($personagem->foto_biografia);
        $foto_biografia = salvar_imagem(
            "foto_biografia",
            "stands",
            $_POST["nome"]
        );
    }

    // Setando novas informações na tabela personagens
    $sql = "UPDATE personagens SET
        nome = :nome,
        biografia = :biografia,
        foto_anime = :foto_anime,
        foto_manga = :foto_manga,
        foto_catalogo = :foto_catalogo,
        foto_biografia = :foto_biografia,
        infor_gerais = :infor_gerais
        WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":nome" => $_POST["nome"],
        ":infor_gerais" => $_POST["infor_gerais"],
        ":foto_anime" => $foto_anime,
        ":foto_manga" => $foto_manga,
        ":foto_biografia" => $foto_biografia,
        ":foto_catalogo" => $foto_catalogo,
        ":biografia" => $_POST["biografia"],
        ":id" => $personagem_id
    ]);

    if ($parte_id) {
        header("Location: index.php?parte_id=" . urlencode($parte_id) . "&status=update_ok");
    } else {
        header("Location: index.php?status=update_ok");
    }

    exit;

} catch (Exception $e) {
    echo "Erro ao editar: " . $e->getMessage();
}