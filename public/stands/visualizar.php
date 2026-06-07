<?php
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
include_once "../../src/functions/gerais.php";

$stand_id = validar_id_get("id_stand");
$parte_id_url = validar_id_get("parte_id");

if (!$stand_id) {
    header("Location: ../index.php?status=id_vazio");
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
    header("Location: ../index.php?status=id_invalido");
    exit;
}

$parte_id = $stand->parte_id ?? $parte_id_url;

function imagemStand(?string $caminho): string
{
    $caminho = trim((string) $caminho);

    if ($caminho === "") {
        return "";
    }

    if (str_starts_with($caminho, "../") || str_starts_with($caminho, "http")) {
        return $caminho;
    }

    return "../" . $caminho;
}

$foto_anime = imagemStand($stand->foto_anime ?? "");
$foto_manga = imagemStand($stand->foto_manga ?? "");
$diagrama_forca = imagemStand($stand->diagrama_forca ?? "");

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escapar($stand->nome); ?> | JoJo Archive</title>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
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
                        "jojo-dark": "#20124d",
                        "jojo-purple": "#7045c9",
                        "jojo-lilac": "#a77be5",
                        "jojo-pink": "#dd438f",
                        "jojo-bg": "#fbf9ff",
                        "jojo-border": "#eadff8"
                    },
                    fontFamily: {
                        title: ["Playfair Display", "Georgia", "serif"],
                        body: ["Inter", "Arial", "sans-serif"]
                    },
                    boxShadow: {
                        card: "0 4px 18px rgba(67, 40, 105, 0.08)"
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background:
                radial-gradient(circle at top right, rgba(167, 123, 229, 0.18), transparent 28%),
                radial-gradient(circle at bottom left, rgba(221, 67, 143, 0.08), transparent 25%),
                #fbf9ff;
        }

        .foto-opcao {
            display: none;
        }

        .foto-opcao.ativa {
            display: block;
        }

        .aba-ativa {
            background: #efe3ff;
            color: #7045c9;
            border-color: #cdb3f4;
        }
    </style>
</head>

