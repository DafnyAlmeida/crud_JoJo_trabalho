<?php
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
require_once "../../src/functions/upload.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

if (empty($_POST["id_stand"]) || !filter_var($_POST["id_stand"], FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=id_invalido");
    exit;
}

$stand_id = $_POST["id_stand"];
$parte_id = $_POST["parte_id"] ?? null;

$sql = "SELECT * FROM stands WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $stand_id
]);

$stand = $stmt->fetch(PDO::FETCH_OBJ);

if (!$stand) {
    header("Location: index.php?status=id_invalido");
    exit;
}

try {
    // Apagando fotos antigas da tabela stand e setando novas
    $foto_anime = $stand->foto_anime;
    $foto_manga = $stand->foto_manga;
    $foto_catalogo = $stand->foto_catalogo;

    if (!empty($_FILES["foto_anime"]["name"])) {
        deletar_arquivo($stand->foto_anime);
        $foto_anime = salvar_imagem(
            "foto_anime",
            "stands",
            $_POST["nome"]
        );
    }

    if (!empty($_FILES["foto_manga"]["name"])) {
        deletar_arquivo($stand->foto_manga);
        $foto_manga = salvar_imagem(
            "foto_manga",
            "stands",
            $_POST["nome"]
        );
    }

    if (!empty($_FILES["foto_catalogo"]["name"])) {
        deletar_arquivo($stand->foto_catalogo);
        $foto_catalogo = salvar_imagem(
            "foto_catalogo",
            "stands",
            $_POST["nome"]
        );
    }

    // Setando novas informações na tabela stand
    $sql = "UPDATE stands SET
        personagem_id = :personagem_id,
        nome = :nome,
        descricao = :descricao,
        foto_anime = :foto_anime,
        foto_manga = :foto_manga,
        foto_catalogo = :foto_catalogo,
        infor_gerais = :infor_gerais,
        habilidade_texto_geral = :habilidade_texto_geral,
        tipo = :tipo
        WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":personagem_id" => $_POST["personagem_id"],
        ":nome" => $_POST["nome"],
        ":descricao" => $_POST["descricao"],
        ":foto_anime" => $foto_anime,
        ":foto_manga" => $foto_manga,
        ":foto_catalogo" => $foto_catalogo,
        ":infor_gerais" => $_POST["detalhado"],
        ":habilidade_texto_geral" => $_POST["detalhado"],
        ":tipo" => $_POST["tipo"],
        ":id" => $stand_id
    ]);

    // Deletando informações da tabela habilidades 
    $sql = "DELETE FROM stand_habilidades WHERE stand_id = :stand_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":stand_id" => $stand_id
    ]);

    if (!empty($_POST["habilidade_nome"])) {
        // Percorre um array de habilidades
        foreach ($_POST["habilidade_nome"] as $index => $nome_habilidade) {

            // Evita salvar habilidades sem nomes
            if (empty(trim($nome_habilidade))) {
                continue;
            }

            // Pega a descrição, tipo, e as imagens antigas de cada habilidade 
            $descricao_habilidade = $_POST["habilidade_descricao"][$index] ?? "";

            $tipo_habilidade = $_POST["habilidade_tipo"][$index] ?? "";

            $imagem_habilidade = $_POST["habilidade_imagem_antiga"][$index] ?? "";

            $diagrama_habilidade = $_POST["habilidade_diagrama_antigo"][$index] ?? "";

            // Vê se uma nova habilidade foi enviada
            if (!empty($_FILES["habilidade_imagem"]["name"][$index])) {

                // Se enviou ele apaga a imagem antiga
                if (!empty($imagem_habilidade)) {
                    deletar_arquivo($imagem_habilidade);
                }

                // E depois salva/gera um novo caminho para a nova
                $imagem_habilidade = salvar_imagem_array(
                    "habilidade_imagem",
                    $index,
                    "habilidades",
                    $_POST["nome"]
                );
            }

            if (!empty($_FILES["habilidade_diagrama_imagem"]["name"][$index])) {

                if (!empty($diagrama_habilidade)) {
                    deletar_arquivo($diagrama_habilidade);
                }

                $diagrama_habilidade = salvar_imagem_array(
                    "habilidade_diagrama_imagem",
                    $index,
                    "diagramas",
                    $_POST["nome"]
                );
            }

            $sql = "INSERT INTO stand_habilidades
            (stand_id, nome, descricao, imagem, forca, tipo)
            VALUES
            (:stand_id, :nome, :descricao, :imagem, :forca, :tipo)";
            
            // Seta tudo de novo
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":stand_id" => $stand_id,
                ":nome" => $nome_habilidade,
                ":descricao" => $descricao_habilidade,
                ":imagem" => $imagem_habilidade,
                ":forca" => $diagrama_habilidade,
                ":tipo" => $tipo_habilidade
            ]);
        }
    }

    if ($parte_id) {
        header("Location: index.php?parte_id=" . urlencode($parte_id) . "&status=update_ok");
    } else {
        header("Location: index.php?status=update_ok");
    }

    exit;

} catch (Exception $e) {
    echo "Erro ao editar: " . $e->getMessage();
}