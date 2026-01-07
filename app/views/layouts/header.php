<!DOCTYPE html>
<html lang="<?php echo Lang::current(); ?>" <?php echo Lang::isRtl() ? 'dir="rtl"' : ''; ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $pageTitle ?? 'Dashboard'; ?> -
        <?php echo APP_NAME; ?>
    </title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/admin-premium.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo APP_URL; ?>/assets/img/favicon.png">
</head>

<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading text-white">
                <?php if (Branding::hasLogo()): ?>
                    <img src="<?php echo Branding::getLogoUrl(); ?>" alt="Logo" class="sidebar-logo">
                <?php else: ?>
                    <div class="sidebar-initials">
                        <?php echo Branding::getInitials(); ?>
                    </div>
                <?php endif; ?>
                <span class="fw-bold"><?php echo Branding::getCompanyName(); ?></span>
            </div>
            <div class="list-group list-group-flush mt-3">
                <a href="<?php echo APP_URL; ?>/dashboard"
                    class="list-group-item <?php echo ($pageTitle ?? '') == 'Dashboard' ? 'active' : ''; ?>">
                    <i class="bi bi-columns-gap"></i><span><?php echo _t('dashboard'); ?></span>
                </a>
                <?php if ($this->hasPermission('clients', 'view')): ?>
                    <a href="<?php echo APP_URL; ?>/clients"
                        class="list-group-item <?php echo ($pageTitle ?? '') == 'Clients' ? 'active' : ''; ?>">
                        <i class="bi bi-person-badge"></i><span><?php echo _t('clients'); ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('leads', 'view')): ?>
                    <a href="<?php echo APP_URL; ?>/leads"
                        class="list-group-item <?php echo ($pageTitle ?? '') == 'Collected Leads' ? 'active' : ''; ?>">
                        <i class="bi bi-bullseye"></i><span><?php echo _t('collected_leads'); ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('deals', 'view')): ?>
                    <a href="<?php echo APP_URL; ?>/deals"
                        class="list-group-item <?php echo ($pageTitle ?? '') == 'Deals' ? 'active' : ''; ?>">
                        <i class="bi bi-briefcase"></i><span><?php echo _t('deals'); ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('followups', 'view')): ?>
                    <a href="<?php echo APP_URL; ?>/followups"
                        class="list-group-item <?php echo ($pageTitle ?? '') == 'Follow-ups' ? 'active' : ''; ?>">
                        <i class="bi bi-arrow-repeat"></i><span><?php echo _t('follow_ups'); ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('invoices', 'view')): ?>
                    <a href="<?php echo APP_URL; ?>/invoices"
                        class="list-group-item <?php echo ($pageTitle ?? '') == 'Invoices' ? 'active' : ''; ?>">
                        <i class="bi bi-file-earmark-diff"></i><span><?php echo _t('invoices'); ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('payments', 'view')): ?>
                    <a href="<?php echo APP_URL; ?>/payments"
                        class="list-group-item <?php echo ($pageTitle ?? '') == 'Public Links' ? 'active' : ''; ?>">
                        <i class="bi bi-link-45deg"></i><span>Public Links</span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('tasks', 'view')): ?>
                    <a href="<?php echo APP_URL; ?>/tasks"
                        class="list-group-item <?php echo ($pageTitle ?? '') == 'Tasks' ? 'active' : ''; ?>">
                        <i class="bi bi-check2-circle"></i><span><?php echo _t('tasks'); ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('reports', 'view')): ?>
                    <a href="<?php echo APP_URL; ?>/reports"
                        class="list-group-item <?php echo ($pageTitle ?? '') == 'Reports' ? 'active' : ''; ?>">
                        <i class="bi bi-graph-up-arrow"></i><span><?php echo _t('reports'); ?></span>
                    </a>
                    <a href="<?php echo APP_URL; ?>/checkout"
                        class="list-group-item <?php echo ($pageTitle ?? '') == 'Checkout' ? 'active' : ''; ?>">
                        <i class="bi bi-cart-check"></i><span>Checkout</span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('messaging', 'send')): ?>
                    <div class="sidebar-section-title">Communications</div>

                    <a href="<?php echo APP_URL; ?>/messaging/sms"
                        class="list-group-item <?php echo strpos($_SERVER['REQUEST_URI'], 'messaging/sms') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-chat-text"></i><span>SMS Sender</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>/messaging/whatsapp"
                        class="list-group-item <?php echo strpos($_SERVER['REQUEST_URI'], 'messaging/whatsapp') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-whatsapp"></i><span>WhatsApp Sender</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>/mailer"
                        class="list-group-item <?php echo ($pageTitle ?? '') == 'Mail List' ? 'active' : ''; ?>">
                        <i class="bi bi-envelope-at"></i><span><?php echo _t('mail_list'); ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('settings', 'view')): ?>
                    <div class="sidebar-section-title">System</div>
                    <a href="<?php echo APP_URL; ?>/settings"
                        class="list-group-item <?php echo ($pageTitle ?? '') == 'Settings' ? 'active' : ''; ?>">
                        <i class="bi bi-gear"></i><span><?php echo _t('settings'); ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid px-0">
                    <button class="btn btn-link text-muted p-0 me-3" id="sidebarToggle">
                        <i class="bi bi-text-indent-left fs-4"></i>
                    </button>

                    <div class="d-none d-md-flex align-items-center ms-2" style="max-width: 400px; flex: 1;">
                        <form action="<?php echo APP_URL; ?>/search" method="GET" class="w-100">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-transparent border-end-0 text-muted">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="q" class="form-control border-start-0 ps-0 bg-transparent"
                                    placeholder="Search anything..." value="<?php echo $_GET['q'] ?? ''; ?>">
                            </div>
                        </form>
                    </div>

                    <div class="ms-auto d-flex align-items-center">
                        <div class="dropdown me-3">
                            <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center" type="button"
                                data-bs-toggle="dropdown">
                                <i class="bi bi-translate me-2"></i>
                                <span class="d-none d-sm-inline"><?php echo strtoupper(Lang::current()); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-premium border-0">
                                <li><a class="dropdown-item py-2"
                                        href="<?php echo APP_URL; ?>/lang/switch/en">English</a></li>
                                <li><a class="dropdown-item py-2"
                                        href="<?php echo APP_URL; ?>/lang/switch/fr">Français</a></li>
                                <li><a class="dropdown-item py-2"
                                        href="<?php echo APP_URL; ?>/lang/switch/ar">العربية</a></li>
                            </ul>
                        </div>

                        <div class="dropdown">
                            <button class="btn btn-light btn-sm d-flex align-items-center border-0 bg-transparent"
                                type="button" data-bs-toggle="dropdown">
                                <?php if (Branding::hasLogo()): ?>
                                    <img src="<?php echo Branding::getLogoUrl(); ?>" class="rounded me-2"
                                        style="width: 32px; height: 32px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="sidebar-initials me-2"
                                        style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        <?php echo Branding::getInitials(); ?>
                                    </div>
                                <?php endif; ?>
                                <span
                                    class="d-none d-sm-inline fw-medium"><?php echo Branding::getCompanyName(); ?></span>
                                <i class="bi bi-chevron-down ms-2 small text-muted"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-premium border-0 mt-2">
                                <li class="px-3 py-2 border-bottom">
                                    <div class="small text-muted">Signed in as</div>
                                    <div class="fw-bold"><?php echo Branding::getCompanyName(); ?>
                                    </div>
                                </li>
                                <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/settings"><i
                                            class="bi bi-gear me-2"></i>Settings</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item py-2 text-danger"
                                        href="<?php echo APP_URL; ?>/auth/logout"><i
                                            class="bi bi-box-arrow-right me-2"></i><?php echo _t('logout'); ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Scrollable Area -->
            <div class="container-fluid p-4">
                <!-- Flash Messages -->
                <?php if ($flash = Session::getFlash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                            <div><?php echo Security::escape($flash); ?></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($flash = Session::getFlash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                            <div><?php echo Security::escape($flash); ?></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php
                if (Session::isAuthenticated()) {
                    require_once APP_PATH . '/helpers/DateFilter.php';
                    DateFilter::render();
                }
                ?>