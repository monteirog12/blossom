<!-- cadastrar_usuario.php -->
<?php
require 'conexao.php';

$usuario = 'admin';
$senha = password_hash('123456', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO usuarios (usuario, senha) VALUES (?, ?)");
$stmt->execute([$usuario, $senha]);
echo "Usuário criado com sucesso!";
?>

