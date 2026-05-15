<?php

function criar_nome_pasta($nome_base) {
    $nome_pasta = mb_strtolower(trim($nome_base), 'UTF-8');
    $nome_pasta = str_replace(' ', '-', $nome_pasta);
    $nome_pasta = preg_replace('/[^a-z0-9-]/', '', $nome_pasta);

    return $nome_pasta;
}

function salvar_imagem($campo, $tipo_pasta, $nome_base) {
    $arquivo = $_FILES[$campo];

    $nome_pasta = criar_nome_pasta($nome_base);

    $pasta_destino = "../../public/uploads/" . $tipo_pasta . "/" . $nome_pasta . "/";

    if (!is_dir($pasta_destino)) {
        mkdir($pasta_destino, 0777, true);
    }

    $extensao = pathinfo($arquivo["name"], PATHINFO_EXTENSION);
    $novo_nome = uniqid($campo . "_") . "." . $extensao;

    $caminho_final = $pasta_destino . $novo_nome;

    move_uploaded_file($arquivo["tmp_name"], $caminho_final);

    return "uploads/" . $tipo_pasta . "/" . $nome_pasta . "/" . $novo_nome;
}

function salvar_imagem_array($campo, $index, $tipo_pasta, $nome_base) {
    $arquivo = $_FILES[$campo];

    $nome_pasta = criar_nome_pasta($nome_base);

    $pasta_destino = "../../public/uploads/" . $tipo_pasta . "/" . $nome_pasta . "/";

    if (!is_dir($pasta_destino)) {
        mkdir($pasta_destino, 0777, true);
    }

    $extensao = pathinfo($arquivo["name"][$index], PATHINFO_EXTENSION);
    $novo_nome = uniqid($campo . "_") . "." . $extensao;

    $caminho_final = $pasta_destino . $novo_nome;

    move_uploaded_file($arquivo["tmp_name"][$index], $caminho_final);

    return "uploads/" . $tipo_pasta . "/" . $nome_pasta . "/" . $novo_nome;
}