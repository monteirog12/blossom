<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo'])) {
    $arquivo = $_FILES['arquivo']['tmp_name'];

    if (($handle = fopen($arquivo, "r")) !== FALSE) {
        while (($dados = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $codigo = $dados[0];
            $estoque = $dados[1];

            $stmt = $pdo->prepare("UPDATE produtos SET estoque = ? WHERE codigo = ?");
            $stmt->execute([$estoque, $codigo]);
        }
        fclose($handle);
        header("Location: index.php");
        exit;
    } else {
        echo "Erro ao abrir o arquivo.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Estoque via CSV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4 text-center">Importar Estoque via CSV</h2>

        <form method="post" enctype="multipart/form-data" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="arquivo" class="form-label">Arquivo CSV</label>
                <input type="file" name="arquivo" id="arquivo" class="form-control" accept=".csv" required>
                <div class="form-text">Formato: código_produto, novo_estoque</div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-secondary">Voltar</a>
                <button type="submit" class="btn btn-primary">Importar Estoques</button>
            </div>
        </form>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

