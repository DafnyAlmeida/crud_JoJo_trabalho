<?php 
session_start();
require_once "../src/config/conexao.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["senha"], $_POST["email"], $_POST["nome"])) {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    try {
        $email = strtolower(trim($_POST['email'])); # Remove espaços e deixa tudo minusculo
        $senha = trim($_POST["senha"]);
        $nome = ucwords(strtolower(trim($_POST['nome'])));

        if (empty($nome) || empty($email) || empty($senha)) {
            throw new Exception("Preencha todos os campos");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email inválido.");
        }

        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":email" => $email
        ]);

        if ($stmt->fetch()) {
            throw new Exception("Este email já está cadastrado.");
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nome, email, senha)
                VALUES (:nome, :email, :senha)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":nome" => $nome,
            ":email" => $email,
            ":senha" => $senhaHash
        ]);

        header("Location: login.php?status=cadastro_ok");
        exit;

    } catch (Exception $e) { # Pega o erro vindo do throw new exeption
        $erro = $e->getMessage(); # Pega a mensagem de erro e seta na variavel
    } catch (PDOException $e) {
        $erro = "Erro interno, pedimos desculpa";
    };
};

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JoJo's Bizarre Adventure - CRUD</title>
</head>
<body>
    <main>
        <div>
            <form action="<?= $_SERVER["PHP_SELF"] ?>" method="post">
                <label for="nome">Nome</label>
                <input type="text" name="nome" id="nome" required>

                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>

                <label for="senha">Senha</label>
                <input type="password" name="senha" id="senha" required>

                <input type="submit" value="Criar">
            </form>

        </div>
        <div>

        </div>
    </main>
    
</body>
</html>