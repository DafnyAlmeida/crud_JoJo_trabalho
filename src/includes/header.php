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
            <!-- Fazer igual ao do wilson que abre o sair e o ver perfil -->
            <button type="button"
                    class="ml-1 text-jojo-dark transition hover:text-jojo-purple">
            </button>
        </div>
    </div>
</header>