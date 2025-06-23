<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Sistema</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSistema" aria-controls="navbarSistema" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSistema">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="produtos.php">Produtos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="clientes.php">Clientes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="controle_financeiro.php">Financeiro</a>
                </li>
		<li class="nav-item">
                    <a class="nav-link" href="venda.php">Nova Venda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="vendas.php">Consulta Vendas</a>
		</li>
                <li class="nav-item">
                    <a class="nav-link" href="atualizar_estoque.php">Atualizar Estoque</a>
                </li>
		<li class="nav-item">
		    <a class="nav-link" href="registrar.php">Usuario</a>
		</li>
            </ul>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    <?= htmlspecialchars($_SESSION['usuario']) ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light">Sair</a>
            </div>
        </div>
    </div>
</nav>

