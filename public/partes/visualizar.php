<?php
require_once "../../src/config/conexao.php";
require_once "../../src/includes/bloqueio.php";
include_once "../../src/functions/gerais.php";

$parte_id = validar_id_get("id");

// Vê se veio ID
if (!$parte_id) {
    header("Location: ../index.php?status=id_vazio");
    exit;
}

// Seleciona uma parte especifica
$sql = "SELECT * FROM partes WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $parte_id
]);

$parte = $stmt->fetch(PDO::FETCH_OBJ);

// Vê se a consulta retornou algo
if (!$parte) {
    header("Location: ../index.php?status=id_invalido");
    exit;
}

// Funções que retornam o total de cada coisa de acordo com a parte
$total_personagens = pegarTotal(
    $pdo,
    $parte_id,
    "SELECT COUNT(DISTINCT personagem_id)
     FROM personagens_partes
     WHERE parte_id = :parte_id"
);

$total_stands = pegarTotal(
    $pdo,
    $parte_id,
    "SELECT COUNT(DISTINCT s.id)
     FROM stands s
     INNER JOIN personagens_partes pp
         ON pp.personagem_id = s.personagem_id
     WHERE pp.parte_id = :parte_id"
);

$total_referencias = pegarTotal(
    $pdo,
    $parte_id,
    "SELECT COUNT(DISTINCT id)
     FROM referencias
     WHERE parte_id = :parte_id"
);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escapar($parte->nome); ?> | JoJo Archive</title>
    <link rel="icon" type="image/png" href="../assets/img/logo.png">

    <!-- Icon Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
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
                        card: "0 4px 18px rgba(67, 40, 105, 0.08)",
                        soft: "0 3px 14px rgba(67, 40, 105, 0.06)"
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="../assets/css/visualizar_parte.css">
</head>

