<?php

include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";

require_once "../../src/functions/upload.php";
require_once "../../src/functions/gerais.php";
require_once "../../src/classes/personagem.class.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$personagem_id = validar_id_post("id_personagem");
$parte_id = validar_id_post("parte_id");
$usuario_id = (int) ($_SESSION["usuario_id"] ?? 0);

if (!$personagem_id || !$usuario_id) {
    header("Location: index.php?status=id_invalido");
    exit;
}

$nome = trim((string) ($_POST["nome"] ?? ""));
$biografia = trim((string) ($_POST["biografia"] ?? ""));
$infor_gerais = trim((string) ($_POST["infor_gerais"] ?? ""));

if ($nome === "") {
    header(
        "Location: edit.php?"
        . http_build_query([
            "id_personagem" => $personagem_id,
            "parte_id" => $parte_id,
            "status" => "nome_obrigatorio"
        ])
    );
    exit;
}

$personagemModel = new Personagem($pdo);

$personagem = $personagemModel->buscarParaEditar(
    $personagem_id,
    $usuario_id
);

if (!$personagem) {
    header("Location: index.php?status=id_invalido");
    exit;
}

/*
|--------------------------------------------------------------------------
| Mantém as fotos atuais como padrão
|--------------------------------------------------------------------------
*/
$foto_anime = $personagem->foto_anime;
$foto_manga = $personagem->foto_manga;
$foto_catalogo = $personagem->foto_catalogo;
$foto_biografia = $personagem->foto_biografia;

/*
|--------------------------------------------------------------------------
| Guarda as imagens antigas que serão apagadas somente após o UPDATE
|--------------------------------------------------------------------------
*/
$fotos_antigas_para_apagar = [];

/*
|--------------------------------------------------------------------------
| Guarda imagens novas para apagar caso o UPDATE falhe
|--------------------------------------------------------------------------
*/
$fotos_novas_para_remover = [];

try {
    /*
    |--------------------------------------------------------------------------
    | Nova foto do anime
    |--------------------------------------------------------------------------
    */
    if (!empty($_FILES["foto_anime"]["name"])) {
        $nova_foto = salvar_imagem(
            "foto_anime",
            "personagens",
            $nome
        );

        $fotos_novas_para_remover[] = $nova_foto;

        if (!empty($personagem->foto_anime)) {
            $fotos_antigas_para_apagar[] = $personagem->foto_anime;
        }

        $foto_anime = $nova_foto;
    }

    /*
    |--------------------------------------------------------------------------
    | Nova foto do mangá
    |--------------------------------------------------------------------------
    */
    if (!empty($_FILES["foto_manga"]["name"])) {
        $nova_foto = salvar_imagem(
            "foto_manga",
            "personagens",
            $nome
        );

        $fotos_novas_para_remover[] = $nova_foto;

        if (!empty($personagem->foto_manga)) {
            $fotos_antigas_para_apagar[] = $personagem->foto_manga;
        }

        $foto_manga = $nova_foto;
    }

    /*
    |--------------------------------------------------------------------------
    | Nova foto de catálogo
    |--------------------------------------------------------------------------
    */
    if (!empty($_FILES["foto_catalogo"]["name"])) {
        $nova_foto = salvar_imagem(
            "foto_catalogo",
            "personagens",
            $nome
        );

        $fotos_novas_para_remover[] = $nova_foto;

        if (!empty($personagem->foto_catalogo)) {
            $fotos_antigas_para_apagar[] = $personagem->foto_catalogo;
        }

        $foto_catalogo = $nova_foto;
    }

    /*
    |--------------------------------------------------------------------------
    | Nova foto de biografia
    |--------------------------------------------------------------------------
    */
    if (!empty($_FILES["foto_biografia"]["name"])) {
        $nova_foto = salvar_imagem(
            "foto_biografia",
            "personagens",
            $nome
        );

        $fotos_novas_para_remover[] = $nova_foto;

        if (!empty($personagem->foto_biografia)) {
            $fotos_antigas_para_apagar[] = $personagem->foto_biografia;
        }

        $foto_biografia = $nova_foto;
    }

    /*
    |--------------------------------------------------------------------------
    | Atualiza os dados no banco usando a classe
    |--------------------------------------------------------------------------
    */
    $personagemModel->editar(
        $personagem_id,
        $usuario_id,
        [
            "nome" => $nome,
            "biografia" => $biografia,
            "foto_anime" => $foto_anime,
            "foto_manga" => $foto_manga,
            "foto_catalogo" => $foto_catalogo,
            "foto_biografia" => $foto_biografia,
            "infor_gerais" => $infor_gerais
        ]
    );

    foreach ($fotos_antigas_para_apagar as $foto_antiga) {
        deletar_arquivo($foto_antiga);
    }

    $parametros = [
        "status" => "update_ok"
    ];

    if ($parte_id) {
        $parametros["parte_id"] = $parte_id;
    }

    header(
        "Location: index.php?"
        . http_build_query($parametros)
    );
    exit;

} catch (Throwable $erro) {
    
    foreach ($fotos_novas_para_remover as $foto_nova) {
        try {
            deletar_arquivo($foto_nova);
        } catch (Throwable $erroArquivo) {
            error_log($erroArquivo->getMessage());
        }
    }

    error_log($erro->getMessage());

    $parametros = [
        "status" => "update_erro"
    ];

    if ($parte_id) {
        $parametros["parte_id"] = $parte_id;
    }

    header(
        "Location: index.php?"
        . http_build_query($parametros)
    );
    exit;
}