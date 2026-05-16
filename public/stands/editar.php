<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
require_once "../../src/functions/upload.php";

if (!isset($_GET["id_stand"])) {
    header("Location: index.php?status=id_vazio");
    exit;
}

$stand_id = $_GET["id_stand"];

if (!filter_var($stand_id, FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=id_invalido");
    exit;
}

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
    <title>Editar Stand</title>
</head>
<body>
    <main>
        <form action="processar_editar.php" method="post" class="max-w-3xl mx-auto p-6 bg-white rounded-xl shadow space-y-6">
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

                <div id="habilidades" class="space-y-4"></div>

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
    let contadorHabilidades = 0;

    function proximaEtapa(etapaAtual) {

        const etapa = document.getElementById(
            "etapa" + etapaAtual
        );

        const campos = etapa.querySelectorAll(
            "input, textarea, select"
        );

        let formularioValido = true;

        campos.forEach((campo) => {

            campo.classList.remove(
                "border-red-500"
            );

            if (
                campo.hasAttribute("required") &&
                !campo.value.trim()
            ) {

                formularioValido = false;

                campo.classList.add(
                    "border-red-500"
                );
            }
        });

        if (!formularioValido) {

            alert(
                "Preencha todos os campos obrigatórios."
            );

            return;
        }

        etapa.classList.add("hidden");

        document
            .getElementById(
                "etapa" + (etapaAtual + 1)
            )
            .classList.remove("hidden");
    }

    function voltarEtapa(etapaAtual) {

        document
            .getElementById(
                "etapa" + etapaAtual
            )
            .classList.add("hidden");

        document
            .getElementById(
                "etapa" + (etapaAtual - 1)
            )
            .classList.remove("hidden");
    }

    function adicionarHabilidade() {

        contadorHabilidades++;

        const container =
            document.getElementById("habilidades");

        const div = document.createElement("div");

        div.className =
            "border rounded-xl p-4 bg-gray-50 shadow space-y-3";

        div.innerHTML = `
            <div class="flex justify-between items-center">

                <h3 class="text-lg font-bold">
                    Habilidade ${contadorHabilidades}
                </h3>

                <button
                    type="button"
                    onclick="removerHabilidade(this)"
                    class="bg-red-600 text-white px-3 py-1 rounded-lg"
                >
                    Remover
                </button>

            </div>

            <div>
                <label class="block mb-1">
                    Nome da habilidade
                </label>

                <input
                    type="text"
                    name="habilidade_nome[]"
                    required
                    class="w-full border rounded-lg p-2"
                >
            </div>

            <div>
                <label class="block mb-1">
                    Descrição da habilidade
                </label>

                <textarea
                    name="habilidade_descricao[]"
                    required
                    class="w-full border rounded-lg p-2"
                ></textarea>
            </div>

            <div>
                <label class="block mb-1">
                    Imagem da habilidade
                </label>

                <input
                    type="file"
                    name="habilidade_imagem[]"
                    required
                    class="w-full border rounded-lg p-2"
                >
            </div>

            <div>
                <label class="block mb-1">
                    Diagrama de força
                </label>

                <input
                    type="file"
                    name="habilidade_diagrama_imagem[]"
                    required
                    class="w-full border rounded-lg p-2"
                >
            </div>

            <label for="tipo">Tipo de stand</label>
                <select name="habilidade_tipo[]" id="habilidade_tipo[]">
                    <option value="Stands de Curto Alcance">Stands de Curto Alcance</option>
                    <option value="Stands de Longa Distancia">Stands de Longa Distancia</option>
                    <option value="Stands Automáticos">Stands Automáticos</option>
                </select>
        `;

        container.appendChild(div);
    }

    function removerHabilidade(botao) {

        botao.closest(".border").remove();

    }
</script>
    
</body>
</html>