<body class="min-h-screen bg-jojo-bg font-body text-jojo-dark">
    <?php require_once "../../src/includes/header.php"; ?>
    <main class="mx-auto w-full max-w-[1450px] px-10 pb-2 pt-6">

        <!-- Caminho da página -->
        <nav class="mb-6 flex flex-wrap items-center gap-4 text-xs md:text-sm">
            <a href="../index.php"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                Todas as Partes
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <span class="font-semibold text-jojo-purple">
                <?= escapar($parte->nome); ?>
            </span>
        </nav>

        <!-- Banner principal -->
        <section class="relative min-h-[265px] overflow-hidden rounded-[22px] border border-purple-100 shadow-soft">
            <div class="absolute inset-0 bg-gradient-to-r from-[#6535ac] via-[#7746c0] to-[#a684dd]"></div>
            <img
                src="../assets/img/visualizar_parte/parte-<?= $parte_id ?>.png"
                alt="<?= escapar($parte->nome); ?>"
                class="absolute inset-0 h-full w-full object-cover object-center">
            <div class="absolute inset-0 bg-gradient-to-r from-[#6132aa]/95 via-[#6f40bb]/70 to-transparent"></div>

            <div class="relative z-10 flex min-h-[265px] items-center px-10 py-10 md:px-16">
                <div class="max-w-[460px] text-white">
                    <div class="mb-5 flex items-center gap-2 text-xl text-pink-300">
                        <i class="fa-solid fa-star text-sm"></i>
                        <i class="fa-solid fa-star text-[10px]"></i>
                        <i class="fa-solid fa-star text-xs"></i>
                    </div>

                    <h1 class="font-title text-4xl font-bold leading-tight md:text-[46px]">
                        <?= escapar($parte->nome); ?>
                    </h1>

                    <p class="mt-4 max-w-[325px] text-sm leading-7 text-purple-100 md:text-base">
                        Gerencie os registros desta parte do universo JoJo.
                    </p>
                </div>
            </div>
        </section>

        <!-- Cards de gerenciamento -->
        <section class="mt-7 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- Card Stands -->
            <article
                class="card-gerenciar relative min-h-[375px] overflow-hidden rounded-[18px] border border-jojo-border bg-white shadow-card"
                style="background: linear-gradient(120deg, #ffffff 0%, #f6f1ff 100%);">

                <!-- Conteúdo do card -->
                <div class="relative z-20 flex min-h-[375px] flex-col px-7 pb-5 pt-7">

                    <div class="flex items-center gap-3">
                        <i class="fa-regular fa-star text-[30px]"
                            style="color: #cf9e40;"></i>

                        <h2 class="font-title text-[29px] font-bold"
                            style="color: #cf9e40;">
                            Stands
                        </h2>
                    </div>

                    <p class="mt-5 font-title text-[48px] font-bold leading-none"
                        style="color: #cf9e40;">
                        <?= $total_stands; ?>
                    </p>

                    <p class="mt-2 text-xs font-semibold"
                        style="color: #cf9e40;">
                        stands registrados
                    </p>

                    <p class="mt-6 max-w-[205px] text-[13px] leading-6 text-[#554572]">
                        Explore e gerencie todos os Stands da parte <?= escapar($parte->nome); ?>.
                    </p>

                    <div class="mt-auto pt-7">
                        <a href="../stands/index.php?parte_id=<?= $parte->id; ?>"
                            class="flex h-[58px] w-full items-center justify-center gap-6 rounded-xl text-sm font-semibold text-white shadow-sm transition hover:brightness-110"
                            style="background: linear-gradient(90deg, #cf9e40, #edc268);">
                            Gerenciar Stands
                            <i class="fa-solid fa-angle-right text-sm"></i>
                        </a>
                    </div>
                </div>

                <!-- Imagem do card -->
                <img src="../assets/img/partes/parte-<?= $parte->id; ?>/stand.png"
                    alt="Stands"
                    class="imagem-card pointer-events-none absolute bottom-[60px] right-0 z-10 h-[275px] w-[58%] object-contain object-bottom">

            </article>

            <!-- Card Personagens -->
            <article
                class="card-gerenciar relative min-h-[375px] overflow-hidden rounded-[18px] border border-jojo-border bg-white shadow-card"
                style="background: linear-gradient(120deg, #ffffff 0%, #fff3f8 100%);">

                <!-- Conteúdo do card -->
                <div class="relative z-20 flex min-h-[375px] flex-col px-7 pb-5 pt-7">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-user text-[30px]"
                            style="color: #cc3782;"></i>

                        <h2 class="font-title text-[29px] font-bold"
                            style="color: #cc3782;">
                            Personagens
                        </h2>
                    </div>

                    <p class="mt-5 font-title text-[48px] font-bold leading-none"
                        style="color: #cc3782;">
                        <?= $total_personagens; ?>
                    </p>

                    <p class="mt-2 text-xs font-semibold"
                        style="color: #cc3782;">
                        personagens cadastrados
                    </p>

                    <p class="mt-6 max-w-[205px] text-[13px] leading-6 text-[#554572]">
                        Visualize e gerencie os personagens que fazem parte desta jornada.
                    </p>

                    <div class="mt-auto pt-7">
                        <a href="../personagens/index.php?parte_id=<?= $parte->id; ?>"
                            class="flex h-[58px] w-full items-center justify-center gap-6 rounded-xl text-sm font-semibold text-white shadow-sm transition hover:brightness-110"
                            style="background: linear-gradient(90deg, #cc3782, #ec77b2);">
                            Gerenciar Personagens
                            <i class="fa-solid fa-angle-right text-sm"></i>
                        </a>
                    </div>
                </div>

                <!-- Imagem do card -->
                <img src="../assets/img/partes/parte-<?= $parte->id; ?>/personagem.png"
                    alt="Personagens"
                    class="imagem-card pointer-events-none absolute bottom-[60px] right-0 z-10 h-[275px] w-[58%] object-contain object-bottom">
            </article>

            <!-- Card Referências -->
            <article
                class="card-gerenciar relative min-h-[375px] overflow-hidden rounded-[18px] border border-jojo-border bg-white shadow-card"
                style="background: linear-gradient(120deg, #ffffff 0%, #f6f1ff 100%);">

                <!-- Conteúdo do card -->
                <div class="relative z-20 flex min-h-[375px] flex-col px-7 pb-5 pt-7">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-book-open text-[30px]"
                            style="color: #3f88b7;"></i>

                        <h2 class="font-title text-[29px] font-bold"
                            style="color: #3f88b7;">
                            Referências
                        </h2>
                    </div>

                    <p class="mt-5 font-title text-[48px] font-bold leading-none"
                        style="color: #3f88b7;">
                        <?= $total_referencias; ?>
                    </p>

                    <p class="mt-2 text-xs font-semibold"
                        style="color: #3f88b7;">
                        referências registradas
                    </p>

                    <p class="mt-6 max-w-[205px] text-[13px] leading-6 text-[#554572]">
                        Acesse e organize entrevistas, guias, livros e muito mais.
                    </p>

                    <div class="mt-auto pt-7">
                        <a href="../referencias/index.php?parte_id=<?= $parte->id; ?>"
                            class="flex h-[58px] w-full items-center justify-center gap-6 rounded-xl text-sm font-semibold text-white shadow-sm transition hover:brightness-110"
                            style="background: linear-gradient(90deg, #3f88b7, #6fb6dc);">

                            Gerenciar Referências

                            <i class="fa-solid fa-angle-right text-sm"></i>
                        </a>
                    </div>
                </div>

                <!-- Imagem do card -->
                <img src="../assets/img/partes/parte-<?= $parte->id; ?>/referencia.png"
                    alt="Referências"
                    class="imagem-card pointer-events-none absolute bottom-[60px] right-0 z-10 h-[275px] w-[58%] object-contain object-bottom">
            </article>
        </section>

        <!-- Sinopse da Parte -->
        <section class="sinopse-parte relative mt-7 min-h-[200px] overflow-hidden rounded-[20px] border border-jojo-border shadow-soft">

            <div class="relative z-20 px-8 py-7 lg:w-[64%]">
                <div class="mb-4 flex items-center gap-4">
                    <i class="fa-solid fa-star text-xl text-jojo-purple"></i>

                    <h2 class="font-title text-2xl font-bold text-jojo-purple md:text-[29px]">
                        Sinopse da Parte
                    </h2>
                </div>
                <p class="pb-2 text-sm leading-7 text-[#46337a] md:text-[15px]">
                    <?= escapar($parte->descricao); ?>
                </p>
            </div>

            <!-- Imagem lateral - logo da parte -->
            <img src="../assets/img/partes/parte-<?= $parte->id; ?>/sinopse.png"
                alt="Logo de <?= escapar($parte->nome); ?>"
                class="pointer-events-none absolute bottom-0 right-0 hidden h-[200px] w-[42%] object-contain object-center lg:block">
        </section>
    </main>
    <?php require_once "../../src/includes/footer.php"; ?>
</body>
</html>