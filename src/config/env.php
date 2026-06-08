<?php

function carregar_env(string $caminho): void {
    
    if (!file_exists($caminho)) {
        return;
    }

    $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($linhas as $linha) {
        $linha = trim($linha);

        if ($linha === "" || str_starts_with($linha, "#")) {
            continue;
        }

        if (!str_contains($linha, "=")) {
            continue;
        }

        [$chave, $valor] = explode("=", $linha, 2);

        $chave = trim($chave);
        $valor = trim($valor);

        $_ENV[$chave] = $valor;
        putenv("$chave=$valor");
    }
}