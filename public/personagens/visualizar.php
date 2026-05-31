<?php
require_once "../../src/config/conexao.php";
require_once "../../src/includes/bloqueio.php";

function escapar(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function formatar_papel(?string $papel): string
{
    $papeis = [
        "vilao" => "Vilão",
        "protagonista" => "Protagonista",
        "jojobro" => "JoJoBro"
    ];

    return $papeis[$papel] ?? ucfirst((string) $papel);
}

$personagem_id = filter_input(INPUT_GET, "id_personagem", FILTER_VALIDATE_INT);
$parte_id = filter_input(INPUT_GET, "parte_id", FILTER_VALIDATE_INT);
$usuario_id = (int) ($_SESSION["usuario_id"] ?? 0);

if (!$personagem_id || !$usuario_id) {
    header("Location: index.php?status=id_invalido");
    exit;
}

/*
|--------------------------------------------------------------------------
| Caso a página tenha sido aberta sem parte_id
|--------------------------------------------------------------------------
| Seu link antigo enviava somente id_personagem. Assim, o sistema encontra
| automaticamente uma parte relacionada ao personagem.
|--------------------------------------------------------------------------
*/

if (!$parte_id) {
    $sql = "
        SELECT pp.parte_id
        FROM personagens_partes pp
        INNER JOIN personagens p
            ON p.id = pp.personagem_id
        WHERE pp.personagem_id = :personagem_id
          AND p.usuario_id = :usuario_id
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":personagem_id" => $personagem_id,
        ":usuario_id" => $usuario_id
    ]);

    $parte_id = (int) $stmt->fetchColumn();
}

if (!$parte_id) {
    header("Location: index.php?status=parte_invalida");
    exit;
}

/*
|--------------------------------------------------------------------------
| Busca personagem, parte, informações da relação e Stand
|--------------------------------------------------------------------------
*/

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
    header("Location: index.php?parte_id=" . urlencode((string) $parte_id) . "&status=id_invalido");
    exit;
}

$fotoAnime = !empty($personagem->foto_anime)
    ? "../" . ltrim($personagem->foto_anime, "/")
    : "";

$fotoManga = !empty($personagem->foto_manga)
    ? "../" . ltrim($personagem->foto_manga, "/")
    : "";

$fotoBiografia = !empty($personagem->foto_biografia)
    ? "../" . ltrim($personagem->foto_biografia, "/")
    : "";

$fotoPrincipal = $fotoAnime ?: $fotoManga;

$descricao = trim((string) $personagem->infor_gerais);

if ($descricao === "") {
    $descricao = "Nenhuma descrição geral foi cadastrada para este personagem.";
}

$biografia = trim((string) $personagem->biografia);

if ($biografia === "") {
    $biografia = "Nenhuma biografia foi cadastrada para este personagem.";
}

$descricaoFoto = trim((string) $personagem->descricao_foto_biografia);

