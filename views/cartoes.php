<?php

use Vtiful\Kernel\Format;
include '../back/connect.php';
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$registro_sucesso = false;

if (isset($_GET['id'])) {
    $deleteId = intval($_GET['id']);

    // Excluir da tabela transacoes
    $deleteTransacao = "DELETE FROM transacoes WHERE id = $deleteId";
    mysqli_query($connect, $deleteTransacao);

    // Excluir da tabela despesas relacionada
    $deleteDespesas = "DELETE FROM despesas WHERE id_transacao = $deleteId";
    mysqli_query($connect, $deleteDespesas);

    // Excluir da tabela receitas_mensais relacionada
    $deleteReceitas = "DELETE FROM receitas_mensais WHERE id_transacao = $deleteId";
    mysqli_query($connect, $deleteReceitas);

    // Redireciona de volta para o dashboard
    header("Location: dashboard.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_transacao = mysqli_real_escape_string($connect, $_POST['tipo_transacao']);
$descricao = mysqli_real_escape_string($connect, $_POST['descricao']);
$valor = mysqli_real_escape_string($connect, $_POST['valor']);
$tipo = mysqli_real_escape_string($connect, $_POST['tipo']);
$categoria_nome = mysqli_real_escape_string($connect, $_POST['categoria_nome']);
$data = mysqli_real_escape_string($connect, $_POST['data_hora']); // só a data


    // Primeiro, insere na tabela de transações
   $sql_transacao = "INSERT INTO transacoes (tipo_transacao, descricao, valor, tipo, categoria_nome, data_hora)
                  VALUES ('$tipo_transacao', '$descricao', '$valor', '$tipo', '$categoria_nome', '$data')";

    if (mysqli_query($connect, $sql_transacao)) {
        // Se for uma receita
        if ($tipo_transacao == 'receita') {
            $sql_receita = "INSERT INTO receitas_mensais (descricao, valor, data_registro)
                        VALUES ('$descricao', '$valor', '$data')";
            mysqli_query($connect, $sql_receita);
        }
        // Se for uma despesa
        elseif ($tipo_transacao == 'despesa') {
            $sql_despesa = "INSERT INTO despesas (descricao, valor, data_hora, tipo, categoria_nome)
                        VALUES ('$descricao', '$valor', '$data', '$tipo', " . ($categoria_nome ? "'$categoria_nome'" : "NULL") . ")";
            mysqli_query($connect, $sql_despesa);
        }

        // Registro foi adicionado
        $registro_sucesso = true;
    }


    header("Location: dashboard.php?success=" . ($registro_sucesso ? "1" : "0"));
    exit();
}
?>

<?php
// Cálculo da receita mensal
$current_month = date('m');
$current_year = date('Y');
$receita_sql = "SELECT SUM(valor) AS total_receita FROM receitas_mensais 
                WHERE MONTH(data_registro) = '$current_month' AND YEAR(data_registro) = '$current_year'";
$receita_result = mysqli_query($connect, $receita_sql);
$receita_row = mysqli_fetch_assoc($receita_result);
$total_receita = $receita_row['total_receita'] ?? 0;
// Cálculo da despesa mensal
$despesa_sql = "SELECT SUM(valor) AS total_despesa FROM despesas 
                WHERE MONTH(data_hora) = '$current_month' AND YEAR(data_hora) = '$current_year'";
$despesa_result = mysqli_query($connect, $despesa_sql);
$despesa_row = mysqli_fetch_assoc($despesa_result);
$total_despesa = $despesa_row['total_despesa'] ?? 0;
// Cálculo do saldo
$saldo = $total_receita - $total_despesa;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de finanças</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>


<body>
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION["usuario"] ?>&background=3498db&color=fff"
                alt="Logo">
            <h2>Bem vindo <?php echo $_SESSION["usuario"] ?> </h2>
        </div>

        <nav class="sidebar-menu">
            <div class="menu-label">Main</div>
            <a href="#" class="menu-item ">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <div class="menu-label">Actions</div>
            <a href="relatorios.php" class="menu-item">
                <i class="fa-solid fa-money-bill"></i>
                <span>Relatorios</span>
            </a>
            <a href="useradd.php" class="menu-item active">
                <i class="fa-solid fa-credit-card"></i>
                <span>Cartões</span>
            </a>

            <div class="menu-label">Content</div>
            <a href="#" class="menu-item" id="contentMenu">
                <i class="fas fa-list"></i>
                <span>Anexos</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="submenu" id="contentSubmenu">
                <a href="allprodu.php" class="menu-item">
                    <i class="fa-solid fa-paperclip"></i>
                    <span>Excel</span>
                </a>
            </div>


            <div class="menu-label">Logout</div>
            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>


    <main class="main-content">
        <div class="topbar">
            <div class="search-bar" id="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search...">
            </div>
        </div>

        <div class="dashboard-content">
            <div class="content-header">
            </div>

            <div class="card-grid">



                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Receita Mensal</h3>
                        <div class="card-icon" style="background-color: #2ecc71;">
                            <i class="fa-solid fa-money-bill-trend-up"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo "R$ " . number_format($total_receita, 2, ',', '.'); ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Despesas Mensais</h3>
                        <div class="card-icon" style="background-color: #ff0000ff;">
                            <i class="fa-solid fa-money-bill"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo "R$ " . number_format($total_despesa, 2, ',', '.'); ?> </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Saldo</h3>
                        <div class="card-icon" style="background-color: #ff5f5fff;">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo "R$ " . number_format($saldo, 2, ',', '.'); ?> </div>
                </div>
            </div>

            <section class="cartoes">
                <h2>Registrar Cartões</h2>
                
                
            </section>

            <section class="TOAST">

                <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
                    <div class="toast" id="myToast" role="alert" aria-live="assertive" aria-atomic="true"
                        data-bs-autohide="true">
                        <div class="toast-header">
                            <img src="../assets/joia.jpg" class="rounded me-2" alt="..."
                                style="width: 100px; height: 100px; object-fit: cover;">
                            <strong class="me-auto">Sistema</strong>
                            <small>Agora</small>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            Registro adicionado com sucesso!
                        </div>
                    </div>
                </div>

            </section>


            <?php
            $showToast = isset($_GET['success']) && $_GET['success'] == "1";
            ?>
    </main>
    <script>
        document.getElementById('contentMenu').addEventListener('click', function (e) {
            e.preventDefault();
            const submenu = document.getElementById('contentSubmenu');
            submenu.classList.toggle('open');
            this.querySelector('.fa-chevron-down').style.transform = submenu.classList.contains('open') ?
                'rotate(180deg)' : 'rotate(0)';
        });

        document.getElementById('menuToggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('open');
        });

        document.addEventListener('click', function (event) {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menuToggle');

            if (window.innerWidth <= 576 &&
                !sidebar.contains(event.target) &&
                !menuToggle.contains(event.target) &&
                sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
            }
        });

        document.getElementById('tipoTransacao').addEventListener('change', function () {
            const categoriaField = document.getElementById('categoriaField');
            if (this.value === 'despesa') {
                categoriaField.classList.remove('hidden');
            } else {
                categoriaField.classList.add('hidden');
            }
        });
        document.addEventListener('DOMContentLoaded', function () {
            <?php if ($showToast): ?>
                var toastEl = document.getElementById('myToast');
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            <?php endif; ?>
        });
    </script>

    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
        </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
        integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous">
        </script>
</body>

</html>