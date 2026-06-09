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

$sql = "SELECT nome FROM partes WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $parte_id
]);

$parte = $stmt->fetch(PDO::FETCH_OBJ);

if (!$parte) {
    header("Location: index.php?status=parte_nao_encontrada");
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
        // Prepara para alterações
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
        // Vê se o banco esta no meio de uma transição e se estiver apaga tudo que foi feito
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
    <title>Adicionar personagem</title>
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
<body class="min-h-screen bg-gradient-to-br from-white via-purple-50/40 to-white font-sans text-jojo-dark body-stands">
    <?php include_once "../../src/includes/header.php"; ?>
    <main class="mx-auto w-full max-w-[1450px] px-10 pb-7 pt-6">
        <!-- Caminho da página -->
        <nav class="mb-6 flex flex-wrap items-center gap-4 text-xs md:text-sm">
            <a href="../index.php"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                Todas as Partes
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <a href="../partes/visualizar.php?id=<?= $parte_id; ?>"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                <?= escapar($parte->nome); ?>
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <a href="index.php?parte_id=<?= $parte_id; ?>"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                Personagens
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <span class="font-semibold text-jojo-purple">
                Novo personagem
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
                        Novo Personagem
                        <span class="ml-1 text-sm text-jojo-lilac">✦✦</span>
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Preencha os dados, biografia e fotos do personagem.
                    </p>
                </div>
            </div>
            <button type="submit" form="form-personagem"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#7045c9] to-[#a77be5] px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-purple-200 transition hover:bg-purple-700">
                <i class="fa-regular fa-floppy-disk"></i>
                Salvar
            </button>
        </section>

    <form 
        id="form-personagem"
        action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" 
        method="post" 
        enctype="multipart/form-data"
        class="grid gap-5 lg:grid-cols-[0.9fr_1.4fr]"
    >
    <input type="hidden" name="parte_id" value="<?= htmlspecialchars($parte_id) ?>">

    <!-- LADO ESQUERDO: FOTOS -->
    <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
        <h2 class="mb-4 flex items-center gap-2 font-title text-lg font-bold text-jojo-purple">
            <i class="fa-regular fa-image"></i>
            Fotos do personagem
        </h2>

        <!-- Botões para trocar somente o campo de foto -->
        <div class="mb-4 grid grid-cols-2 gap-2">
            <button type="button"
                onclick="trocarFotoCampo(event, 'anime')"
                class="botao-foto-campo foto-campo-ativo h-10 rounded-xl text-xs font-semibold transition">
                Anime
            </button>

            <button type="button"
                onclick="trocarFotoCampo(event, 'manga')"
                class="botao-foto-campo h-10 rounded-xl text-xs font-semibold transition">
                Mangá
            </button>

            <button type="button"
                onclick="trocarFotoCampo(event, 'catalogo')"
                class="botao-foto-campo h-10 rounded-xl text-xs font-semibold transition">
                Catálogo
            </button>

            <button type="button"
                onclick="trocarFotoCampo(event, 'biografia')"
                class="botao-foto-campo h-10 rounded-xl text-xs font-semibold transition">
                Biografia
            </button>
        </div>

        <!-- Foto anime -->
        <div data-foto-campo="anime" class="foto-campo">
            <label class="mb-1 block text-xs font-semibold text-[#473267]">
                Foto do anime <span class="text-red-500">*</span>
            </label>

            <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                <img 
                    id="preview_foto_anime" 
                    class="hidden h-[500px] w-full rounded-lg bg-white object-contain object-center" 
                    alt="Prévia anime"
                >

                <div id="placeholder_foto_anime" class="flex h-[500px] w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                    <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                    Clique para enviar a foto do anime
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

        <!-- Foto mangá -->
        <div data-foto-campo="manga" class="foto-campo hidden">
            <label class="mb-1 block text-xs font-semibold text-[#473267]">
                Foto do mangá <span class="text-red-500">*</span>
            </label>

            <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                <img 
                    id="preview_foto_manga" 
                    class="hidden h-[500px] w-full rounded-lg bg-white object-contain object-center" 
                    alt="Prévia mangá"
                >

                <div id="placeholder_foto_manga" class="flex h-[500px] w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                    <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                    Clique para enviar a foto do mangá
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

        <!-- Foto catálogo -->
        <div data-foto-campo="catalogo" class="foto-campo hidden">
            <label class="mb-1 block text-xs font-semibold text-[#473267]">
                Foto catálogo <span class="text-red-500">*</span>
            </label>

            <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                <img 
                    id="preview_foto_catalogo" 
                    class="hidden h-[500px] w-full rounded-lg bg-white object-contain object-center" 
                    alt="Prévia catálogo"
                >

                <div id="placeholder_foto_catalogo" class="flex h-[500px] w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                    <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                    Clique para enviar a foto catálogo
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

        <!-- Foto biografia -->
        <div data-foto-campo="biografia" class="foto-campo hidden">
            <label class="mb-1 block text-xs font-semibold text-[#473267]">
                Foto biografia <span class="text-red-500">*</span>
            </label>

            <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                <img 
                    id="preview_foto_biografia" 
                    class="hidden h-[500px] w-full rounded-lg bg-white object-contain object-center" 
                    alt="Prévia biografia"
                >

                <div id="placeholder_foto_biografia" class="flex h-[500px] w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
                    <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                    Clique para enviar a foto da biografia
                </div>

                <input 
                    type="file" 
                    name="foto_biografia" 
                    required
                    accept="image/*" 
                    class="hidden"
                    onchange="previewImagem(this, 'preview_foto_biografia', 'placeholder_foto_biografia')"
                >
            </label>
        </div>
    </section>

    <!-- LADO DIREITO: CAMPOS -->
    <div class="space-y-5">
        <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
            <h2 class="mb-4 flex items-center gap-2 font-title text-lg font-bold text-jojo-purple">
                <i class="fa-regular fa-address-card"></i>
                Informações principais
            </h2>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="nome" class="mb-1 block text-xs font-semibold text-[#473267]">
                        Nome do personagem <span class="text-red-500">*</span>
                    </label>

                    <input 
                        type="text" 
                        name="nome" 
                        id="nome" 
                        required 
                        placeholder="Ex.: Jotaro Kujo"
                        class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                    >
                </div>

                <div>
                    <label for="idade" class="mb-1 block text-xs font-semibold text-[#473267]">
                        Idade <span class="text-red-500">*</span>
                    </label>

                    <input 
                        type="number" 
                        name="idade" 
                        id="idade" 
                        required 
                        min="0" 
                        placeholder="Ex.: 17"
                        class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                    >
                </div>

                <div>
                    <label for="papel" class="mb-1 block text-xs font-semibold text-[#473267]">
                        Papel <span class="text-red-500">*</span>
                    </label>

                    <select 
                        name="papel" 
                        id="papel" 
                        required
                        class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                    >
                        <option value="protagonista">Protagonista</option>
                        <option value="vilao">Vilão</option>
                        <option value="jojobro">JoJoBro</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="descricao_foto_biografia" class="mb-1 block text-xs font-semibold text-[#473267]">
                        Descrição da foto da biografia
                    </label>

                    <input 
                        type="text" 
                        name="descricao_foto_biografia" 
                        id="descricao_foto_biografia"
                        placeholder="Ex.: Cena marcante do personagem"
                        class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                    >
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
            <h2 class="mb-4 flex items-center gap-2 font-title text-lg font-bold text-jojo-purple">
                <i class="fa-regular fa-file-lines"></i>
                Textos
            </h2>

            <div class="space-y-4">
                <div>
                    <label for="infor_gerais" class="mb-1 block text-xs font-semibold text-[#473267]">
                        Informações gerais
                    </label>

                    <textarea 
                        name="infor_gerais" 
                        id="infor_gerais" 
                        rows="4"
                        placeholder="Descreva informações gerais sobre o personagem..."
                        class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] leading-6 text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                    ></textarea>
                </div>

                <div>
                    <label for="biografia" class="mb-1 block text-xs font-semibold text-[#473267]">
                        Biografia
                    </label>

                    <textarea 
                        name="biografia" 
                        id="biografia" 
                        rows="5"
                        placeholder="Conte a história e participação do personagem..."
                        class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] leading-6 text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                    ></textarea>
                </div>
            </div>
        </section>
    </div>
</form>
</main>
<script>
    function trocarFotoCampo(evento, campo) {
        document.querySelectorAll(".foto-campo").forEach(function(item) {
            item.classList.add("hidden");
        });

        const campoAtivo = document.querySelector(`[data-foto-campo="${campo}"]`);

        if (campoAtivo) {
            campoAtivo.classList.remove("hidden");
        }

        document.querySelectorAll(".botao-foto-campo").forEach(function(botao) {
            botao.classList.remove("foto-campo-ativo");
        });

        evento.currentTarget.classList.add("foto-campo-ativo");
    }

    function previewImagem(input, previewId, placeholderId) {
        const arquivo = input.files[0];
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);

        if (!arquivo) {
            preview.src = "";
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