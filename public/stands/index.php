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
    s.id,
    s.nome,
    s.descricao,
    s.foto_anime,
    s.foto_manga
FROM stands s

INNER JOIN personagens p
    ON s.personagem_id = p.id

INNER JOIN personagens_partes pp
    ON p.id = pp.personagem_id

WHERE pp.parte_id = :parte_id
AND s.usuario_id = :usuario_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":parte_id" => $parte_id,
    ":usuario_id" => $usuario_id
]);
$stands = $stmt->fetchAll(PDO::FETCH_OBJ);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stands</title>
</head>
<body>
    <header>
        <a href="adicionar.php?parte_id=<?= $parte_id ?>">
            Adicionar novo stand
        </a>
    </header>
    <main>
        <div>
            <h1>Olá</h1>
        </div>
        <?php foreach ($stands as $stand): ?>
            <div>
                <a href="visualizar.php?id_stand=<?= $stand->id ?>">
                    <img src="../<?= $stand->foto_anime ?>" alt="Foto: <?= htmlspecialchars($stand->nome) ?>">
                    <h2><?= htmlspecialchars($stand->nome) ?></h2>
                </a>

                <a href="editar.php?id_stand=<?= $stand->id ?>&parte_id=<?= $parte_id ?>">
                    Modificar
                </a>

                <a href="excluir.php?id_stand=<?= $stand->id ?>&parte_id=<?= $parte_id ?>" onclick="return confirm('Tem certeza que deseja excluir este stand?')">
                    Excluir
                </a>
            </div>
        <?php endforeach; ?>
    </main>
</body>
</html>