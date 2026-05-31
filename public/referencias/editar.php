<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";

if (!isset($_GET["id_referencia"])) {
    header("Location: index.php?status=id_vazio");
}

$referencia_id = $_GET["id_referencia"];

$parte_id = $_GET["parte_id"];
if (!$parte_id) {
    header("Location: index.php");
    exit;
}

if (!filter_var($referencia_id, FILTER_VALIDATE_INT)) {
    header("Location: index.php?status=id_invalido");
    exit;
}

// SELEÇÃO DAS INFORMAÇÕES SOBRE OS STANDS PARA PRE-PREENCHER
$sql = "SELECT * FROM referencias WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $referencia_id
]);

$referencia = $stmt->fetch(PDO::FETCH_OBJ);

if (!$referencia) {
    header("Location: ../index.php?status=id_invalido");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Editar referencia</title>
</head>
<body>
    <main>
        <form action="processar_editar.php" method="post" enctype="multipart/form-data" class="max-w-3xl mx-auto p-6 bg-white rounded-xl shadow space-y-6">
        <input type="hidden" name="parte_id" value="<?= htmlspecialchars($parte_id) ?>">

        <input type="hidden" name="id_referencia" value="<?= htmlspecialchars($referencia->id) ?>">
        <input type="hidden" name="parte_id" value="<?= htmlspecialchars($parte_id) ?>">

            <!-- ETAPA 1 -->
            <div class="etapa" id="etapa1">
                <h2 class="text-2xl font-bold mb-4">Informações</h2>

                <label class="block mb-1">Título</label>
                <input type="text" name="titulo" value="<?= $referencia->titulo ?>" required class="w-full border rounded-lg p-2 mb-4">

                <label class="block mb-1">Descrição</label>
                <textarea name="descricao" class="w-full border rounded-lg p-2 mb-4">
                    <?= $referencia->descricao ?>
                </textarea>

                <label for="tipo">Tipo</label>
                <select name="tipo" id="tipo">
                    <option value="musical" <?= $referencia->tipo == "musical" ? 'selected' : '' ?>>Musical</option>
                    <option value="literaria" <?= $referencia->tipo == "literaria" ? 'selected' : '' ?>>Literaria</option>
                    <option value="moda" <?= $referencia->tipo == "moda" ? 'selected' : '' ?>>Moda</option>
                </select>

                <label class="block mb-1">Imagem</label>
                <input type="file" name="imagem" class="w-full border rounded-lg p-2 mb-4">
                <?php if (!empty($referencia->imagem)): ?>
                    <img 
                        src="../<?= htmlspecialchars($referencia->imagem) ?>" 
                        alt="Foto atual do mangá"
                        class="w-32 h-40 object-cover rounded-lg border mb-3"
                    >
                <?php endif; ?>

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