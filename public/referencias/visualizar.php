<?php 
include_once "../../src/config/conexao.php";

if (!isset($_GET["id_referencia"])) {
    header("Location: ../index.php?status=id_vazio");
    exit;
}

$referencia_id = $_GET["id_referencia"];
$sql = "SELECT * FROM referencias WHERE id = :id";
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
    <title><?= $referencia->titulo ?></title>
</head>
<body>
    <header>

    </header>
    <main>
        <div>
            <h2>Informações gerais</h2>
            <div>
                <div>
                    <p><?= $referencia->descricao ?></p>
                </div>
                <div>
                    <img src="../<?= $referencia->imagem ?>" alt="">
                </div>
            </div>
        </div>
    </main>
</body>
</html>