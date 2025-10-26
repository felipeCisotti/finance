<?php
include '../back/connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($connect, $_POST['username']);
    $password = mysqli_real_escape_string($connect, $_POST['password']);

    // Verifica se o usuário existe
    $checkSql = "SELECT * FROM usuarios WHERE nome = '$username'";
    $checkResult = mysqli_query($connect, $checkSql);

    if (mysqli_num_rows($checkResult) > 0) {
        // Usuário existe → verificar senha
        $user = mysqli_fetch_assoc($checkResult);

        if (password_verify($password, $user['senha'])) {
            // Senha correta → faz login
            $_SESSION['usuario'] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<p style='color:red;'>Senha incorreta!</p>";
        }

    } else {
        // Usuário não existe → cria conta
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insertSql = "INSERT INTO usuarios (nome, senha) VALUES ('$username', '$hash')";
        if (mysqli_query($connect, $insertSql)) {
            $_SESSION['usuario'] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Erro ao criar usuário: " . mysqli_error($connect);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form action="" method="POST">
            <label for="username">Nome:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
