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

    <title>Editar Stand</title>
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
                    <span><?= htmlspecialchars($stand->nome) ?></span>
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                    <span>Editar</span>
                </div>

                <h1 class="text-3xl font-bold text-jojo-dark">
                    <i class="fa-regular fa-star mr-2 text-jojo-purple"></i>
                    Editar Stand
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Atualize as informações, fotos e habilidades do stand.
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
                    Salvar alterações
                </button>
            </div>
        </div>

        <form 
            id="form-stand"
            action="processar_editar.php" 
            method="post" 
            enctype="multipart/form-data"
            class="grid gap-5 lg:grid-cols-[0.9fr_1.4fr]"
        >
            <input type="hidden" name="id_stand" value="<?= (int) $stand->id ?>">
            <input type="hidden" name="parte_id" value="<?= (int) $parte_id ?>">

            <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                <h2 class="mb-4 flex items-center gap-2 text-lg font-bold text-jojo-purple">
                    <i class="fa-regular fa-image"></i>
                    Fotos do stand
                </h2>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">
                            Foto do anime
                        </label>

                        <label class="flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                            <img 
                                id="preview_foto_anime" 
                                src="../<?= htmlspecialchars($stand->foto_anime) ?>"
                                class="<?= empty($stand->foto_anime) ? 'hidden' : '' ?> h-40 w-full rounded-lg object-cover" 
                                alt="Prévia anime"
                            >

                            <div id="placeholder_foto_anime" class="<?= !empty($stand->foto_anime) ? 'hidden' : '' ?> flex h-40 w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                                <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                                Clique para trocar
                            </div>

                            <input 
                                type="file" 
                                name="foto_anime" 
                                accept="image/*" 
                                class="hidden"
                                onchange="previewImagem(this, 'preview_foto_anime', 'placeholder_foto_anime')"
                            >
                        </label>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">
                            Foto do mangá
                        </label>

                        <label class="flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                            <img 
                                id="preview_foto_manga" 
                                src="../<?= htmlspecialchars($stand->foto_manga) ?>"
                                class="<?= empty($stand->foto_manga) ? 'hidden' : '' ?> h-40 w-full rounded-lg object-cover" 
                                alt="Prévia mangá"
                            >

                            <div id="placeholder_foto_manga" class="<?= !empty($stand->foto_manga) ? 'hidden' : '' ?> flex h-40 w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                                <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                                Clique para trocar
                            </div>

                            <input 
                                type="file" 
                                name="foto_manga" 
                                accept="image/*" 
                                class="hidden"
                                onchange="previewImagem(this, 'preview_foto_manga', 'placeholder_foto_manga')"
                            >
                        </label>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">
                            Foto para catálogo
                        </label>

                        <label class="flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                            <img 
                                id="preview_foto_catalogo" 
                                src="../<?= htmlspecialchars($stand->foto_catalogo) ?>"
                                class="<?= empty($stand->foto_catalogo) ? 'hidden' : '' ?> h-40 w-full rounded-lg object-cover" 
                                alt="Prévia catálogo"
                            >

                            <div id="placeholder_foto_catalogo" class="<?= !empty($stand->foto_catalogo) ? 'hidden' : '' ?> flex h-40 w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                                <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                                Clique para trocar
                            </div>

                            <input 
                                type="file" 
                                name="foto_catalogo" 
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
                                value="<?= htmlspecialchars($stand->nome) ?>"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
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
                                    <option 
                                        value="<?= (int) $personagem->id ?>"
                                        <?= $personagem->id == $stand->personagem_id ? "selected" : "" ?>
                                    >
                                        <?= htmlspecialchars($personagem->nome) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="tipo" class="mb-1 block text-xs font-bold text-slate-600">
                                Tipo de stand
                            </label>

                            <select 
                                name="tipo" 
                                id="tipo"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            >
                                <option value="Stands de Curto Alcance" <?= $stand->tipo == "Stands de Curto Alcance" ? "selected" : "" ?>>
                                    Stands de Curto Alcance
                                </option>

                                <option value="Stands de Longa Distancia" <?= $stand->tipo == "Stands de Longa Distancia" ? "selected" : "" ?>>
                                    Stands de Longa Distância
                                </option>

                                <option value="Stands Automáticos" <?= $stand->tipo == "Stands Automáticos" ? "selected" : "" ?>>
                                    Stands Automáticos
                                </option>
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
                                class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            ><?= htmlspecialchars(trim($stand->descricao ?? "")) ?></textarea>
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
                        class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                    ><?= htmlspecialchars(trim($stand->infor_gerais ?? "")) ?></textarea>
                </section>

                <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="flex items-center gap-2 text-lg font-bold text-jojo-purple">
                                <i class="fa-solid fa-bolt"></i>
                                Habilidades
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Edite, remova ou adicione habilidades do stand.
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

                    <div id="habilidades" class="space-y-4">
                        <?php if (empty($habilidades)): ?>
                            <div class="rounded-xl border border-dashed border-jojo-border bg-purple-50/30 p-4 text-center text-sm font-medium text-slate-400">
                                Nenhuma habilidade cadastrada ainda.
                            </div>
                        <?php endif; ?>

                        <?php foreach ($habilidades as $index => $habilidade): ?>
                            <div class="habilidade-item rounded-2xl border border-jojo-border bg-purple-50/30 p-4 shadow-sm">

                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <h3 class="flex items-center gap-2 text-base font-bold text-jojo-dark">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-jojo-purple text-sm text-white">
                                            <?= $index + 1 ?>
                                        </span>
                                        Habilidade <?= $index + 1 ?>
                                    </h3>

                                    <button 
                                        type="button" 
                                        onclick="removerHabilidade(this)"
                                        class="inline-flex items-center gap-2 rounded-xl border border-red-100 bg-white px-3 py-2 text-xs font-bold text-red-500 transition hover:bg-red-50"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                        Remover
                                    </button>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-slate-600">
                                            Nome da habilidade
                                        </label>

                                        <input 
                                            type="text" 
                                            name="habilidade_nome[]" 
                                            value="<?= htmlspecialchars($habilidade->nome) ?>"
                                            class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                                        >
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-slate-600">
                                            Tipo da habilidade
                                        </label>

                                        <select 
                                            name="habilidade_tipo[]"
                                            class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                                        >
                                            <option value="Stands de Curto Alcance" <?= $habilidade->tipo == "Stands de Curto Alcance" ? "selected" : "" ?>>
                                                Stands de Curto Alcance
                                            </option>

                                            <option value="Stands de Longa Distancia" <?= $habilidade->tipo == "Stands de Longa Distancia" ? "selected" : "" ?>>
                                                Stands de Longa Distância
                                            </option>

                                            <option value="Stands Automáticos" <?= $habilidade->tipo == "Stands Automáticos" ? "selected" : "" ?>>
                                                Stands Automáticos
                                            </option>
                                        </select>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="mb-1 block text-xs font-bold text-slate-600">
                                            Descrição da habilidade
                                        </label>

                                        <textarea 
                                            name="habilidade_descricao[]" 
                                            rows="3"
                                            class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                                        ><?= htmlspecialchars(trim($habilidade->descricao ?? "")) ?></textarea>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-slate-600">
                                            Imagem da habilidade
                                        </label>

                                        <label class="flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-white p-3 transition hover:bg-purple-50">
                                            <img 
                                                id="preview_habilidade_imagem_<?= $index ?>"
                                                src="../<?= htmlspecialchars($habilidade->imagem) ?>"
                                                class="<?= empty($habilidade->imagem) ? 'hidden' : '' ?> h-32 w-full rounded-lg object-cover"
                                                alt="Imagem da habilidade"
                                            >

                                            <div id="placeholder_habilidade_imagem_<?= $index ?>" class="<?= !empty($habilidade->imagem) ? 'hidden' : '' ?> flex h-32 w-full flex-col items-center justify-center text-center text-sm text-jojo-purple">
                                                <i class="fa-solid fa-cloud-arrow-up mb-2 text-xl"></i>
                                                Clique para trocar
                                            </div>

                                            <input type="hidden" name="habilidade_imagem_antiga[]" value="<?= htmlspecialchars($habilidade->imagem) ?>">

                                            <input 
                                                type="file" 
                                                name="habilidade_imagem[]" 
                                                accept="image/*"
                                                class="hidden"
                                                onchange="previewImagem(this, 'preview_habilidade_imagem_<?= $index ?>', 'placeholder_habilidade_imagem_<?= $index ?>')"
                                            >
                                        </label>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-slate-600">
                                            Diagrama de força
                                        </label>

                                        <label class="flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-white p-3 transition hover:bg-purple-50">
                                            <img 
                                                id="preview_habilidade_diagrama_<?= $index ?>"
                                                src="../<?= htmlspecialchars($habilidade->forca) ?>"
                                                class="<?= empty($habilidade->forca) ? 'hidden' : '' ?> h-32 w-full rounded-lg object-cover"
                                                alt="Diagrama da habilidade"
                                            >

                                            <div id="placeholder_habilidade_diagrama_<?= $index ?>" class="<?= !empty($habilidade->forca) ? 'hidden' : '' ?> flex h-32 w-full flex-col items-center justify-center text-center text-sm text-jojo-purple">
                                                <i class="fa-solid fa-chart-simple mb-2 text-xl"></i>
                                                Clique para trocar
                                            </div>

                                            <input type="hidden" name="habilidade_diagrama_antigo[]" value="<?= htmlspecialchars($habilidade->forca) ?>">

                                            <input 
                                                type="file" 
                                                name="habilidade_diagrama_imagem[]" 
                                                accept="image/*"
                                                class="hidden"
                                                onchange="previewImagem(this, 'preview_habilidade_diagrama_<?= $index ?>', 'placeholder_habilidade_diagrama_<?= $index ?>')"
                                            >
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
        window.modoFormulario = "editar";
        window.habilidadesIniciais = <?= count($habilidades) ?>;
    </script>

    <script src="../assets/js/adicionar_habilidade.js"></script>

</body>
</html>