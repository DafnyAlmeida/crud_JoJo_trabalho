<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";

if (!isset($_GET["id_referencia"])) {
    header("Location: index.php?status=id_vazio");
    exit;
}

$referencia_id = $_GET["id_referencia"];
$parte_id = $_GET["parte_id"] ?? null;

if (!$parte_id || !filter_var($parte_id, FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=parte_invalida");
    exit;
}

// Buscar nome da parte 
$sql = "SELECT nome FROM partes WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $parte_id
]);

$parte = $stmt->fetch(PDO::FETCH_OBJ);

if (!$parte) {
    header("Location: ../index.php?status=parte_nao_encontrada");
    exit;
}

if (!filter_var($referencia_id, FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=id_invalido");
    exit;
}

// Buscar referência
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
    <title>Editar Referência</title>
    <link rel="icon" type="image/png" href="../assets/img/logo.png">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "jojo-dark": "#30204f",
                        "jojo-purple": "#7045c9",
                        "jojo-lilac": "#a77be5",
                        "jojo-pink": "#dd438f",
                        "jojo-bg": "#fbf9ff",
                        "jojo-border": "#ece4fa"
                    },
                    fontFamily: {
                        title: ["Playfair Display", "Georgia", "serif"],
                        body: ["Inter", "Arial", "sans-serif"]
                    },
                    boxShadow: {
                        card: "0 5px 18px rgba(66, 38, 102, 0.08)",
                        soft: "0 3px 14px rgba(66, 38, 102, 0.06)",
                        button: "0 6px 15px rgba(112, 69, 201, 0.20)"
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="../assets/css/geral.css">
</head>

<body class="min-h-screen font-body text-jojo-dark body-stands">

    <?php include_once "../../src/includes/header.php"; ?>

    <main class="mx-auto w-full max-w-[1450px] px-10 pb-7 pt-6">

        <!-- Caminho da página -->
        <nav class="mb-6 flex flex-wrap items-center gap-4 text-xs md:text-sm">
            <a href="../index.php"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                Todas as Partes
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <a href="index.php?parte_id=<?= (int) $parte_id; ?>"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                <?= htmlspecialchars($parte->nome); ?>
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <span class="font-semibold text-[#665387]">
                <?= htmlspecialchars($referencia->titulo); ?>
            </span>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <span class="font-semibold text-jojo-purple">
                Editar referência
            </span>
        </nav>

        <!-- Cabeçalho -->
        <section class="mb-7 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
            <div class="flex items-center gap-4">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-purple-100 bg-white text-xl text-jojo-purple shadow-soft">
                    <i class="fa-regular fa-star text-jojo-purple"></i>
                </span>

                <div>
                    <h1 class="font-title text-xl font-bold text-jojo-dark md:text-[25px]">
                        Editar Referência
                        <span class="ml-1 text-sm text-jojo-lilac">✦✦</span>
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Atualize o título, tipo, descrição e imagem da referência.
                    </p>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" form="form-referencia"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#7045c9] to-[#a77be5] px-5 py-2.5 text-sm font-bold text-white shadow-button transition hover:bg-purple-700">
                    <i class="fa-regular fa-floppy-disk"></i>
                    Salvar
                </button>
            </div>
        </section>

        <!-- Formulário -->
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

            <!-- Lado esquerdo: imagem -->
            <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                <h2 class="mb-4 flex items-center gap-2 font-title text-lg font-bold text-jojo-purple">
                    <i class="fa-regular fa-image"></i>
                    Imagem da referência
                </h2>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-[#473267]">
                        Imagem atual
                    </label>

                    <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                        <img 
                            id="preview_imagem" 
                            src="../<?= htmlspecialchars($referencia->imagem ?? '') ?>"
                            class="<?= empty($referencia->imagem) ? 'hidden' : '' ?> h-[460px] w-full rounded-lg bg-white object-contain object-center" 
                            alt="Imagem atual da referência"
                        >

                        <div id="placeholder_imagem"
                            class="<?= !empty($referencia->imagem) ? 'hidden' : '' ?> flex h-[460px] w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                            <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>

                            <span class="font-semibold">
                                Clique para enviar a imagem
                            </span>

                            <span class="mt-1 text-xs text-slate-400">
                                PNG, JPG ou WEBP
                            </span>
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

            <!-- Lado direito: campos -->
            <div class="space-y-5">
                <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 font-title text-lg font-bold text-jojo-purple">
                        <i class="fa-regular fa-address-card"></i>
                        Informações principais
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label for="titulo" class="mb-1 block text-xs font-semibold text-[#473267]">
                                Título <span class="text-red-500">*</span>
                            </label>

                            <input 
                                type="text" 
                                name="titulo" 
                                id="titulo" 
                                required
                                value="<?= htmlspecialchars($referencia->titulo ?? '') ?>"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition placeholder:text-slate-400 focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            >
                        </div>

                        <div>
                            <label for="tipo" class="mb-1 block text-xs font-semibold text-[#473267]">
                                Tipo <span class="text-red-500">*</span>
                            </label>

                            <select 
                                name="tipo" 
                                id="tipo"
                                required
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            >
                                <option value="musical" <?= ($referencia->tipo ?? '') == "musical" ? "selected" : "" ?>>
                                    Musical
                                </option>

                                <option value="literaria" <?= ($referencia->tipo ?? '') == "literaria" ? "selected" : "" ?>>
                                    Literária
                                </option>

                                <option value="moda" <?= ($referencia->tipo ?? '') == "moda" ? "selected" : "" ?>>
                                    Moda
                                </option>
                            </select>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 font-title text-lg font-bold text-jojo-purple">
                        <i class="fa-regular fa-file-lines"></i>
                        Descrição
                    </h2>

                    <div>
                        <label for="descricao" class="mb-1 block text-xs font-semibold text-[#473267]">
                            Descrição da referência
                        </label>

                        <textarea 
                            name="descricao" 
                            id="descricao" 
                            rows="10"
                            class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] leading-6 text-[#433366] outline-none transition placeholder:text-slate-400 focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                        ><?= htmlspecialchars(trim($referencia->descricao ?? "")) ?></textarea>
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