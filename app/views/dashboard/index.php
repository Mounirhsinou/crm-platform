<?php $pageTitle = 'Dashboard'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Dashboard</h1>
        <p class="text-muted small mb-0">Overview of your business performance</p>
    </div>
    <div class="col-auto">
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false" data-bs-popper-config='{"strategy":"fixed"}'>
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu shadow-premium border-0">
                    <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/clients/export">
                            <i class="bi bi-person-badge me-2"></i>Export Clients (CSV)
                        </a></li>
                    <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/deals/export">
                            <i class="bi bi-briefcase me-2"></i>Export Deals (CSV)
                        </a></li>
                    <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/invoices/export">
                            <i class="bi bi-file-earmark-diff me-2"></i>Export Invoices (CSV)
                        </a></li>
                    <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/tasks/export">
                            <i class="bi bi-check2-circle me-2"></i>Export Tasks (CSV)
                        </a></li>
                </ul>
            </div>
            <a href="<?php echo APP_URL; ?>/deals/create" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> New Deal
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-card-label mb-0">Total Clients</div>
                    <div class="stat-card-icon">
                        <i class="bi bi-person-badge fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between">
                    <h3 class="stat-card-value data-numeric mb-0"><?php echo $stats['total_clients']; ?></h3>
                    <?php if ($stats['growth']['clients'] !== null): ?>
                        <?php if ($stats['growth']['clients'] === 'new'): ?>
                            <span class="badge badge-soft-secondary">New</span>
                        <?php else: ?>
                            <?php $isPos = $stats['growth']['clients'] >= 0; ?>
                            <span class="badge badge-soft-<?php echo $isPos ? 'success' : 'danger'; ?>">
                                <i class="bi bi-arrow-<?php echo $isPos ? 'up' : 'down'; ?>-short"></i>
                                <?php echo abs($stats['growth']['clients']); ?>%
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-card-label mb-0">Active Deals</div>
                    <div class="stat-card-icon">
                        <i class="bi bi-briefcase fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between">
                    <div>
                        <h3 class="stat-card-value data-numeric mb-0"><?php echo $stats['active_deals']; ?></h3>
                        <div class="text-muted small">of <span
                                class="data-numeric"><?php echo $stats['total_deals']; ?></span> total</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-card-label mb-0">Revenue</div>
                    <div class="stat-card-icon">
                        <i class="bi bi-currency-dollar fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between">
                    <h3 class="stat-card-value data-amount mb-0">
                        <?php echo CURRENCY_SYMBOL . number_format($stats['total_revenue'], 2); ?>
                    </h3>
                    <?php if ($stats['growth']['revenue'] !== null): ?>
                        <?php if ($stats['growth']['revenue'] === 'new'): ?>
                            <span class="badge badge-soft-secondary">—</span>
                        <?php else: ?>
                            <?php $isPos = $stats['growth']['revenue'] >= 0; ?>
                            <span class="badge badge-soft-<?php echo $isPos ? 'success' : 'danger'; ?>">
                                <i class="bi bi-arrow-<?php echo $isPos ? 'up' : 'down'; ?>-short"></i>
                                <?php echo abs($stats['growth']['revenue']); ?>%
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-card-label mb-0">Pending Invoices</div>
                    <div class="stat-card-icon">
                        <i class="bi bi-file-earmark-diff fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between">
                    <h3 class="stat-card-value data-numeric mb-0"><?php echo $stats['pending_invoices']; ?></h3>
                    <span class="badge-soft-danger badge">Action needed</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Deals -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold">Recent Deals</h6>
                <a href="<?php echo APP_URL; ?>/deals" class="text-decoration-none small text-primary">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Deal Title</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th class="pe-4 text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_deals)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                    No deals found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_deals as $deal): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-medium text-main">
                                            <a href="<?php echo APP_URL; ?>/deals/show/<?php echo $deal['id']; ?>"
                                                class="text-decoration-none text-main hover-primary">
                                                <?php echo Security::escape($deal['title']); ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="client-name"><?php echo Security::escape($deal['client_name']); ?></div>
                                    </td>
                                    <td class="data-amount text-main">
                                        <?php echo CURRENCY_SYMBOL . number_format($deal['amount'], 2); ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <?php
                                        $statusClass = [
                                            'new' => 'primary',
                                            'in_progress' => 'warning',
                                            'completed' => 'success'
                                        ];
                                        $class = $statusClass[$deal['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-soft-<?php echo $class; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $deal['status'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Upcoming Tasks & Follow-ups -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold">Upcoming</h6>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm px-2 py-1" data-bs-toggle="dropdown"
                        data-bs-popper-config='{"strategy":"fixed"}'>
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                        <li><a class="dropdown-item small" href="<?php echo APP_URL; ?>/tasks">View Tasks</a></li>
                        <li><a class="dropdown-item small" href="<?php echo APP_URL; ?>/followups">View Follow-ups</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="px-3">
                    <ul class="nav nav-pills nav-fill bg-light rounded-pill p-1 mb-3" id="upcomingTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active py-1 small rounded-pill" id="tasks-tab" data-bs-toggle="tab"
                                data-bs-target="#tasks-pane">Tasks</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1 small rounded-pill" id="followups-tab" data-bs-toggle="tab"
                                data-bs-target="#followups-pane">Follow-ups</button>
                        </li>
                    </ul>
                </div>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tasks-pane">
                        <?php if (empty($upcoming_tasks ?? [])): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-check2-circle fs-2 d-block mb-2 opacity-25"></i>
                                <p class="small mb-0">No upcoming tasks</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($upcoming_tasks, 0, 5) as $task): ?>
                                    <div
                                        class="list-group-item px-4 border-light d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center min-width-0">
                                            <div class="form-check me-2">
                                                <input class="form-check-input" type="checkbox"
                                                    id="task_<?php echo $task['id']; ?>">
                                            </div>
                                            <div class="min-width-0">
                                                <div class="fw-medium small text-truncate text-main">
                                                    <?php echo Security::escape($task['title']); ?>
                                                </div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    Due <?php echo date('M d', strtotime($task['due_date'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        $priorityClass = ($task['priority'] === 'high') ? 'danger' : (($task['priority'] === 'medium') ? 'warning' : 'primary');
                                        ?>
                                        <span class="badge badge-soft-<?php echo $priorityClass; ?>"
                                            style="font-size: 0.65rem;">
                                            <?php echo strtoupper($task['priority']); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade" id="followups-pane">
                        <?php if (empty($upcoming_followups)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-check fs-2 d-block mb-2 opacity-25"></i>
                                <p class="small mb-0">No follow-ups scheduled</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($upcoming_followups, 0, 5) as $followup): ?>
                                    <div class="list-group-item px-4 border-light">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div class="client-name small">
                                                <?php echo Security::escape($followup['client_name'] ?? 'N/A'); ?>
                                            </div>
                                            <span class="text-primary fw-semibold" style="font-size: 0.7rem;">
                                                <?php echo date('M d', strtotime($followup['followup_date'])); ?>
                                            </span>
                                        </div>
                                        <div class="text-muted small text-truncate" style="font-size: 0.75rem;">
                                            <?php echo Security::escape($followup['notes']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($overdue_followups)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert badge-soft-danger border-0 d-flex align-items-center py-3">
                <i class="bi bi-exclamation-circle-fill me-3 fs-5"></i>
                <div class="small">
                    <strong class="fw-bold">Action Required:</strong> You have <?php echo count($overdue_followups); ?>
                    overdue follow-ups.
                    <a href="<?php echo APP_URL; ?>/followups" class="alert-link text-decoration-underline ms-2">Review
                        now</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>