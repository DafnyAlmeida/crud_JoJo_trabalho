<?php
require_once "../../src/config/conexao.php";
require_once "../../src/includes/bloqueio.php";

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, "UTF-8");
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: ../index.php?status=id_vazio");
    exit;
}

/* Buscando a parte */
$sql = "SELECT * FROM partes WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $id
]);

$parte = $stmt->fetch(PDO::FETCH_OBJ);

if (!$parte) {
    header("Location: ../index.php?status=id_invalido");
    exit;
}

/*
|--------------------------------------------------------------------------
| Contagens
|--------------------------------------------------------------------------
| As consultas abaixo consideram:
| - personagens relacionados por personagens_partes
| - stands relacionados aos personagens
| - referencias contendo a coluna parte_id
|--------------------------------------------------------------------------
*/

$sqlStands = "
    SELECT COUNT(DISTINCT s.id)
    FROM stands s
    INNER JOIN personagens_partes pp
        ON pp.personagem_id = s.personagem_id
    WHERE pp.parte_id = :parte_id
";

$stmtStands = $pdo->prepare($sqlStands);
$stmtStands->execute([
    ":parte_id" => $id
]);
$totalStands = (int) $stmtStands->fetchColumn();


$sqlPersonagens = "
    SELECT COUNT(DISTINCT personagem_id)
    FROM personagens_partes
    WHERE parte_id = :parte_id
";

$stmtPersonagens = $pdo->prepare($sqlPersonagens);
$stmtPersonagens->execute([
    ":parte_id" => $id
]);
$totalPersonagens = (int) $stmtPersonagens->fetchColumn();


$sqlReferencias = "
    SELECT COUNT(*)
    FROM referencias
    WHERE parte_id = :parte_id
";

$stmtReferencias = $pdo->prepare($sqlReferencias);
$stmtReferencias->execute([
    ":parte_id" => $id
]);
$totalReferencias = (int) $stmtReferencias->fetchColumn();


/* Informações visuais */
$numeroParte = str_pad((string) $parte->id, 2, "0", STR_PAD_LEFT);

$pastaImagem = "../img/partes/parte-" . $parte->id;

$imagens = [
    "banner" => $pastaImagem . "/banner.png",
    "stands" => $pastaImagem . "/stands.png",
    "personagens" => $pastaImagem . "/personagens.png",
    "referencias" => $pastaImagem . "/referencias.png",
    "sinopse" => $pastaImagem . "/sinopse.png"
];

$descricao = trim((string) ($parte->descricao ?? ""));

if ($descricao === "") {
    $descricao = "Cadastre uma descrição para apresentar a história, os personagens e os acontecimentos desta parte.";
}

$paragrafosDescricao = preg_split('/\r?\n\s*\r?\n/', $descricao);

