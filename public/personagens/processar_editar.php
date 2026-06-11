<?php
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";
require_once "../../src/functions/upload.php";
require_once "../../src/functions/gerais.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function voltar_para_editar_personagem(
    int $personagem_id,
    ?int $parte_id = null
): void {
    $parametros = [
        "id_personagem" => $personagem_id
    ];

    if (!empty($parte_id)) {
        $parametros["parte_id"] = $parte_id;
    }

    header(
        "Location: editar.php?"
        . http_build_query($parametros)
    );

    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$personagem_id_recebido =
    $_POST["id_personagem"]
    ?? $_GET["id_personagem"]
    ?? null;

$parte_id_recebida =
    $_POST["parte_id"]
    ?? $_GET["parte_id"]
    ?? null;

$personagem_id = filter_var(
    $personagem_id_recebido,
    FILTER_VALIDATE_INT
);

$parte_id = filter_var(
    $parte_id_recebida,
    FILTER_VALIDATE_INT
);

$usuario_id = (int) ($_SESSION["usuario_id"] ?? 0);

if (!$personagem_id || !$usuario_id) {
    $_SESSION["erro"] = "Personagem inválido.";

    header("Location: index.php?status=erro");
    exit;
}

if (!$parte_id) {
    $_SESSION["erro"] = "Parte inválida.";

    header("Location: index.php?status=erro");
    exit;
}

$personagem = buscarParaEditar(
    $pdo,
    (int) $personagem_id,
    $usuario_id
);

if (!$personagem) {
    $_SESSION["erro"] = "Personagem não encontrado.";

    header("Location: index.php?status=erro");
    exit;
}

$limite_post = converter_tamanho_php_para_bytes(
    ini_get("post_max_size")
);

$tamanho_enviado = (int) (
    $_SERVER["CONTENT_LENGTH"] ?? 0
);

if (
    $limite_post > 0 &&
    $tamanho_enviado > $limite_post
) {
    $_SESSION["erro"] =
        "As imagens são grandes demais. "
        . "Envie imagens menores. "
        . "O limite total atual é "
        . ini_get("post_max_size")
        . ".";

    voltar_para_editar_personagem(
        (int) $personagem_id,
        (int) $parte_id
    );
}

$fotos_novas_para_remover = [];
$fotos_antigas_para_apagar = [];

try {

    $nome = trim(
        (string) ($_POST["nome"] ?? "")
    );

    $biografia = trim(
        (string) ($_POST["biografia"] ?? "")
    );

    $infor_gerais = trim(
        (string) ($_POST["infor_gerais"] ?? "")
    );

    $idade = filter_input(
        INPUT_POST,
        "idade",
        FILTER_VALIDATE_INT,
        [
            "options" => [
                "min_range" => 0
            ]
        ]
    );

    $papel = $_POST["papel"] ?? "";

    if ($nome === "") {
        throw new RuntimeException(
            "Preencha o nome do personagem."
        );
    }

    if ($idade === false || $idade === null) {
        throw new RuntimeException(
            "Informe uma idade válida."
        );
    }

    $papeis_permitidos = [
        "protagonista",
        "vilao",
        "jojobro"
    ];

    if (!in_array($papel, $papeis_permitidos, true)) {
        throw new RuntimeException(
            "Selecione um papel válido."
        );
    }

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

    $pdo->beginTransaction();

    $editado = editar(
        $pdo,
        (int) $personagem_id,
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

    if (!$editado) {
        throw new RuntimeException(
            "Não foi possível atualizar o personagem."
        );
    }

    $sql = "
        UPDATE personagens_partes
        SET
            idade = :idade,
            papel = :papel
        WHERE personagem_id = :personagem_id
        AND parte_id = :parte_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":idade" => $idade,
        ":papel" => $papel,
        ":personagem_id" => (int) $personagem_id,
        ":parte_id" => (int) $parte_id
    ]);

    $pdo->commit();

    foreach ($fotos_antigas_para_apagar as $foto_antiga) {
        if (empty($foto_antiga)) {
            continue;
        }

        try {
            deletar_arquivo($foto_antiga);
        } catch (Throwable $erro_arquivo) {
            error_log(
                "Erro ao apagar foto antiga: "
                . $erro_arquivo->getMessage()
            );
        }
    }

    header(
        "Location: index.php?"
        . http_build_query([
            "parte_id" => (int) $parte_id,
            "status" => "editado"
        ])
    );

    exit;

} catch (PDOException $erro) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    foreach ($fotos_novas_para_remover as $foto_nova) {
        if (empty($foto_nova)) {
            continue;
        }

        try {
            deletar_arquivo($foto_nova);
        } catch (Throwable $erro_arquivo) {
            error_log(
                "Erro ao apagar nova foto: "
                . $erro_arquivo->getMessage()
            );
        }
    }

    error_log($erro->getMessage());

    $_SESSION["erro"] =
        "Não foi possível editar o personagem. "
        . "Verifique os dados e tente novamente.";

    voltar_para_editar_personagem(
        (int) $personagem_id,
        (int) $parte_id
    );

} catch (Throwable $erro) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    foreach ($fotos_novas_para_remover as $foto_nova) {
        if (empty($foto_nova)) {
            continue;
        }

        try {
            deletar_arquivo($foto_nova);
        } catch (Throwable $erro_arquivo) {
            error_log(
                "Erro ao apagar nova foto: "
                . $erro_arquivo->getMessage()
            );
        }
    }

    $_SESSION["erro"] = $erro->getMessage();

    voltar_para_editar_personagem(
        (int) $personagem_id,
        (int) $parte_id
    );
}