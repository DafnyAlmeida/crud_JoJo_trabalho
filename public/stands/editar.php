<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";

if (!isset($_GET["id_stand"])) {
    header("Location: index.php?status=id_vazio");
    exit;
}

$stand_id = $_GET["id_stand"];

$parte_id = $_GET["parte_id"];
if (!$parte_id) {
    header("Location: index.php");
    exit;
}

if (!filter_var($stand_id, FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=id_invalido");
    exit;
}

// SELEÇÃO DAS INFORMAÇÕES SOBRE OS STANDS PARA PRE-PREENCHER
$sql = "SELECT * FROM stands WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $stand_id
]);

$stand = $stmt->fetch(PDO::FETCH_OBJ);

if (!$stand) {
    header("Location: ../index.php?status=id_invalido");
    exit;
}

// SELEÇÃO DOS PERSONAGENS PARA PRE-PREENCHER
$sql = "SELECT DISTINCT p.id, p.nome 
        FROM personagens p 
        INNER JOIN personagens_partes pp ON p.id = pp.personagem_id 
        INNER JOIN partes pt ON pp.parte_id = pt.id 
        LEFT JOIN stands s ON p.id = s.personagem_id 
        WHERE (s.id IS NULL OR p.id = :personagem_atual)
        AND pt.numero NOT IN (1, 2)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":personagem_atual" => $stand->personagem_id
]);

$personagens = $stmt->fetchAll(PDO::FETCH_OBJ);

