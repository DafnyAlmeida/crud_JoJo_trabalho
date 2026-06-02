<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
include_once "../../src/functions/upload.php";
require_once "../../src/functions/gerais.php";
require_once "../../src/classes/personagem.class.php";

$personagem_id = validar_id_get("id_personagem");
$parte_id = validar_id_get("parte_id");
$usuario_id = (int) ($_SESSION["usuario_id"] ?? 0);

if (!$personagem_id || !$parte_id || !$usuario_id) {
    header("Location: index.php?status=id_invalido");
    exit;
}

$personagemModel = new Personagem($pdo);

$personagem = $personagemModel->buscarDeletar(
    $personagem_id,
    $usuario_id
);

if (!$personagem) {
    header(
        "Location: index.php?"
        . http_build_query([
            "parte_id" => $parte_id,
            "status" => "id_invalido"
        ])
    );
    exit;
}

try {

    $personagemModel->deletar(
        $personagem_id,
        $usuario_id
    );

    if (!empty($personagem->foto_anime)) {
        deletar_pasta($personagem->foto_anime);
    }

    header("Location: index.php?parte_id=<?= $parte_id ?>&status=delete_ok");
    exit;

} catch (Throwable $erro) {
    error_log($erro->getMessage());

    header("Location: index.php?parte_id=<?= $parte_id ?>&status=delete_erro");
    exit;
}