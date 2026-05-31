<?php 
session_start();
require_once "../src/config/conexao.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["senha"], $_POST["email"])) {
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    try {
        $email = strtolower(trim($_POST['email'])); # Remove espaços e deixa tudo minusculo
        $senha = trim($_POST["senha"]);

        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":email" => $email
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$usuario || !password_verify($senha, $usuario->senha)) {
            throw new Exception("Email ou senha inválidos");
        }

        $_SESSION['usuario_id'] = $usuario->id;
        $_SESSION['usuario_nome'] = $usuario->nome;

        header("Location: index.php?status=sucesso_login");
        exit;

    } catch (Exception $e) { # Pega o erro vindo do throw new exeption
        $erro = $e->getMessage();
        echo $erro; # Pega a mensagem de erro e seta na variavel
    } catch (PDOException $e) {
        $erro = "Erro interno, pedimos desculpa";
    };
};
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JoJo's Bizarre Adventure - CRUD</title>
    <link rel="stylesheet" href="assets/css/login_singup.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700;900&family=Cinzel:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'jojo-purple': '#6B21A8',
                        'jojo-purple-light': '#A855F7',
                        'jojo-purple-bg': '#C4B5FD',
                        'jojo-pink': '#EC4899',
                        'jojo-pink-dark': '#BE185D',
                        'jojo-rose': '#F43F5E',
                    },
                    fontFamily: {
                        'cinzel': ['Cinzel Decorative', 'serif'],
                        'cinzel-reg': ['Cinzel', 'serif'],
                        'lato': ['Lato', 'sans-serif'],
                    },
                    backgroundImage: {
                        'jojo-gradient': 'linear-gradient(135deg, #9333ea 0%, #c084fc 50%, #ddd6fe 100%)',
                        'btn-gradient': 'linear-gradient(90deg, #ec4899 0%, #f43f5e 100%)',
                        'panel-gradient': 'linear-gradient(180deg, #f5f3ff 0%, #fdf4ff 100%)',
                    },
                    boxShadow: {
                        'jojo': '0 25px 60px rgba(107, 33, 168, 0.35)',
                        'input': '0 2px 8px rgba(168, 85, 247, 0.12)',
                        'btn': '0 8px 24px rgba(236, 72, 153, 0.45)',
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <main class="w-full h-[calc(100dvh-40px)] rounded-3xl overflow-hidden shadow-jojo flex" style="min-height: 560px; background: rgba(255,255,255,0.08); backdrop-filter: blur(2px);">

        <!-- Left: Artwork panel -->
        <div class="hidden md:flex relative w-[50%] manga-bg overflow-hidden items-end justify-center max">


            <!-- Sparkle stars -->
            <!-- <div class="absolute top-10 left-10 text-purple-300 text-2xl opacity-70 animate-pulse">✦</div>
            <div class="absolute top-1/3 right-8 text-purple-200 text-lg opacity-60" style="animation: twinkle 1.8s ease-in-out infinite;">✦</div>
            <div class="absolute bottom-32 left-8 text-purple-300 text-xl opacity-50" style="animation: twinkle 2.4s ease-in-out infinite;">✦</div> -->

        </div>

        <!-- Right: Login panel -->
        <div class="panel flex-1 flex flex-col items-center justify-center px-10 py-12">

            <!-- Badge / Logo -->
            <div class="badge-glow mb-4">
                <img
                    src="assets/img/login_singup/logo.png"
                    alt="JoJo Archive Logo"
                    class="w-20 h-20 object-contain"
                />
            </div>

            <!-- Title -->
            <h1 class="font-cinzel py-2 text-3xl font-black tracking-widest mb-1"
                style="background: linear-gradient(90deg, #6b21a8, #a855f7, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                JOJO ARCHIVE
            </h1>

            <!-- CRUD badge -->
            <div class="relative mb-5 flex items-center gap-3">
                <span class="text-yellow-500 text-sm">✦ ✦</span>
                <span class="bg-jojo-pink text-white font-cinzel-reg font-bold text-xs tracking-[0.25em] px-5 py-1.5 rounded-full shadow-btn">
                    SISTEMA CRUD
                </span>
                <span class="text-yellow-500 text-sm">✦ ✦</span>
            </div>

            <!-- Subtitle -->
            <p class="font-lato text-purple-500 text-sm mb-7 tracking-wide">
                Entre na sua conta para continuar
            </p>

            <!-- Form -->
            <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" class="w-full max-w-sm space-y-4">

                <!-- Email -->
                <div class="bg-white rounded-xl px-4 py-3 shadow-input border border-purple-100">
                    
                    <label for="email" class="block font-lato font-bold text-gray-700 text-xs mb-1.5 tracking-wide">Email</label>
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-jojo-pink flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            placeholder="seu@email.com"
                            required
                            class="input-focus flex-1 font-lato text-sm text-gray-500 placeholder-gray-300 bg-transparent border-none focus:ring-0 p-0"
                        />
                    </div>
                </div>

                <!-- Senha -->
                <div class="bg-white rounded-xl px-4 py-3 shadow-input border border-purple-100">
                    <label for="senha" class="block font-lato font-bold text-gray-700 text-xs mb-1.5 tracking-wide">Senha</label>
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-jojo-pink flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <input
                            type="password"
                            name="senha"
                            id="senha"
                            placeholder="••••••••••"
                            required
                            class="input-focus flex-1 font-lato text-sm text-gray-500 placeholder-gray-400 bg-transparent border-none focus:ring-0 p-0"
                        />
                        <button type="button" onclick="toggleSenha()" class="text-purple-300 hover:text-purple-500 transition-colors">
                            <svg id="eye-icon" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Lembrar de mim -->
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="lembrar"
                        name="lembrar"
                        class="w-4 h-4 rounded border-purple-300 text-jojo-pink focus:ring-jojo-pink cursor-pointer"
                    />
                    <label for="lembrar" class="font-lato text-sm text-gray-600 cursor-pointer select-none">
                        Lembrar de mim
                    </label>
                </div>

                <!-- Botão Entrar -->
                <button
                    type="submit"
                    class="btn-login w-full py-4 rounded-2xl text-white font-cinzel-reg font-bold tracking-[0.2em] text-sm flex items-center justify-center gap-3 shadow-btn"
                >
                    <span class="text-pink-200 text-xs">✦</span>
                    ENTRAR
                    <span class="text-lg">→</span>
                </button>

            </form>

            <!-- Links -->
            <div class="mt-5 flex flex-col items-center gap-3 w-full max-w-sm">
                <a href="" class="font-lato text-sm text-purple-500 underline underline-offset-2 hover:text-jojo-pink transition-colors decoration-purple-300">
                    Esqueci minha senha
                </a>

                <div class="divider-star w-full text-purple-400 text-xs">
                    <span>✦</span>
                </div>

                <a href="singup.php" class="font-lato text-sm text-purple-500 underline underline-offset-2 hover:text-jojo-pink transition-colors decoration-purple-300">
                    Criar conta
                </a>
            </div>
        </div>
    </main>
    <script src="assets/js/login_singup.js"></script>
</body>
</html>