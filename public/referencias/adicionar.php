<?php 
include_once "../../src/includes/bloqueio.php";
include_once "../../src/config/conexao.php";
require_once "../../src/functions/upload.php";

$parte_id = $_GET["parte_id"] ?? $_POST["parte_id"] ?? null;

if (!$parte_id || !filter_var($parte_id, FILTER_VALIDATE_INT)) {
    header("Location: ../referencias/index.php?status=parte_invalida");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    try {

        $imagem = salvar_imagem("imagem", "referencias", $_POST["titulo"]);

        $sql = "INSERT INTO referencias
        (usuario_id, parte_id, titulo, descricao, imagem, tipo)
        VALUES
        (:usuario_id, :parte_id, :titulo, :descricao, :imagem, :tipo)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":usuario_id" => $_SESSION["usuario_id"],
            ":titulo" => $_POST["titulo"],
            ":tipo" => $_POST["tipo"],
            ":parte_id" => $_POST["parte_id"],
            ":imagem" => $imagem,
            ":descricao" => $_POST["descricao"]
        ]);

        $personagem_id = $pdo->lastInsertId();

        header("Location: index.php?parte_id=". $_POST["parte_id"]);
        exit;

    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Adicionar nova referencia</title>
</head>

<body>
    <main>
        <form action="<?= $_SERVER["PHP_SELF"] ?>" method="post" enctype="multipart/form-data" class="max-w-3xl mx-auto p-6 bg-white rounded-xl shadow space-y-6">
        <input type="hidden" name="parte_id" value="<?= htmlspecialchars($parte_id) ?>">

            <!-- ETAPA 1 -->
            <div class="etapa" id="etapa1">
                <h2 class="text-2xl font-bold mb-4">Informações</h2>

                <label class="block mb-1">Título</label>
                <input type="text" name="titulo" required class="w-full border rounded-lg p-2 mb-4">

                <label class="block mb-1">Descrição</label>
                <textarea name="descricao" class="w-full border rounded-lg p-2 mb-4"></textarea>

                <label for="tipo">Tipo</label>
                <select name="tipo" id="tipo">
                    <option value="musical">Musical</option>
                    <option value="literaria">Literaria</option>
                    <option value="moda">Moda</option>
                </select>

                <label class="block mb-1">Imagem</label>
                <input type="file" name="imagem" class="w-full border rounded-lg p-2 mb-4">

                <div class="flex justify-between mt-6">

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Salvar referencia
                    </button>
                </div>
            </div>
        </form>
    </main>
</body>
</html>