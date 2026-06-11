<?php 
session_start();
require_once "../src/config/conexao.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["senha"], $_POST["email"], $_POST["nome"])) {

    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    try {
        // trim - remove espaços
        // str - letras minusculas
        // ucwoeds - coloca primeiro letra maiscula
        $email = strtolower(trim($_POST['email']));
        $senha = trim($_POST["senha"]);
        $nome = ucwords(strtolower(trim($_POST['nome'])));

        // Verifica se esta vazio
        if (empty($nome) || empty($email) || empty($senha)) {
            throw new Exception("Preencha todos os campos");
        }

        // Verifica se o campo é valido de acordo com um tipo
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email inválido.");
        }

        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":email" => $email
        ]);

        if (!$stmt->fetch()) {
            throw new Exception("Email já cadastrado.");
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nome, email, senha)
                VALUES (:nome, :email, :senha)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":nome" => $nome,
            ":email" => $email,
            ":senha" => $senhaHash
        ]);

        header("Location: login.php?status=cadastro_ok");
        exit;

    } catch (Exception $e) {
        $erro = $e->getMessage();
    } catch (PDOException $e) {
        $erro = "Erro interno, pedimos desculpa";
    }
};

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta | JoJo Archive</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">

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

    <link rel="stylesheet" href="assets/css/geral.css">
    
</head>

