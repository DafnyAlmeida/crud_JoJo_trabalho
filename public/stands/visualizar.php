<?php
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
include_once "../../src/functions/gerais.php";

$stand_id = validar_id_get("id_stand");
$parte_id_url = validar_id_get("parte_id");

if (!$stand_id) {
    header("Location: index.php?status=erro");
    exit;
}

$sql = "
    SELECT 
        s.*,
        p.nome AS personagem_nome,
        pt.id AS parte_id,
        pt.nome AS parte_nome
    FROM stands s
    LEFT JOIN personagens p
        ON p.id = s.personagem_id
    LEFT JOIN personagens_partes pp
        ON pp.personagem_id = p.id
    LEFT JOIN partes pt
        ON pt.id = pp.parte_id
    WHERE s.id = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $stand_id
]);

$stand = $stmt->fetch(PDO::FETCH_OBJ);

if (!$stand) {
    header("Location: index.php?status=erro");
    exit;
}

$parte_id = $stand->parte_id ?? $parte_id_url;

$sql = "SELECT * FROM stand_habilidades WHERE stand_id = :stand_id ORDER BY id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":stand_id" => $stand_id
]);

$habilidades = $stmt->fetchAll(PDO::FETCH_OBJ);

$foto_anime = imagemStand($stand->foto_anime ?? "");
$foto_manga = imagemStand($stand->foto_manga ?? "");

$foto_inicial = "";

if ($foto_anime !== "") {
    $foto_inicial = "anime";
} elseif ($foto_manga !== "") {
    $foto_inicial = "manga";
}

