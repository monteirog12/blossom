<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'conexao.php';
require_once 'vendor/autoload.php';

use TCPDF;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\LabelAlignment\LabelAlignmentCenter;
use Endroid\QrCode\Writer\PngWriter;

if (!isset($_GET['id'])) {
    die("ID da venda não especificado.");
}

$idVenda = $_GET['id'];

// Buscar dados da venda
$stmt = $pdo->prepare("SELECT v.*, c.nome AS cliente_nome, c.sobrenome AS cliente_sobrenome FROM vendas v INNER JOIN clientes c ON c.id = v.cliente_id WHERE v.id = ?");
$stmt->execute([$idVenda]);
$venda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venda) {
    die("Venda não encontrada.");
}

// Buscar produtos da venda
$stmt = $pdo->prepare("SELECT vp.*, p.descricao FROM vendas_produtos vp INNER JOIN produtos p ON p.id = vp.produto_id WHERE vp.venda_id = ?");
$stmt->execute([$idVenda]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Criar o QR Code com a nova API
$urlPagamento = 'https://seusite.com/pagamento?id=' . $venda['id'];

$result = Builder::create()
    ->writer(new PngWriter())
    ->data($urlPagamento)
    ->encoding(new Encoding('UTF-8'))
    ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
    ->size(200)
    ->margin(10)
    ->build();

// Gerar a imagem em base64
$imageData = $result->getString();
$qrBase64 = base64_encode($imageData);

// Gerar PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

$pdf->Write(0, 'Recibo de Venda #' . $venda['numero_pedido']);
$pdf->Ln(10);
$pdf->Write(0, 'Cliente: ' . $venda['cliente_nome'] . ' ' . $venda['cliente_sobrenome']);
$pdf->Ln(10);
$pdf->Write(0, 'Data da Venda: ' . date('d/m/Y', strtotime($venda['data_venda'])));
$pdf->Ln(10);
$pdf->Write(0, 'Forma de Pagamento: ' . $venda['forma_pagamento']);
$pdf->Ln(10);

$pdf->Write(0, 'Produtos:');
$pdf->Ln(10);

$total = 0;
foreach ($produtos as $produto) {
    $linha = $produto['descricao'] . ' - Qtde: ' . $produto['quantidade'] . ' - Preço: R$ ' . number_format($produto['preco_unitario'], 2, ',', '.') . ' - Desc: ' . $produto['desconto_percentual'] . '%';
    $pdf->Write(0, $linha);
    $pdf->Ln(6);
    $subtotal = ($produto['preco_unitario'] * $produto['quantidade']) * (1 - $produto['desconto_percentual'] / 100);
    $total += $subtotal;
}

$pdf->Ln(10);
$pdf->Write(0, 'Total do Pedido: R$ ' . number_format($total, 2, ',', '.'));
$pdf->Ln(10);

// Inserir o QR code no canto superior direito
$pdf->Image('data://text/plain;base64,' . $qrBase64, 150, 10, 40, 40, 'PNG');

$pdf->Output('recibo.pdf', 'I');

