// exportar_financeiro_pdf.php
require 'conexao.php';
require_once 'vendor/autoload.php';

use TCPDF;

// Captura os filtros da query string
$cliente_id = $_GET['cliente_id'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';
$forma_pagamento = $_GET['forma_pagamento'] ?? '';

// Monta a query
$where = [];
$params = [];

if ($cliente_id !== '') {
    $where[] = 'v.cliente_id = ?';
    $params[] = $cliente_id;
}
if ($data_inicio !== '') {
    $where[] = 'v.data_venda >= ?';
    $params[] = $data_inicio;
}
if ($data_fim !== '') {
    $where[] = 'v.data_venda <= ?';
    $params[] = $data_fim;
}
if ($forma_pagamento !== '') {
    $where[] = 'v.forma_pagamento = ?';
    $params[] = $forma_pagamento;
}

$sql = "SELECT v.*, c.nome, c.sobrenome FROM vendas v INNER JOIN clientes c ON c.id = v.cliente_id";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY v.data_venda DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cria PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

$pdf->Write(0, 'Relatório Financeiro de Vendas');
$pdf->Ln(10);

$totalGeral = 0;
foreach ($vendas as $venda) {
    $linha = 'Pedido #' . $venda['numero_pedido'] . ' - Cliente: ' . $venda['nome'] . ' ' . $venda['sobrenome'] .
             ' - Data: ' . date('d/m/Y', strtotime($venda['data_venda'])) .
             ' - Pagamento: ' . $venda['forma_pagamento'] .
             ' - Total: R$ ' . number_format($venda['total_pedido'], 2, ',', '.');
    $pdf->Write(0, $linha);
    $pdf->Ln(6);
    $totalGeral += $venda['total_pedido'];
}

$pdf->Ln(10);
$pdf->Write(0, 'Total Geral: R$ ' . number_format($totalGeral, 2, ',', '.'));

$pdf->Output('financeiro.pdf', 'I');

