<?php 
include_once "../../src/includes/bloqueio.php";
include_once "../../src/config/conexao.php";
require_once "../../src/functions/upload.php";

$parte_id = $_GET["parte_id"] ?? $_POST["parte_id"] ?? null;

if (!$parte_id || !filter_var($parte_id, FILTER_VALIDATE_INT)) {
    header("Location: ../personagens/index.php?status=parte_invalida");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");

    if ($nome === "") {
        die("Erro: nome do personagem não foi enviado.");
    }

    if (!isset($_POST["parte_id"]) || !filter_var($_POST["parte_id"], FILTER_VALIDATE_INT)) {
        die("Erro: parte inválida.");
    }

    foreach (["foto_anime", "foto_manga", "foto_catalogo", "foto_biografia"] as $campoFoto) {
        if (!isset($_FILES[$campoFoto])) {
            die("Erro: o campo {$campoFoto} não chegou ao PHP.");
        }

        if ($_FILES[$campoFoto]["error"] !== UPLOAD_ERR_OK) {
            die("Erro no upload de {$campoFoto}. Código: " . $_FILES[$campoFoto]["error"]);
        }
    }

    try {
        $pdo->beginTransaction();

        $foto_anime = salvar_imagem("foto_anime", "personagens", $nome);
        $foto_manga = salvar_imagem("foto_manga", "personagens", $nome);
        $foto_catalogo = salvar_imagem("foto_catalogo", "personagens", $nome);
        $foto_biografia = salvar_imagem("foto_biografia", "personagens", $nome);

        $sql = "
            INSERT INTO personagens (
                usuario_id,
                nome,
                biografia,
                foto_anime,
                foto_manga,
                foto_catalogo,
                foto_biografia,
                infor_gerais,
                descricao_foto_biografia
            ) VALUES (
                :usuario_id,
                :nome,
                :biografia,
                :foto_anime,
                :foto_manga,
                :foto_catalogo,
                :foto_biografia,
                :infor_gerais,
                :descricao_foto_biografia
            )
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":usuario_id" => $_SESSION["usuario_id"],
            ":nome" => $nome,
            ":biografia" => trim($_POST["biografia"] ?? ""),
            ":foto_anime" => $foto_anime,
            ":foto_manga" => $foto_manga,
            ":foto_catalogo" => $foto_catalogo,
            ":foto_biografia" => $foto_biografia,
            ":infor_gerais" => trim($_POST["infor_gerais"] ?? ""),
            ":descricao_foto_biografia" => trim($_POST["descricao_foto_biografia"] ?? "")
        ]);

        $personagem_id = (int) $pdo->lastInsertId();

        $sql = "
            INSERT INTO personagens_partes (
                personagem_id,
                parte_id,
                idade,
                papel
            ) VALUES (
                :personagem_id,
                :parte_id,
                :idade,
                :papel
            )
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":personagem_id" => $personagem_id,
            ":parte_id" => (int) $_POST["parte_id"],
            ":idade" => (int) $_POST["idade"],
            ":papel" => $_POST["papel"]
        ]);

        $pdo->commit();

        header("Location: index.php?parte_id=" . (int) $_POST["parte_id"] . "&status=salvo");
        exit;

    } catch (Throwable $erro) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        die("<pre>Erro ao salvar personagem:\n" . $erro->getMessage() . "</pre>");
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Adicionar novo personagem</title>
</head>

<body>
    <main>
        <form action="<?= $_SERVER["PHP_SELF"] ?>" method="post" enctype="multipart/form-data" class="max-w-3xl mx-auto p-6 bg-white rounded-xl shadow space-y-6">
        <input type="hidden" name="parte_id" value="<?= htmlspecialchars($parte_id) ?>">

            <!-- ETAPA 1 -->
            <div class="etapa" id="etapa1">
                <h2 class="text-2xl font-bold mb-4">Informações Gerais</h2>

                <label class="block mb-1">Nome do personagem</label>
                <input type="text" name="nome" required class="w-full border rounded-lg p-2 mb-4">

                <label class="block mb-1">Informações gerais</label>
                <textarea name="infor_gerais" class="w-full border rounded-lg p-2 mb-4"></textarea>

                <button type="button" onclick="proximaEtapa(1)" class="bg-purple-600 text-white px-4 py-2 rounded-lg">
                    Próximo
                </button>
            </div>

            <!-- ETAPA 2 -->
            <div class="etapa hidden" id="etapa2">
                <h2 class="text-2xl font-bold mb-4">Fotos e Informações Específicas</h2>

                <label class="block mb-1">Biografia</label>
                <textarea name="biografia" class="w-full border rounded-lg p-2 mb-4"></textarea>

                <label for="idade">Idade</label>
                <input type="number" name="idade" id="idade">

                <label for="papel">Papel</label>
                <select name="papel" id="papel">
                    <option value="vilao">Vilão</option>
                    <option value="protagonista">Protagonista</option>
                    <option value="jojobro">JoJoBro</option>
                </select>

                <label class="block mb-1">Foto do mangá</label>
                <input type="file" name="foto_manga" class="w-full border rounded-lg p-2 mb-4">

                <label class="block mb-1">Foto do anime</label>
                <input type="file" name="foto_anime" class="w-full border rounded-lg p-2 mb-4">

                <label class="block mb-1">Foto para catálogo</label>
                <input type="file" name="foto_catalogo" class="w-full border rounded-lg p-2 mb-4">

                <label class="block mb-1">Foto para biografia</label>
                <input type="file" name="foto_biografia" class="w-full border rounded-lg p-2 mb-4">

                <label for="descricao_foto_biografia">Descrição para foto da biografia</label>
                <input type="text" name="descricao_foto_biografia" id="descricao_foto_biografia">

                <div class="flex justify-between mt-6">
                    <button type="button" onclick="voltarEtapa(2)" class="bg-gray-500 text-white px-4 py-2 rounded-lg">
                        Voltar
                    </button>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Salvar Personagem
                    </button>
                </div>
            </div>
        </form>
    </main>

    
    <!-- <script src="../assets/js/adicionar_habilidade.js"></script> -->

    <script src="../assets/js/form_etapas.js"></script>
</body>
</html>