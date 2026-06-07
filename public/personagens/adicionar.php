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

    <title>Adicionar Personagem</title>
</head>

<body class="min-h-screen bg-gradient-to-br from-white via-purple-50/40 to-white font-sans text-jojo-dark">

    <?php include_once "../../src/includes/header.php"; ?>

    <main class="mx-auto max-w-6xl px-6 py-7">

        <div class="mb-5 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>

                <h1 class="text-3xl font-bold text-jojo-dark">
                    <i class="fa-regular fa-star mr-2 text-jojo-purple"></i>
                    Novo Personagem
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Preencha os dados, biografia e fotos do personagem.
                </p>
            </div>

            <div class="flex gap-3">

                <button type="submit" form="form-personagem"
                    class="inline-flex items-center gap-2 rounded-xl bg-jojo-purple px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-purple-200 transition hover:bg-purple-700">
                    <i class="fa-regular fa-floppy-disk"></i>
                    Salvar
                </button>
            </div>
        </div>

        <form 
            id="form-personagem"
            action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" 
            method="post" 
            enctype="multipart/form-data"
            class="grid gap-5 lg:grid-cols-[0.9fr_1.4fr]"
        >
            <input type="hidden" name="parte_id" value="<?= htmlspecialchars($parte_id) ?>">

            <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                <h2 class="mb-4 flex items-center gap-2 text-lg font-bold text-jojo-purple">
                    <i class="fa-regular fa-image"></i>
                    Fotos do personagem
                </h2>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                    <div class="foto-card">
                        <label class="mb-1 block text-xs font-bold text-slate-600">
                            Foto do anime <span class="text-red-500">*</span>
                        </label>

                        <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                            <img id="preview_foto_anime" class="hidden h-40 w-full rounded-lg object-cover" alt="Prévia anime">

                            <div id="placeholder_foto_anime" class="flex h-40 w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                                <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                                Clique para enviar
                            </div>

                            <input type="file" name="foto_anime" required accept="image/*" class="hidden"
                                   onchange="previewImagem(this, 'preview_foto_anime', 'placeholder_foto_anime')">
                        </label>
                    </div>

                    <div class="foto-card">
                        <label class="mb-1 block text-xs font-bold text-slate-600">
                            Foto do mangá <span class="text-red-500">*</span>
                        </label>

                        <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                            <img id="preview_foto_manga" class="hidden h-40 w-full rounded-lg object-cover" alt="Prévia mangá">

                            <div id="placeholder_foto_manga" class="flex h-40 w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                                <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                                Clique para enviar
                            </div>

                            <input type="file" name="foto_manga" required accept="image/*" class="hidden"
                                   onchange="previewImagem(this, 'preview_foto_manga', 'placeholder_foto_manga')">
                        </label>
                    </div>

                    <div class="foto-card">
                        <label class="mb-1 block text-xs font-bold text-slate-600">
                            Foto catálogo <span class="text-red-500">*</span>
                        </label>

                        <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                            <img id="preview_foto_catalogo" class="hidden h-40 w-full rounded-lg object-cover" alt="Prévia catálogo">

                            <div id="placeholder_foto_catalogo" class="flex h-40 w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                                <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                                Clique para enviar
                            </div>

                            <input type="file" name="foto_catalogo" required accept="image/*" class="hidden"
                                   onchange="previewImagem(this, 'preview_foto_catalogo', 'placeholder_foto_catalogo')">
                        </label>
                    </div>

                    <div class="foto-card">
                        <label class="mb-1 block text-xs font-bold text-slate-600">
                            Foto biografia <span class="text-red-500">*</span>
                        </label>

                        <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                            <img id="preview_foto_biografia" class="hidden h-40 w-full rounded-lg object-cover" alt="Prévia biografia">

                            <div id="placeholder_foto_biografia" class="flex h-40 w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                                <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                                Clique para enviar
                            </div>

                            <input type="file" name="foto_biografia" required accept="image/*" class="hidden"
                                   onchange="previewImagem(this, 'preview_foto_biografia', 'placeholder_foto_biografia')">
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
                            <input type="text" name="nome" id="nome" required placeholder="Ex.: Jotaro Kujo"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100">
                        </div>

                        <div>
                            <label for="idade" class="mb-1 block text-xs font-bold text-slate-600">
                                Idade <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="idade" id="idade" required min="0" placeholder="Ex.: 17"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100">
                        </div>

                        <div>
                            <label for="papel" class="mb-1 block text-xs font-bold text-slate-600">
                                Papel <span class="text-red-500">*</span>
                            </label>
                            <select name="papel" id="papel" required
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100">
                                <option value="protagonista">Protagonista</option>
                                <option value="vilao">Vilão</option>
                                <option value="jojobro">JoJoBro</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="descricao_foto_biografia" class="mb-1 block text-xs font-bold text-slate-600">
                                Descrição da foto da biografia
                            </label>
                            <input type="text" name="descricao_foto_biografia" id="descricao_foto_biografia"
                                placeholder="Ex.: Cena marcante do personagem"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100">
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
                            <textarea name="infor_gerais" id="infor_gerais" rows="4"
                                placeholder="Descreva informações gerais sobre o personagem..."
                                class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"></textarea>
                        </div>

                        <div>
                            <label for="biografia" class="mb-1 block text-xs font-bold text-slate-600">
                                Biografia
                            </label>
                            <textarea name="biografia" id="biografia" rows="5"
                                placeholder="Conte a história e participação do personagem..."
                                class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"></textarea>
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

</body>
</html>