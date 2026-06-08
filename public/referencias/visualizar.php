<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
require_once "../../src/functions/gerais.php";

if (!isset($_GET["id_referencia"]) || !filter_var($_GET["id_referencia"], FILTER_VALIDATE_INT)) {
    header("Location: ../index.php?status=id_vazio");
    exit;
}

$referencia_id = (int) $_GET["id_referencia"];
$usuario_id = (int) $_SESSION["usuario_id"];

$sql = "
SELECT 
    r.*,
    p.nome AS parte_nome
FROM referencias r
LEFT JOIN partes p
    ON p.id = r.parte_id
WHERE r.id = :id
AND r.usuario_id = :usuario_id
LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $referencia_id,
    ":usuario_id" => $usuario_id
]);

$referencia = $stmt->fetch(PDO::FETCH_OBJ);

if (!$referencia) {
    header("Location: ../index.php?status=id_invalido");
    exit;
}

$parte_id = (int) $referencia->parte_id;
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escapar($referencia->titulo); ?> | JoJo Archive</title>
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

        .painel {
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(2px);
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
                <?= escapar($referencia->parte_nome); ?>
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <a href="index.php?id=<?= $parte_id; ?>"
                class="font-semibold text-[#665387] transition hover:text-jojo-purple">
                Referências
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

        <!-- Conteúdo principal -->
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-[0.85fr_1.15fr]">

            <!-- Imagem -->
            <article class="painel overflow-hidden rounded-[18px] border border-jojo-border p-3 shadow-card">

                <div class="relative h-[270px] overflow-hidden rounded-[14px] bg-purple-50 md:h-[340px]">

                    <?php if (!empty($referencia->imagem)): ?>
                        <img src="../<?= escapar($referencia->imagem); ?>"
                            alt="Imagem da referência <?= escapar($referencia->titulo); ?>"
                            class="h-full w-full object-cover">
                    <?php else: ?>
                        <div class="flex h-full w-full items-center justify-center text-5xl text-purple-200">
                            <i class="fa-regular fa-image"></i>
                        </div>
                    <?php endif; ?>

                </div>

            </article>

            <!-- Informações -->
            <article class="painel rounded-[18px] border border-jojo-border p-5 shadow-card">

                <span class="inline-flex w-fit items-center gap-2 rounded-md bg-purple-100 px-3 py-1.5 text-[11px] font-semibold text-jojo-purple">
                    <i class="fa-solid fa-book-open"></i>
                    Referência
                </span>

                <h2 class="mt-3 font-title text-[26px] font-bold leading-tight text-jojo-dark md:text-[32px]">
                    <?= escapar($referencia->titulo); ?>
                </h2>

                <div class="mt-5 overflow-hidden rounded-[13px] border border-jojo-border bg-white/70">

                    <div class="flex items-center justify-between gap-4 border-b border-jojo-border px-4 py-4">
                        <div class="flex items-center gap-3 text-xs font-medium text-[#473267]">
                            <i class="fa-regular fa-compass text-base text-jojo-purple"></i>
                            Parte
                        </div>

                        <p class="text-right text-xs font-semibold text-jojo-purple">
                            <?= escapar($referencia->parte_nome ?? "Não informada"); ?>
                        </p>
                    </div>

                    <div class="flex items-center justify-between gap-4 px-4 py-4">
                        <div class="flex items-center gap-3 text-xs font-medium text-[#473267]">
                            <i class="fa-solid fa-hashtag text-sm text-jojo-purple"></i>
                            Tipo
                        </div>

                        <p class="text-right text-xs font-semibold text-jojo-dark">
                            <?= escapar($referencia->tipo); ?>
                        </p>
                    </div>

                </div>

                <div class="mt-4 rounded-[13px] border border-jojo-border bg-white/70 px-4 py-4">

                    <div class="mb-3 flex items-center gap-2 text-xs font-semibold text-jojo-purple">
                        <i class="fa-solid fa-align-left"></i>
                        Descrição
                    </div>

                    <p class="text-[12px] leading-6 text-[#433366] md:text-[13px]">
                        <?= !empty($referencia->descricao)
                            ? nl2br(escapar($referencia->descricao))
                            : "Nenhuma descrição foi cadastrada para esta referência."; ?>
                    </p>

                </div>

            </article>

        </section>

        <!-- Botões finais -->
        <section class="mx-auto mt-6 grid max-w-[760px] grid-cols-1 gap-3 sm:grid-cols-3">

            <a href="index.php?parte_id=<?= escapar($parte_id); ?>"
                class="botao-acao flex h-[46px] items-center justify-center gap-2 rounded-xl border border-purple-200 bg-white text-xs font-semibold text-jojo-purple shadow-soft">
                <i class="fa-solid fa-arrow-left"></i>
                Voltar
            </a>

            <a href="excluir.php?id_referencia=<?= escapar($referencia->id); ?>&parte_id=<?= escapar($parte_id); ?>"
                onclick="return confirm('Tem certeza que deseja excluir esta referência?')"
                class="botao-acao flex h-[46px] items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#dd438f] to-[#ed599b] text-xs font-semibold text-white shadow-button">
                <i class="fa-regular fa-trash-can"></i>
                Excluir
            </a>

            <a href="editar.php?id_referencia=<?= escapar($referencia->id); ?>&parte_id=<?= escapar($parte_id); ?>"
                class="botao-acao flex h-[46px] items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#7045c9] to-[#9665dc] text-xs font-semibold text-white shadow-button">
                <i class="fa-solid fa-pencil"></i>
                Editar
            </a>

        </section>

    </main>

</body>
</html>