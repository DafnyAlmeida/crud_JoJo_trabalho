<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
include_once "../../src/functions/gerais.php";

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

$sql = "SELECT nome FROM partes WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $parte_id
]);

$parte = $stmt->fetch(PDO::FETCH_OBJ);

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
    <title>Editar personagem</title>
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

    <style>
        body {
            background:
                radial-gradient(circle at top left, rgba(113, 69, 201, 0.06), transparent 34%),
                linear-gradient(180deg, #fdfcff 0%, #fbf9ff 100%);
        }

        .card-personagem {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .card-personagem:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(66, 38, 102, 0.12);
        }

        .card-personagem:hover .foto-personagem {
            transform: scale(1.05);
        }

        .foto-personagem {
            transition: transform 0.35s ease;
        }

        .linha-paginacao {
            background: linear-gradient(
                90deg,
                transparent,
                rgba(167, 123, 229, 0.55),
                transparent
            );
        }

        .botao-foto-campo {
            color: #7045c9;
            background: #f7f2ff;
            border: 1px solid #e7ddfa;
        }

        .botao-foto-campo.foto-campo-ativo {
            color: #fff;
            background: linear-gradient(135deg, #7045c9, #9665dc);
            box-shadow: 0 10px 20px rgba(112, 69, 201, 0.18);
            border-color: transparent;
        }

    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-white via-purple-50/40 to-white font-sans text-jojo-dark">

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
                <?= escapar($personagem->nome) ?>
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <span class="font-semibold text-jojo-purple">
                Editar personagem
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
                        Editar Personagem
                        <span class="ml-1 text-sm text-jojo-lilac">✦✦</span>
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Modifique os dados, biografia e fotos do personagem.
                    </p>
                </div>
            </div>
            <button type="submit" form="form-personagem"
                class="inline-flex items-center gap-2 rounded-xl bg-jojo-purple px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-purple-200 transition hover:bg-purple-700">
                <i class="fa-regular fa-floppy-disk"></i>
                Salvar
            </button>
        </section>


        <form
            id="form-personagem"
            action="processar_editar.php"
            method="post"
            enctype="multipart/form-data"
            class="grid gap-4 lg:grid-cols-[0.8fr_1.2fr]"
        >
            <input type="hidden" name="id_personagem" value="<?= (int) $personagem->id ?>">
            <input type="hidden" name="parte_id" value="<?= (int) $parte_id ?>">

            <!-- LADO ESQUERDO: FOTOS -->
            <section class="painel rounded-2xl border border-jojo-border bg-white/85 p-4 shadow-card">

                <div class="mb-4 flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-100 text-jojo-purple">
                        <i class="fa-regular fa-image text-sm"></i>
                    </span>

                    <div>
                        <h2 class="font-title text-lg font-bold text-jojo-purple">
                            Fotos
                        </h2>

                        <p class="text-[12px] text-[#66567d]">
                            Escolha qual imagem deseja trocar.
                        </p>
                    </div>
                </div>

                <!-- Botões para escolher a foto -->
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
                    <label class="mb-1.5 block text-[12px] font-semibold text-[#473267]">
                        Foto do anime
                    </label>

                    <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border border-dashed border-purple-200 bg-purple-50/40 p-2.5 transition hover:bg-purple-50">
                        <img
                            id="preview_foto_anime"
                            src="../<?= htmlspecialchars($personagem->foto_anime ?? '') ?>"
                            class="<?= empty($personagem->foto_anime) ? 'hidden' : '' ?> h-[500px] w-full rounded-xl bg-white object-contain object-center"
                            alt="Prévia anime"
                        >

                        <div id="placeholder_foto_anime"
                            class="<?= !empty($personagem->foto_anime) ? 'hidden' : '' ?> flex h-[500px] w-full flex-col items-center justify-center rounded-xl text-center text-[12px] font-semibold text-jojo-purple">
                            <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                            Clique para trocar a foto do anime
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

                <!-- Foto mangá -->
                <div data-foto-campo="manga" class="foto-campo hidden">
                    <label class="mb-1.5 block text-[12px] font-semibold text-[#473267]">
                        Foto do mangá
                    </label>

                    <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border border-dashed border-purple-200 bg-purple-50/40 p-2.5 transition hover:bg-purple-50">
                        <img
                            id="preview_foto_manga"
                            src="../<?= htmlspecialchars($personagem->foto_manga ?? '') ?>"
                            class="<?= empty($personagem->foto_manga) ? 'hidden' : '' ?> h-[500px] w-full rounded-xl bg-white object-contain object-center"
                            alt="Prévia mangá"
                        >

                        <div id="placeholder_foto_manga"
                            class="<?= !empty($personagem->foto_manga) ? 'hidden' : '' ?> flex h-[500px] w-full flex-col items-center justify-center rounded-xl text-center text-[12px] font-semibold text-jojo-purple">
                            <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                            Clique para trocar a foto do mangá
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

                <!-- Foto catálogo -->
                <div data-foto-campo="catalogo" class="foto-campo hidden">
                    <label class="mb-1.5 block text-[12px] font-semibold text-[#473267]">
                        Foto catálogo
                    </label>

                    <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border border-dashed border-purple-200 bg-purple-50/40 p-2.5 transition hover:bg-purple-50">
                        <img
                            id="preview_foto_catalogo"
                            src="../<?= htmlspecialchars($personagem->foto_catalogo ?? '') ?>"
                            class="<?= empty($personagem->foto_catalogo) ? 'hidden' : '' ?> h-[500px] w-full rounded-xl bg-white object-contain object-center"
                            alt="Prévia catálogo"
                        >

                        <div id="placeholder_foto_catalogo"
                            class="<?= !empty($personagem->foto_catalogo) ? 'hidden' : '' ?> flex h-[500px] w-full flex-col items-center justify-center rounded-xl text-center text-[12px] font-semibold text-jojo-purple">
                            <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                            Clique para trocar a foto catálogo
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

                <!-- Foto biografia -->
                <div data-foto-campo="biografia" class="foto-campo hidden">
                    <label class="mb-1.5 block text-[12px] font-semibold text-[#473267]">
                        Foto biografia
                    </label>

                    <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border border-dashed border-purple-200 bg-purple-50/40 p-2.5 transition hover:bg-purple-50">
                        <img
                            id="preview_foto_biografia"
                            src="../<?= htmlspecialchars($personagem->foto_biografia ?? '') ?>"
                            class="<?= empty($personagem->foto_biografia) ? 'hidden' : '' ?> h-[500px] w-full rounded-xl bg-white object-contain object-center"
                            alt="Prévia biografia"
                        >

                        <div id="placeholder_foto_biografia"
                            class="<?= !empty($personagem->foto_biografia) ? 'hidden' : '' ?> flex h-[500px] w-full flex-col items-center justify-center rounded-xl text-center text-[12px] font-semibold text-jojo-purple">
                            <i class="fa-solid fa-cloud-arrow-up mb-2 text-2xl"></i>
                            Clique para trocar a foto da biografia
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

            </section>

            <!-- LADO DIREITO: CAMPOS -->
            <div class="space-y-4">

                <!-- Informações principais -->
                <section class="painel rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-card">

                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-100 text-jojo-purple">
                            <i class="fa-regular fa-address-card text-sm"></i>
                        </span>

                        <div>
                            <h2 class="font-title text-lg font-bold text-jojo-purple">
                                Informações principais
                            </h2>

                            <p class="text-[12px] text-[#66567d]">
                                Dados básicos do personagem.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">

                        <div class="md:col-span-2">
                            <label for="nome" class="mb-1.5 block text-[12px] font-semibold text-[#473267]">
                                Nome do personagem <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="text"
                                name="nome"
                                id="nome"
                                required
                                value="<?= htmlspecialchars($personagem->nome ?? '') ?>"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition placeholder:text-slate-300 focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            >
                        </div>

                        <div>
                            <label for="idade" class="mb-1.5 block text-[12px] font-semibold text-[#473267]">
                                Idade
                            </label>

                            <input
                                type="number"
                                name="idade"
                                id="idade"
                                min="0"
                                value="<?= htmlspecialchars($personagens_partes->idade ?? '') ?>"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition placeholder:text-slate-300 focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            >
                        </div>

                        <div>
                            <label for="papel" class="mb-1.5 block text-[12px] font-semibold text-[#473267]">
                                Papel
                            </label>

                            <select
                                name="papel"
                                id="papel"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            >
                                <option value="protagonista" <?= ($personagens_partes->papel ?? '') === "protagonista" ? "selected" : "" ?>>
                                    Protagonista
                                </option>

                                <option value="vilao" <?= ($personagens_partes->papel ?? '') === "vilao" ? "selected" : "" ?>>
                                    Vilão
                                </option>

                                <option value="jojobro" <?= ($personagens_partes->papel ?? '') === "jojobro" ? "selected" : "" ?>>
                                    JoJoBro
                                </option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="descricao_foto_biografia" class="mb-1.5 block text-[12px] font-semibold text-[#473267]">
                                Descrição da foto da biografia
                            </label>

                            <input
                                type="text"
                                name="descricao_foto_biografia"
                                id="descricao_foto_biografia"
                                value="<?= htmlspecialchars($personagem->descricao_foto_biografia ?? '') ?>"
                                placeholder="Ex.: Cena marcante do personagem"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition placeholder:text-slate-300 focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            >
                        </div>

                    </div>
                </section>

                <!-- Textos -->
                <section class="painel rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-card">

                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-100 text-jojo-purple">
                            <i class="fa-regular fa-file-lines text-sm"></i>
                        </span>

                        <div>
                            <h2 class="font-title text-lg font-bold text-jojo-purple">
                                Textos
                            </h2>

                            <p class="text-[12px] text-[#66567d]">
                                Descrição e biografia do personagem.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3">

                        <div>
                            <label for="infor_gerais" class="mb-1.5 block text-[12px] font-semibold text-[#473267]">
                                Informações gerais
                            </label>

                            <textarea
                                name="infor_gerais"
                                id="infor_gerais"
                                rows="4"
                                class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] leading-6 text-[#433366] outline-none transition placeholder:text-slate-300 focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            ><?= htmlspecialchars(trim($personagem->infor_gerais ?? "")) ?></textarea>
                        </div>

                        <div>
                            <label for="biografia" class="mb-1.5 block text-[12px] font-semibold text-[#473267]">
                                Biografia
                            </label>

                            <textarea
                                name="biografia"
                                id="biografia"
                                rows="5"
                                class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] leading-6 text-[#433366] outline-none transition placeholder:text-slate-300 focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            ><?= htmlspecialchars(trim($personagem->biografia ?? "")) ?></textarea>
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