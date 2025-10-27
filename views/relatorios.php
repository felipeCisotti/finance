<?php
include '../back/connect.php';
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Mês e ano atuais
$current_month = date('m');
$current_year = date('Y');

// Receita mensal
$receita_sql = "SELECT SUM(valor) AS total_receita FROM receitas_mensais 
                WHERE MONTH(data_registro) = '$current_month' AND YEAR(data_registro) = '$current_year'";
$receita_result = mysqli_query($connect, $receita_sql);
$total_receita = mysqli_fetch_assoc($receita_result)['total_receita'] ?? 0;

// Despesa mensal
$despesa_sql = "SELECT SUM(valor) AS total_despesa FROM despesas 
                WHERE MONTH(data_hora) = '$current_month' AND YEAR(data_hora) = '$current_year'";
$despesa_result = mysqli_query($connect, $despesa_sql);
$total_despesa = mysqli_fetch_assoc($despesa_result)['total_despesa'] ?? 0;

// Saldo
$saldo = $total_receita - $total_despesa;

// Saldo diário do mês
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
$saldo_diario = [];
$saldo_acumulado = 0;
for($day=1; $day<=$days_in_month; $day++){
    $receita_dia_sql = "SELECT SUM(valor) AS r FROM receitas_mensais 
                        WHERE DAY(data_registro)='$day' AND MONTH(data_registro)='$current_month' AND YEAR(data_registro)='$current_year'";
    $r = mysqli_fetch_assoc(mysqli_query($connect, $receita_dia_sql))['r'] ?? 0;

    $despesa_dia_sql = "SELECT SUM(valor) AS d FROM despesas 
                        WHERE DAY(data_hora)='$day' AND MONTH(data_hora)='$current_month' AND YEAR(data_hora)='$current_year'";
    $d = mysqli_fetch_assoc(mysqli_query($connect, $despesa_dia_sql))['d'] ?? 0;

    $saldo_acumulado += ($r - $d);
    $saldo_diario[] = $saldo_acumulado;
}

// Mostrar toast
$showToast = isset($_GET['success']) && $_GET['success'] == "1";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard de Finanças</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/dashboard.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['usuario']; ?>&background=3498db&color=fff" alt="Avatar">
        <h2>Bem-vindo, <?php echo $_SESSION['usuario']; ?></h2>
    </div>
    <nav class="sidebar-menu">
        <div class="menu-label">Main</div>
        <a href="dashboard.php" class="menu-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <div class="menu-label">Actions</div>
        <a href="relatorios.php" class="menu-item active"><i class="fa-solid fa-money-bill"></i><span>Relatórios</span></a>
        <a href="useradd.php" class="menu-item"><i class="fa-solid fa-credit-card"></i><span>Cartões</span></a>
        <div class="menu-label">Content</div>
        <a href="#" class="menu-item" id="contentMenu"><i class="fas fa-list"></i><span>Anexos</span><i class="fas fa-chevron-down"></i></a>
        <div class="submenu" id="contentSubmenu">
            <a href="allprodu.php" class="menu-item"><i class="fa-solid fa-paperclip"></i><span>Excel</span></a>
        </div>
        <div class="menu-label">Logout</div>
        <a href="logout.php" class="menu-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
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
        <!-- Cards -->
        <div class="card-grid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Receita Mensal</h3>
                    <div class="card-icon" style="background-color: #2ecc71;"><i class="fa-solid fa-money-bill-trend-up"></i></div>
                </div>
                <div class="card-value"><?php echo "R$ ".number_format($total_receita,2,',','.'); ?></div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Despesas Mensais</h3>
                    <div class="card-icon" style="background-color: #ff0000;"><i class="fa-solid fa-money-bill"></i></div>
                </div>
                <div class="card-value"><?php echo "R$ ".number_format($total_despesa,2,',','.'); ?></div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Saldo</h3>
                    <div class="card-icon" style="background-color: #ff5f5f;"><i class="fa-solid fa-wallet"></i></div>
                </div>
                <div class="card-value"><?php echo "R$ ".number_format($saldo,2,',','.'); ?></div>
            </div>
        </div>

        <!-- Gráficos lado a lado -->
        <div class="row mt-4">
            <div class="col-md-6 mb-4">
                <div class="chart-container">
                    <canvas id="financeChart"></canvas>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="chart-container">
                    <canvas id="saldoDiarioChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Toast -->
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
            <div class="toast" id="myToast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true">
                <div class="toast-header">
                    <img src="../assets/joia.jpg" class="rounded me-2" alt="..." style="width:50px;height:50px;object-fit:cover;">
                    <strong class="me-auto">Sistema</strong>
                    <small>Agora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">Registro adicionado com sucesso!</div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
<script>
// Toggle submenu
document.getElementById('contentMenu').addEventListener('click', function(e){
    e.preventDefault();
    const submenu = document.getElementById('contentSubmenu');
    submenu.classList.toggle('open');
    this.querySelector('.fa-chevron-down').style.transform = submenu.classList.contains('open') ? 'rotate(180deg)' : 'rotate(0)';
});

// Toggle sidebar
document.getElementById('menuToggle').addEventListener('click', function(){
    document.getElementById('sidebar').classList.toggle('open');
});

// Close sidebar on mobile click outside
document.addEventListener('click', function(event){
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');
    if(window.innerWidth <= 576 && !sidebar.contains(event.target) && !menuToggle.contains(event.target) && sidebar.classList.contains('open')){
        sidebar.classList.remove('open');
    }
});

// Mostrar toast
document.addEventListener('DOMContentLoaded', function(){
    <?php if($showToast): ?>
    new bootstrap.Toast(document.getElementById('myToast')).show();
    <?php endif; ?>

    // Gráfico Receitas x Despesas
    const receita = <?php echo $total_receita; ?>;
    const despesa = <?php echo $total_despesa; ?>;
    const ctx = document.getElementById('financeChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Receitas', 'Despesas'],
            datasets: [{
                label: 'R$',
                data: [receita, despesa],
                backgroundColor: ['#2ecc71','#ff0000'],
                borderRadius: 5,
                barThickness: 50
            }]
        },
        options: {
            responsive: true,
            plugins: { legend:{display:false}, tooltip:{
                callbacks: {label: function(context){
                    return "R$ "+context.raw.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2});
                }}
            }},
            scales:{y:{beginAtZero:true,ticks:{callback:function(value){return "R$ "+value.toLocaleString('pt-BR',{minimumFractionDigits:2});}}}}
        }
    });

    // Gráfico Saldo Diário
    const saldoDiario = <?php echo json_encode($saldo_diario); ?>;
    const dias = Array.from({length: <?php echo $days_in_month; ?>}, (_,i)=>i+1);
    const ctx2 = document.getElementById('saldoDiarioChart').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: dias,
            datasets:[{
                label:'Saldo Diário',
                data: saldoDiario,
                fill:true,
                borderColor:'#3498db',
                backgroundColor:'rgba(52,152,219,0.2)',
                tension:0.3,
                pointRadius:4
            }]
        },
        options:{
            responsive:true,
            plugins:{legend:{display:true}},
            scales:{y:{beginAtZero:true,ticks:{callback:function(value){return "R$ "+value.toLocaleString('pt-BR',{minimumFractionDigits:2});}}}}
        }
    });
});
</script>
</body>
</html>
