<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
require_once "../../src/functions/gerais.php";

$parte_id = validar_id_get("parte_id");
$usuario_id = (int) ($_SESSION["usuario_id"] ?? 0);

if (!$parte_id || !$usuario_id) {
    header("Location: ../index.php?status=parte_invalida");
    exit;
}

/* Buscar a parte */
$sql = "
    SELECT id, nome
    FROM partes
    WHERE id = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $parte_id
]);

$parte = $stmt->fetch(PDO::FETCH_OBJ);

if (!$parte) {
    header("Location: ../index.php?status=parte_invalida");
    exit;
}

/* Paginação */
$por_pagina = 8;
$pagina_atual = filter_input(INPUT_GET, "pagina", FILTER_VALIDATE_INT) ?: 1;

/* Total de referências */
$sql = "
    SELECT COUNT(*)
    FROM referencias
    WHERE parte_id = :parte_id
    AND usuario_id = :usuario_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":parte_id" => $parte_id,
    ":usuario_id" => $usuario_id
]);

$total_referencias = (int) $stmt->fetchColumn();

$total_paginas = max(1, (int) ceil($total_referencias / $por_pagina));

if ($pagina_atual < 1) {
    $pagina_atual = 1;
} elseif ($pagina_atual > $total_paginas) {
    $pagina_atual = $total_paginas;
}

$offset = ($pagina_atual - 1) * $por_pagina;

/* Buscar referências da página atual */
$sql = "
    SELECT 
        id,
        usuario_id,
        titulo,
        imagem,
        descricao
    FROM referencias
    WHERE parte_id = :parte_id
    AND usuario_id = :usuario_id
    ORDER BY id DESC
    LIMIT :limite OFFSET :offset
";

$stmt = $pdo->prepare($sql);

