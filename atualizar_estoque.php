<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
include 'menu.php';
require 'conexao.php';

// Atualização de estoque
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['estoques'])) {
    foreach ($_POST['estoques'] as $id => $novoEstoque) {
        $stmt = $pdo->prepare("UPDATE produtos SET estoque = ? WHERE id = ?");
        $stmt->execute([$novoEstoque, $id]);
    }
    header("Location: atualizar_estoque.php?sucesso=1");
    exit;
}

// Filtros de busca
$filtro = $_GET['filtro'] ?? '';
$params = [];

$query = "SELECT * FROM produtos WHERE 1=1";
if ($filtro) {
    $query .= " AND (codigo LIKE ? OR descricao LIKE ?)";
    $params[] = "%$filtro%";
    $params[] = "%$filtro%";
}

$query .= " ORDER BY descricao ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <h2 class="text-center mb-4">Atualizar Estoque</h2>

        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert alert-success">Saldos atualizados com sucesso!</div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="get" class="row gy-2 gx-3 align-items-end mb-4">
                    <div class="col-sm-6">
                        <label for="filtro" class="form-label">Código ou Descrição</label>
                        <input type="text" name="filtro" id="filtro" class="form-control" value="<?= htmlspecialchars($filtro) ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-secondary">Buscar</button>
                    </div>
                    <div class="col-auto">
                        <a href="atualizar_estoque.php" class="btn btn-outline-secondary">Limpar</a>
                    </div>
                </form>

                <?php if ($produtos): ?>
                    <form method="post">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Descrição</th>
                                    <th>Estoque Atual</th>
                                    <th>Novo Estoque</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['codigo']) ?></td>
                                        <td><?= htmlspecialchars($p['descricao']) ?> - <?= htmlspecialchars($p['tamanho']) ?></td>
                                        <td><?= $p['estoque'] ?></td>
                                        <td>
                                            <input type="number" name="estoques[<?= $p['id'] ?>]" class="form-control" value="<?= $p['estoque'] ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-success">Salvar Atualizações</button>
                            <a href="index.php" class="btn btn-outline-primary">Voltar</a>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="text-muted">Nenhum produto encontrado.</p>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

