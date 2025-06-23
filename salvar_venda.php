<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // Captura dados da venda
        $tipo = $_POST['tipo'];
        $numero_pedido = $_POST['numero_pedido'];
        $cliente_id = $_POST['cliente_id'];
        $data_venda = $_POST['data_venda'];
        $data_entrega = !empty($_POST['data_entrega']) ? $_POST['data_entrega'] : null;
        $forma_pagamento = $_POST['forma_pagamento'];
	if ($tipo === 'venda') {
	   $subtotal = $_POST['subtotal_pedido'];
           $total_desconto = $_POST['total_desconto'];
           $total_pedido = $_POST['total_pedido'];
	} else {
	   $subtotal = 0;
           $total_desconto = 0;
           $total_pedido = 0;
	}

        // Inserir venda
        $stmt = $pdo->prepare("INSERT INTO vendas 
            (tipo, numero_pedido, cliente_id, data_venda, data_entrega, forma_pagamento, subtotal, total_desconto, total_pedido)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $tipo, $numero_pedido, $cliente_id, $data_venda,
            $data_entrega, $forma_pagamento, $subtotal, $total_desconto, $total_pedido
        ]);

        $venda_id = $pdo->lastInsertId();

        // Captura e insere itens da venda
        foreach ($_POST['produto_id'] as $index => $produto_id) {
            $quantidade = $_POST['quantidade'][$index];
            $desconto = $_POST['desconto'][$index];
            $subtotal_item = $_POST['subtotal'][$index];

            // Buscar preço e estoque atual do produto
            $stmtProduto = $pdo->prepare("SELECT preco, estoque FROM produtos WHERE id = ?");
            $stmtProduto->execute([$produto_id]);
            $produto = $stmtProduto->fetch(PDO::FETCH_ASSOC);
            $preco_unitario = $produto['preco'];

            if (!$produto) {
                throw new Exception("Produto ID $produto_id não encontrado.");
            }

            // Verifica se tem estoque suficiente (se for venda)
            if ($tipo === 'venda') {
                if ($produto['estoque'] < $quantidade) {
                    throw new Exception("Estoque insuficiente para o produto ID $produto_id.");
                }

                // Atualizar estoque
                $stmtEstoque = $pdo->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id = ?");
                $stmtEstoque->execute([$quantidade, $produto_id]);
            }

            // Inserir item na tabela vendas_produtos
            $stmtItem = $pdo->prepare("INSERT INTO vendas_produtos
                (venda_id, produto_id, quantidade, preco_unitario, desconto, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmtItem->execute([
                $venda_id, $produto_id, $quantidade, $preco_unitario, $desconto, $subtotal_item
            ]);
        }

        $pdo->commit();

        header("Location: vendas.php?sucesso=1");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erro ao salvar a venda: " . $e->getMessage();
    }
} else {
    header("Location: venda.php");
    exit;
}

