<?php

require_once "../../src/config/conexao.php";
require_once "../../src/includes/bloqueio.php";
require_once "../../src/functions/gerais.php";

$personagem_id = validar_id_get("id_personagem");
$parte_id = validar_id_get("parte_id");

$usuario_id = (int) ($_SESSION["usuario_id"] ?? 0);

if (!$personagem_id || !$usuario_id) {
    header("Location: index.php?status=personagem_invalido");
    exit;
}

if (!$parte_id) {
    $parte_id = buscarParteId($pdo, $personagem_id, $usuario_id);
}

if (!$parte_id) {
    header("Location: index.php?status=parte_invalida");
    exit;
}

// Buscar detalhes da parte

$sql = "
    SELECT
        p.*,
        pp.idade,
        pp.papel,
        pa.nome AS parte_nome,
        (
            SELECT s.nome
            FROM stands s
            WHERE s.personagem_id = p.id
            AND s.usuario_id = p.usuario_id
            ORDER BY s.id ASC
            LIMIT 1
        ) AS stand_nome
    FROM personagens p

    INNER JOIN personagens_partes pp
        ON pp.personagem_id = p.id

    INNER JOIN partes pa
        ON pa.id = pp.parte_id

    WHERE p.id = :personagem_id
    AND p.usuario_id = :usuario_id
    AND pp.parte_id = :parte_id

    LIMIT 1
    ";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":personagem_id" => $personagem_id,
    ":usuario_id" => $usuario_id,
    ":parte_id" => $parte_id
]);

$personagem = $stmt->fetch(PDO::FETCH_OBJ);

if (!$personagem) {
    header("Location: index.php?parte_id=" . urlencode((string) $parte_id) . "&status=personagem_invalido");
    exit;
}

$fotoAnime = caminho_foto($personagem->foto_anime);
$fotoManga = caminho_foto($personagem->foto_manga);
$fotoBiografia = caminho_foto($personagem->foto_biografia);

$fotoPrincipal = $fotoAnime ?: $fotoManga;

$descricao = texto_ou_padrao(
    $personagem->infor_gerais,
    "Nenhuma descrição geral foi cadastrada para este personagem."
);

$biografia = texto_ou_padrao(
    $personagem->biografia,
    "Nenhuma biografia foi cadastrada para este personagem."
);