// SELEÇÃO DAS HABILIDADES PARA PRE-PREENCHER
$sql = "SELECT * FROM stand_habilidades WHERE stand_id = :stand_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":stand_id" => $stand_id
]);
$habilidades = $stmt->fetchAll(PDO::FETCH_OBJ);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Editar Stand</title>
</head>
<body>
    <main>
        <form action="processar_editar.php" method="post" class="max-w-3xl mx-auto p-6 bg-white rounded-xl shadow space-y-6" enctype="multipart/form-data">

            <input type="hidden" name="id_stand" value="<?= $stand->id ?>">
            <input type="hidden" name="parte_id" value="<?= $parte_id ?>">
        <!-- ETAPA 1 -->
            <div class="etapa" id="etapa1">
                <h2 class="text-2xl font-bold mb-4">Informações Gerais</h2>

                <label class="block mb-1">Nome do Stand</label>
                <input type="text" name="nome" required class="w-full border rounded-lg p-2 mb-4" value="<?= $stand->nome ?>">

                <label class="block mb-1">Descrição</label>
                <textarea name="descricao" class="w-full border rounded-lg p-2 mb-4">
                <?= htmlspecialchars($stand->descricao) ?>
                </textarea>

                <label class="block mb-1">Personagem</label>
                <select name="personagem_id" required class="w-full border rounded-lg p-2 mb-4">
                    <option value="">Selecione</option>

                    <?php foreach ($personagens as $personagem): ?>
                        <option value="<?= $personagem->id ?>"<?= $personagem->id == $stand->personagem_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($personagem->nome) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="button" onclick="proximaEtapa(1)" class="bg-purple-600 text-white px-4 py-2 rounded-lg">
                    Próximo
                </button>
            </div>

            <!-- ETAPA 2 -->
            <div class="etapa hidden" id="etapa2">
                <h2 class="text-2xl font-bold mb-4">Fotos e Informações Específicas</h2>

                <label class="block mb-1">Texto detalhado</label>
                <textarea name="detalhado" class="w-full border rounded-lg p-2 mb-4">
                <?= htmlspecialchars($stand->infor_gerais) ?>
                </textarea>

                <label for="tipo">Tipo de stand</label>
                <select name="tipo" id="tipo">
                    <option value="Stands de Curto Alcance" <?= $stand->tipo == "Stands de Curto Alcance" ? 'selected' : '' ?>>Stands de Curto Alcance</option>
                    <option value="Stands de Longa Distancia" <?= $stand->tipo == "Stands de Longa Distancia" ? 'selected' : '' ?>>Stands de Longa Distancia</option>
                    <option value="Stands Automáticos" <?= $stand->tipo == "Stands Automáticos" ? 'selected' : '' ?>>Stands Automáticos</option>
                </select>

                <label class="block mb-1">Foto do mangá</label>
                <input type="file" name="foto_manga" class="w-full border rounded-lg p-2 mb-4">

                <label class="block mb-1">Foto do anime</label>
                <input type="file" name="foto_anime" class="w-full border rounded-lg p-2 mb-4">

                <label class="block mb-1">Foto para catálogo</label>
                <input type="file" name="foto_catalogo" class="w-full border rounded-lg p-2 mb-4">

                <div class="flex justify-between">
                    <button type="button" onclick="voltarEtapa(2)" class="bg-gray-500 text-white px-4 py-2 rounded-lg">
                        Voltar
                    </button>

                    <button type="button" onclick="proximaEtapa(2)" class="bg-purple-600 text-white px-4 py-2 rounded-lg">
                        Próximo
                    </button>
                </div>
            </div>

            <!-- ETAPA 3 -->
            <div class="etapa hidden" id="etapa3">
                <h2 class="text-2xl font-bold mb-4">Habilidades</h2>

                <button type="button" onclick="adicionarHabilidade()" class="bg-green-600 text-white px-4 py-2 rounded-lg mb-4">
                    + Adicionar habilidade
                </button>

                <div id="habilidades" class="space-y-4">

                    <?php foreach ($habilidades as $index => $habilidade): ?>
                        <div class="habilidade-item border rounded-xl p-4 bg-gray-50 shadow space-y-3">

                            <h3 class="text-lg font-bold">
                                Habilidade <?= $index + 1 ?>
                            </h3>

                            <button type="button" onclick="removerHabilidade(this)">
                                Remover
                            </button>

                            <label for="nome">Nome da habilidade</label>
                            <input 
                                type="text" 
                                name="habilidade_nome[]" 
                                id="nome" value="<?= htmlspecialchars($habilidade->nome) ?>" 
                            >

                            <label for="descricao">Descrição da habilidade</label>

                            <textarea name="habilidade_descricao[]" id="descricao"><?= htmlspecialchars($habilidade->descricao) ?></textarea>

                            <label for="imagem">Imagem da habilidade</label>

                            <?php if (!empty($habilidade->imagem)): ?>
                                <img src="../<?= $habilidade->imagem ?>" width="100">
                            <?php endif; ?>

                            <input type="hidden" name="habilidade_imagem_antiga[]" value="<?= $habilidade->imagem ?>">
                            <input type="file" name="habilidade_imagem[]" id="imagem">

                            <label for="forca">Diagrama de força</label>

                            <?php if (!empty($habilidade->forca)): ?>
                                <img src="../<?= $habilidade->forca ?>" width="100">
                            <?php endif; ?>

                            <input type="hidden" name="habilidade_diagrama_antigo[]" value="<?= $habilidade->forca ?>">
                            <input type="file" name="habilidade_diagrama_imagem[]" id="forca">

                            <select name="habilidade_tipo[]">
                                <option value="Stands de Curto Alcance" <?= $habilidade->tipo == "Stands de Curto Alcance" ? "selected" : "" ?>>
                                    Stands de Curto Alcance
                                </option>
                                <option value="Stands de Longa Distancia" <?= $habilidade->tipo == "Stands de Longa Distancia" ? "selected" : "" ?>>
                                    Stands de Longa Distancia
                                </option>
                                <option value="Stands Automáticos" <?= $habilidade->tipo == "Stands Automáticos" ? "selected" : "" ?>>
                                    Stands Automáticos
                                </option>
                            </select>
                        </div>
                    <?php endforeach; ?>

                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" onclick="voltarEtapa(3)" class="bg-gray-500 text-white px-4 py-2 rounded-lg">
                        Voltar
                    </button>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Salvar Update
                    </button>
                </div>
            </div>
        </form>
    </main>

    <script>
        const habilidadesIniciais = <?= count($habilidades) ?>;
    </script>

    <script src="../assets/js/adicionar_habilidade.js"></script>

    <script src="../assets/js/form_etapas.js"></script>
    
</body>
</html>