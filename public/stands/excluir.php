<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";

function deletar_pasta($pasta) {

    if (!is_dir($pasta)) {
        return;
    }

    $arquivos = scandir($pasta);

    foreach ($arquivos as $arquivo) {

        if ($arquivo === "." || $arquivo === "..") {
            continue;
        }

        $caminho = $pasta . "/" . $arquivo;

        if (is_dir($caminho)) {

            deletar_pasta($caminho);

        } else {

            unlink($caminho);
        }
    }

    rmdir($pasta);
}

if (
    empty($_GET["id_stand"]) ||
    !filter_var($_GET["id_stand"], FILTER_VALIDATE_INT)
) {
    header("Location: index.php?status=id_invalido");
    exit;
}

$stand_id = $_GET["id_stand"];
$parte_id = $_GET["parte_id"];

if (!$parte_id) {
    header("Location: index.php");
    exit;
}

// =========================
// STAND
// =========================

$sql = "SELECT foto_anime
        FROM stands
        WHERE id = :id
        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":id" => $stand_id
]);

$stand = $stmt->fetch(PDO::FETCH_OBJ);

if (!$stand) {
    header("Location: index.php?status=id_invalido");
    exit;
}


// =========================
// HABILIDADES
// =========================

$sql = "SELECT imagem, forca
        FROM stand_habilidades
        WHERE stand_id = :stand_id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":stand_id" => $stand_id
]);

$habilidades = $stmt->fetchAll(PDO::FETCH_OBJ);

try {

    // =========================
    // APAGA PASTA STAND
    // =========================

    if (!empty($stand->foto_anime)) {

        $pasta_stand =
            "../" . dirname($stand->foto_anime);

        deletar_pasta($pasta_stand);
    }


    // =========================
    // APAGA PASTAS HABILIDADES
    // =========================

    foreach ($habilidades as $habilidade) {

        // pasta habilidades
        if (!empty($habilidade->imagem)) {

            $pasta_habilidade =
                "../" . dirname($habilidade->imagem);

            deletar_pasta($pasta_habilidade);
        }

        // pasta diagramas
        if (!empty($habilidade->forca)) {

            $pasta_diagrama =
                "../" . dirname($habilidade->forca);

            deletar_pasta($pasta_diagrama);
        }
    }


    // =========================
    // DELETE HABILIDADES
    // =========================

    $sql = "DELETE FROM stand_habilidades
            WHERE stand_id = :stand_id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":stand_id" => $stand_id
    ]);


    // =========================
    // DELETE STAND
    // =========================

    $sql = "DELETE FROM stands
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":id" => $stand_id
    ]);


    header("Location: index.php?parte_id=" . urlencode($parte_id) . "&status=delete_ok");
    exit;

} catch (Exception $e) {

    echo $e->getMessage();
}