$descricaoFoto = texto_ou_padrao(
    $personagem->descricao_foto_biografia,
    "Nenhuma descrição foi cadastrada para esta imagem."
);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escapar($personagem->nome); ?> | JoJo Archive</title>
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
                        card: "0 5px 18px rgba(67, 40, 105, 0.08)",
                        soft: "0 3px 14px rgba(67, 40, 105, 0.06)",
                        button: "0 7px 17px rgba(101, 52, 197, 0.18)"
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background:
                radial-gradient(circle at 94% 16%, rgba(112, 69, 201, 0.08), transparent 17%),
                radial-gradient(circle at 5% 85%, rgba(221, 67, 143, 0.04), transparent 19%),
                linear-gradient(180deg, #fefcff 0%, #fbf9ff 100%);
        }

        .foto-opcao {
            display: none;
        }

        .foto-opcao.ativa {
            display: block;
        }

        .botao-foto {
            color: #7045c9;
            background: #f7f2ff;
            border: 1px solid #e7ddfa;
        }

        .botao-foto.ativo {
            color: #fff;
            background: linear-gradient(135deg, #7045c9, #9665dc);
            box-shadow: 0 10px 20px rgba(112, 69, 201, 0.18);
            border-color: transparent;
        }
        
    </style>
</head>

<body class="flex min-h-screen flex-col bg-jojo-bg font-body text-jojo-dark">

    <?php require_once "../../src/includes/header.php"; ?>

    <main class="mx-auto w-full max-w-[1450px] px-10 pb-7 pt-6">

        <!-- Caminho da página -->
        <nav class="mb-4 flex flex-wrap items-center gap-4 text-xs md:text-sm">
            <a href="../index.php"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                Todas as Partes
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <a href="../partes/visualizar.php?id=<?= $parte_id; ?>"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                <?= escapar($personagem->parte_nome); ?>
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <a href="index.php?parte_id=<?= $parte_id; ?>"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                Personagens
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <span class="font-semibold text-jojo-purple">
               Ver detalhes
            </span>
        </nav>

        <!-- Cabeçalho -->
        <section class="mb-7 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
            <div class="flex items-center gap-4">
                <div>
                    <h1 class="font-title text-xl font-bold text-jojo-dark md:text-[25px]">
                        Ver detalhes
                        <span class="ml-1 text-sm text-jojo-lilac">✦✦</span>
                    </h1>
                    <p class="mt-1 text-xs font-medium text-[#887d98]">
                        Informações cadastradas sobre esta referência.
                    </p>
                </div>
            </div>
        </section>

        <!-- Bloco principal -->
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-[0.78fr_1.22fr]">
            <!-- Fotos anime / mangá -->
            <article class="painel overflow-hidden rounded-2xl border border-jojo-border p-5 shadow-card">

                <h2 class="mb-4 flex items-center gap-2 font-title text-lg font-bold text-jojo-purple">
                    <i class="fa-regular fa-image"></i>
                    Fotos do personagem
                </h2>

                <div class="mb-4 grid grid-cols-2 gap-2">
                    <button type="button"
                        id="btnAnime"
                        onclick="trocarFoto('anime')"
                        <?= $fotoAnime === "" ? "disabled" : ""; ?>
                        class="botao-foto <?= $fotoAnime !== "" ? "ativo" : ""; ?> h-10 rounded-xl text-xs font-semibold transition">
                        Anime
                    </button>

                    <button type="button"
                        id="btnManga"
                        onclick="trocarFoto('manga')"
                        <?= $fotoManga === "" ? "disabled" : ""; ?>
                        class="botao-foto <?= $fotoAnime === "" && $fotoManga !== "" ? "ativo" : ""; ?> h-10 rounded-xl text-xs font-semibold transition">
                        Mangá
                    </button>
                </div>

                <div class="overflow-hidden rounded-xl border border-dashed border-jojo-border bg-purple-50/40 p-3">
                    <?php if ($fotoAnime !== ""): ?>
                        <img
                            id="fotoAnime"
                            src="<?= escapar($fotoAnime); ?>"
                            alt="Foto no anime de <?= escapar($personagem->nome); ?>"
                            class="foto-opcao <?= $fotoAnime !== "" ? "ativa" : ""; ?> h-[560px] w-full rounded-lg bg-white object-contain object-center">
                    <?php endif; ?>

                    <?php if ($fotoManga !== ""): ?>
                        <img
                            id="fotoManga"
                            src="<?= escapar($fotoManga); ?>"
                            alt="Foto no mangá de <?= escapar($personagem->nome); ?>"
                            class="foto-opcao <?= $fotoAnime === "" && $fotoManga !== "" ? "ativa" : ""; ?> h-[560px] w-full rounded-lg bg-white object-contain object-center">
                    <?php endif; ?>

                    <?php if ($fotoAnime === "" && $fotoManga === ""): ?>
                        <div class="flex h-[560px] w-full flex-col items-center justify-center rounded-lg bg-white text-center text-sm text-jojo-purple">
                            <i class="fa-regular fa-image mb-2 text-3xl"></i>
                            Nenhuma imagem cadastrada.
                        </div>
                    <?php endif; ?>
                </div>

            </article>

            <!-- Informações do personagem -->
            <article class="painel flex flex-col rounded-2xl border border-jojo-border p-5 shadow-card md:p-6">

                <span class="inline-flex w-fit items-center gap-2 rounded-lg bg-purple-100 px-3 py-1.5 text-[11px] font-semibold text-jojo-purple">
                    <i class="fa-solid fa-user text-xs"></i>
                    Personagem
                </span>

                <h2 class="mt-3 font-title text-[30px] font-bold leading-none text-jojo-dark md:text-[27px]">
                    <?= escapar($personagem->nome); ?>
                </h2>

                <!-- Informações -->
                <div class="mt-5 overflow-hidden rounded-2xl border border-jojo-border bg-white/70 px-4">

                    <div class="detalhe-linha flex items-center justify-between gap-4 border-b border-jojo-border py-3.5">
                        <div class="flex items-center gap-3 text-xs font-medium text-[#473267] md:text-sm">
                            <i class="fa-regular fa-compass text-base text-jojo-purple"></i>
                            Parte
                        </div>

                        <div class="flex items-center gap-3 text-right text-xs font-semibold text-jojo-purple md:text-sm">
                            <?= escapar($personagem->parte_nome); ?>

                            <span class="flex h-8 w-8 items-center justify-center text-[10px] font-bold text-white"
                                style="clip-path: polygon(50% 0%, 63% 19%, 85% 15%, 81% 38%, 100% 50%, 81% 62%, 85% 85%, 63% 81%, 50% 100%, 37% 81%, 15% 85%, 19% 62%, 0% 50%, 19% 38%, 15% 15%, 37% 19%); background: #7045c9;">
                                <?= str_pad((string) $parte_id, 2, "0", STR_PAD_LEFT); ?>
                            </span>
                        </div>
                    </div>

                    <div class="detalhe-linha flex items-center justify-between gap-4 border-b border-jojo-border py-3.5">
                        <div class="flex items-center gap-3 text-xs font-medium text-[#473267] md:text-sm">
                            <i class="fa-regular fa-star text-base text-jojo-purple"></i>
                            Stand associado
                        </div>

                        <p class="text-right text-xs font-semibold text-jojo-dark md:text-sm">
                            <?= escapar($personagem->stand_nome ?: "Sem Stand associado"); ?>
                        </p>
                    </div>

                    <div class="detalhe-linha flex items-center justify-between gap-4 border-b border-jojo-border py-3.5">
                        <div class="flex items-center gap-3 text-xs font-medium text-[#473267] md:text-sm">
                            <i class="fa-solid fa-tag text-sm text-jojo-purple"></i>
                            Papel
                        </div>

                        <p class="text-right text-xs font-semibold text-jojo-dark md:text-sm">
                            <?= escapar(formatar_papel($personagem->papel)); ?>
                        </p>
                    </div>

                    <div class="detalhe-linha flex items-center justify-between gap-4 py-3.5">
                        <div class="flex items-center gap-3 text-xs font-medium text-[#473267] md:text-sm">
                            <i class="fa-solid fa-user-clock text-sm text-jojo-purple"></i>
                            Idade
                        </div>

                        <p class="text-right text-xs font-semibold text-jojo-dark md:text-sm">
                            <?= !empty($personagem->idade) ? escapar($personagem->idade) . " anos" : "Não informada"; ?>
                        </p>
                    </div>

                </div>

                <!-- Descrição -->
                <div class="mt-4 flex-1 rounded-2xl border border-jojo-border bg-white/70 px-4 py-4">

                    <div class="mb-3 font-title flex items-center gap-2 text-lg font-semibold text-jojo-purple">
                        <i class="fa-solid fa-star text-sm text-jojo-purple"></i>
                        Descrição
                    </div>

                    <p class="text-[12px] leading-6 text-[#433366] md:text-[13px]">
                        <?= nl2br(escapar($descricao)); ?>
                    </p>
                </div>
            </article>
        </section>

        <!-- Biografia -->
        <section class="painel mt-4 overflow-hidden rounded-2xl border border-jojo-border shadow-card">
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-[1.4fr_0.8fr]">

                <!-- Texto da biografia -->
                <div class="px-5 py-5 md:px-6">

                    <div class="mb-3 flex items-center gap-3">
                        <i class="fa-solid fa-star text-sm text-jojo-purple"></i>

                        <h2 class="font-title text-lg font-bold text-jojo-purple md:text-xl">
                            Biografia
                        </h2>
                    </div>

                    <p class="max-w-[760px] text-[12px] leading-6 text-[#433366] md:text-[13px]">
                        <?= nl2br(escapar($biografia)); ?>
                    </p>
                </div>

                <!-- Foto da biografia -->
                <div class="m-3 overflow-hidden rounded-2xl border border-jojo-border bg-white">
                    <?php if ($fotoBiografia !== ""): ?>
                        <img src="<?= escapar($fotoBiografia); ?>"
                            alt="Biografia de <?= escapar($personagem->nome); ?>"
                            class="h-[150px] w-full object-cover object-top md:h-[165px]">
                    <?php else: ?>
                        <div class="flex h-[150px] items-center justify-center bg-purple-50 text-4xl text-purple-200 md:h-[165px]">
                            <i class="fa-regular fa-image"></i>
                        </div>
                    <?php endif; ?>

                    <div class="px-4 py-3">
                        <div class="mb-1.5 flex items-center gap-2 text-[11px] font-semibold text-jojo-purple">
                            <i class="fa-solid fa-camera"></i>
                            Descrição da foto biográfica
                        </div>

                        <p class="text-[11px] leading-5 text-[#66567d]">
                            <?= escapar($descricaoFoto); ?>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Botões finais -->
        <section class="mx-auto mt-6 grid max-w-[880px] grid-cols-1 gap-3 sm:grid-cols-3">
            <a href="index.php?parte_id=<?= $parte_id; ?>"
                class="botao-acao flex h-11 items-center justify-center gap-2 rounded-xl border border-purple-200 bg-white text-xs font-semibold text-jojo-purple shadow-soft md:text-sm">
                <i class="fa-solid fa-arrow-left"></i>
                Voltar
            </a>

            <a href="excluir.php?id_personagem=<?= $personagem->id; ?>&parte_id=<?= $parte_id; ?>"
                onclick="return confirm('Tem certeza que deseja excluir este personagem?')"
                class="botao-acao flex h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#dd438f] to-[#ed599b] text-xs font-semibold text-white shadow-button md:text-sm">
                <i class="fa-regular fa-trash-can"></i>
                Apagar
            </a>

            <a href="editar.php?id_personagem=<?= $personagem->id; ?>&parte_id=<?= $parte_id; ?>"
                class="botao-acao flex h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#7045c9] to-[#9665dc] text-xs font-semibold text-white shadow-button md:text-sm">
                <i class="fa-solid fa-pencil"></i>
                Editar
            </a>
        </section>
        </section>
    </main>

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