<?php 
session_start();
require_once "../src/config/conexao.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["senha"], $_POST["email"])) {
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    try {
        $email = strtolower(trim($_POST['email'])); # Remove espaços e deixa tudo minusculo
        $senha = trim($_POST["senha"]);

        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":email" => $email
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$usuario || !password_verify($senha, $usuario->senha)) {
            throw new Exception("Email ou senha inválidos");
        }

        $_SESSION['usuario_id'] = $usuario->id;
        $_SESSION['usuario_nome'] = $usuario->nome;

        header("Location: index.php?status=sucesso_login");
        exit;

    } catch (Exception $e) { # Pega o erro vindo do throw new exeption
        $erro = $e->getMessage();
        echo $erro; # Pega a mensagem de erro e seta na variavel
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

                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>

                <label for="senha">Senha</label>
                <input type="password" name="senha" id="senha" required>

                <input type="submit" value="Logar">
            </form>
            <a href="../singup.php">Criar conta</a>
        </div>
        <div>

        </div>
    </main>
    
</body>
</html>