$texto_habilidades_gerais = $stand->habilidade_texto_geral ?? $stand->infor_gerais ?? "";

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escapar($stand->nome); ?> | JoJo Archive</title>
    <link rel="icon" type="image/png" href="../assets/img/logo.png">

    <!-- Icon Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        crossorigin="anonymous"
        referrerpolicy="no-referrer">

    <!-- Fontes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Tailwind -->
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
    <?php require_once "../../src/includes/header.php"; ?>
    <main class="mx-auto w-full max-w-[1450px] px-10 pb-4 pt-6">

        <!-- Caminho da página -->
        <nav class="mb-6 flex flex-wrap items-center gap-4 text-xs md:text-sm">
            <a href="../index.php"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                Todas as Partes
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <a href="..partes/visualizar.php?id=<?= $parte_id; ?>"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                <?= escapar($stand->parte_nome); ?>
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <a href="index.php?parte_id=<?= $parte_id; ?>"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                Stands
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <span class="font-semibold text-jojo-purple">
                Ver detalhes
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
                        Ver detalhes
                        <span class="ml-1 text-sm text-jojo-lilac">✦✦</span>
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Visualize as imagens, descrição e habilidades cadastradas.
                    </p>
                </div>
            </div>

            <div class="flex gap-3">
                <a href="editar.php?id_stand=<?= (int) $stand->id; ?>&parte_id=<?= (int) $parte_id; ?>"
                    class="inline-flex items-center gap-2 rounded-xl bg-jojo-purple px-5 py-2.5 text-sm font-bold text-white shadow-button transition hover:bg-purple-700">
                    <i class="fa-solid fa-pen"></i>
                    Editar
                </a>
            </div>
        </section>

        <!-- Topo: imagens + informações -->
        <section class="grid grid-cols-1 gap-5 lg:grid-cols-[0.95fr_1.05fr]">

            <!-- Imagens anime/mangá -->
            <article class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                <h2 class="mb-4 flex items-center gap-2 font-title text-lg font-bold text-jojo-purple">
                    <i class="fa-regular fa-image"></i>
                    Imagens do stand
                </h2>

                <div class="mb-4 grid grid-cols-2 gap-2">
                    <button type="button"
                        id="btnAnime"
                        onclick="trocarFoto('anime')"
                        class="botao-foto <?= $foto_inicial === 'anime' ? 'ativo' : ''; ?> h-10 rounded-xl text-xs font-semibold transition">
                        Anime
                    </button>

                    <button type="button"
                        id="btnManga"
                        onclick="trocarFoto('manga')"
                        class="botao-foto <?= $foto_inicial === 'manga' ? 'ativo' : ''; ?> h-10 rounded-xl text-xs font-semibold transition">
                        Mangá
                    </button>
                </div>

                <div class="overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3">
                    <?php if (!empty($foto_anime)): ?>
                        <img id="fotoAnime"
                            src="<?= escapar($foto_anime); ?>"
                            alt="Foto no anime de <?= escapar($stand->nome); ?>"
                            class="foto-opcao <?= $foto_inicial === 'anime' ? 'ativa' : ''; ?> h-[560px] w-full rounded-lg bg-white object-contain object-center">
                    <?php endif; ?>

                    <?php if (!empty($foto_manga)): ?>
                        <img id="fotoManga"
                            src="<?= escapar($foto_manga); ?>"
                            alt="Foto no mangá de <?= escapar($stand->nome); ?>"
                            class="foto-opcao <?= $foto_inicial === 'manga' ? 'ativa' : ''; ?> h-[560px] w-full rounded-lg bg-white object-contain object-center">
                    <?php endif; ?>

                    <?php if (empty($foto_anime) && empty($foto_manga)): ?>
                        <div class="flex h-[560px] w-full flex-col items-center justify-center rounded-lg bg-white text-center text-sm text-jojo-purple">
                            <i class="fa-regular fa-image mb-2 text-3xl"></i>
                            Nenhuma imagem cadastrada.
                        </div>
                    <?php endif; ?>
                </div>
            </article>

            <!-- Informações principais -->
            <article class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
                <span class="inline-flex rounded-md bg-purple-100 px-3 py-1 text-[11px] font-semibold text-jojo-purple">
                    Stand
                </span>

                <h2 class="mt-3 font-title text-4xl font-bold leading-tight text-jojo-dark md:text-[27px]">
                    <?= escapar($stand->nome); ?>
                </h2>

                <div class="mt-5 overflow-hidden rounded-xl border border-jojo-border bg-white/70 text-sm">
                    <div class="grid grid-cols-[24px_1fr_auto] items-center gap-3 border-b border-jojo-border px-4 py-4">
                        <i class="fa-solid fa-star text-jojo-purple"></i>

                        <span class="font-semibold text-[#604b8d]">
                            Parte
                        </span>

                        <strong class="text-right text-jojo-purple">
                            <?= escapar($stand->parte_nome ?? "Não informado"); ?>
                        </strong>
                    </div>

                    <div class="grid grid-cols-[24px_1fr_auto] items-center gap-3 border-b border-jojo-border px-4 py-4">
                        <i class="fa-solid fa-user text-jojo-purple"></i>

                        <span class="font-semibold text-[#604b8d]">
                            Usuário
                        </span>

                        <strong class="text-right text-[#30204f]">
                            <?= escapar($stand->personagem_nome ?? "Não informado"); ?>
                        </strong>
                    </div>

                    <div class="grid grid-cols-[24px_1fr_auto] items-center gap-3 px-4 py-4">
                        <i class="fa-solid fa-bullseye text-jojo-purple"></i>

                        <span class="font-semibold text-[#604b8d]">
                            Tipo
                        </span>

                        <strong class="text-right text-[#30204f]">
                            <?= escapar($stand->tipo ?? "Não informado"); ?>
                        </strong>
                    </div>
                </div>

                <div class="mt-5 rounded-xl border border-jojo-border bg-white/70 p-5">
                    <h3 class="mb-2 flex items-center gap-2 font-title text-xl font-bold text-jojo-purple">
                        <i class="fa-solid fa-star text-sm text-jojo-purple"></i>
                        Resumo
                    </h3>

                    <p class="text-[13px] leading-7 text-[#433366]">
                        <?= nl2br(escapar($stand->descricao ?? "Nenhuma descrição cadastrada.")); ?>
                    </p>
                </div>

            </article>
        </section>

        <!-- Descrição -->
        <section class="mt-5 rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
            <h2 class="mb-3 flex items-center gap-2 font-title text-2xl font-bold text-jojo-purple">
                <i class="fa-regular fa-file-lines text-base"></i>
                Descrição
            </h2>

            <p class="text-[13px] leading-7 text-[#433366]">
                <?= nl2br(escapar($stand->descricao ?? "Nenhuma descrição cadastrada.")); ?>
            </p>
        </section>

        <!-- Habilidades gerais -->
        <section class="mt-5 rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
            <h2 class="mb-3 flex items-center gap-2 font-title text-2xl font-bold text-jojo-purple">
                <i class="fa-solid fa-bolt text-base"></i>
                Habilidades gerais
            </h2>

            <p class="text-[13px] leading-7 text-[#433366]">
                <?= nl2br(escapar($texto_habilidades_gerais ?: "Nenhuma informação geral de habilidade cadastrada.")); ?>
            </p>
        </section>

        <!-- Habilidades cadastradas -->
        <section class="mt-5 rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <h2 class="flex items-center gap-2 font-title text-2xl font-bold text-jojo-purple">
                        <i class="fa-solid fa-star text-base"></i>
                        Habilidades cadastradas
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Foto da habilidade, descrição e diagrama de força.
                    </p>
                </div>
            </div>

            <?php if (empty($habilidades)): ?>
                <div class="rounded-xl border border-dashed border-jojo-border bg-purple-50/30 p-6 text-center text-sm font-medium text-slate-400">
                    Nenhuma habilidade cadastrada ainda.
                </div>
            <?php endif; ?>

            <div class="space-y-5">
                <?php foreach ($habilidades as $index => $habilidade): ?>
                    <?php
                        $imagem_habilidade = imagemStand($habilidade->imagem ?? "");
                        $diagrama_habilidade = imagemStand($habilidade->forca ?? "");
                    ?>

                    <article class="rounded-2xl border border-jojo-border bg-purple-50/30 p-4 shadow-soft">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-jojo-purple text-sm font-bold text-white">
                                <?= $index + 1; ?>
                            </span>

                            <div>
                                <h3 class="font-title text-xl font-bold text-jojo-dark">
                                    <?= escapar($habilidade->nome ?? "Habilidade sem nome"); ?>
                                </h3>

                                <?php if (!empty($habilidade->tipo)): ?>
                                    <p class="text-xs font-semibold text-jojo-purple">
                                        <?= escapar($habilidade->tipo); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[280px_1fr_280px]">

                            <!-- Foto da habilidade -->
                            <div class="rounded-xl border border-jojo-border bg-white p-3">
                                <h4 class="mb-2 text-center text-xs font-bold text-jojo-purple">
                                    Foto da habilidade
                                </h4>

                                <?php if (!empty($imagem_habilidade)): ?>
                                    <img src="<?= escapar($imagem_habilidade); ?>"
                                        alt="Imagem da habilidade <?= escapar($habilidade->nome ?? ""); ?>"
                                        class="h-[230px] w-full rounded-lg object-contain object-center">
                                <?php else: ?>
                                    <div class="flex h-[230px] w-full flex-col items-center justify-center rounded-lg bg-purple-50 text-center text-xs text-[#604b8d]">
                                        <i class="fa-regular fa-image mb-2 text-2xl text-jojo-lilac"></i>
                                        Sem imagem.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Texto no centro -->
                            <div class="flex min-h-[230px] items-center rounded-xl border border-jojo-border bg-white p-5">
                                <p class="text-[13px] leading-7 text-[#433366]">
                                    <?= nl2br(escapar($habilidade->descricao ?? "Nenhuma descrição cadastrada para esta habilidade.")); ?>
                                </p>
                            </div>

                            <!-- Diagrama de força -->
                            <div class="rounded-xl border border-jojo-border bg-white p-3">
                                <h4 class="mb-2 text-center text-xs font-bold text-jojo-purple">
                                    Diagrama de força
                                </h4>

                                <?php if (!empty($diagrama_habilidade)): ?>
                                    <img src="<?= escapar($diagrama_habilidade); ?>"
                                        alt="Diagrama de força de <?= escapar($habilidade->nome ?? ""); ?>"
                                        class="h-[230px] w-full rounded-lg object-contain object-center">
                                <?php else: ?>
                                    <div class="flex h-[230px] w-full flex-col items-center justify-center rounded-lg bg-purple-50 text-center text-xs text-[#604b8d]">
                                        <i class="fa-solid fa-chart-simple mb-2 text-2xl text-jojo-lilac"></i>
                                        Sem diagrama.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Botões finais -->
        <section class="mx-auto mt-6 grid max-w-[880px] grid-cols-1 gap-3 sm:grid-cols-3">
            <a href="index.php?parte_id=<?= $parte_id; ?>"
                class="botao-acao flex h-11 items-center justify-center gap-2 rounded-xl border border-purple-200 bg-white text-xs font-semibold text-jojo-purple shadow-soft md:text-sm">
                <i class="fa-solid fa-arrow-left"></i>
                Voltar
            </a>

            <a href="excluir.php?id_stand=<?= $stand->id; ?>&parte_id=<?= $parte_id; ?>"
                onclick="return confirm('Tem certeza que deseja excluir este stand?')"
                class="botao-acao flex h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#dd438f] to-[#ed599b] text-xs font-semibold text-white shadow-button md:text-sm">
                <i class="fa-regular fa-trash-can"></i>
                Apagar
            </a>

            <a href="editar.php?id_stand=<?= $stand->id; ?>&parte_id=<?= $parte_id; ?>"
                class="botao-acao flex h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#7045c9] to-[#9665dc] text-xs font-semibold text-white shadow-button md:text-sm">
                <i class="fa-solid fa-pencil"></i>
                Editar
            </a>
        </section>
    </main>

    <?php require_once "../../src/includes/footer.php"; ?>

    <script>
        function trocarFoto(tipo) {
            const fotoAnime = document.getElementById("fotoAnime");
            const fotoManga = document.getElementById("fotoManga");
            const btnAnime = document.getElementById("btnAnime");
            const btnManga = document.getElementById("btnManga");

            if (tipo === "anime") {
                if (fotoAnime) {
                    fotoAnime.classList.add("ativa");
                }

                if (fotoManga) {
                    fotoManga.classList.remove("ativa");
                }

                if (btnAnime) {
                    btnAnime.classList.add("ativo");
                }

                if (btnManga) {
                    btnManga.classList.remove("ativo");
                }
            }

            if (tipo === "manga") {
                if (fotoManga) {
                    fotoManga.classList.add("ativa");
                }

                if (fotoAnime) {
                    fotoAnime.classList.remove("ativa");
                }

                if (btnManga) {
                    btnManga.classList.add("ativo");
                }

                if (btnAnime) {
                    btnAnime.classList.remove("ativo");
                }
            }
        }
    </script>
</body>
</html>