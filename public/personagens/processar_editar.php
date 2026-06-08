<?php

include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
require_once "../../src/functions/upload.php";
require_once "../../src/functions/gerais.php";

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

$idade = $_POST["idade"] ?? null;
$papel = $_POST["papel"] ?? null;

if ($nome === "") {
    header(
        "Location: editar.php?"
        . http_build_query([
            "id_personagem" => $personagem_id,
            "parte_id" => $parte_id,
            "status" => "nome_obrigatorio"
        ])
    );
    exit;
}

$personagem = buscarParaEditar(
    $pdo,
    $personagem_id,
    $usuario_id
);

if (!$personagem) {
    header("Location: index.php?status=id_invalido");
    exit;
}

$foto_anime = $personagem->foto_anime;
$foto_manga = $personagem->foto_manga;
$foto_catalogo = $personagem->foto_catalogo;
$foto_biografia = $personagem->foto_biografia;

$fotos_antigas_para_apagar = [];
$fotos_novas_para_remover = [];

try {
    $foto_anime = processar_upload_foto(
        "foto_anime",
        "personagens",
        $nome,
        $personagem->foto_anime,
        $fotos_novas_para_remover,
        $fotos_antigas_para_apagar
    );

    $foto_manga = processar_upload_foto(
        "foto_manga",
        "personagens",
        $nome,
        $personagem->foto_manga,
        $fotos_novas_para_remover,
        $fotos_antigas_para_apagar
    );

    $foto_catalogo = processar_upload_foto(
        "foto_catalogo",
        "personagens",
        $nome,
        $personagem->foto_catalogo,
        $fotos_novas_para_remover,
        $fotos_antigas_para_apagar
    );

    $foto_biografia = processar_upload_foto(
        "foto_biografia",
        "personagens",
        $nome,
        $personagem->foto_biografia,
        $fotos_novas_para_remover,
        $fotos_antigas_para_apagar
    );

    editar(
        $pdo, 
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

    $sql = "UPDATE personagens_partes
            SET idade = :idade,
                papel = :papel
            WHERE personagem_id = :personagem_id
            AND parte_id = :parte_id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":idade" => $idade,
        ":papel" => $papel,
        ":personagem_id" => $personagem_id,
        ":parte_id" => $parte_id
    ]);

    foreach ($fotos_antigas_para_apagar as $foto_antiga) {
        deletar_arquivo($foto_antiga);
    }

    $parametros = [
        "status" => "update_ok"
    ];

    if ($parte_id) {
        $parametros["parte_id"] = $parte_id;
    }

    header("Location: index.php?" . http_build_query($parametros));
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

    header("Location: index.php?" . http_build_query($parametros));
    exit;
}