<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";

if (!isset($_GET["id_personagem"])) {
    header("Location: index.php?status=id_vazio");
    exit;
}

$personagem_id = $_GET["id_personagem"];

$parte_id = $_GET["parte_id"];
if (!$parte_id) {
    header("Location: index.php");
    exit;
}

if (!filter_var($personagem_id, FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=id_invalido");
    exit;
}

// SELEÇÃO DAS INFORMAÇÕES SOBRE OS STANDS PARA PRE-PREENCHER
$sql = "SELECT * FROM personagens WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $personagem_id
]);

$personagem = $stmt->fetch(PDO::FETCH_OBJ);

$sql = "SELECT * FROM personagens_partes WHERE personagem_id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $personagem_id
]);

$personagens_partes = $stmt->fetch(PDO::FETCH_OBJ);


if (!$personagem) {
    header("Location: ../index.php?status=id_invalido");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Editar Personagem</title>
</head>
<body>
    <main>
        <form action="processar_editar.php" method="post" class="max-w-3xl mx-auto p-6 bg-white rounded-xl shadow space-y-6" enctype="multipart/form-data">

            <input type="hidden" name="id_personagem" value="<?= $personagem->id ?>">
            <input type="hidden" name="parte_id" value="<?= $parte_id ?>">
        <!-- ETAPA 1 -->
            <div class="etapa" id="etapa1">
                <h2 class="text-2xl font-bold mb-4">Informações Gerais</h2>

                <label class="block mb-1">Nome do personagem</label>
                <input type="text" name="nome" required class="w-full border rounded-lg p-2 mb-4" value="<?= $personagem->nome ?>">

                <label for="idade">Idade</label>
                <input type="number" name="idade" id="idade" value="<?= $personagens_partes->idade ?>">

                <label class="block mb-1">Informações gerais</label>
                <textarea name="infor_gerais" class="w-full border rounded-lg p-2 mb-4">
                <?= htmlspecialchars($personagem->infor_gerais) ?>
                </textarea>

                <button type="button" onclick="proximaEtapa(1)" class="bg-purple-600 text-white px-4 py-2 rounded-lg">
                    Próximo
                </button>
            </div>

            <!-- ETAPA 2 -->
            <div class="etapa hidden" id="etapa2">
                <h2 class="text-2xl font-bold mb-4">Fotos e Informações Específicas</h2>

                <label class="block mb-1">Biografia</label>
                <textarea name="biografia" class="w-full border rounded-lg p-2 mb-4">
                <?= htmlspecialchars($personagem->biografia) ?>
                </textarea>

                <label for="papel">Papel</label>
                <select name="papel" id="papel">
                    <option value="vilao" <?= $personagens_partes->papel == "vilao" ? 'selected' : '' ?>>Vilão</option>
                    <option value="protagonista" <?= $personagens_partes->papel == "protagonista" ? 'selected' : '' ?>>
                    Protagonista
                    </option>
                    <option value="jojobro" <?= $personagens_partes->papel == "jojobro" ? 'selected' : '' ?>>
                        JojoBro
                    </option>
                </select>

                <label class="block mb-1">Foto do mangá</label>
                <input type="file" name="foto_manga" class="w-full border rounded-lg p-2 mb-4">

                <label class="block mb-1">Foto do anime</label>
                <input type="file" name="foto_anime" class="w-full border rounded-lg p-2 mb-4">

                <label class="block mb-1">Foto para catálogo</label>
                <input type="file" name="foto_catalogo" class="w-full border rounded-lg p-2 mb-4">

                <label class="block mb-1">Foto para biografia</label>
                <input type="file" name="foto_biografia" class="w-full border rounded-lg p-2 mb-4">

                <div class="flex justify-between mt-6">
                    <button type="button" onclick="voltarEtapa(2)" class="bg-gray-500 text-white px-4 py-2 rounded-lg">
                        Voltar
                    </button>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Salvar Update
                    </button>
                </div>
            </div>
        </form>
    </main>

    <!-- <script src="../assets/js/adicionar_habilidade.js"></script> -->

    <script src="../assets/js/form_etapas.js"></script>
    
</body>
</html>