<body class="min-h-screen font-body text-jojo-dark">
    <main class="flex min-h-screen items-center justify-center px-5 py-8">
        <section class="grid w-full max-w-[1040px] overflow-hidden rounded-[26px] border border-jojo-border bg-white shadow-card lg:grid-cols-[1fr_1fr]">

            <!-- Formulário - lado esquerdo -->
            <div class="flex min-h-[560px] items-center justify-center px-6 py-8 md:px-10">
                <div class="w-full max-w-[390px]">
                    <!-- Logo -->
                    <div class="mb-7 text-center">
                        <img
                            src="assets/img/logo.png"
                            alt="Logo JoJo Archive"
                            class="mx-auto mb-4 h-16 w-16 rounded-2xl object-contain shadow-soft"
                        >

                        <span class="mb-2 inline-flex rounded-md bg-purple-100 px-3 py-1 text-[11px] font-semibold text-jojo-purple">
                            Criar nova conta
                        </span>

                        <h2 class="font-title text-[30px] font-bold leading-none text-jojo-dark">
                            Comece agora
                        </h2>

                        <p class="mt-2 text-[13px] leading-6 text-[#66567d]">
                            Cadastre-se para gerenciar seu acervo JoJo.
                        </p>
                    </div>

                    <?php if (!empty($erro)): ?>
                        <div class="mb-4 rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-semibold text-red-500">
                            <i class="fa-solid fa-circle-exclamation mr-2"></i>
                            <?= htmlspecialchars($erro); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" class="space-y-4">

                        <div>
                            <label for="nome" class="mb-1.5 block text-[12px] font-semibold text-[#473267]">
                                Nome
                            </label>

                            <div class="flex items-center gap-3 rounded-xl border border-jojo-border bg-white px-3 py-2.5 shadow-soft transition focus-within:border-jojo-lilac focus-within:ring-4 focus-within:ring-purple-100">
                                <i class="fa-regular fa-user text-sm text-jojo-purple"></i>

                                <input
                                    type="text"
                                    name="nome"
                                    id="nome"
                                    placeholder="Seu nome"
                                    required
                                    class="w-full border-none bg-transparent text-[13px] text-[#433366] outline-none placeholder:text-slate-300 focus:ring-0"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="email" class="mb-1.5 block text-[12px] font-semibold text-[#473267]">
                                Email
                            </label>

                            <div class="flex items-center gap-3 rounded-xl border border-jojo-border bg-white px-3 py-2.5 shadow-soft transition focus-within:border-jojo-lilac focus-within:ring-4 focus-within:ring-purple-100">
                                <i class="fa-regular fa-envelope text-sm text-jojo-purple"></i>

                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    placeholder="seu@email.com"
                                    required
                                    class="w-full border-none bg-transparent text-[13px] text-[#433366] outline-none placeholder:text-slate-300 focus:ring-0"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="senha" class="mb-1.5 block text-[12px] font-semibold text-[#473267]">
                                Senha
                            </label>

                            <div class="flex items-center gap-3 rounded-xl border border-jojo-border bg-white px-3 py-2.5 shadow-soft transition focus-within:border-jojo-lilac focus-within:ring-4 focus-within:ring-purple-100">
                                <i class="fa-solid fa-lock text-sm text-jojo-purple"></i>

                                <input
                                    type="password"
                                    name="senha"
                                    id="senha"
                                    placeholder="••••••••"
                                    required
                                    class="w-full border-none bg-transparent text-[13px] text-[#433366] outline-none placeholder:text-slate-300 focus:ring-0"
                                >

                                <button type="button" onclick="toggleSenha()" class="text-jojo-lilac transition hover:text-jojo-purple">
                                    <i id="iconeSenha" class="fa-regular fa-eye text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#7045c9] to-[#9665dc] text-sm font-bold text-white shadow-button transition hover:opacity-95"
                        >
                            Criar conta
                            <i class="fa-solid fa-angle-right text-xs"></i>
                        </button>

                    </form>

                    <div class="mt-6 flex items-center gap-3">
                        <span class="h-px flex-1 bg-jojo-border"></span>
                        <i class="fa-solid fa-star text-[10px] text-jojo-lilac"></i>
                        <span class="h-px flex-1 bg-jojo-border"></span>
                    </div>

                    <p class="mt-5 text-center text-[13px] text-[#66567d]">
                        Já tem uma conta?
                        <a href="login.php" class="font-bold text-jojo-purple transition hover:text-jojo-pink">
                            Entrar
                        </a>
                    </p>
                </div>
            </div>

            <!-- Lado direito -->
            <div class="banner-jojos relative hidden min-h-[560px] overflow-hidden p-8 lg:flex lg:flex-col lg:justify-between">

                <div class="relative z-10">
                    <img
                        src="assets/img/logo.png"
                        alt="Logo JoJo Archive"
                        class="mb-5 h-16 w-16 rounded-2xl object-contain"
                    >

                    <span class="inline-flex rounded-md bg-white/15 px-3 py-1 text-[11px] font-semibold text-white">
                        Novo usuário
                    </span>

                    <h1 class="mt-4 font-title text-[42px] font-bold leading-tight text-white">
                        Crie sua conta
                    </h1>

                    <p class="mt-3 max-w-[340px] text-sm leading-7 text-purple-100">
                        Organize personagens, stands, partes e referências em uma experiência temática.
                    </p>
                </div>

                <div class="relative z-10 rounded-2xl border border-white/20 bg-white/10 p-5">
                    <div class="mb-2 flex items-center gap-2 text-sm font-semibold text-white">
                        <i class="fa-regular fa-star"></i>
                        JoJo Archive
                    </div>

                    <p class="text-xs leading-6 text-purple-100">
                        Sua jornada começa aqui. Cadastre-se para continuar.
                    </p>
                </div>

                <div class="absolute bottom-8 right-8 text-7xl text-white/10">
                    ✦
                </div>
            </div>
        </section>
    </main>

    <script>
        function toggleSenha() {
            const campoSenha = document.getElementById("senha");
            const iconeSenha = document.getElementById("iconeSenha");

            if (campoSenha.type === "password") {
                campoSenha.type = "text";
                iconeSenha.classList.remove("fa-eye");
                iconeSenha.classList.add("fa-eye-slash");
                return;
            }

            campoSenha.type = "password";
            iconeSenha.classList.remove("fa-eye-slash");
            iconeSenha.classList.add("fa-eye");
        }
    </script>
</body>
</html>