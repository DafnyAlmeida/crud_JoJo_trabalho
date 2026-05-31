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
    p.id,
    p.usuario_id,
    p.nome,
    p.foto_catalogo
FROM personagens p

INNER JOIN personagens_partes pp
    ON p.id = pp.personagem_id

WHERE pp.parte_id = :parte_id
AND p.usuario_id = :usuario_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":parte_id" => $parte_id,
    ":usuario_id" => $usuario_id
]);
$personagens = $stmt->fetchAll(PDO::FETCH_OBJ);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personagem</title>
</head>
<body>
    <header>
        <a href="adicionar.php?parte_id=<?= $parte_id ?>">
            Adicionar novo personagem
        </a>
    </header>
    <main>
        <div>
            <h1>Olá</h1>
        </div>
        <?php foreach ($personagens as $personagem): ?>
            <div>
                <a href="visualizar.php?id_personagem=<?= $personagem->id ?>">
                    <img src="../<?= $personagem->foto_catalogo ?>" alt="Foto: <?= htmlspecialchars($personagem->nome) ?>">
                    <h2><?= htmlspecialchars($personagem->nome) ?></h2>
                </a>

                <a href="editar.php?id_personagem=<?= $personagem->id ?>&parte_id=<?= $parte_id ?>">
                    Modificar
                </a>

                <a href="excluir.php?id_personagem=<?= $personagem->id ?>&parte_id=<?= $parte_id ?>" onclick="return confirm('Tem certeza que deseja excluir este personagem?')">
                    Excluir
                </a>
            </div>
        <?php endforeach; ?>
    </main>
</body>
</html>