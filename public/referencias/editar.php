<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";

if (!isset($_GET["id_referencia"])) {
    header("Location: index.php?status=id_vazio");
}

$referencia_id = $_GET["id_referencia"];

$parte_id = $_GET["parte_id"];
if (!$parte_id) {
    header("Location: index.php");
    exit;
}

if (!filter_var($referencia_id, FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=id_invalido");
    exit;
}

// SELEÇÃO DAS INFORMAÇÕES SOBRE OS STANDS PARA PRE-PREENCHER
$sql = "SELECT * FROM referencias WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $referencia_id
]);

$referencia = $stmt->fetch(PDO::FETCH_OBJ);

if (!$referencia) {
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

    <title>Editar Referência</title>
</head>

<body class="min-h-screen bg-gradient-to-br from-white via-purple-50/40 to-white font-sans text-jojo-dark">

    <?php include_once "../../src/includes/header.php"; ?>

    <main class="mx-auto max-w-5xl px-6 py-7">

        <div class="mb-5 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm font-semibold text-jojo-purple">
                    <a href="" class="hover:underline">
                        Referências
                    </a>
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                    <span><?= htmlspecialchars($referencia->titulo) ?></span>
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                    <span>Editar</span>
                </div>

                <h1 class="text-3xl font-bold text-jojo-dark">
                    <i class="fa-regular fa-star mr-2 text-jojo-purple"></i>
                    Editar Referência
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Atualize o título, tipo, descrição e imagem da referência.
                </p>
            </div>

            <div class="flex gap-3">
                <a href=""
                   class="inline-flex items-center gap-2 rounded-xl border border-jojo-border bg-white px-4 py-2.5 text-sm font-bold text-jojo-purple shadow-sm transition hover:bg-purple-50">
                    <i class="fa-solid fa-arrow-left"></i>
                    Voltar
                </a>

                <button type="submit" form="form-referencia"
                    class="inline-flex items-center gap-2 rounded-xl bg-jojo-purple px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-purple-200 transition hover:bg-purple-700">
                    <i class="fa-regular fa-floppy-disk"></i>
                    Salvar alterações
                </button>
            </div>
        </div>

        <form 
            id="form-referencia"
            action="processar_editar.php" 
            method="post" 
            enctype="multipart/form-data"
            class="grid gap-5 lg:grid-cols-[0.9fr_1.4fr]"
        >
            <input type="hidden" name="id_referencia" value="<?= htmlspecialchars($referencia->id) ?>">
            <input type="hidden" name="parte_id" value="<?= htmlspecialchars($parte_id) ?>">
            <input type="hidden" name="imagem_antiga" value="<?= htmlspecialchars($referencia->imagem ?? '') ?>">

            <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                <h2 class="mb-4 flex items-center gap-2 text-lg font-bold text-jojo-purple">
                    <i class="fa-regular fa-image"></i>
                    Imagem
                </h2>

                <div>
                    <label class="mb-1 block text-xs font-bold text-slate-600">
                        Imagem da referência
                    </label>

                    <label class="flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                        <img 
                            id="preview_imagem" 
                            src="../<?= htmlspecialchars($referencia->imagem) ?>"
                            class="<?= empty($referencia->imagem) ? 'hidden' : '' ?> h-64 w-full rounded-lg object-cover" 
                            alt="Imagem atual da referência"
                        >

                        <div id="placeholder_imagem" class="<?= !empty($referencia->imagem) ? 'hidden' : '' ?> flex h-64 w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                            <i class="fa-solid fa-cloud-arrow-up mb-2 text-3xl"></i>
                            <span class="font-bold">Clique para enviar</span>
                            <span class="mt-1 text-xs text-slate-400">PNG, JPG ou WEBP</span>
                        </div>

                        <input 
                            type="file" 
                            name="imagem" 
                            accept="image/*" 
                            class="hidden"
                            onchange="previewImagem(this, 'preview_imagem', 'placeholder_imagem')"
                        >
                    </label>

                    <p class="mt-2 text-xs text-slate-400">
                        Deixe vazio para manter a imagem atual.
                    </p>
                </div>
            </section>

            <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                <h2 class="mb-4 flex items-center gap-2 text-lg font-bold text-jojo-purple">
                    <i class="fa-regular fa-address-card"></i>
                    Informações
                </h2>

                <div class="space-y-4">
                    <div>
                        <label for="titulo" class="mb-1 block text-xs font-bold text-slate-600">
                            Título <span class="text-red-500">*</span>
                        </label>

                        <input 
                            type="text" 
                            name="titulo" 
                            id="titulo" 
                            required
                            value="<?= htmlspecialchars($referencia->titulo) ?>"
                            class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                        >
                    </div>

                    <div>
                        <label for="tipo" class="mb-1 block text-xs font-bold text-slate-600">
                            Tipo <span class="text-red-500">*</span>
                        </label>

                        <select 
                            name="tipo" 
                            id="tipo"
                            required
                            class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                        >
                            <option value="musical" <?= $referencia->tipo == "musical" ? "selected" : "" ?>>
                                Musical
                            </option>

                            <option value="literaria" <?= $referencia->tipo == "literaria" ? "selected" : "" ?>>
                                Literária
                            </option>

                            <option value="moda" <?= $referencia->tipo == "moda" ? "selected" : "" ?>>
                                Moda
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="descricao" class="mb-1 block text-xs font-bold text-slate-600">
                            Descrição
                        </label>

                        <textarea 
                            name="descricao" 
                            id="descricao" 
                            rows="7"
                            class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-sm outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                        ><?= htmlspecialchars(trim($referencia->descricao ?? "")) ?></textarea>
                    </div>
                </div>
            </section>
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