<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
require 'conexao.php';

// Buscar vendas
$stmt = $pdo->query("
    SELECT v.*, c.nome AS cliente_nome, c.sobrenome AS cliente_sobrenome
    FROM vendas v
    INNER JOIN clientes c ON c.id = v.cliente_id
    ORDER BY v.data_venda DESC
");
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Vendas</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<?php include 'menu.php'; ?>

<div class="container">
    <h1>Vendas e Orçamentos</h1>

    <a href="nova_venda.php" class="btn">Nova Venda/Orçamento</a>

    <?php if (isset($_GET['sucesso'])): ?>
        <p class="sucesso">Venda cadastrada com sucesso!</p>
    <?php endif; ?>

    <table class="tabela">
        <thead>
            <tr>
                <th>#Pedido</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Data Venda</th>
                <th>Data Entrega</th>
                <th>Forma Pagto</th>
                <th>Total</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($vendas) > 0): ?>
                <?php foreach ($vendas as $venda): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($venda['numero_pedido']); ?></td>
                        <td><?php echo htmlspecialchars($venda['cliente_nome'] . ' ' . $venda['cliente_sobrenome']); ?></td>
                        <td><?php echo ucfirst($venda['tipo']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($venda['data_venda'])); ?></td>
                        <td><?php echo $venda['data_entrega'] ? date('d/m/Y', strtotime($venda['data_entrega'])) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($venda['forma_pagamento']); ?></td>
                        <td>R$ <?php echo number_format($venda['total_pedido'], 2, ',', '.'); ?></td>
                        <td>
                            <a href="detalhes_venda.php?id=<?php echo $venda['id']; ?>" class="btn-pequeno">Ver</a>
                            <a href="excluir_venda.php?id=<?php echo $venda['id']; ?>" class="btn-pequeno excluir" onclick="return confirm('Deseja excluir esta venda?');">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8">Nenhuma venda encontrada.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
<?php include 'footer.php'; ?>

