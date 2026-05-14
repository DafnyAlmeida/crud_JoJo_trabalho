<?php 
include_once "../../src/config/conexao.php";
include_once "../../src/includes/bloqueio.php";

if (!isset($_GET["parte_id"])) {
    header("Location: ../index.php");
    exit;
}

$parte_id = $_GET["parte_id"];
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

// print_r($stands);
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
        <a href="adicionar.php">
            Adicionar novo stand
        </a>
    </header>
    <main>
        <div>
            <h1>Olá</h1>
        </div>
        <div>
            <?php foreach ($stands as $stand): ?>
                <a href="visualizar.php?id_stand=<?= $stand->id?>">
                    <div>
                        <img src="../<?= $stand->foto_anime ?>" alt="Foto: <?= $stand->nome ?>">
                        <h2><?= $stand->nome ?></h2>
                        <a href="editar.php?id_stand=<?= $stand->id?>">Modificar</a>
                        <a href="excluir.php?id_stand=<?= $stand->id?>">Excluir</a>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
    
</body>
</html>