$cards = [
    [
        "titulo" => "Stands",
        "quantidade" => $totalStands,
        "legenda" => "stands registrados",
        "descricao" => "Explore e gerencie todos os Stands da parte " . $parte->nome . ".",
        "icone" => "fa-regular fa-star",
        "imagem" => $imagens["stands"],
        "link" => "../stands/index.php?parte_id=" . $parte->id,
        "botao" => "Gerenciar Stands",
        "cor" => "#6534c5",
        "cor_secundaria" => "#7647d4",
        "fundo" => "#f6f1ff"
    ],
    [
        "titulo" => "Personagens",
        "quantidade" => $totalPersonagens,
        "legenda" => "personagens cadastrados",
        "descricao" => "Visualize e gerencie os personagens que fazem parte desta jornada.",
        "icone" => "fa-solid fa-user",
        "imagem" => $imagens["personagens"],
        "link" => "../personagens/index.php?parte_id=" . $parte->id,
        "botao" => "Gerenciar Personagens",
        "cor" => "#cc3782",
        "cor_secundaria" => "#df478f",
        "fundo" => "#fff3f8"
    ],
    [
        "titulo" => "Referências",
        "quantidade" => $totalReferencias,
        "legenda" => "referências registradas",
        "descricao" => "Acesse e organize entrevistas, guias, livros e muito mais.",
        "icone" => "fa-solid fa-book-open",
        "imagem" => $imagens["referencias"],
        "link" => "../referencias/index.php?parte_id=" . $parte->id,
        "botao" => "Gerenciar Referências",
        "cor" => "#6534c5",
        "cor_secundaria" => "#7650cf",
        "fundo" => "#f6f1ff"
    ]
];
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= escapar($parte->nome); ?> | JoJo Archive</title>

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

    <main class="mx-auto w-full max-w-[1450px] px-5 pb-7 pt-6">

        <!-- Caminho da página -->
        <nav class="mb-5 flex items-center gap-5 text-sm md:text-base">
            <a href="../index.php"
                class="font-semibold text-jojo-purple transition hover:text-jojo-pink">
                Todas as Partes
            </a>

            <i class="fa-solid fa-chevron-right text-xs text-jojo-lilac"></i>

            <span class="font-medium text-jojo-dark">
                <?= escapar($parte->nome); ?>
            </span>
        </nav>

        <!-- Banner principal -->
        <section
            class="banner-parte relative flex min-h-[285px] items-center overflow-hidden rounded-[20px] border border-purple-100 px-8 shadow-soft md:min-h-[310px] md:px-16">

            <div class="relative z-10 max-w-[460px] text-white">
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
        </section>

        <!-- Cards de gerenciamento -->
        <section class="mt-7 grid grid-cols-1 gap-4 lg:grid-cols-3">

            <?php foreach ($cards as $card): ?>
                <article
                    class="card-gerenciar relative min-h-[375px] overflow-hidden rounded-[18px] border border-jojo-border bg-white shadow-card"
                    style="background: linear-gradient(120deg, #ffffff 0%, <?= escapar($card["fundo"]); ?> 100%);">

                    <!-- Conteúdo do card -->
                    <div class="relative z-20 flex min-h-[375px] flex-col px-7 pb-5 pt-7">

                        <div class="flex items-center gap-3">
                            <i class="<?= escapar($card["icone"]); ?> text-[30px]"
                                style="color: <?= escapar($card["cor"]); ?>;"></i>

                            <h2 class="font-title text-[29px] font-bold"
                                style="color: <?= escapar($card["cor"]); ?>;">
                                <?= escapar($card["titulo"]); ?>
                            </h2>
                        </div>

                        <p class="mt-5 font-title text-[48px] font-bold leading-none"
                            style="color: <?= escapar($card["cor"]); ?>;">
                            <?= $card["quantidade"]; ?>
                        </p>

                        <p class="mt-2 text-xs font-semibold"
                            style="color: <?= escapar($card["cor"]); ?>;">
                            <?= escapar($card["legenda"]); ?>
                        </p>

                        <p class="mt-6 max-w-[205px] text-[13px] leading-6 text-[#554572]">
                            <?= escapar($card["descricao"]); ?>
                        </p>

                        <div class="mt-auto pt-7">
                            <a href="<?= escapar($card["link"]); ?>"
                                class="flex h-[58px] w-full items-center justify-center gap-6 rounded-xl text-sm font-semibold text-white shadow-sm transition hover:brightness-110"
                                style="background: linear-gradient(90deg, <?= escapar($card["cor"]); ?>, <?= escapar($card["cor_secundaria"]); ?>);">

                                <?= escapar($card["botao"]); ?>

                                <i class="fa-solid fa-arrow-right-long"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Imagem do card -->
                    <img src="<?= escapar($card["imagem"]); ?>"
                        alt="<?= escapar($card["titulo"]); ?>"
                        class="imagem-card pointer-events-none absolute bottom-[60px] right-0 z-10 h-[275px] w-[58%] object-contain object-bottom">

                </article>
            <?php endforeach; ?>

        </section>

        <!-- Sinopse da Parte -->
        <section class="sinopse-parte relative mt-7 min-h-[280px] overflow-hidden rounded-[20px] border border-jojo-border shadow-soft">

            <div class="relative z-20 px-8 py-7 lg:w-[64%]">
                <div class="mb-4 flex items-center gap-4">
                    <i class="fa-solid fa-star text-xl text-jojo-purple"></i>

                    <h2 class="font-title text-2xl font-bold text-jojo-purple md:text-[29px]">
                        Sinopse da Parte
                    </h2>
                </div>

                <?php foreach ($paragrafosDescricao as $paragrafo): ?>
                    <p class="mb-4 text-sm leading-7 text-[#46337a] md:text-[15px]">
                        <?= escapar($paragrafo); ?>
                    </p>
                <?php endforeach; ?>
            </div>

            <!-- Decorações -->
            <div class="pointer-events-none absolute right-[29%] top-8 hidden text-jojo-lilac lg:block">
                <i class="fa-solid fa-star text-3xl opacity-80"></i>
            </div>

            <div class="pointer-events-none absolute right-[32%] top-20 hidden text-jojo-lilac lg:block">
                <i class="fa-solid fa-star text-lg opacity-60"></i>
            </div>

            <!-- Imagem lateral -->
            <img src="<?= escapar($imagens["sinopse"]); ?>"
                alt="Personagens de <?= escapar($parte->nome); ?>"
                class="pointer-events-none absolute bottom-0 right-0 hidden h-[255px] w-[42%] object-contain object-bottom lg:block">
        </section>

    </main>

    <?php require_once "../../src/includes/footer.php"; ?>

</body>

</html>