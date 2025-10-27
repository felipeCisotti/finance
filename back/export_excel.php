<?php
require '../vendor/autoload.php';
include '../back/connect.php';
session_start();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Mês e ano atuais
$current_month = date('m');
$current_year = date('Y');

// Pegar receitas do mês
$receita_sql = "SELECT * FROM receitas_mensais 
                WHERE MONTH(data_registro) = '$current_month' AND YEAR(data_registro) = '$current_year'";
$receita_result = mysqli_query($connect, $receita_sql);

// Pegar despesas do mês
$despesa_sql = "SELECT * FROM despesas 
                WHERE MONTH(data_hora) = '$current_month' AND YEAR(data_hora) = '$current_year'";
$despesa_result = mysqli_query($connect, $despesa_sql);

// Criar planilha
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Financas');

// Cabeçalhos
$sheet->setCellValue('A1','Tipo');
$sheet->setCellValue('B1','Descrição');
$sheet->setCellValue('C1','Valor');
$sheet->setCellValue('D1','Data');
$sheet->setCellValue('E1', 'Categoria');

// Preencher Receitas
$row = 2;
while($r = mysqli_fetch_assoc($receita_result)){
    $sheet->setCellValue("A$row",'Receita');
    $sheet->setCellValue("B$row",$r['descricao'] ?? '');
    $sheet->setCellValue("C$row",$r['valor']);
    $sheet->setCellValue("D$row",$r['data_registro']);
    $sheet->setCellValue("E$row",$r['categoria_nome'] ?? '');
    $row++;
}

// Preencher Despesas
while($d = mysqli_fetch_assoc($despesa_result)){
    $sheet->setCellValue("A$row",'Despesa');
    $sheet->setCellValue("B$row",$d['descricao'] ?? '');
    $sheet->setCellValue("C$row",$d['valor']);
    $sheet->setCellValue("D$row",$d['data_hora']);
    $sheet->setCellValue("E$row",$d['categoria_nome'] ?? '');
    $row++;
}

// Gerar arquivo Excel
$writer = new Xlsx($spreadsheet);

// Forçar download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="financas_mes_'.$current_month.'.xlsx"');
$writer->save('php://output');
exit;
?>
