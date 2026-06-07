<?php 
include_once "../../src/includes/bloqueio.php";
include_once "../../src/config/conexao.php";
require_once "../../src/functions/upload.php";
require_once "../../src/functions/gerais.php";

$parte_id = $_GET["parte_id"] ?? $_POST["parte_id"] ?? null;

if (!$parte_id || !filter_var($parte_id, FILTER_VALIDATE_INT)) {
    header("Location: ../personagens/index.php?status=parte_invalida");
    exit;
}
$personagens = buscarPersonagensDisponiveis($pdo);

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        jojo: {
                            dark: "#30204f",
                            purple: "#7045c9",
                            lilac: "#a78bfa",
                            soft: "#f7f2ff",
                            border: "#e7ddfa"
                        }
                    }
                }
            }
        }
    </script>

    <title>Adicionar Stand</title>
</head>

<body class="min-h-screen bg-gradient-to-br from-white via-purple-50/40 to-white font-sans text-jojo-dark">

    <?php include_once "../../src/includes/header.php"; ?>

    <main class="mx-auto max-w-6xl px-6 py-7">

        <div class="mb-5 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm font-semibold text-jojo-purple">
                    <a href="" class="hover:underline">
                        Stands
                    </a>
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                    <span>Adicionar stand</span>
                </div>

                <h1 class="text-3xl font-bold text-jojo-dark">
                    <i class="fa-regular fa-star mr-2 text-jojo-purple"></i>
                    Novo Stand
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Cadastre as informações, fotos e habilidades do stand.
                </p>
            </div>

            <div class="flex gap-3">
                <a href=""
                   class="inline-flex items-center gap-2 rounded-xl border border-jojo-border bg-white px-4 py-2.5 text-sm font-bold text-jojo-purple shadow-sm transition hover:bg-purple-50">
                    <i class="fa-solid fa-arrow-left"></i>
                    Voltar
                </a>

                <button type="submit" form="form-stand"
                    class="inline-flex items-center gap-2 rounded-xl bg-jojo-purple px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-purple-200 transition hover:bg-purple-700">
                    <i class="fa-regular fa-floppy-disk"></i>
                    Salvar Stand
                </button>
            </div>
        </div>

        <form 
            id="form-stand"
            action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" 
            method="post" 
            enctype="multipart/form-data"
            class="grid gap-5 lg:grid-cols-[0.9fr_1.4fr]"
        >
            <input type="hidden" name="parte_id" value="<?= htmlspecialchars($parte_id) ?>">

            <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                <h2 class="mb-4 flex items-center gap-2 text-lg font-bold text-jojo-purple">
                    <i class="fa-regular fa-image"></i>
                    Fotos do stand
                </h2>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">
                            Foto do anime <span class="text-red-500">*</span>
                        </label>

                        <label class="flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                            <img id="preview_foto_anime" class="hidden h-40 w-full rounded-lg object-cover" alt="Prévia anime">

                            <div id="placeholder_foto_anime" class="flex h-40 w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                                <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                                Clique para enviar
                            </div>

                            <input 
                                type="file" 
                                name="foto_anime" 
                                required
                                accept="image/*" 
                                class="hidden"
                                onchange="previewImagem(this, 'preview_foto_anime', 'placeholder_foto_anime')"
                            >
                        </label>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">
                            Foto do mangá <span class="text-red-500">*</span>
                        </label>

                        <label class="flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                            <img id="preview_foto_manga" class="hidden h-40 w-full rounded-lg object-cover" alt="Prévia mangá">

                            <div id="placeholder_foto_manga" class="flex h-40 w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                                <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                                Clique para enviar
                            </div>

                            <input 
                                type="file" 
                                name="foto_manga" 
                                required
                                accept="image/*" 
                                class="hidden"
                                onchange="previewImagem(this, 'preview_foto_manga', 'placeholder_foto_manga')"
                            >
                        </label>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">
                            Foto para catálogo <span class="text-red-500">*</span>
                        </label>

                        <label class="flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                            <img id="preview_foto_catalogo" class="hidden h-40 w-full rounded-lg object-cover" alt="Prévia catálogo">

                            <div id="placeholder_foto_catalogo" class="flex h-40 w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                                <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                                Clique para enviar
                            </div>

                            <input 
                                type="file" 
                                name="foto_catalogo" 
                                required
                                accept="image/*" 
                                class="hidden"
                                onchange="previewImagem(this, 'preview_foto_catalogo', 'placeholder_foto_catalogo')"
                            >
                        </label>
                    </div>

                </div>
            </section>

            <div class="space-y-5">

                <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-bold text-jojo-purple">
                        <i class="fa-regular fa-address-card"></i>
                        Informações principais
                    </h2>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="nome" class="mb-1 block text-xs font-bold text-slate-600">
                                Nome do Stand <span class="text-red-500">*</span>
                            </label>

                            <input 
                                type="text" 
                                name="nome" 
                                id="nome" 
                                required
                                placeholder="Ex.: Star Platinum"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            >
                        </div>

                        <div>
                            <label for="personagem_id" class="mb-1 block text-xs font-bold text-slate-600">
                                Personagem <span class="text-red-500">*</span>
                            </label>

                            <select 
                                name="personagem_id" 
                                id="personagem_id" 
                                required
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            >
                                <option value="">Selecione</option>

                                <?php foreach ($personagens as $personagem): ?>
                                    <option value="<?= (int) $personagem->id ?>">
                                        <?= htmlspecialchars($personagem->nome) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="tipo" class="mb-1 block text-xs font-bold text-slate-600">
                                Tipo de stand <span class="text-red-500">*</span>
                            </label>

                            <select 
                                name="tipo" 
                                id="tipo"
                                required
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            >
                                <option value="Stands de Curto Alcance">Stands de Curto Alcance</option>
                                <option value="Stands de Longa Distancia">Stands de Longa Distância</option>
                                <option value="Stands Automáticos">Stands Automáticos</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="descricao" class="mb-1 block text-xs font-bold text-slate-600">
                                Descrição
                            </label>

                            <textarea 
                                name="descricao" 
                                id="descricao" 
                                rows="4"
                                placeholder="Descreva brevemente o stand..."
                                class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            ></textarea>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-bold text-jojo-purple">
                        <i class="fa-regular fa-file-lines"></i>
                        Texto detalhado
                    </h2>

                    <label for="detalhado" class="mb-1 block text-xs font-bold text-slate-600">
                        Informações gerais e habilidades
                    </label>

                    <textarea 
                        name="detalhado" 
                        id="detalhado" 
                        rows="5"
                        placeholder="Explique melhor o funcionamento, aparência e poderes gerais do stand..."
                        class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                    ></textarea>
                </section>

                <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="flex items-center gap-2 text-lg font-bold text-jojo-purple">
                                <i class="fa-solid fa-bolt"></i>
                                Habilidades
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Adicione o nome, descrição, tipo, imagem e diagrama de cada habilidade.
                            </p>
                        </div>

                        <button 
                            type="button" 
                            onclick="adicionarHabilidade()" 
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-jojo-border bg-purple-50 px-4 py-2.5 text-sm font-bold text-jojo-purple transition hover:bg-purple-100"
                        >
                            <i class="fa-solid fa-plus"></i>
                            Adicionar
                        </button>
                    </div>

                    <div 
                        id="habilidades" 
                        class="space-y-4 rounded-xl border border-dashed border-jojo-border bg-purple-50/30 p-4"
                    >
                        <p class="text-center text-sm font-medium text-slate-400">
                            Nenhuma habilidade adicionada ainda.
                        </p>
                    </div>
                </section>

            </div>
        </form>

    </main>

    <script>
        function previewImagem(input, previewId, placeholderId) {
            const arquivo = input.files[0];
            const preview = document.getElementById(previewId);
            const placeholder = document.getElementById(placeholderId);

            if (!arquivo) {
                preview.classList.add("hidden");
                placeholder.classList.remove("hidden");
                return;
            }

            const leitor = new FileReader();

            leitor.onload = function(evento) {
                preview.src = evento.target.result;
                preview.classList.remove("hidden");
                placeholder.classList.add("hidden");
            };

            leitor.readAsDataURL(arquivo);
        }
    </script>

    <script>
        window.modoFormulario = "adicionar";
        window.habilidadesIniciais = 0;
    </script>

    <script src="../assets/js/adicionar_habilidade.js"></script>

</body>
</html>