$stmt->bindValue(":parte_id", $parte_id, PDO::PARAM_INT);
$stmt->bindValue(":usuario_id", $usuario_id, PDO::PARAM_INT);
$stmt->bindValue(":limite", $por_pagina, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

$stmt->execute();

$referencias = $stmt->fetchAll(PDO::FETCH_OBJ);

function link_pagina_referencia(int $parte_id, int $pagina): string {
    return "index.php?parte_id=" . urlencode((string) $parte_id) . "&pagina=" . urlencode((string) $pagina);
}

$temas_referencias = [
    [
        "cor" => "#7045c9",
        "clara" => "#f1e9ff",
        "icone" => "fa-solid fa-book-open",
        "decoracao" => "fa-solid fa-wand-magic-sparkles"
    ],
    [
        "cor" => "#dd438f",
        "clara" => "#ffe8f3",
        "icone" => "fa-solid fa-feather",
        "decoracao" => "fa-regular fa-star"
    ],
    [
        "cor" => "#8b5cf6",
        "clara" => "#efe7ff",
        "icone" => "fa-solid fa-scroll",
        "decoracao" => "fa-solid fa-star"
    ],
    [
        "cor" => "#5b49b8",
        "clara" => "#ebe9ff",
        "icone" => "fa-solid fa-compass",
        "decoracao" => "fa-solid fa-gem"
    ]
];
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referências | <?= escapar($parte->nome); ?></title>

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

        .card-referencia {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .card-referencia:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(66, 38, 102, 0.12);
        }

        .card-referencia:hover .foto-referencia {
            transform: scale(1.05);
        }

        .foto-referencia {
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

        .texto-limitado {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>

<body class="flex min-h-screen flex-col bg-jojo-bg font-body text-jojo-dark">

    <?php require_once "../../src/includes/header.php"; ?>

    <main class="mx-auto w-full max-w-[1450px] flex-1 px-5 pb-10 pt-7 md:px-7">

        <!-- Caminho da página -->
        <nav class="mb-8 flex flex-wrap items-center gap-4 text-xs md:text-sm">
            <a href="../index.php"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                Todas as Partes
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <a href="../partes/visualizar.php?id=<?= escapar($parte_id); ?>"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                <?= escapar($parte->nome); ?>
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <span class="font-semibold text-[#665387]">
                Referências
            </span>
        </nav>

        <!-- Cabeçalho -->
        <section class="mb-7 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">

            <div class="flex items-center gap-4">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-purple-100 bg-white text-xl text-jojo-purple shadow-soft">
                    <i class="fa-solid fa-book-open"></i>
                </span>

                <div>
                    <h1 class="font-title text-xl font-bold text-jojo-dark md:text-[25px]">
                        Minhas Referências
                        <span class="ml-1 text-sm text-jojo-lilac">✦✦</span>
                    </h1>

                    <p class="mt-1 text-xs font-medium text-[#887d98]">
                        Página <?= escapar($pagina_atual); ?> de <?= escapar($total_paginas); ?>
                    </p>
                </div>
            </div>

            <a href="adicionar.php?parte_id=<?= escapar($parte_id); ?>"
                class="flex h-[50px] items-center justify-center gap-3 rounded-xl bg-gradient-to-r from-[#df438d] to-[#7447ca] px-9 text-[13px] font-semibold text-white shadow-button transition hover:brightness-110">

                <i class="fa-solid fa-plus"></i>
                Nova Referência
            </a>
        </section>

        <!-- Cards -->
        <?php if (count($referencias) > 0): ?>

            <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                <?php foreach ($referencias as $indice => $referencia): ?>
                    <?php $tema = $temas_referencias[$indice % count($temas_referencias)]; ?>

                    <article class="card-referencia overflow-hidden rounded-[18px] border border-jojo-border bg-white shadow-card">

                        <a href="visualizar.php?id_referencia=<?= escapar($referencia->id); ?>&parte_id=<?= escapar($parte_id); ?>">
                            <div class="relative h-[210px] overflow-hidden border-b border-jojo-border"
                                style="background: linear-gradient(135deg, #ffffff 0%, <?= escapar($tema["clara"]); ?> 100%);">

                                <span class="absolute left-5 top-5 z-20 flex h-10 w-10 items-center justify-center rounded-xl bg-white/80 text-[20px] shadow-soft"
                                    style="color: <?= escapar($tema["cor"]); ?>;">
                                    <i class="<?= escapar($tema["icone"]); ?>"></i>
                                </span>

                                <i class="<?= escapar($tema["decoracao"]); ?> absolute right-5 top-6 text-[38px] opacity-[0.12]"
                                    style="color: <?= escapar($tema["cor"]); ?>;"></i>

                                <i class="fa-regular fa-star absolute left-8 bottom-12 text-[70px] opacity-[0.10]"
                                    style="color: <?= escapar($tema["cor"]); ?>;"></i>

                                <?php if (!empty($referencia->imagem)): ?>
                                    <img src="../<?= escapar($referencia->imagem); ?>"
                                        alt="Foto: <?= escapar($referencia->titulo); ?>"
                                        class="foto-referencia absolute inset-0 z-10 h-full w-full object-cover">
                                <?php else: ?>
                                    <div class="absolute inset-0 z-10 flex items-center justify-center">
                                        <i class="fa-regular fa-image text-[80px] opacity-20"
                                            style="color: <?= escapar($tema["cor"]); ?>;"></i>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </a>

                        <div class="px-5 pb-5 pt-4">

                            <h2 class="font-title text-lg font-bold leading-snug"
                                style="color: <?= escapar($tema["cor"]); ?>;">
                                <?= escapar($referencia->titulo); ?>
                            </h2>

                            <div class="mt-4 grid grid-cols-1 gap-3 text-[11px]">

                                <div>
                                    <p class="mb-2 flex items-center gap-2 font-semibold text-[#887d98]">
                                        <i class="fa-solid fa-align-left" style="color: <?= escapar($tema["cor"]); ?>;"></i>
                                        Descrição
                                    </p>

                                    <p class="texto-limitado min-h-[34px] font-semibold leading-5 text-[#493760]">
                                        <?= !empty($referencia->descricao)
                                            ? escapar($referencia->descricao)
                                            : "Sem descrição cadastrada."; ?>
                                    </p>
                                </div>

                                <div class="flex items-center justify-between gap-3 border-t border-jojo-border pt-3">
                                    <p class="flex items-center gap-2 font-semibold text-[#887d98]">
                                        <i class="fa-solid fa-book" style="color: <?= escapar($tema["cor"]); ?>;"></i>
                                        Parte
                                    </p>

                                    <p class="max-w-[150px] truncate font-semibold text-[#493760]">
                                        <?= escapar($parte->nome); ?>
                                    </p>
                                </div>

                            </div>

                            <div class="mt-6 grid grid-cols-3 gap-2">

                                <a href="visualizar.php?id_referencia=<?= escapar($referencia->id); ?>&parte_id=<?= escapar($parte_id); ?>"
                                    class="flex h-10 items-center justify-center gap-2 rounded-lg border bg-white text-[11px] font-semibold transition hover:bg-purple-50"
                                    style="border-color: <?= escapar($tema["cor"]); ?>33; color: <?= escapar($tema["cor"]); ?>;">
                                    <i class="fa-regular fa-eye"></i>
                                    Ver
                                </a>

                                <a href="editar.php?id_referencia=<?= escapar($referencia->id); ?>&parte_id=<?= escapar($parte_id); ?>"
                                    class="flex h-10 items-center justify-center gap-2 rounded-lg border bg-white text-[11px] font-semibold transition hover:bg-purple-50"
                                    style="border-color: <?= escapar($tema["cor"]); ?>33; color: <?= escapar($tema["cor"]); ?>;">
                                    <i class="fa-solid fa-pencil"></i>
                                    Editar
                                </a>

                                <a href="excluir.php?id_referencia=<?= escapar($referencia->id); ?>&parte_id=<?= escapar($parte_id); ?>"
                                    onclick="return confirm('Tem certeza que deseja excluir esta referência?')"
                                    class="flex h-10 items-center justify-center gap-2 rounded-lg border bg-white text-[11px] font-semibold transition hover:bg-red-50"
                                    style="border-color: <?= escapar($tema["cor"]); ?>33; color: <?= escapar($tema["cor"]); ?>;">
                                    <i class="fa-regular fa-trash-can"></i>
                                    Excluir
                                </a>

                            </div>
                        </div>
                    </article>

                <?php endforeach; ?>

            </section>

        <?php else: ?>

            <section class="flex min-h-[400px] flex-col items-center justify-center rounded-[20px] border border-dashed border-purple-200 bg-white px-8 text-center shadow-soft">

                <span class="mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-purple-50 text-3xl text-jojo-purple">
                    <i class="fa-solid fa-book-medical"></i>
                </span>

                <h2 class="font-title text-2xl font-bold text-jojo-dark">
                    Nenhuma referência cadastrada
                </h2>

                <p class="mt-3 max-w-[430px] text-sm leading-7 text-[#776a88]">
                    Adicione referências da parte <?= escapar($parte->nome); ?> para organizar imagens, descrições e informações importantes.
                </p>

                <a href="adicionar.php?parte_id=<?= escapar($parte_id); ?>"
                    class="mt-7 flex h-12 items-center justify-center gap-3 rounded-xl bg-jojo-purple px-7 text-sm font-semibold text-white transition hover:brightness-110">
                    <i class="fa-solid fa-plus"></i>
                    Adicionar Referência
                </a>

            </section>

        <?php endif; ?>

    </main>

    <!-- Footer com paginação -->
    <?php if ($total_referencias > 0): ?>
        <footer class="mt-auto border-t border-purple-100 bg-[#faf7ff] px-5 py-7">

            <div class="mx-auto flex max-w-[1450px] flex-wrap items-center justify-center gap-6">

                <span class="linha-paginacao hidden h-px w-[240px] md:block"></span>

                <i class="fa-solid fa-star hidden text-xs text-jojo-lilac md:block"></i>

                <p class="font-title text-base font-semibold text-[#7d68a0]">
                    Página <?= escapar($pagina_atual); ?> de <?= escapar($total_paginas); ?>
                </p>

                <div class="flex items-center gap-2">

                    <?php if ($pagina_atual > 1): ?>
                        <a href="<?= escapar(link_pagina_referencia($parte_id, $pagina_atual - 1)); ?>"
                            class="flex h-11 w-11 items-center justify-center rounded-lg border border-purple-100 bg-white text-sm text-jojo-purple shadow-soft transition hover:bg-purple-50">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    <?php else: ?>
                        <span class="flex h-11 w-11 cursor-not-allowed items-center justify-center rounded-lg border border-purple-100 bg-white text-sm text-purple-200">
                            <i class="fa-solid fa-chevron-left"></i>
                        </span>
                    <?php endif; ?>

                    <?php for ($pagina = 1; $pagina <= $total_paginas; $pagina++): ?>
                        <a href="<?= escapar(link_pagina_referencia($parte_id, $pagina)); ?>"
                            class="flex h-11 w-11 items-center justify-center rounded-lg border text-sm font-semibold shadow-soft transition
                            <?= $pagina === $pagina_atual
                                ? "border-jojo-purple bg-jojo-purple text-white"
                                : "border-purple-100 bg-white text-jojo-dark hover:bg-purple-50"; ?>">
                            <?= escapar($pagina); ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pagina_atual < $total_paginas): ?>
                        <a href="<?= escapar(link_pagina_referencia($parte_id, $pagina_atual + 1)); ?>"
                            class="flex h-11 w-11 items-center justify-center rounded-lg border border-purple-100 bg-white text-sm text-jojo-purple shadow-soft transition hover:bg-purple-50">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="flex h-11 w-11 cursor-not-allowed items-center justify-center rounded-lg border border-purple-100 bg-white text-sm text-purple-200">
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>

                </div>

                <i class="fa-solid fa-star hidden text-xs text-jojo-lilac md:block"></i>

                <span class="linha-paginacao hidden h-px w-[240px] md:block"></span>

            </div>
        </footer>
    <?php endif; ?>

</body>
</html>