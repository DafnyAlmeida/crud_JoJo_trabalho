<?php 
include_once "../../src/includes/bloqueio.php";
include_once "../../src/config/conexao.php";
require_once "../../src/functions/upload.php";

$parte_id = $_GET["parte_id"];
$sql = "SELECT DISTINCT p.id, p.nome FROM personagens p INNER JOIN personagens_partes pp ON p.id = pp.personagem_id INNER JOIN partes pt ON pp.parte_id = pt.id LEFT JOIN stands s ON p.id = s.personagem_id WHERE s.id IS NULL AND pt.numero NOT IN (1, 2)";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$personagens = $stmt->fetchAll(PDO::FETCH_OBJ);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $parte_id = $_POST["parte_id"];
        if (empty($_POST['personagem_id'])) {
            throw new Exception(
                "Selecione um personagem."
            );
        }

        $sql = "SELECT id FROM personagens WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $_POST['personagem_id']
        ]);

        if (!$stmt->fetch()) {
            throw new Exception(
                "Personagem não encontrado."
            );
        }

        $foto_anime = salvar_imagem("foto_anime", "stands", $_POST["nome"]);
        $foto_manga = salvar_imagem("foto_manga", "stands", $_POST["nome"]);
        $foto_catalogo = salvar_imagem("foto_catalogo", "stands", $_POST["nome"]);

        $sql = "INSERT INTO stands
        (usuario_id, personagem_id, nome, descricao, foto_anime, foto_manga, foto_catalogo, infor_gerais, habilidade_texto_geral, tipo)
        VALUES
        (:usuario_id, :personagem_id, :nome, :descricao, :foto_anime, :foto_manga, :foto_catalogo, :infor_gerais, :habilidade_texto_geral, :tipo)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":usuario_id" => $_SESSION["usuario_id"],
            ":personagem_id" => $_POST["personagem_id"],
            ":nome" => $_POST["nome"],
            ":descricao" => $_POST["descricao"],
            ":foto_anime" => $foto_anime,
            ":foto_manga" => $foto_manga,
            ":foto_catalogo" => $foto_catalogo,
            ":infor_gerais" => $_POST["detalhado"],
            ":habilidade_texto_geral" => $_POST["detalhado"],
            ":tipo" => $_POST["tipo"]
        ]);

        $stand_id = $pdo->lastInsertId();

        foreach ($_POST["habilidade_nome"] as $index => $nome_habilidade) {
            $descricao_habilidade = $_POST["habilidade_descricao"][$index];

            $tipo_habilidade = $_POST["habilidade_tipo"][$index];

            $imagem_habilidade = salvar_imagem_array(
                "habilidade_imagem",
                $index,
                "habilidades",
                $_POST["nome"]
            );

            $diagrama_habilidade = salvar_imagem_array(
                "habilidade_diagrama_imagem",
                $index,
                "diagramas",
                $_POST["nome"]
            );

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

        header("Location: index.php?parte_id=". urlencode($parte_id));
        exit;

    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Adicionar novo stand</title>
</head>

<body>
    <main>
        <form action="<?= $_SERVER["PHP_SELF"] ?>" method="post" enctype="multipart/form-data" class="max-w-3xl mx-auto p-6 bg-white rounded-xl shadow space-y-6">
        <input type="hidden" name="parte_id" value="<?= $parte_id ?>">

            <!-- ETAPA 1 -->
            <div class="etapa" id="etapa1">
                <h2 class="text-2xl font-bold mb-4">Informações Gerais</h2>

                <label class="block mb-1">Nome do Stand</label>
                <input type="text" name="nome" required class="w-full border rounded-lg p-2 mb-4">

                <label class="block mb-1">Descrição</label>
                <textarea name="descricao" class="w-full border rounded-lg p-2 mb-4"></textarea>

                <label class="block mb-1">Personagem</label>
                <select name="personagem_id" required class="w-full border rounded-lg p-2 mb-4">
                    <option value="">Selecione</option>

                    <?php foreach ($personagens as $personagem): ?>
                        <option value="<?= $personagem->id ?>">
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
                <textarea name="detalhado" class="w-full border rounded-lg p-2 mb-4"></textarea>

                <label for="tipo">Tipo de stand</label>
                <select name="tipo" id="tipo">
                    <option value="Stands de Curto Alcance">Stands de Curto Alcance</option>
                    <option value="Stands de Longa Distancia">Stands de Longa Distancia</option>
                    <option value="Stands Automáticos">Stands Automáticos</option>
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
                        Salvar Stand
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