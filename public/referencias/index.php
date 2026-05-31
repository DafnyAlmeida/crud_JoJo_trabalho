<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";

if (!isset($_GET["parte_id"])) {
    header("Location: ../index.php");
    exit;
}

$parte_id = (int) $_GET["parte_id"];
$usuario_id = $_SESSION["usuario_id"];

$sql = "
SELECT DISTINCT
    r.id,
    r.usuario_id,
    r.titulo,
    r.imagem,
    r.descricao
FROM referencias r

WHERE r.parte_id = :parte_id
AND r.usuario_id = :usuario_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":parte_id" => $parte_id,
    ":usuario_id" => $usuario_id
]);
$referencias = $stmt->fetchAll(PDO::FETCH_OBJ);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referencias</title>
</head>
<body>
    <header>
        <a href="adicionar.php?parte_id=<?= $parte_id ?>">
            Adicionar nova referência
    </header>
    <main>
        <div>
            <h1>Olá</h1>
        </div>
        <?php foreach ($referencias as $referencia): ?>
            <div>
                <a href="visualizar.php?id_referencia=<?= $referencia->id ?>">
                    <img src="../<?= $referencia->imagem ?>" alt="Foto: <?= htmlspecialchars($referencia->titulo) ?>">
                    <h2><?= htmlspecialchars($referencia->titulo) ?></h2>
                </a>

                <a href="editar.php?id_referencia=<?= $referencia->id ?>&parte_id=<?= $parte_id ?>">
                    Modificar
                </a>

                <a href="excluir.php?id_referencia=<?= $referencia->id ?>&parte_id=<?= $parte_id ?>" onclick="return confirm('Tem certeza que deseja excluir esta referencia?')">
                    Excluir
                </a>
            </div>
        <?php endforeach; ?>
    </main>
</body>
</html>