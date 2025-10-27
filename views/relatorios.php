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
            <a href="#" class="menu-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <div class="menu-label">Actions</div>
            <a href="relatorios.php" class="menu-item">
                <i class="fa-solid fa-money-bill"></i>
                <span>Despesas</span>
            </a>
            <a href="useradd.php" class="menu-item">
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