<?php
session_start();
require 'conexao.php';

if (!isset($_GET['id'])) {
    header("Location: vendas.php");
    exit;
}

$idVenda = $_GET['id'];

// Pega a venda
$stmt = $pdo->prepare("SELECT * FROM vendas WHERE id = ?");
$stmt->execute([$idVenda]);
$venda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venda) {
    die('Venda não encontrada.');
}

// Cria novo pedido
$novoNumero = time(); // Número único baseado no timestamp

$stmt = $pdo->prepare("
    INSERT INTO vendas (cliente_id, data_venda, data_entrega, tipo, forma_pagamento, numero_pedido, created_at)
    VALUES (?, NOW(), ?, ?, ?, ?, NOW())
");
$stmt->execute([
    $venda['cliente_id'],
    $venda['data_entrega'],
    $venda['tipo'],
    $venda['forma_pagamento'],
    $novoNumero
]);

$idNovaVenda = $pdo->lastInsertId();

// Copia os produtos
$stmtProdutos = $pdo->prepare("
    SELECT * FROM vendas_produtos WHERE venda_id = ?
");
$stmtProdutos->

