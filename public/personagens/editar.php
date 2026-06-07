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

// Infos para pre-preencher
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

if (!$personagem | !$personagens_partes) {
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

    <title>Editar Personagem</title>
</head>

<body class="min-h-screen bg-gradient-to-br from-white via-purple-50/40 to-white font-sans text-jojo-dark">

    <?php include_once "../../src/includes/header.php"; ?>

    <main class="mx-auto max-w-6xl px-6 py-7">

        <div class="mb-5 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm font-semibold text-jojo-purple">
                    <a href="" class="hover:underline">
                        Personagens
                    </a>
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                    <span><?= htmlspecialchars($personagem->nome) ?></span>
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                    <span>Editar</span>
                </div>

                <h1 class="text-3xl font-bold text-jojo-dark">
                    <i class="fa-regular fa-star mr-2 text-jojo-purple"></i>
                    Editar Personagem
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Atualize as informações, biografia e imagens do personagem.
                </p>
            </div>

            <div class="flex gap-3">
                <a href=""
                   class="inline-flex items-center gap-2 rounded-xl border border-jojo-border bg-white px-4 py-2.5 text-sm font-bold text-jojo-purple shadow-sm transition hover:bg-purple-50">
                    <i class="fa-solid fa-arrow-left"></i>
                    Voltar
                </a>

                <button type="submit" form="form-personagem"
                    class="inline-flex items-center gap-2 rounded-xl bg-jojo-purple px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-purple-200 transition hover:bg-purple-700">
                    <i class="fa-regular fa-floppy-disk"></i>
                    Salvar alterações
                </button>
            </div>
        </div>

        <form 
            id="form-personagem"
            action="processar_editar.php" 
            method="post" 
            enctype="multipart/form-data"
            class="grid gap-5 lg:grid-cols-[0.9fr_1.4fr]"
        >
            <input type="hidden" name="id_personagem" value="<?= (int) $personagem->id ?>">
            <input type="hidden" name="parte_id" value="<?= (int) $parte_id ?>">

            <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                <h2 class="mb-4 flex items-center gap-2 text-lg font-bold text-jojo-purple">
                    <i class="fa-regular fa-image"></i>
                    Fotos do personagem
                </h2>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">
                            Foto do anime
                        </label>

                        <label class="flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                            <img 
                                id="preview_foto_anime" 
                                src="../<?= $personagem->foto_anime ?>"
                                class="<?= empty($personagem->foto_anime) ? 'hidden' : '' ?> h-40 w-full rounded-lg object-cover" 
                                alt="Prévia anime"
                            >

                            <div id="placeholder_foto_anime" class="<?= !empty($personagem->foto_anime) ? 'hidden' : '' ?> flex h-40 w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
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
                                src="../<?= $personagem->foto_manga ?>"
                                class="<?= empty($personagem->foto_manga) ? 'hidden' : '' ?> h-40 w-full rounded-lg object-cover" 
                                alt="Prévia mangá"
                            >

                            <div id="placeholder_foto_manga" class="<?= !empty($personagem->foto_manga) ? 'hidden' : '' ?> flex h-40 w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
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
                            Foto catálogo
                        </label>

                        <label class="flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                            <img 
                                id="preview_foto_catalogo" 
                                src="../<?= $personagem->foto_catalogo ?>"
                                class="<?= empty($personagem->foto_catalogo) ? 'hidden' : '' ?> h-40 w-full rounded-lg object-cover" 
                                alt="Prévia catálogo"
                            >

                            <div id="placeholder_foto_catalogo" class="<?= !empty($personagem->foto_catalogo) ? 'hidden' : '' ?> flex h-40 w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
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

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">
                            Foto biografia
                        </label>

                        <label class="flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                            <img 
                                id="preview_foto_biografia" 
                                src="../<?= $personagem->foto_biografia ?>"
                                class="<?= empty($personagem->foto_biografia) ? 'hidden' : '' ?> h-40 w-full rounded-lg object-cover" 
                                alt="Prévia biografia"
                            >

                            <div id="placeholder_foto_biografia" class="<?= !empty($personagem->foto_biografia) ? 'hidden' : '' ?> flex h-40 w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                                <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                                Clique para trocar
                            </div>

                            <input 
                                type="file" 
                                name="foto_biografia" 
                                accept="image/*" 
                                class="hidden"
                                onchange="previewImagem(this, 'preview_foto_biografia', 'placeholder_foto_biografia')"
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
                                Nome do personagem <span class="text-red-500">*</span>
                            </label>

                            <input 
                                type="text" 
                                name="nome" 
                                id="nome" 
                                required
                                value="<?= htmlspecialchars($personagem->nome) ?>"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            >
                        </div>

                        <div>
                            <label for="idade" class="mb-1 block text-xs font-bold text-slate-600">
                                Idade
                            </label>

                            <input 
                                type="number" 
                                name="idade" 
                                id="idade" 
                                min="0"
                                value="<?= htmlspecialchars($personagens_partes->idade) ?>"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            >
                        </div>

                        <div>
                            <label for="papel" class="mb-1 block text-xs font-bold text-slate-600">
                                Papel
                            </label>

                            <select 
                                name="papel" 
                                id="papel"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            >
                                <option value="vilao" <?= $personagens_partes->papel === "vilao" ? "selected" : "" ?>>
                                    Vilão
                                </option>

                                <option value="protagonista" <?= $personagens_partes->papel === "protagonista" ? "selected" : "" ?>>
                                    Protagonista
                                </option>

                                <option value="jojobro" <?= $personagens_partes->papel === "jojobro" ? "selected" : "" ?>>
                                    JoJoBro
                                </option>
                            </select>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-bold text-jojo-purple">
                        <i class="fa-regular fa-file-lines"></i>
                        Textos
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label for="infor_gerais" class="mb-1 block text-xs font-bold text-slate-600">
                                Informações gerais
                            </label>

                            <textarea 
                                name="infor_gerais" 
                                id="infor_gerais" 
                                rows="4"
                                class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            ><?= htmlspecialchars(trim($personagem->infor_gerais ?? "")) ?></textarea>
                        </div>

                        <div>
                            <label for="biografia" class="mb-1 block text-xs font-bold text-slate-600">
                                Biografia
                            </label>

                            <textarea 
                                name="biografia" 
                                id="biografia" 
                                rows="5"
                                class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            ><?= htmlspecialchars(trim($personagem->biografia ?? "")) ?></textarea>
                        </div>
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

</body>
</html>