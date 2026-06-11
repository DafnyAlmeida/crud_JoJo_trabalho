<?php
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
require_once "../../src/functions/upload.php";
require_once "../../src/functions/gerais.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function converterTamanhoPhpParaBytesStands(string $valor): int
{
    $valor = trim($valor);

    if ($valor === "") {
        return 0;
    }

    $ultimo = strtolower($valor[strlen($valor) - 1]);
    $numero = (int) $valor;

    return match ($ultimo) {
        "g" => $numero * 1024 * 1024 * 1024,
        "m" => $numero * 1024 * 1024,
        "k" => $numero * 1024,
        default => $numero
    };
}

function voltar_para_editar_stand($stand_id, $parte_id = null): void
{
    $url = "editar.php?id_stand=" . urlencode((string) $stand_id);

    if (!empty($parte_id)) {
        $url .= "&parte_id=" . urlencode((string) $parte_id);
    }

    header("Location: " . $url);
    exit;
}

function temArquivoNovo(string $campo): bool
{
    return isset($_FILES[$campo])
        && isset($_FILES[$campo]["error"])
        && $_FILES[$campo]["error"] !== UPLOAD_ERR_NO_FILE
        && !empty($_FILES[$campo]["name"]);
}

function validarUploadOpcional(string $campo, string $nomeBonito): void
{
    if (!isset($_FILES[$campo]) || $_FILES[$campo]["error"] === UPLOAD_ERR_NO_FILE) {
        return;
    }

    if ($_FILES[$campo]["error"] === UPLOAD_ERR_INI_SIZE || $_FILES[$campo]["error"] === UPLOAD_ERR_FORM_SIZE) {
        throw new Exception("A imagem {$nomeBonito} é grande demais. Envie uma imagem menor.");
    }

    if ($_FILES[$campo]["error"] === UPLOAD_ERR_PARTIAL) {
        throw new Exception("A imagem {$nomeBonito} foi enviada pela metade. Tente novamente.");
    }

    if ($_FILES[$campo]["error"] !== UPLOAD_ERR_OK) {
        throw new Exception("Erro ao enviar {$nomeBonito}. Tente novamente.");
    }
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$stand_id = $_POST["id_stand"] ?? $_GET["id_stand"] ?? null;
$parte_id = $_POST["parte_id"] ?? $_GET["parte_id"] ?? null;

if (!$stand_id || !filter_var($stand_id, FILTER_VALIDATE_INT)) {
    $_SESSION["erro"] = "Stand inválido.";
    header("Location: index.php?status=erro");
    exit;
}

$sql = "SELECT * FROM stands WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $stand_id
]);

$stand = $stmt->fetch(PDO::FETCH_OBJ);

if (!$stand) {
    $_SESSION["erro"] = "Stand não encontrado.";
    header("Location: index.php?status=erro");
    exit;
}

$limitePost = converterTamanhoPhpParaBytesStands(ini_get("post_max_size"));
$tamanhoEnviado = (int) ($_SERVER["CONTENT_LENGTH"] ?? 0);

if ($limitePost > 0 && $tamanhoEnviado > $limitePost) {
    $_SESSION["erro"] = "As imagens são grandes demais. Envie imagens menores. O limite atual do PHP é " . ini_get("post_max_size") . ".";

    voltar_para_editar($stand_id, $parte_id);
}

$arquivosNovos = [];
$arquivosAntigosParaApagar = [];

