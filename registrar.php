<!-- registrar.php -->
<?php
include 'menu.php';
require 'conexao.php';

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (!empty($usuario) && !empty($senha)) {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, senha) VALUES (?, ?)");
            $stmt->execute([$usuario, $hash]);
            $mensagem = 'Usuário cadastrado com sucesso!';
        } catch (PDOException $e) {
            $mensagem = 'Erro: ' . $e->getMessage();
        }
    } else {
        $mensagem = 'Preencha todos os campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Cadastro de Usuário</h3>
                    <?php if (!empty($mensagem)): ?>
                        <div class="alert alert-info text-center"><?= $mensagem ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Usuário</label>
                            <input type="text" name="usuario" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Senha</label>
                            <input type="password" name="senha" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Cadastrar</button>
                        <a href="login.php" class="btn btn-secondary w-100 mt-2">Voltar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

