<?php
#ini_set('display_errors', 1);
#ini_set('display_startup_errors', 1);
#error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
include 'menu.php';
require 'conexao.php';

// Gerar número automático de pedido
$numero_pedido = 'PED-' . date('YmdHis') . rand(1000, 9999);


// Buscar clientes
$stmt = $pdo->query("SELECT id, nome, sobrenome FROM clientes WHERE status = 'ativo' ORDER BY nome ASC");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar produtos
$stmt = $pdo->query("SELECT id, descricao, tamanho, preco FROM produtos WHERE status = 'ativo' ORDER BY descricao ASC");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Nova Venda / Orçamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .produto-row { margin-bottom: 1rem; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4 text-center">Nova Venda / Orçamento</h2>

    <form method="post" action="salvar_venda.php" class="card p-4 shadow-sm" id="formVenda">
        <div class="row mb-3">
            <div class="col-md-4">

		<label class="form-label">Tipo:</label>
            	<input class="form-check-input" type="radio" name="tipo" id="tipoVenda" value="venda" checked>
            	<label class="form-check-label" for="tipoVenda">Venda</label>
            	<input class="form-check-input" type="radio" name="tipo" id="tipoOrcamento" value="orcamento">
            	<label class="form-check-label" for="tipoOrcamento">Orçamento</label>

	    </div>
            <div class="col-md-4">
                <label class="form-label">Data da Venda</label>
                <input type="date" name="data_venda" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Número do Pedido</label>
                <input type="text" name="numero_pedido" class="form-control" value="<?= $numero_pedido ?>" readonly>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-8">
                <label class="form-label">Cliente</label>
                <select name="cliente_id" class="form-select" required>
                    <option value="">Selecione o Cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nome'] . ' ' . $cliente['sobrenome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Data de Entrega</label>
                <input type="date" name="data_entrega" class="form-control">
            </div>
        </div>

        <hr>

        <h5>Produtos</h5>

        <div id="produtos">
            <div class="row produto-row">
                <div class="col-md-5">
                    <select name="produto_id[]" class="form-select produto-select" required>
                        <option value="">Selecione o Produto</option>
                        <?php foreach ($produtos as $produto): ?>
                            <option value="<?= $produto['id'] ?>" data-preco="<?= $produto['preco'] ?>">
                                <?= htmlspecialchars($produto['descricao']) ?> - <?= htmlspecialchars($produto['tamanho']) ?> - $<?= number_format($produto['preco'], 2, ',', '.') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
		</div>

		<div class="col-md-2">
                    <input type="number" name="quantidade[]" class="form-control quantidade" placeholder="Qtd" min="1" value="1" required>
		</div>
		
                <div class="col-md-2">
                    <input type="number" name="desconto[]" class="form-control desconto" placeholder="Desconto" min="0" max="100" value="0">
                </div>
                <div class="col-md-2">
                    <input type="text" name="subtotal[]" class="form-control subtotal" readonly>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm remove-produto">X</button>
                </div>
            </div>
        </div>

        <div class="mb-3 text-end">
            <button type="button" id="addProduto" class="btn btn-primary btn-sm">+ Adicionar Produto</button>
        </div>

        <hr>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Forma de Pagamento</label>
                <select name="forma_pagamento" class="form-select" required>
                    <option value="Dinheiro">Dinheiro</option>
                    <option value="Zelle">Zelle</option>
                    <option value="Apple Pay">Apple Pay</option>
                    <option value="Cartao">Cartão</option>
                </select>
            </div>
            <div class="col-md-2 offset-md-6">
                <label class="form-label">Subtotal</label>
                <input type="text" name="subtotal_pedido" id="subtotal_pedido" class="form-control" readonly>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2 offset-md-8">
                <label class="form-label">Total de Desconto</label>
                <input type="text" name="total_desconto" id="total_desconto" class="form-control" readonly>
            </div>
            <div class="col-md-2">
                <label class="form-label">Total do Pedido</label>
                <input type="text" name="total_pedido" id="total_pedido" class="form-control fw-bold" readonly>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="index.php" class="btn btn-secondary">Voltar</a>
            <button type="submit" class="btn btn-success">Finalizar Pedido</button>
        </div>

    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function atualizarTotais() {
    let subtotal = 0;
    let totalDesconto = 0;

    $('#produtos .produto-row').each(function() {
        let preco = parseFloat($(this).find('.produto-select option:selected').data('preco') || 0);
        let quantidade = parseInt($(this).find('.quantidade').val() || 1);
        let desconto = parseFloat($(this).find('.desconto').val() || 0);

        let valorTotal = preco * quantidade;
        let valorDesconto = desconto;
        let valorFinal = valorTotal - valorDesconto;

        $(this).find('.subtotal').val(valorFinal.toFixed(2));

        subtotal += valorTotal;
        totalDesconto += valorDesconto;
    });

    $('#subtotal_pedido').val(subtotal.toFixed(2));
    $('#total_desconto').val(totalDesconto.toFixed(2));
    $('#total_pedido').val((subtotal - totalDesconto).toFixed(2));
}

$(document).on('change', '.produto-select, .quantidade, .desconto', function() {
    atualizarTotais();
});

$('#addProduto').click(function() {
    let produtoRow = $('#produtos .produto-row:first').clone();
    produtoRow.find('input').val('');
    produtoRow.find('select').val('');
    $('#produtos').append(produtoRow);
});

$(document).on('click', '.remove-produto', function() {
    if ($('#produtos .produto-row').length > 1) {
        $(this).closest('.produto-row').remove();
        atualizarTotais();
    }
});

atualizarTotais();
</script>

</body>
</html>