<body class="min-h-screen font-body text-jojo-dark">

    <?php require_once "../../src/includes/header.php"; ?>

    <main class="mx-auto w-full max-w-[1180px] px-5 pb-7 pt-5">

        <!-- Caminho -->
        <nav class="mb-4 flex flex-wrap items-center gap-3 text-xs font-semibold text-jojo-purple">
            <a href="../index.php" class="hover:text-jojo-pink">
                Todas as Partes
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <?php if (!empty($stand->parte_nome)): ?>
                <a href="index.php?parte_id=<?= $parte_id; ?>" class="hover:text-jojo-pink">
                    <?= escapar($stand->parte_nome); ?>
                </a>

                <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>
            <?php endif; ?>

            <span>Stands</span>

            <i class="fa-solid fa-chevron-right text-[10px] text-jojo-lilac"></i>

            <span class="text-jojo-dark">Ver Detalhes</span>
        </nav>

        <!-- Título -->
        <div class="mb-4">
            <a href="index.php?parte_id=<?= $parte_id; ?>"
                class="mb-2 inline-flex items-center gap-2 text-sm font-bold text-jojo-purple hover:text-jojo-pink">
                <i class="fa-solid fa-arrow-left"></i>
                Voltar
            </a>

            <div class="flex items-center gap-3">
                <i class="fa-solid fa-star text-2xl text-jojo-purple"></i>

                <h1 class="font-title text-4xl font-bold leading-none text-jojo-dark">
                    Ver Detalhes
                </h1>
            </div>
        </div>

        <!-- Topo -->
        <section class="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_1.05fr]">

            <!-- Foto -->
            <article class="rounded-2xl border border-jojo-border bg-white/85 p-3 shadow-card">
                <div class="mb-3 grid grid-cols-2 overflow-hidden rounded-lg border border-jojo-border bg-white">
                    <button type="button"
                        id="btnAnime"
                        onclick="trocarFoto('anime')"
                        class="aba-ativa h-8 text-xs font-bold text-jojo-purple">
                        <i class="fa-solid fa-star mr-1 text-[10px]"></i>
                        Foto no anime
                    </button>

                    <button type="button"
                        id="btnManga"
                        onclick="trocarFoto('manga')"
                        class="h-8 text-xs font-bold text-jojo-purple">
                        Foto no mangá
                    </button>
                </div>

                <?php if (!empty($foto_anime)): ?>
                    <img id="fotoAnime"
                        src="<?= escapar($foto_anime); ?>"
                        alt="Foto no anime de <?= escapar($stand->nome); ?>"
                        class="foto-opcao ativa h-[245px] w-full rounded-xl object-cover">
                <?php endif; ?>

                <?php if (!empty($foto_manga)): ?>
                    <img id="fotoManga"
                        src="<?= escapar($foto_manga); ?>"
                        alt="Foto no mangá de <?= escapar($stand->nome); ?>"
                        class="foto-opcao h-[245px] w-full rounded-xl object-cover">
                <?php endif; ?>
            </article>

            <!-- Informações -->
            <article class="rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-card">
                <span class="inline-flex rounded-md bg-purple-200 px-8 py-1 text-xs font-bold text-jojo-purple">
                    Stand
                </span>

                <h2 class="mt-2 font-title text-4xl font-bold text-jojo-dark">
                    <?= escapar($stand->nome); ?>
                </h2>

                <div class="mt-3 overflow-hidden rounded-xl border border-jojo-border bg-white/70 text-sm">
                    <div class="grid grid-cols-[24px_1fr_auto] items-center gap-3 border-b border-jojo-border px-4 py-3">
                        <i class="fa-solid fa-star text-jojo-purple"></i>
                        <span class="font-semibold text-[#604b8d]">Parte</span>
                        <strong class="text-jojo-purple">
                            <?= escapar($stand->parte_nome ?? "Não informado"); ?>
                        </strong>
                    </div>

                    <div class="grid grid-cols-[24px_1fr_auto] items-center gap-3 border-b border-jojo-border px-4 py-3">
                        <i class="fa-solid fa-user text-jojo-purple"></i>
                        <span class="font-semibold text-[#604b8d]">Usuário</span>
                        <strong>
                            <?= escapar($stand->personagem_nome ?? "Não informado"); ?>
                        </strong>
                    </div>

                    <div class="grid grid-cols-[24px_1fr_auto] items-center gap-3 px-4 py-3">
                        <i class="fa-solid fa-bullseye text-jojo-purple"></i>
                        <span class="font-semibold text-[#604b8d]">Tipo</span>
                        <strong>
                            <?= escapar($stand->tipo ?? "Não informado"); ?>
                        </strong>
                    </div>
                </div>

                <div class="mt-3 rounded-xl border border-jojo-border bg-white/70 p-4">
                    <h3 class="mb-1 flex items-center gap-2 font-title text-xl font-bold">
                        <i class="fa-solid fa-star text-sm text-jojo-purple"></i>
                        Informações gerais
                    </h3>

                    <p class="text-[13px] leading-6 text-[#372466]">
                        <?= nl2br(escapar($stand->descricao ?? "Nenhuma descrição cadastrada.")); ?>
                    </p>
                </div>
            </article>

        </section>

        <!-- Descrição -->
        <section class="mt-5 grid grid-cols-1 gap-5 rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-card lg:grid-cols-[1fr_430px]">
            <div>
                <h2 class="mb-2 flex items-center gap-2 font-title text-2xl font-bold">
                    <i class="fa-solid fa-star text-base text-jojo-purple"></i>
                    Descrição
                </h2>

                <p class="text-[13px] leading-6 text-[#372466]">
                    <?= nl2br(escapar($stand->descricao ?? "Nenhuma descrição cadastrada.")); ?>
                </p>
            </div>

            <?php if (!empty($foto_anime)): ?>
                <div class="overflow-hidden rounded-xl border border-jojo-border bg-white">
                    <img src="<?= escapar($foto_anime); ?>"
                        alt="<?= escapar($stand->nome); ?>"
                        class="h-[115px] w-full object-cover">

                    <div class="p-3">
                        <h3 class="flex items-center gap-2 text-xs font-bold text-jojo-purple">
                            <i class="fa-solid fa-camera"></i>
                            Descrição foto <?= escapar($stand->nome); ?>
                        </h3>

                        <p class="mt-1 text-xs leading-5 text-[#604b8d]">
                            Imagem cadastrada para representar este Stand.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Habilidades -->
        <section class="mt-5 grid grid-cols-1 gap-5 rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-card lg:grid-cols-[430px_1fr]">
            <?php if (!empty($foto_manga) || !empty($foto_anime)): ?>
                <img src="<?= escapar(!empty($foto_manga) ? $foto_manga : $foto_anime); ?>"
                    alt="Habilidades de <?= escapar($stand->nome); ?>"
                    class="h-[105px] w-full rounded-xl object-cover">
            <?php endif; ?>

            <div>
                <h2 class="mb-1 flex items-center gap-2 font-title text-2xl font-bold">
                    <i class="fa-solid fa-star text-base text-jojo-purple"></i>
                    Habilidades
                </h2>

                <p class="text-[13px] leading-6 text-[#372466]">
                    <?= nl2br(escapar($stand->habilidades ?? "As habilidades deste Stand podem ser registradas e organizadas nesta área.")); ?>
                </p>
            </div>
        </section>

        <!-- Diagrama de força -->
        <section class="mt-5 rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-card">
            <h2 class="mb-3 flex items-center gap-2 font-title text-2xl font-bold">
                <i class="fa-solid fa-star text-base text-jojo-purple"></i>
                Diagrama de força
            </h2>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-[220px_1fr_390px]">

                <?php if (!empty($foto_anime)): ?>
                    <img src="<?= escapar($foto_anime); ?>"
                        alt="<?= escapar($stand->nome); ?>"
                        class="h-[100px] w-full rounded-xl object-cover">
                <?php endif; ?>

                <div class="rounded-xl border border-jojo-border bg-white/70 p-4">
                    <h3 class="mb-1 font-title text-xl font-bold">
                        <?= escapar($stand->nome); ?>
                    </h3>

                    <p class="text-[13px] leading-6 text-[#372466]">
                        <?= nl2br(escapar($stand->descricao_forca ?? $stand->descricao ?? "Nenhuma descrição cadastrada.")); ?>
                    </p>
                </div>

                <div class="rounded-xl border border-jojo-border bg-white/70 p-4">
                    <h3 class="mb-2 text-center text-xs font-bold text-jojo-purple">
                        Diagrama de força
                    </h3>

                    <?php if (!empty($diagrama_forca)): ?>
                        <img src="<?= escapar($diagrama_forca); ?>"
                            alt="Diagrama de força de <?= escapar($stand->nome); ?>"
                            class="mx-auto h-[125px] w-full object-contain">
                    <?php else: ?>
                        <p class="flex h-[125px] items-center justify-center text-center text-xs text-[#604b8d]">
                            Nenhum diagrama cadastrado.
                        </p>
                    <?php endif; ?>
                </div>

            </div>
        </section>

        <!-- Botões -->
        <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2 md:px-[26%]">
            <a href="excluir.php?id_stand=<?= $stand->id; ?>&parte_id=<?= $parte_id; ?>"
                onclick="return confirm('Tem certeza que deseja excluir este Stand?')"
                class="flex h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-pink-500 to-pink-700 text-sm font-bold text-white shadow-card transition hover:brightness-110">
                <i class="fa-solid fa-trash-can"></i>
                Excluir
            </a>

            <a href="editar.php?id_stand=<?= $stand->id; ?>&parte_id=<?= $parte_id; ?>"
                class="flex h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-purple-500 to-jojo-purple text-sm font-bold text-white shadow-card transition hover:brightness-110">
                <i class="fa-solid fa-pen"></i>
                Editar
            </a>
        </div>

    </main>

    <?php require_once "../../src/includes/footer.php"; ?>

    <script>
        function trocarFoto(tipo) {
            const fotoAnime = document.getElementById("fotoAnime");
            const fotoManga = document.getElementById("fotoManga");
            const btnAnime = document.getElementById("btnAnime");
            const btnManga = document.getElementById("btnManga");

            if (!fotoAnime || !fotoManga) {
                return;
            }

            if (tipo === "anime") {
                fotoAnime.classList.add("ativa");
                fotoManga.classList.remove("ativa");

                btnAnime.classList.add("aba-ativa");
                btnManga.classList.remove("aba-ativa");
            } else {
                fotoManga.classList.add("ativa");
                fotoAnime.classList.remove("ativa");

                btnManga.classList.add("aba-ativa");
                btnAnime.classList.remove("aba-ativa");
            }
        }
    </script>
</body>
</html>