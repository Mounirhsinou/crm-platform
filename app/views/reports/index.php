<?php $pageTitle = 'Business Reports'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Business Analytics</h1>
        <p class="text-muted small mb-0">Insights into your CRM performance and financial health</p>
    </div>
    <div class="col-auto">
        <form method="GET" action="<?php echo APP_URL; ?>/reports" class="d-flex gap-2">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-0 text-muted px-2"><i
                        class="bi bi-calendar3 small"></i></span>
                <input type="date" name="start_date" class="form-control border-0 bg-light"
                    value="<?php echo $startDate ?? ''; ?>">
            </div>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-0 text-muted px-2"><i
                        class="bi bi-calendar3 small"></i></span>
                <input type="date" name="end_date" class="form-control border-0 bg-light"
                    value="<?php echo $endDate ?? ''; ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
            <?php if ($startDate || $endDate): ?>
                <a href="<?php echo APP_URL; ?>/reports" class="btn btn-light btn-sm"><i class="bi bi-x"></i></a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="row g-4">
    <!-- Revenue Report -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h6 class="fw-bold text-main mb-1">Revenue Performance</h6>
                        <p class="text-muted small mb-0">Monthly breakdown of settled invoices</p>
                    </div>
                    <div class="badge border py-2 px-3"
                        style="background: #f0fdf4; border-color: #bbf7d0 !important; color: #166534; font-weight: 600;">
                        Yearly: <span
                            class="text-success-strong fw-bold"><?php echo CURRENCY_SYMBOL . number_format($revenueStats['yearly'], 2); ?></span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th class="border-0 pb-3">Month</th>
                                <th class="border-0 pb-3 text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($revenueStats['monthly'])): ?>
                                <tr>
                                    <td colspan="2" class="text-center py-5 text-muted small opacity-50">
                                        <i class="bi bi-bar-chart d-block fs-3 mb-2"></i>
                                        No data available for this period
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($revenueStats['monthly'] as $row): ?>
                                    <tr>
                                        <td class="small fw-medium">
                                            <?php echo date('F Y', strtotime($row['month'])); ?>
                                        </td>
                                        <td class="text-end fw-bold text-dark-strong secondary-metric"
                                            style="font-size: 1.1rem !important;">
                                            <?php echo CURRENCY_SYMBOL . number_format($row['revenue'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Report -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-bold text-main mb-4">Invoices Summary</h6>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-3 border-0 h-100">
                            <small class="text-muted d-block mb-2 small-title fw-medium">Total Invoiced</small>
                            <div class="display-metric mb-1 text-dark-strong">
                                <?php echo $invoiceStats['total_count']; ?>
                            </div>
                            <div class="secondary-metric text-primary fw-bold">
                                <?php echo CURRENCY_SYMBOL . number_format($invoiceStats['total_amount'], 2); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3 border-0 h-100" style="background-color: rgba(22, 163, 74, 0.08);">
                            <small class="text-success-strong d-block mb-2 small-title fw-bold">Paid</small>
                            <div class="display-metric mb-1 text-success-strong">
                                <?php echo $invoiceStats['paid_count']; ?>
                            </div>
                            <div class="secondary-metric text-success-strong fw-bold">
                                <?php echo CURRENCY_SYMBOL . number_format($invoiceStats['paid_amount'], 2); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3 border-0 h-100" style="background-color: rgba(245, 158, 11, 0.08);">
                            <small class="text-warning-strong d-block mb-2 small-title fw-bold">Pending</small>
                            <div class="display-metric mb-1 text-warning-strong">
                                <?php echo $invoiceStats['unpaid_count']; ?>
                            </div>
                            <div class="secondary-metric text-warning-strong fw-bold">
                                <?php echo CURRENCY_SYMBOL . number_format($invoiceStats['unpaid_amount'], 2); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3 border-0 h-100" style="background-color: rgba(220, 38, 38, 0.08);">
                            <small class="text-danger-strong d-block mb-2 small-title fw-bold">Overdue</small>
                            <div class="display-metric mb-1 text-danger-strong">
                                <?php echo $invoiceStats['overdue_count']; ?>
                            </div>
                            <div class="text-danger-strong small fw-medium">Requires attention</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deals Report -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold text-main mb-0">Deals Pipeline</h6>
                    <div class="text-end">
                        <small class="text-muted d-block mb-1 small-title fw-medium uppercase-xs">Pipeline Value</small>
                        <div class="secondary-metric text-dark-strong fw-bold" style="font-size: 1.4rem !important;">
                            <?php echo CURRENCY_SYMBOL . number_format($dealStats['total_value'] ?? 0, 2); ?>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <?php
                    $total = max(1, $dealStats['total']);
                    $compP = ($dealStats['completed'] / $total) * 100;
                    $progP = ($dealStats['in_progress'] / $total) * 100;
                    $newP = ($dealStats['new'] / $total) * 100;
                    ?>
                    <div class="progress rounded-pill shadow-none" style="height: 10px; background-color: #f1f5f9;">
                        <div class="progress-bar bg-success rounded-start-pill" role="progressbar"
                            style="width: <?php echo $compP; ?>%"></div>
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $progP; ?>%">
                        </div>
                        <div class="progress-bar bg-primary rounded-end-pill" role="progressbar"
                            style="width: <?php echo $newP; ?>%"></div>
                    </div>
                </div>

                <div class="list-group list-group-flush border-0">
                    <div
                        class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0 bg-transparent">
                        <span class="small d-flex align-items-center text-muted">
                            <i class="bi bi-circle-fill text-success me-2" style="font-size: 6px;"></i> Completed
                        </span>
                        <span class="secondary-metric text-dark-strong"
                            style="font-size: 1.25rem !important;"><?php echo $dealStats['completed']; ?></span>
                    </div>
                    <div
                        class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0 bg-transparent">
                        <span class="small d-flex align-items-center text-muted">
                            <i class="bi bi-circle-fill text-warning me-2" style="font-size: 6px;"></i> In Progress
                        </span>
                        <span class="secondary-metric text-dark-strong"
                            style="font-size: 1.25rem !important;"><?php echo $dealStats['in_progress']; ?></span>
                    </div>
                    <div
                        class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0 bg-transparent">
                        <span class="small d-flex align-items-center text-muted">
                            <i class="bi bi-circle-fill text-primary me-2" style="font-size: 6px;"></i> New Leads
                        </span>
                        <span class="secondary-metric text-dark-strong"
                            style="font-size: 1.25rem !important;"><?php echo $dealStats['new']; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Report -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-bold text-main mb-4">Task Efficiency</h6>
                <div class="row text-center g-3 mb-4">
                    <div class="col-4">
                        <div class="display-metric mb-0 text-dark-strong" style="font-size: 2rem !important;">
                            <?php echo $taskStats['total']; ?></div>
                        <small class="text-muted small-title fw-bold">Assigned</small>
                    </div>
                    <div class="col-4">
                        <div class="display-metric mb-0 text-success-strong" style="font-size: 2rem !important;">
                            <?php echo $taskStats['completed']; ?></div>
                        <small class="text-success-strong small-title fw-bold">Finished</small>
                    </div>
                    <div class="col-4">
                        <div class="display-metric mb-0 text-danger-strong" style="font-size: 2rem !important;">
                            <?php echo $taskStats['overdue']; ?></div>
                        <small class="text-danger-strong small-title fw-bold">Overdue</small>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 text-center">
                    <?php
                    $tTotal = max(1, $taskStats['total']);
                    $completionRate = ($taskStats['completed'] / $tTotal) * 100;
                    ?>
                    <div class="display-metric mb-0 text-dark-strong" style="font-size: 2.25rem !important;">
                        <?php echo round($completionRate); ?>%</div>
                    <small class="text-muted small-title fw-bold uppercase-xs">Completion Rate</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 d-flex justify-content-end gap-2">
    <button onclick="window.print()" class="btn btn-indigo btn-sm px-4 shadow-sm border-0">
        <i class="bi bi-printer me-2"></i> Export Audit
    </button>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>