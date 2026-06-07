<?php
include_once "../src/config/conexao.php";
include_once "../src/includes/bloqueio.php";
include_once "../src/functions/gerais.php";

$usuario_id = $_SESSION["usuario_id"];
$totaisPorParte = listarTotaisPorParte($pdo, $usuario_id);

$partes = [
    [
        "id" => 1,
        "numero" => "01",
        "nome" => "Phantom Blood",
        "imagem" => "img/partes/parte-1.png",
        "icone" => "fa-solid fa-star",
        "cor" => "#7446c7",
        "cor_clara" => "#a874e5",
        "fundo" => "#f7f1ff",
        "decoracao" => "fa-solid fa-sun"
    ],
    [
        "id" => 2,
        "numero" => "02",
        "nome" => "Battle Tendency",
        "imagem" => "img/partes/parte-2.png",
        "icone" => "fa-solid fa-star",
        "cor" => "#df468d",
        "cor_clara" => "#ee76b0",
        "fundo" => "#fff2f8",
        "decoracao" => "fa-solid fa-circle-nodes"
    ],
    [
        "id" => 3,
        "numero" => "03",
        "nome" => "Stardust Crusaders",
        "imagem" => "img/partes/parte-3.png",
        "icone" => "fa-solid fa-star",
        "cor" => "#7045c9",
        "cor_clara" => "#9565dd",
        "fundo" => "#f5f1ff",
        "decoracao" => "fa-regular fa-star"
    ],
    [
        "id" => 4,
        "numero" => "04",
        "nome" => "Diamond is Unbreakable",
        "imagem" => "img/partes/parte-4.png",
        "icone" => "fa-solid fa-star",
        "cor" => "#d1a042",
        "cor_clara" => "#ecc767",
        "fundo" => "#fff9ed",
        "decoracao" => "fa-regular fa-heart"
    ],
    [
        "id" => 5,
        "numero" => "05",
        "nome" => "Golden Wind",
        "imagem" => "img/partes/parte-5.png",
        "icone" => "fa-solid fa-star",
        "cor" => "#dc438c",
        "cor_clara" => "#ec77b2",
        "fundo" => "#fff2f8",
        "decoracao" => "fa-solid fa-clover"
    ],
    [
        "id" => 6,
        "numero" => "06",
        "nome" => "Stone Ocean",
        "imagem" => "img/partes/parte-6.png",
        "icone" => "fa-solid fa-star",
        "cor" => "#4d9ac8",
        "cor_clara" => "#9d82e2",
        "fundo" => "#eefaff",
        "decoracao" => "fa-solid fa-water"
    ],
    [
        "id" => 7,
        "numero" => "07",
        "nome" => "Steel Ball Run",
        "imagem" => "img/partes/parte-7.png",
        "icone" => "fa-solid fa-star",
        "cor" => "#cf9e40",
        "cor_clara" => "#edc268",
        "fundo" => "#fff9ed",
        "decoracao" => "fa-solid fa-horse"
    ],
    [
        "id" => 8,
        "numero" => "08",
        "nome" => "JoJolion",
        "imagem" => "img/partes/parte-8.png",
        "icone" => "fa-solid fa-star",
        "cor" => "#7143c5",
        "cor_clara" => "#9970dc",
        "fundo" => "#f6f1ff",
        "decoracao" => "fa-solid fa-anchor"
    ],
    [
        "id" => 9,
        "numero" => "09",
        "nome" => "The JOJOLands",
        "imagem" => "img/partes/parte-9.png",
        "icone" => "fa-solid fa-star",
        "cor" => "#3f88b7",
        "cor_clara" => "#6fb6dc",
        "fundo" => "#effaff",
        "decoracao" => "fa-solid fa-gem"
    ]
];

foreach ($partes as &$parte) {
    $idParte = (int) $parte["id"];

    $parte["stands"] = $totaisPorParte[$idParte]["stands"] ?? 0;
    $parte["personagens"] = $totaisPorParte[$idParte]["personagens"] ?? 0;
    $parte["referencias"] = $totaisPorParte[$idParte]["referencias"] ?? 0;
}

unset($parte);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>JoJo Archive</title>

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
                        card: "0 5px 16px rgba(63, 35, 99, 0.08)",
                        soft: "0 3px 12px rgba(63, 35, 99, 0.06)"
                    }
                }
            }
        }
    </script>

    <style>
        .losango {
            transform: rotate(45deg);
        }

        .losango span {
            transform: rotate(-45deg);
        }

        .card-parte:hover img {
            transform: scale(1.06);
        }

        .card-parte:hover {
            transform: translateY(-4px);
        }
    </style>
</head>

