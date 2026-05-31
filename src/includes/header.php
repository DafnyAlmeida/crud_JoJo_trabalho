<?php
require_once "bloqueio.php";
$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Administrador';
?>

<?php
$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Administrador';
// $avatarUsuario = $baseUrl . '/assets/img/avatar.png';
?>

<header class="h-[76px] w-full border-b border-jojo-border bg-white/90 backdrop-blur-sm">
    <div class="mx-auto flex h-full items-center justify-between gap-8 px-8">

        <!-- Logo -->
        <div class="flex min-w-[245px] items-center gap-2 no-underline">
            <span class="text-4xl text-jojo-purple">✦</span>

            <span class="font-title text-[28px] font-bold tracking-wide text-jojo-dark">
                JOJO
                <span class="text-jojo-lilac">DEX</span>
            </span>
        </div>

        <!-- Barra de pesquisa -->
        <form action="busca.php"
              method="GET"
              class="translate-y-[6px] flex h-[46px] w-full max-w-[470px] items-center gap-3 rounded-xl border border-jojo-border bg-white px-4 shadow-sm">
            
            <svg class="h-5 w-5 shrink-0 text-jojo-purple"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="m21 21-4.35-4.35m1.1-5.15a6.25 6.25 0 1 1-12.5 0 6.25 6.25 0 0 1 12.5 0Z"/>
            </svg>

            <input type="search"
                   name="pesquisa"
                   placeholder="Buscar em todo o arquivo..."
                   class="flex-1 bg-transparent text-sm text-jojo-dark outline-none placeholder:text-slate-400">

            <span class="rounded-md bg-purple-50 px-2 py-1 text-xs font-semibold text-jojo-purple">
                Ctrl + K
            </span>
        </form>

        <!-- Usuário -->
        <div class="flex min-w-[245px] items-center justify-end gap-4">
            <a href="../../logout.php">Logout</a>

            <!-- Avatar -->
            <img src="<?= htmlspecialchars($avatarUsuario) ?>"
                 alt="Foto do usuário"
                 class="h-11 w-11 rounded-full border-2 border-purple-100 object-cover">

            <!-- Dados -->
            <div class="flex">
                <div class="flex flex-col">
                    <strong class="text-sm font-semibold text-jojo-dark">
                        <?= htmlspecialchars($nomeUsuario) ?>
                    </strong>
                    <span class="text-xs text-slate-500">
                        Usuário
                    </span>
                </div>

                <div class="pt-3 pl-2">
                    <i class="fa-solid fa-angle-down"></i>
                </div>
            </div>

            <!-- Menu -->
            <button type="button"
                    class="ml-1 text-jojo-dark transition hover:text-jojo-purple">
            </button>
        </div>
    </div>
</header>