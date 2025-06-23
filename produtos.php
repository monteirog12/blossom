<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
?>


<?php
include 'menu.php';
require 'conexao.php';

// Funções auxiliares
function salvarProduto($pdo) {
    $id = $_POST['id'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $tamanho = $_POST['tamanho'] ?? '';
    $preco = $_POST['preco'] ?? 0;
    $estoque = $_POST['estoque'] ?? 0;
    $custo = $_POST['custo'] ?? 0;
    $data_inclusao = $_POST['data_inclusao'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'ativo';

    $foto_nome = null;
    if (!empty($_FILES['foto']['name'])) {
        if (!is_dir('fotos')) {
            mkdir('fotos', 0777, true);
        }
        $foto_nome = uniqid() . '_' . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], 'fotos/' . $foto_nome);
    }

    if ($id) {
        $sql = "UPDATE produtos SET descricao=?, tamanho=?, preco=?, estoque=?, custo=?, data_inclusao=?, status=?";
        $params = [$descricao, $tamanho, $preco, $estoque, $custo, $data_inclusao, $status];
        if ($foto_nome) {
            $sql .= ", foto=?";
            $params[] = $foto_nome;
        }
        $sql .= " WHERE id=?";
        $params[] = $id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        $stmt = $pdo->prepare("INSERT INTO produtos (descricao, tamanho, preco, estoque, custo, data_inclusao, foto, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$descricao, $tamanho, $preco, $estoque, $custo, $data_inclusao, $foto_nome, $status]);
    }
    header("Location: produtos.php");
    exit;
}

function excluirProduto($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: produtos.php");
    exit;
}

function exportarCSV($pdo) {
    $filtro = $_GET['filtro'] ?? '';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=produtos.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Descrição', 'Tamanho', 'Preço', 'Estoque', 'Custo', 'Data Inclusão', 'Status']);

    if ($filtro) {
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE descricao LIKE ?");
        $stmt->execute(["%$filtro%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM produtos");
    }

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['descricao'],
            $row['tamanho'],
            $row['preco'],
            $row['estoque'],
            $row['custo'],
            $row['data_inclusao'],
            $row['status']
        ]);
    }
    fclose($output);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') salvarProduto($pdo);
if (isset($_GET['excluir'])) excluirProduto($pdo, $_GET['excluir']);
if (isset($_GET['exportar'])) exportarCSV($pdo);

$editar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}
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

<div class="container mt-4">
    <h2 class="text-center mb-4">Cadastro de Produtos</h2>
    <form action="" method="post" enctype="multipart/form-data" class="row g-3">
        <input type="hidden" name="id" value="<?= $editar['id'] ?? '' ?>">
        <div class="col-md-6">
            <label class="form-label">Descrição</label>
            <input type="text" class="form-control" name="descricao" value="<?= $editar['descricao'] ?? '' ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tamanho</label>
            <input type="text" class="form-control" name="tamanho" value="<?= $editar['tamanho'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Preço</label>
            <input type="number" step="0.01" class="form-control" name="preco" value="<?= $editar['preco'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Estoque</label>
            <input type="number" class="form-control" name="estoque" value="<?= $editar['estoque'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Custo</label>
            <input type="number" step="0.01" class="form-control" name="custo" value="<?= $editar['custo'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Data de Inclusão</label>
            <input type="date" class="form-control" name="data_inclusao" value="<?= $editar['data_inclusao'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Foto</label>
            <input type="file" class="form-control" name="foto">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="ativo" <?= ($editar['status'] ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="inativo" <?= ($editar['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="?exportar=1&filtro=<?= urlencode($_GET['filtro'] ?? '') ?>" class="btn btn-secondary">Exportar CSV</a>
        </div>
    </form>

    <form method="get" class="mt-4">
        <input type="text" name="filtro" placeholder="Filtrar produtos" class="form-control" value="<?= $_GET['filtro'] ?? '' ?>">
    </form>

    <table class="table table-striped table-responsive mt-4">
        <thead>
            <tr>
                <th>Foto</th>
                <th>Descrição</th>
                <th>Tamanho</th>
                <th>Preço</th>
                <th>Estoque</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $filtro = $_GET['filtro'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM produtos WHERE descricao LIKE ?");
            $stmt->execute(["%$filtro%"]);
            while ($row = $stmt->fetch()) {
                $fotoPath = 'fotos/' . $row['foto'];
                $fotoTag = file_exists($fotoPath) && $row['foto'] ? "<img src='$fotoPath' width='50'>" : "Sem imagem";
                echo "<tr>
                    <td>$fotoTag</td>
                    <td>{$row['descricao']}</td>
                    <td>{$row['tamanho']}</td>
                    <td>R$ {$row['preco']}</td>
                    <td>{$row['estoque']}</td>
                    <td>{$row['status']}</td>
                    <td>
                        <a href='?editar={$row['id']}' class='btn btn-primary btn-sm'>Editar</a>
                        <a href='?excluir={$row['id']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Deseja excluir este produto?')\">Excluir</a>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include 'footer.php'; ?>