<body class="min-h-screen bg-jojo-bg font-body text-jojo-dark">

    <?php include_once "../src/includes/header.php"; ?>

    <main class="mx-auto w-full max-w-[1500px] px-5 pb-10 pt-5">

        <!-- Banner principal -->
        <section class="relative min-h-[265px] overflow-hidden rounded-[22px] border border-purple-100 shadow-soft">

            <div class="absolute inset-0 bg-gradient-to-r from-[#6535ac] via-[#7746c0] to-[#a684dd]"></div>

            <img
                src="img/banner-jojo.png"
                alt="Personagens de JoJo"
                class="absolute inset-0 h-full w-full object-cover object-center">

            <div class="absolute inset-0 bg-gradient-to-r from-[#6132aa]/95 via-[#6f40bb]/70 to-transparent"></div>

            <!-- detalhes decorativos -->
            <div class="absolute left-8 top-10 text-xl text-white/50">✦</div>
            <div class="absolute left-16 top-16 text-xs text-white/60">✦</div>
            <div class="absolute bottom-10 left-[38%] text-xl text-white/50">✦</div>

            <div class="relative flex min-h-[265px] items-center px-10 py-10 md:px-16">
                <div class="max-w-[420px] text-white">
                    <h1 class="font-title text-3xl font-bold leading-tight md:text-[42px]">
                        Bem-vindo ao JoJo Dex - CRUD!
                    </h1>

                    <p class="mt-4 max-w-[370px] text-base leading-relaxed text-purple-100 md:text-lg">
                        Escolha uma parte para gerenciar Stands, Personagens e Referências.
                    </p>
                </div>
            </div>

            <div>
                <!-- Colocar a imagem aqui -->
                <img src="" alt="">
            </div>
        </section>

        <!-- Cabeçalho da listagem -->
        <section class="mb-5 mt-7 flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-title text-2xl font-bold text-jojo-dark md:text-[30px]">
                Todas as Partes
                <span class="ml-1 text-sm text-jojo-lilac">✦✦</span>
            </h2>

        </section>

        <!-- Cards das partes -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

            <?php foreach ($partes as $parte): ?>
                <article
                    class="card-parte overflow-hidden rounded-[18px] border border-jojo-border bg-white shadow-card transition duration-300">

                    <div
                        class="relative h-[220px] overflow-hidden border-b border-jojo-border"
                        style="background: linear-gradient(135deg, #ffffff 0%, <?= $parte['fundo']; ?> 100%);">

                        <div
                            class="losango absolute left-5 top-5 z-20 flex h-11 w-11 items-center justify-center rounded-sm shadow-sm"
                            style="background-color: <?= $parte['cor']; ?>;">
                            <span class="font-title text-base font-bold text-white">
                                <?= htmlspecialchars($parte["numero"]); ?>
                            </span>
                        </div>

                        <div class="absolute left-5 top-[88px] z-20 w-[130px]">
                            <h3
                                class="font-title text-[20px] font-bold leading-[1.05]"
                                style="color: <?= $parte['cor']; ?>;">
                                <?= htmlspecialchars($parte["nome"]); ?>
                            </h3>
                        </div>

                        <i
                            class="<?= $parte['decoracao']; ?> absolute bottom-7 left-12 text-[58px] opacity-[0.10]"
                            style="color: <?= $parte['cor']; ?>;">
                        </i>

                        <img
                            src="<?= htmlspecialchars($parte["imagem"]); ?>"
                            alt="<?= htmlspecialchars($parte["nome"]); ?>"
                            class="absolute bottom-0 right-0 z-10 h-[205px] w-[68%] object-contain object-bottom transition duration-500">

                        <div
                            class="absolute bottom-0 right-0 h-24 w-full opacity-20"
                            style="background: linear-gradient(to top, <?= $parte['cor']; ?>, transparent);">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 divide-x divide-jojo-border border-b border-jojo-border bg-white px-2 py-3">

                        <div class="flex flex-col items-center justify-center gap-1">
                            <span class="text-[10px] font-medium text-[#857a96]">Stands</span>
                            <span class="flex items-center gap-1 text-base font-bold"
                                style="color: <?= $parte['cor']; ?>;">
                                <i class="<?= $parte['icone']; ?> text-xs"></i>
                                <?= $parte["stands"];; ?>
                            </span>
                        </div>

                        <div class="flex flex-col items-center justify-center gap-1">
                            <span class="text-[10px] font-medium text-[#857a96]">Personagens</span>
                            <span class="flex items-center gap-1 text-base font-bold"
                                style="color: <?= $parte['cor']; ?>;">
                                <i class="fa-solid fa-user text-xs"></i>
                                <?= $parte["personagens"]; ?>
                            </span>
                        </div>

                        <div class="flex flex-col items-center justify-center gap-1">
                            <span class="text-[10px] font-medium text-[#857a96]">Referências</span>
                            <span class="flex items-center gap-1 text-base font-bold"
                                style="color: <?= $parte['cor']; ?>;">
                                <i class="fa-solid fa-book-open text-xs"></i>
                                <?= $parte["referencias"]; ?>
                            </span>
                        </div>

                    </div>

                    <div class="p-3">
                        <a
                            href="partes/visualizar.php?id=<?= $parte["id"]; ?>"
                            class="flex h-11 items-center justify-center gap-3 rounded-xl text-sm font-semibold text-white shadow-sm transition hover:brightness-105"
                            style="background: linear-gradient(90deg, <?= $parte['cor']; ?> 0%, <?= $parte['cor_clara']; ?> 100%);">

                            Abrir Parte

                            <i class="fa-solid fa-arrow-right-long text-sm"></i>
                        </a>
                    </div>

                </article>
            <?php endforeach; ?>

        </section>

    </main>

    <?php require_once "../src/includes/footer.php"; ?>

</body>
</html>