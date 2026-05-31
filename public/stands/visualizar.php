<?php 
include_once "../../src/config/conexao.php";

if (!isset($_GET["id_stand"])) {
    header("Location: ../index.php?status=id_vazio");
    exit;
}

$stand_id = $_GET["id_stand"];
$sql = "SELECT * FROM stands WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $stand_id
]);
$stand = $stmt->fetch(PDO::FETCH_OBJ);

if (!$stand) {
    header("Location: ../index.php?status=id_invalido");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stands/Parte 4 - <?= $stand->nome ?></title>
</head>
<body>
    <header>

    </header>
    <main>
        <div>
            <h2>Informações gerais</h2>
            <div>
                <div>
                    <p><?= $stand->descricao ?></p>
                </div>
                <div>
                    <img src="../<?= $stand->foto_manga ?>" alt="">
                </div>
            </div>
        </div>
    </main>
    
</body>
</html>