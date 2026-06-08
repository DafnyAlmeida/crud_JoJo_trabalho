<?php
require_once "bloqueio.php";

$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Administrador';

?>

<header class="h-[76px] w-full border-b border-jojo-border bg-white/90 backdrop-blur-sm relative z-[9998]">
    <div class="mx-auto flex h-full items-center justify-between gap-8 px-8">

        <!-- Logo -->
        <div class="flex min-w-[245px] items-center gap-2 no-underline">
            <span class="text-4xl text-jojo-purple">✦</span>

            <span class="font-title text-[28px] font-bold tracking-wide text-jojo-dark">
                JOJO
                <span class="text-jojo-lilac">DEX</span>
            </span>
        </div>

        <!-- Usuário -->
        <div class="relative flex min-w-[245px] items-center justify-end gap-4">

            <!-- Dados do usuário -->
            <button type="button"id="btnUserMenu" class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-purple-50">
                <div class="flex flex-col text-right">
                    <strong class="text-sm font-semibold text-jojo-dark">
                        <?= htmlspecialchars($nomeUsuario) ?>
                    </strong>

                    <span class="text-xs text-slate-500">
                        Usuário
                    </span>
                </div>

                <i id="iconeSeta" class="fa-solid fa-angle-down text-jojo-dark transition"></i>
            </button>

            <!-- Menu flutuante -->
            <div id="userMenu" class="absolute right-0 top-[58px] z-[9998] hidden w-44 rounded-xl border border-purple-100 bg-white p-2 shadow-lg">
                <a href="../../logout.php" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-base text-red-600 transition hover:bg-red-50">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    Sair
                </a>
            </div>
        </div>
    </div>
</header>

<script>
    const btnUserMenu = document.getElementById("btnUserMenu");
    const userMenu = document.getElementById("userMenu");
    const iconeSeta = document.getElementById("iconeSeta");

    btnUserMenu.addEventListener("click", function (event) {
        event.stopPropagation();

        userMenu.classList.toggle("hidden");
        iconeSeta.classList.toggle("rotate-180");
    });

    document.addEventListener("click", function () {
        userMenu.classList.add("hidden");
        iconeSeta.classList.remove("rotate-180");
    });
</script>