<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";

if (!isset($_GET["id_stand"])) {
    header("Location: index.php?status=id_vazio");
    exit;
}

$stand_id = $_GET["id_stand"];
$parte_id = $_GET["parte_id"] ?? null;

if (!$parte_id || !filter_var($parte_id, FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=parte_invalida");
    exit;
}

if (!filter_var($stand_id, FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=id_invalido");
    exit;
}

/* Buscar stand */
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

/* Buscar nome da parte */
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

/* Buscar personagens */
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

/* Buscar habilidades */
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

<body class="min-h-screen font-body text-jojo-dark">

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
                <?= htmlspecialchars($parte->nome); ?>
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <a href="index.php?parte_id=<?= $parte_id; ?>"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                Stands
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <span class="font-semibold text-[#665387]">
                <?= htmlspecialchars($stand->nome); ?>
            </span>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <span class="font-semibold text-jojo-purple">
                Editar stand
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
                        Editar Stand
                        <span class="ml-1 text-sm text-jojo-lilac">✦✦</span>
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Atualize as informações, fotos e habilidades do stand.
                    </p>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" form="form-stand"
                    class="inline-flex items-center gap-2 rounded-xl bg-jojo-purple px-5 py-2.5 text-sm font-bold text-white shadow-button transition hover:bg-purple-700">
                    <i class="fa-regular fa-floppy-disk"></i>
                    Salvar alterações
                </button>
            </div>
        </section>

        <form 
            id="form-stand"
            action="processar_editar.php" 
            method="post" 
            enctype="multipart/form-data"
            class="grid gap-5 lg:grid-cols-[0.9fr_1.4fr]"
        >
            <input type="hidden" name="id_stand" value="<?= (int) $stand->id ?>">
            <input type="hidden" name="parte_id" value="<?= (int) $parte_id ?>">

            <!-- LADO ESQUERDO: FOTOS -->
            <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                <h2 class="mb-4 flex items-center gap-2 font-title text-lg font-bold text-jojo-purple">
                    <i class="fa-regular fa-image"></i>
                    Fotos do stand
                </h2>

                <div class="mb-4 grid grid-cols-3 gap-2">
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
                </div>

                <!-- Foto anime -->
                <div data-foto-campo="anime" class="foto-campo">
                    <label class="mb-1 block text-xs font-semibold text-[#473267]">
                        Foto do anime
                    </label>

                    <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                        <img 
                            id="preview_foto_anime" 
                            src="../<?= htmlspecialchars($stand->foto_anime ?? '') ?>"
                            class="<?= empty($stand->foto_anime) ? 'hidden' : '' ?> h-[650px] w-full rounded-lg bg-white object-contain object-center" 
                            alt="Prévia anime"
                        >

                        <div id="placeholder_foto_anime"
                            class="<?= !empty($stand->foto_anime) ? 'hidden' : '' ?> flex h-[650px] w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
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
                    <label class="mb-1 block text-xs font-semibold text-[#473267]">
                        Foto do mangá
                    </label>

                    <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                        <img 
                            id="preview_foto_manga" 
                            src="../<?= htmlspecialchars($stand->foto_manga ?? '') ?>"
                            class="<?= empty($stand->foto_manga) ? 'hidden' : '' ?> h-[650px] w-full rounded-lg bg-white object-contain object-center" 
                            alt="Prévia mangá"
                        >

                        <div id="placeholder_foto_manga"
                            class="<?= !empty($stand->foto_manga) ? 'hidden' : '' ?> flex h-[650px] w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
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
                    <label class="mb-1 block text-xs font-semibold text-[#473267]">
                        Foto para catálogo
                    </label>

                    <label class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3 transition hover:bg-purple-50">
                        <img 
                            id="preview_foto_catalogo" 
                            src="../<?= htmlspecialchars($stand->foto_catalogo ?? '') ?>"
                            class="<?= empty($stand->foto_catalogo) ? 'hidden' : '' ?> h-[650px] w-full rounded-lg bg-white object-contain object-center" 
                            alt="Prévia catálogo"
                        >

                        <div id="placeholder_foto_catalogo"
                            class="<?= !empty($stand->foto_catalogo) ? 'hidden' : '' ?> flex h-[650px] w-full flex-col items-center justify-center rounded-lg text-center text-sm text-jojo-purple">
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
                                Nome do Stand <span class="text-red-500">*</span>
                            </label>

                            <input 
                                type="text" 
                                name="nome" 
                                id="nome" 
                                required
                                value="<?= htmlspecialchars($stand->nome ?? '') ?>"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition placeholder:text-slate-400 focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            >
                        </div>

                        <div>
                            <label for="personagem_id" class="mb-1 block text-xs font-semibold text-[#473267]">
                                Personagem <span class="text-red-500">*</span>
                            </label>

                            <select 
                                name="personagem_id" 
                                id="personagem_id" 
                                required
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
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
                            <label for="tipo" class="mb-1 block text-xs font-semibold text-[#473267]">
                                Tipo de stand
                            </label>

                            <select 
                                name="tipo" 
                                id="tipo"
                                class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            >
                                <option value="Stands de Curto Alcance" <?= ($stand->tipo ?? '') == "Stands de Curto Alcance" ? "selected" : "" ?>>
                                    Stands de Curto Alcance
                                </option>

                                <option value="Stands de Longa Distancia" <?= ($stand->tipo ?? '') == "Stands de Longa Distancia" ? "selected" : "" ?>>
                                    Stands de Longa Distância
                                </option>

                                <option value="Stands Automáticos" <?= ($stand->tipo ?? '') == "Stands Automáticos" ? "selected" : "" ?>>
                                    Stands Automáticos
                                </option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="descricao" class="mb-1 block text-xs font-semibold text-[#473267]">
                                Descrição
                            </label>

                            <textarea 
                                name="descricao" 
                                id="descricao" 
                                rows="4"
                                class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] leading-6 text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                            ><?= htmlspecialchars(trim($stand->descricao ?? "")) ?></textarea>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 font-title text-lg font-bold text-jojo-purple">
                        <i class="fa-regular fa-file-lines"></i>
                        Texto detalhado
                    </h2>

                    <label for="detalhado" class="mb-1 block text-xs font-semibold text-[#473267]">
                        Informações gerais e habilidades
                    </label>

                    <textarea 
                        name="detalhado" 
                        id="detalhado" 
                        rows="5"
                        class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] leading-6 text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                    ><?= htmlspecialchars(trim($stand->infor_gerais ?? "")) ?></textarea>
                </section>

                <section class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="flex items-center gap-2 font-title text-lg font-bold text-jojo-purple">
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
                                    <h3 class="flex items-center gap-2 font-title text-base font-bold text-jojo-dark">
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
                                        <label class="mb-1 block text-xs font-semibold text-[#473267]">
                                            Nome da habilidade
                                        </label>

                                        <input 
                                            type="text" 
                                            name="habilidade_nome[]" 
                                            value="<?= htmlspecialchars($habilidade->nome ?? '') ?>"
                                            class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                                        >
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-[#473267]">
                                            Tipo da habilidade
                                        </label>

                                        <select 
                                            name="habilidade_tipo[]"
                                            class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                                        >
                                            <option value="Stands de Curto Alcance" <?= ($habilidade->tipo ?? '') == "Stands de Curto Alcance" ? "selected" : "" ?>>
                                                Stands de Curto Alcance
                                            </option>

                                            <option value="Stands de Longa Distancia" <?= ($habilidade->tipo ?? '') == "Stands de Longa Distancia" ? "selected" : "" ?>>
                                                Stands de Longa Distância
                                            </option>

                                            <option value="Stands Automáticos" <?= ($habilidade->tipo ?? '') == "Stands Automáticos" ? "selected" : "" ?>>
                                                Stands Automáticos
                                            </option>
                                        </select>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="mb-1 block text-xs font-semibold text-[#473267]">
                                            Descrição da habilidade
                                        </label>

                                        <textarea 
                                            name="habilidade_descricao[]" 
                                            rows="3"
                                            class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] leading-6 text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                                        ><?= htmlspecialchars(trim($habilidade->descricao ?? "")) ?></textarea>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-[#473267]">
                                            Imagem da habilidade
                                        </label>

                                        <label class="flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-white p-3 transition hover:bg-purple-50">
                                            <img 
                                                id="preview_habilidade_imagem_<?= $index ?>"
                                                src="../<?= htmlspecialchars($habilidade->imagem ?? '') ?>"
                                                class="<?= empty($habilidade->imagem) ? 'hidden' : '' ?> h-32 w-full rounded-lg bg-white object-contain object-center"
                                                alt="Imagem da habilidade"
                                            >

                                            <div id="placeholder_habilidade_imagem_<?= $index ?>" class="<?= !empty($habilidade->imagem) ? 'hidden' : '' ?> flex h-32 w-full flex-col items-center justify-center text-center text-sm text-jojo-purple">
                                                <i class="fa-solid fa-cloud-arrow-up mb-2 text-xl"></i>
                                                Clique para trocar
                                            </div>

                                            <input type="hidden" name="habilidade_imagem_antiga[]" value="<?= htmlspecialchars($habilidade->imagem ?? '') ?>">

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
                                        <label class="mb-1 block text-xs font-semibold text-[#473267]">
                                            Diagrama de força
                                        </label>

                                        <label class="flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-jojo-border bg-white p-3 transition hover:bg-purple-50">
                                            <img 
                                                id="preview_habilidade_diagrama_<?= $index ?>"
                                                src="../<?= htmlspecialchars($habilidade->forca ?? '') ?>"
                                                class="<?= empty($habilidade->forca) ? 'hidden' : '' ?> h-32 w-full rounded-lg bg-white object-contain object-center"
                                                alt="Diagrama da habilidade"
                                            >

                                            <div id="placeholder_habilidade_diagrama_<?= $index ?>" class="<?= !empty($habilidade->forca) ? 'hidden' : '' ?> flex h-32 w-full flex-col items-center justify-center text-center text-sm text-jojo-purple">
                                                <i class="fa-solid fa-chart-simple mb-2 text-xl"></i>
                                                Clique para trocar
                                            </div>

                                            <input type="hidden" name="habilidade_diagrama_antigo[]" value="<?= htmlspecialchars($habilidade->forca ?? '') ?>">

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