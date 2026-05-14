<?php

require_once "../../src/config/conexao.php";

if (!isset($_GET['id'])) {
    header("Location: ../index.php?status=id_vazio");
    exit;
}

$id = $_GET['id'];

$sql = "SELECT * FROM partes WHERE id = :id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id' => $id
]);

$parte = $stmt->fetch(PDO::FETCH_OBJ);

if (!$parte) {
    header("Location: ../index.php?status=id_invalido");
    exit;
}
// print_r($parte);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $parte->nome ?></title>
</head>
<body>
    <main>
        <div>
            <h1><?= $parte->nome ?></h1>
        </div>
        <div>
            <a href="../stands/index.php?parte_id=<?= $parte->id ?>">
                <div>
                    <h2>Stands</h2>
                </div>
            </a>
            <a href="../personagens/index.php?parte_id=<?= $parte->id ?>">
                <div>
                    <h2>Personagens</h2>
                </div>
            </a>
            <a href="../referencias/index.php?parte_id=<?= $parte->id ?>">
                <div>
                    <h2>Referências</h2>
                </div>
            </a>
        </div>
    </main>
</body>
</html>