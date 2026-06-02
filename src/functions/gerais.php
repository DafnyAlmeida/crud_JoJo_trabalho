<?php 

// Função que garante a exibição de texto
function escapar(?string $valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function link_pagina(int $parte_id, int $pagina): string {
    return "index.php?" . http_build_query([
        "parte_id" => $parte_id,
        "pagina" => $pagina
    ]);
}

function pegar_pagina_atual(): int {
    $pagina = filter_input(INPUT_GET, "pagina", FILTER_VALIDATE_INT);

    if (!$pagina || $pagina < 1) {
        return 1;
    }

    return $pagina;
}

function validar_id_get(String $campo_nome): int|false|null {
    return filter_input(INPUT_GET, "$campo_nome", FILTER_VALIDATE_INT);
}

function validar_id_post(string $campo_nome): int|false|null {
    return filter_input(INPUT_POST, $campo_nome, FILTER_VALIDATE_INT,);
}

function caminho_foto(?string $foto, string $prefixo = "../"): string {

    if (empty($foto)) {
        return "";
    }

    return $prefixo . ltrim($foto, "/");
}

function texto_ou_padrao(?string $texto, string $mensagem_padrao): string {

    $texto = trim((string) $texto);

    if ($texto === "") {
        return $mensagem_padrao;
    }

    return $texto;
}

// function paginacao(int $por_pagina, int $pagina_atual, int $parte_id, int $usuario_id) {

//     $sql = "
//         SELECT COUNT(DISTINCT p.id)
//         FROM personagens p
//         INNER JOIN personagens_partes pp
//             ON pp.personagem_id = p.id
//         WHERE pp.parte_id = :parte_id
//         AND p.usuario_id = :usuario_id
//     ";

//     $stmt = $pdo->prepare($sql);
//     $stmt->execute([
//         ":parte_id" => $parte_id,
//         ":usuario_id" => $usuario_id
//     ]);

// }