if ($descricaoFoto === "") {
    $descricaoFoto = "Nenhuma descrição foi cadastrada para esta imagem.";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= escapar($personagem->nome); ?> | JoJo Archive</title>

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

        .painel {
            background: rgba(255, 255, 255, 0.76);
            backdrop-filter: blur(2px);
        }

        .tab-foto {
            color: #615078;
            background: transparent;
            transition: all 0.2s ease;
        }

        .tab-foto:hover:not(:disabled) {
            background: #faf5ff;
            color: #7045c9;
        }

        .tab-foto.tab-ativa {
            color: #7045c9;
            background: #f4ecff;
            box-shadow: inset 0 0 0 1px rgba(112, 69, 201, 0.2);
        }

        .tab-foto:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .imagem-principal {
            transition: opacity 0.22s ease;
        }

        .detalhe-linha:last-child {
            border-bottom: none;
        }

        .botao-acao {
            transition: transform 0.2s ease, filter 0.2s ease;
        }

        .botao-acao:hover {
            transform: translateY(-2px);
            filter: brightness(1.04);
        }
    </style>
</head>

<body class="flex min-h-screen flex-col bg-jojo-bg font-body text-jojo-dark">

    <?php require_once "../../src/includes/header.php"; ?>

    <main class="mx-auto w-full max-w-[1450px] flex-1 px-5 pb-10 pt-6 md:px-8">

        <!-- Breadcrumb -->
        <nav class="mb-7 flex flex-wrap items-center gap-4 text-xs font-semibold text-[#75658f] md:text-sm">

            <a href="../index.php" class="transition hover:text-jojo-purple">
                Todas as Partes
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <a href="../partes/visualizar.php?id=<?= $parte_id; ?>"
                class="transition hover:text-jojo-purple">
                <?= escapar($personagem->parte_nome); ?>
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <a href="index.php?parte_id=<?= $parte_id; ?>"
                class="transition hover:text-jojo-purple">
                Personagens
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <span class="text-jojo-purple">
                Ver Detalhes
            </span>

        </nav>

        <!-- Título -->
        <section class="mb-7 flex items-center gap-8">

            <div class="flex items-center gap-4">
                <span class="text-3xl text-jojo-lilac">✦</span>

                <h1 class="font-title text-3xl font-bold text-jojo-dark md:text-[37px]">
                    Ver Detalhes
                </h1>
            </div>

        </section>

        <!-- Bloco principal -->
        <section class="grid grid-cols-1 gap-5 lg:grid-cols-[0.9fr_1.1fr]">

            <!-- Fotos anime / mangá -->
            <article class="painel overflow-hidden rounded-[22px] border border-jojo-border p-4 shadow-card">

                <div class="mb-4 grid grid-cols-2 overflow-hidden rounded-lg border border-jojo-border bg-white p-1">

                    <button type="button"
                        data-imagem="<?= escapar($fotoAnime); ?>"
                        onclick="trocarFoto(this)"
                        <?= $fotoAnime === "" ? "disabled" : ""; ?>
                        class="tab-foto <?= $fotoAnime !== "" ? "tab-ativa" : ""; ?> flex h-10 items-center justify-center rounded-md text-xs font-semibold md:text-sm">
                        Foto no anime
                    </button>

                    <button type="button"
                        data-imagem="<?= escapar($fotoManga); ?>"
                        onclick="trocarFoto(this)"
                        <?= $fotoManga === "" ? "disabled" : ""; ?>
                        class="tab-foto <?= $fotoAnime === "" && $fotoManga !== "" ? "tab-ativa" : ""; ?> flex h-10 items-center justify-center rounded-md text-xs font-semibold md:text-sm">
                        Foto no mangá
                    </button>

                </div>

                <div class="relative h-[470px] overflow-hidden rounded-[17px] bg-purple-50 md:h-[555px]">

                    <?php if ($fotoPrincipal !== ""): ?>
                        <img id="fotoPrincipal"
                            src="<?= escapar($fotoPrincipal); ?>"
                            alt="Foto de <?= escapar($personagem->nome); ?>"
                            class="imagem-principal h-full w-full object-cover object-top">
                    <?php else: ?>
                        <div class="flex h-full w-full items-center justify-center text-7xl text-purple-200">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    <?php endif; ?>

                </div>

            </article>

            <!-- Informações do personagem -->
            <article class="painel flex flex-col rounded-[22px] border border-jojo-border p-6 shadow-card md:p-7">

                <span class="inline-flex w-fit items-center gap-2 rounded-md bg-purple-100 px-4 py-2 text-xs font-semibold text-jojo-purple">
                    <i class="fa-solid fa-user"></i>
                    Personagem
                </span>

                <h2 class="mt-4 font-title text-[38px] font-bold leading-none text-jojo-dark md:text-[43px]">
                    <?= escapar($personagem->nome); ?>
                </h2>

                <!-- Informações -->
                <div class="mt-8 overflow-hidden rounded-[15px] border border-jojo-border bg-white/70 px-5 md:px-6">

                    <div class="detalhe-linha flex items-center justify-between gap-4 border-b border-jojo-border py-5">
                        <div class="flex items-center gap-4 text-sm font-medium text-[#473267]">
                            <i class="fa-regular fa-compass text-xl text-jojo-purple"></i>
                            Parte
                        </div>

                        <div class="flex items-center gap-4 text-right text-sm font-semibold text-jojo-purple">
                            <?= escapar($personagem->parte_nome); ?>

                            <span class="flex h-10 w-10 items-center justify-center text-xs font-bold text-white"
                                style="clip-path: polygon(50% 0%, 63% 19%, 85% 15%, 81% 38%, 100% 50%, 81% 62%, 85% 85%, 63% 81%, 50% 100%, 37% 81%, 15% 85%, 19% 62%, 0% 50%, 19% 38%, 15% 15%, 37% 19%); background: #7045c9;">
                                <?= str_pad((string) $parte_id, 2, "0", STR_PAD_LEFT); ?>
                            </span>
                        </div>
                    </div>

                    <div class="detalhe-linha flex items-center justify-between gap-4 border-b border-jojo-border py-5">
                        <div class="flex items-center gap-4 text-sm font-medium text-[#473267]">
                            <i class="fa-regular fa-star text-xl text-jojo-purple"></i>
                            Stand associado
                        </div>

                        <p class="text-right text-sm font-semibold text-jojo-dark">
                            <?= escapar($personagem->stand_nome ?: "Sem Stand associado"); ?>
                        </p>
                    </div>

                    <div class="detalhe-linha flex items-center justify-between gap-4 border-b border-jojo-border py-5">
                        <div class="flex items-center gap-4 text-sm font-medium text-[#473267]">
                            <i class="fa-solid fa-tag text-lg text-jojo-purple"></i>
                            Papel
                        </div>

                        <p class="text-right text-sm font-semibold text-jojo-dark">
                            <?= escapar(formatar_papel($personagem->papel)); ?>
                        </p>
                    </div>

                    <div class="detalhe-linha flex items-center justify-between gap-4 py-5">
                        <div class="flex items-center gap-4 text-sm font-medium text-[#473267]">
                            <i class="fa-solid fa-user-clock text-lg text-jojo-purple"></i>
                            Idade
                        </div>

                        <p class="text-right text-sm font-semibold text-jojo-dark">
                            <?= !empty($personagem->idade) ? escapar($personagem->idade) . " anos" : "Não informada"; ?>
                        </p>
                    </div>

                </div>

                <!-- Descrição -->
                <div class="mt-5 flex-1 rounded-[15px] border border-jojo-border bg-white/70 px-5 py-5 md:px-6">

                    <div class="mb-4 flex items-center gap-3 text-sm font-semibold text-jojo-purple">
                        <i class="fa-solid fa-sparkles"></i>
                        Descrição
                    </div>

                    <p class="text-[13px] leading-7 text-[#433366] md:text-sm">
                        <?= nl2br(escapar($descricao)); ?>
                    </p>

                </div>

            </article>

        </section>

        <!-- Biografia -->
        <section class="painel mt-5 overflow-hidden rounded-[22px] border border-jojo-border shadow-card">

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1.35fr_0.85fr]">

                <!-- Texto da biografia -->
                <div class="px-7 py-6 md:px-8">

                    <div class="mb-4 flex items-center gap-4">
                        <i class="fa-solid fa-star text-lg text-jojo-purple"></i>

                        <h2 class="font-title text-xl font-bold text-jojo-purple md:text-2xl">
                            Biografia
                        </h2>
                    </div>

                    <p class="max-w-[740px] text-[13px] leading-7 text-[#433366] md:text-sm">
                        <?= nl2br(escapar($biografia)); ?>
                    </p>

                </div>

                <!-- Foto da biografia -->
                <div class="m-4 overflow-hidden rounded-[15px] border border-jojo-border bg-white">

                    <?php if ($fotoBiografia !== ""): ?>
                        <img src="<?= escapar($fotoBiografia); ?>"
                            alt="Biografia de <?= escapar($personagem->nome); ?>"
                            class="h-[185px] w-full object-cover object-top md:h-[195px]">
                    <?php else: ?>
                        <div class="flex h-[185px] items-center justify-center bg-purple-50 text-5xl text-purple-200 md:h-[195px]">
                            <i class="fa-regular fa-image"></i>
                        </div>
                    <?php endif; ?>

                    <div class="px-5 py-4">

                        <div class="mb-2 flex items-center gap-2 text-xs font-semibold text-jojo-purple">
                            <i class="fa-solid fa-camera"></i>
                            Descrição da foto biográfica
                        </div>

                        <p class="text-[12px] leading-6 text-[#66567d]">
                            <?= escapar($descricaoFoto); ?>
                        </p>

                    </div>

                </div>

            </div>

        </section>

        <!-- Botões finais -->
        <section class="mx-auto mt-8 grid max-w-[1060px] grid-cols-1 gap-4 sm:grid-cols-3">

            <a href="index.php?parte_id=<?= $parte_id; ?>"
                class="botao-acao flex h-[56px] items-center justify-center gap-3 rounded-xl border border-purple-200 bg-white text-sm font-semibold text-jojo-purple shadow-soft">
                <i class="fa-solid fa-arrow-left"></i>
                Voltar
            </a>

            <a href="excluir.php?id_personagem=<?= $personagem->id; ?>&parte_id=<?= $parte_id; ?>"
                onclick="return confirm('Tem certeza que deseja excluir este personagem?')"
                class="botao-acao flex h-[56px] items-center justify-center gap-3 rounded-xl bg-gradient-to-r from-[#dd438f] to-[#ed599b] text-sm font-semibold text-white shadow-button">
                <i class="fa-regular fa-trash-can"></i>
                Apagar
            </a>

            <a href="editar.php?id_personagem=<?= $personagem->id; ?>&parte_id=<?= $parte_id; ?>"
                class="botao-acao flex h-[56px] items-center justify-center gap-3 rounded-xl bg-gradient-to-r from-[#7045c9] to-[#9665dc] text-sm font-semibold text-white shadow-button">
                <i class="fa-solid fa-pencil"></i>
                Editar
            </a>

        </section>

    </main>

    <script>
        function trocarFoto(botao) {
            const novaImagem = botao.dataset.imagem;
            const imagemPrincipal = document.getElementById("fotoPrincipal");

            if (!novaImagem || !imagemPrincipal) {
                return;
            }

            document.querySelectorAll(".tab-foto").forEach((tab) => {
                tab.classList.remove("tab-ativa");
            });

            botao.classList.add("tab-ativa");

            imagemPrincipal.style.opacity = "0";

            setTimeout(() => {
                imagemPrincipal.src = novaImagem;
                imagemPrincipal.style.opacity = "1";
            }, 150);
        }
    </script>

</body>
</html>