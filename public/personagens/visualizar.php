<?php 
include_once "../../src/config/conexao.php";

if (!isset($_GET["id_personagem"])) {
    header("Location: ../index.php?status=id_vazio");
    exit;
}

$personagem_id = $_GET["id_personagem"];
$sql = "SELECT * FROM personagens WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $personagem_id
]);
$personagem = $stmt->fetch(PDO::FETCH_OBJ);

if (!$personagem) {
    header("Location: ../index.php?status=id_invalido");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $personagem->nome ?></title>
</head>
<body>
    <header>

    </header>
    <main>
        <div>
            <h2>Informações gerais</h2>
            <div>
                <div>
                    <p><?= $personagem->biografia ?></p>
                </div>
                <div>
                    <img src="../<?= $personagem->foto_manga ?>" alt="">
                </div>
            </div>
        </div>
    </main>
</body>
</html>