try {
    if (empty($_POST["nome"])) {
        throw new Exception("Preencha o nome do stand.");
    }

    if (empty($_POST["personagem_id"])) {
        throw new Exception("Selecione um personagem.");
    }

    if (empty($_POST["tipo"])) {
        throw new Exception("Selecione o tipo do stand.");
    }

    $foto_anime = $stand->foto_anime;
    $foto_manga = $stand->foto_manga;
    $foto_catalogo = $stand->foto_catalogo;

    validarUploadOpcional("foto_anime", "foto do anime");
    validarUploadOpcional("foto_manga", "foto do mangá");
    validarUploadOpcional("foto_catalogo", "foto do catálogo");

    if (temArquivoNovo("foto_anime")) {
        $foto_anime_nova = salvar_imagem(
            "foto_anime",
            "stands",
            $_POST["nome"]
        );

        $arquivosNovos[] = $foto_anime_nova;
        $arquivosAntigosParaApagar[] = $stand->foto_anime;
        $foto_anime = $foto_anime_nova;
    }

    if (temArquivoNovo("foto_manga")) {
        $foto_manga_nova = salvar_imagem(
            "foto_manga",
            "stands",
            $_POST["nome"]
        );

        $arquivosNovos[] = $foto_manga_nova;
        $arquivosAntigosParaApagar[] = $stand->foto_manga;
        $foto_manga = $foto_manga_nova;
    }

    if (temArquivoNovo("foto_catalogo")) {
        $foto_catalogo_nova = salvar_imagem(
            "foto_catalogo",
            "stands",
            $_POST["nome"]
        );

        $arquivosNovos[] = $foto_catalogo_nova;
        $arquivosAntigosParaApagar[] = $stand->foto_catalogo;
        $foto_catalogo = $foto_catalogo_nova;
    }

    $pdo->beginTransaction();

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
        ":descricao" => $_POST["descricao"] ?? "",
        ":foto_anime" => $foto_anime,
        ":foto_manga" => $foto_manga,
        ":foto_catalogo" => $foto_catalogo,
        ":infor_gerais" => $_POST["detalhado"] ?? "",
        ":habilidade_texto_geral" => $_POST["detalhado"] ?? "",
        ":tipo" => $_POST["tipo"],
        ":id" => $stand_id
    ]);

    $sql = "DELETE FROM stand_habilidades WHERE stand_id = :stand_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":stand_id" => $stand_id
    ]);

    if (!empty($_POST["habilidade_nome"]) && is_array($_POST["habilidade_nome"])) {
        foreach ($_POST["habilidade_nome"] as $index => $nome_habilidade) {

            $nome_habilidade = trim($nome_habilidade);

            if ($nome_habilidade === "") {
                continue;
            }

            $descricao_habilidade = $_POST["habilidade_descricao"][$index] ?? "";
            $tipo_habilidade = $_POST["habilidade_tipo"][$index] ?? "";

            $imagem_habilidade = $_POST["habilidade_imagem_antiga"][$index] ?? "";
            $diagrama_habilidade = $_POST["habilidade_diagrama_antigo"][$index] ?? "";

            if (!empty($_FILES["habilidade_imagem"]["name"][$index])) {
                $imagem_nova = salvar_imagem_array(
                    "habilidade_imagem",
                    $index,
                    "habilidades",
                    $_POST["nome"]
                );

                $arquivosNovos[] = $imagem_nova;

                if (!empty($imagem_habilidade)) {
                    $arquivosAntigosParaApagar[] = $imagem_habilidade;
                }

                $imagem_habilidade = $imagem_nova;
            }

            if (!empty($_FILES["habilidade_diagrama_imagem"]["name"][$index])) {
                $diagrama_novo = salvar_imagem_array(
                    "habilidade_diagrama_imagem",
                    $index,
                    "diagramas",
                    $_POST["nome"]
                );

                $arquivosNovos[] = $diagrama_novo;

                if (!empty($diagrama_habilidade)) {
                    $arquivosAntigosParaApagar[] = $diagrama_habilidade;
                }

                $diagrama_habilidade = $diagrama_novo;
            }

            $sql = "INSERT INTO stand_habilidades
            (stand_id, nome, descricao, imagem, forca, tipo)
            VALUES
            (:stand_id, :nome, :descricao, :imagem, :forca, :tipo)";

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

    $pdo->commit();

    foreach ($arquivosAntigosParaApagar as $arquivoAntigo) {
        if (!empty($arquivoAntigo)) {
            deletar_arquivo($arquivoAntigo);
        }
    }

    if ($parte_id) {
        header("Location: index.php?parte_id=" . urlencode((string) $parte_id) . "&status=editado");
    } else {
        header("Location: index.php?status=editado");
    }

    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    foreach ($arquivosNovos as $arquivoNovo) {
        if (!empty($arquivoNovo)) {
            deletar_arquivo($arquivoNovo);
        }
    }

    $_SESSION["erro"] = "Não foi possível editar o stand. Verifique os dados e tente novamente.";

    voltar_para_editar_stand($stand_id, $parte_id);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    foreach ($arquivosNovos as $arquivoNovo) {
        if (!empty($arquivoNovo)) {
            deletar_arquivo($arquivoNovo);
        }
    }

    $_SESSION["erro"] = $e->getMessage();

    voltar_para_editar_stand($stand_id, $parte_id);
}