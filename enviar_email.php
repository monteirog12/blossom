use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Carregar o conteúdo do PDF como string
$pdfContent = $dompdf->output();

// Agora enviar o email
$mail = new PHPMailer(true);

try {
    // Configurações do servidor de e-mail
    $mail->isSMTP();
    $mail->Host       = 'smtp.seudominio.com'; // Coloque o seu servidor SMTP
    $mail->SMTPAuth   = true;
    $mail->Username   = 'seuemail@seudominio.com'; // Seu e-mail
    $mail->Password   = 'suasenha'; // Sua senha
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Remetente e destinatário
    $mail->setFrom('seuemail@seudominio.com', 'Sua Empresa');
    $mail->addAddress($venda['email'], $venda['cliente_nome']); // Email do cliente

    // Conteúdo do e-mail
    $mail->isHTML(true);
    $mail->Subject = 'Recibo do Pedido #' . $venda['numero_pedido'];
    $mail->Body    = 'Olá ' . htmlspecialchars($venda['cliente_nome']) . ',<br><br>Segue em anexo o recibo do seu pedido.<br><br>Obrigado pela preferência!';

    // Anexar o PDF
    $mail->addStringAttachment($pdfContent, "Recibo_Pedido_{$venda['numero_pedido']}.pdf");

    $mail->send();
    // Opcional: Mensagem de sucesso
    // echo 'Recibo enviado para o cliente!';
} catch (Exception $e) {
    // echo "Erro ao enviar email: {$mail->ErrorInfo}";
}

