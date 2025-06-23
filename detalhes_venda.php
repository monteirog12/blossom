<?php

#ini_set('display_errors', 1);
#ini_set('display_startup_errors', 1);
#error_reporting(E_ALL);


session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require 'conexao.php';

if (!isset($_GET['id'])) {
    header("Location: vendas.php");
    exit;
}

$idVenda = $_GET['id'];

// Pega a venda
$stmt = $pdo->prepare("
    SELECT v.*, c.nome AS cliente_nome, c.sobrenome AS cliente_sobrenome
    FROM vendas v
    INNER JOIN clientes c ON c.id = v.cliente_id
    WHERE v.id = ?
");
$stmt->execute([$idVenda]);
$venda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venda) {
    echo "Venda não encontrada.";
    exit;
}

// Pega os produtos
$stmt = $pdo->prepare("
    SELECT vp.*, p.id, p.descricao
    FROM vendas_produtos vp
    INNER JOIN produtos p ON p.id = vp.produto_id
    WHERE vp.venda_id = ?
");
$stmt->execute([$idVenda]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Detalhes da Venda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-light">
<?php include 'menu.php'; ?>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4>Detalhes da Venda #<?= htmlspecialchars($venda['numero_pedido']) ?></h4>
            <div class="no-print">
                <a href="gerar_pdf.php?id=<?= $venda['id'] ?>" class="btn btn-light btn-sm">Gerar PDF</a>
                <a href="duplicar_venda.php?id=<?= $venda['id'] ?>" class="btn btn-warning btn-sm">Duplicar Orçamento</a>
                <button onclick="window.print()" class="btn btn-success btn-sm">Imprimir</button>
            </div>
        </div>
        <div class="card-body">

            <div class="mb-4">
                <h5>Informações do Cliente</h5>
                <p><strong>Cliente:</strong> <?= htmlspecialchars($venda['cliente_nome'] . ' ' . $venda['cliente_sobrenome']) ?></p>
                <p><strong>Data da Venda:</strong> <?= date('d/m/Y', strtotime($venda['data_venda'])) ?></p>
                <p><strong>Data de Entrega:</strong> <?= $venda['data_entrega'] ? date('d/m/Y', strtotime($venda['data_entrega'])) : '-' ?></p>
                <p><strong>Tipo:</strong> <?= ucfirst($venda['tipo']) ?></p>
                <p><strong>Forma de Pagamento:</strong> <?= htmlspecialchars($venda['forma_pagamento']) ?></p>
            </div>

            <div class="table-responsive mb-4">
                <h5>Produtos</h5>
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Descrição</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>Desconto</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotalGeral = 0;
                        $totalDesconto = 0;
                        foreach ($produtos as $produto): 
                            $subtotal = ($produto['preco_unitario'] * $produto['quantidade']) - $produto['desconto'];
                            $subtotalGeral += $subtotal;
                            $totalDesconto += $produto['desconto'];
                        ?>
                            <tr>
				<td><?= htmlspecialchars($produto['codigo'] ?? '') ?></td>
                                <td><?= htmlspecialchars($produto['descricao'] ?? '') ?></td>
				<td><?= $produto['quantidade'] ?></td>
                                <td>R$ <?= number_format($produto['preco_unitario'], 2, ',', '.') ?></td>
                                <td>R$ <?= number_format($produto['desconto'], 2, ',', '.') ?></td>
                                <td>R$ <?= number_format($subtotal, 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-md-4 ms-auto">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>Subtotal</strong></span>
                            <span>R$ <?= number_format($subtotalGeral + $totalDesconto, 2, ',', '.') ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>Total Desconto</strong></span>
                            <span>- R$ <?= number_format($totalDesconto, 2, ',', '.') ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between bg-success text-white">
                            <span><strong>Total do Pedido</strong></span>
                            <span><strong>R$ <?= number_format($subtotalGeral, 2, ',', '.') ?></strong></span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-4 no-print">
                <a href="vendas.php" class="btn btn-secondary">Voltar</a>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

