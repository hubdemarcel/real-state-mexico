<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Tierras.mx - Agentes. Visitas. Créditos. Casas.'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&subset=latin,latin-ext&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <style>
    .user-menu {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .user-menu-btn {
        background: #28a745 !important;
        color: white !important;
    }

    .user-menu-btn:hover {
        background: #218838 !important;
    }

    .logout-btn {
        background: #dc3545 !important;
        color: white !important;
    }

    .logout-btn:hover {
        background: #c82333 !important;
    }

    @media (max-width: 768px) {
        .user-menu {
            flex-direction: column;
            width: 100%;
        }

        .user-menu-btn,
        .logout-btn {
            width: 100%;
            margin: 0.25rem 0;
        }
    }
   .logo-icon {
       width: 40px;
       height: 40px;
       margin-right: 10px;
       object-fit: contain;
   }
    </style>
    <?php if (isset($additionalCss)) echo $additionalCss; ?>
</head>
<body>
    <header class="header">
        <div class="header-container container">
            <a href="index.php" class="logo">
                <i class="fas fa-home logo-icon"></i>
                Tierras.mx
            </a>
            <nav class="primary-nav">
                <div class="dropdown">
                    <a href="comprar.php" class="nav-link dropdown-toggle">Comprar</a>
                    <div class="dropdown-menu">
                        <a href="casas_compra.php">Comprar Casa</a>
                        <a href="construccionesnuevas.php">Construcciones Nuevas</a>
                        <a href="casas_embargadas.php">Casas Embargadas</a>
                        <a href="puertasabiertas.php">Puertas Abiertas</a>
                        <a href="proximamente.php">Próximamente</a>
                        <a href="ofertadecompra.php">Hacer Oferta</a>
                    </div>
                </div>
                <a href="renta.php" class="nav-link">Rentar</a>
                <div class="dropdown">
                    <a href="venta.php" class="nav-link dropdown-toggle">Vender</a>
                    <div class="dropdown-menu">
                        <a href="anunciar.php">Anunciar Propiedad</a>
                        <a href="recursos_vendedor.php">Recursos para Vendedores</a>
                        <a href="ventasrecientes.php">Ventas Recientes</a>
                    </div>
                </div>
                <div class="dropdown">
                    <a href="creditos.php" class="nav-link dropdown-toggle">Créditos</a>
                    <div class="dropdown-menu">
                        <a href="prestamos.php">Préstamos</a>
                        <a href="financiamientotierras.php">Financiamiento de Tierras</a>
                    </div>
                </div>
                <a href="encuentraunagente.php" class="nav-link">Agentes</a>
            </nav>
            <div class="secondary-actions">
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <div class="user-menu">
                        <button class="search-button user-menu-btn" onclick="window.location.href='<?php echo ($_SESSION['user_type'] === 'agent') ? 'agent_dashboard.php' : 'user_dashboard.php'; ?>'">
                            <i class="fas fa-user"></i> Mi Panel
                        </button>
                        <button class="search-button logout-btn" onclick="window.location.href='logout.php'">Cerrar Sesión</button>
                    </div>
                <?php else: ?>
                    <button class="search-button" onclick="window.location.href='login.php'">Iniciar Sesión</button>
                    <button class="search-button" onclick="window.location.href='register.php'">Unirse</button>
                <?php endif; ?>

                <div class="notification-container">
                    <button class="notification-btn" id="notificationBtn" aria-label="Notificaciones">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                    </button>
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-header">
                            <h4>Notificaciones</h4>
                            <button class="mark-all-read" id="markAllRead">Marcar todas como leídas</button>
                        </div>
                        <div class="notification-list" id="notificationList">
                            <div class="notification-empty">
                                <i class="fas fa-bell-slash"></i>
                                <p>No tienes notificaciones</p>
                            </div>
                        </div>
                        <div class="notification-footer">
                            <a href="#" class="view-all-notifications">Ver todas las notificaciones</a>
                        </div>
                    </div>
                </div>
            </div>
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Mobile Navigation -->
        <nav class="nav-mobile" id="mobileNav">
            <a href="comprar.php" class="nav-link-mobile">Comprar</a>
            <a href="renta.php" class="nav-link-mobile">Rentar</a>
            <a href="venta.php" class="nav-link-mobile">Vender</a>
            <a href="creditos.php" class="nav-link-mobile">Créditos</a>
            <a href="encuentraunagente.php" class="nav-link-mobile">Agentes</a>
            <div class="mobile-auth">
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <button class="btn btn-ghost w-100" onclick="window.location.href='<?php echo ($_SESSION['user_type'] === 'agent') ? 'agent_dashboard.php' : 'user_dashboard.php'; ?>'">
                        <i class="fas fa-user"></i> Mi Panel
                    </button>
                    <button class="btn btn-primary w-100" onclick="window.location.href='logout.php'">Cerrar Sesión</button>
                <?php else: ?>
                    <button class="btn btn-ghost w-100" onclick="window.location.href='login.php'">Iniciar Sesión</button>
                    <button class="btn btn-primary w-100" onclick="window.location.href='register.php'">Unirse</button>
                <?php endif; ?>
            </div>
        </nav>
    </header>