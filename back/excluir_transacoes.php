<?php 
session_start();
require_once '../back/connect.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Primeiro, descobrir o tipo da transação
    $stmt = $connect->prepare("SELECT tipo_transacao, descricao, valor FROM transacoes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $transacao = $resultado->fetch_assoc();
    $stmt->close();

    if ($transacao) {
        $tipo = $transacao['tipo_transacao'];
        $descricao = $transacao['descricao'];
        $valor = $transacao['valor'];

        // Excluir da tabela correspondente
        if ($tipo === 'receita') {
            $stmt = $connect->prepare("DELETE FROM receitas_mensais WHERE descricao = ? AND valor = ?");
            $stmt->bind_param("sd", $descricao, $valor);
            $stmt->execute();
            $stmt->close();
        } elseif ($tipo === 'despesa') {
            $stmt = $connect->prepare("DELETE FROM despesas WHERE descricao = ? AND valor = ?");
            $stmt->bind_param("sd", $descricao, $valor);
            $stmt->execute();
            $stmt->close();
        }

        // Por fim, excluir da tabela transacoes
        $stmt = $connect->prepare("DELETE FROM transacoes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: ../views/dashboard.php');
    exit();
} else {
    header('Location: ../views/dashboard.php');
    exit();
}
?>
