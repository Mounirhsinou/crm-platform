<?php $pageTitle = $deal['title']; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<!-- Deal Header -->
<div class="row mb-4 align-items-center">
    <div class="col">
        <div class="d-flex align-items-center">
            <div class="bg-indigo text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm me-3"
                style="width: 48px; height: 48px; font-size: 1.2rem;">
                <i class="bi bi-briefcase"></i>
            </div>
            <div>
                <h1 class="h4 mb-1"><?php echo Security::escape($deal['title']); ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider: '·';">
                        <li class="breadcrumb-item small"><a href="<?php echo APP_URL; ?>/deals"
                                class="text-muted text-decoration-none">Deals</a></li>
                        <li class="breadcrumb-item small active text-indigo fw-medium" aria-current="page">Opportunity
                            Details</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="col-auto">
        <div class="d-flex gap-2">
            <?php if ($this->hasPermission('deals', 'edit')): ?>
                <a href="<?php echo APP_URL; ?>/deals/edit/<?php echo $deal['id']; ?>" class="btn btn-white btn-sm border">
                    <i class="bi bi-pencil me-1"></i> Adjust Opportunity
                </a>
            <?php endif; ?>

            <div class="dropdown">
                <button class="btn btn-white btn-sm border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Governance
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <?php if ($this->hasPermission('invoices', 'create')): ?>
                        <li><a class="dropdown-item small"
                                href="<?php echo APP_URL; ?>/invoices/create?deal_id=<?php echo $deal['id']; ?>&client_id=<?php echo $deal['client_id']; ?>"><i
                                    class="bi bi-file-earmark-plus me-2 opacity-50"></i>Issue Invoice</a></li>
                    <?php endif; ?>

                    <?php if ($this->hasPermission('followups', 'create')): ?>
                        <li><a class="dropdown-item small"
                                href="<?php echo APP_URL; ?>/followups/create?deal_id=<?php echo $deal['id']; ?>"><i
                                    class="bi bi-calendar-check me-2 opacity-50"></i>Log Engagement</a></li>
                    <?php endif; ?>

                    <?php if ($this->hasPermission('deals', 'delete')): ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item small text-danger"
                                href="<?php echo APP_URL; ?>/deals/delete/<?php echo $deal['id']; ?>"
                                onclick="return confirm('Are you sure you want to delete this deal?')"><i
                                    class="bi bi-trash me-2 opacity-50"></i>Abort Opportunity</a></li>
                    <?php endif; ?>

                    <?php if (!$this->hasPermission('invoices', 'create') && !$this->hasPermission('followups', 'create') && !$this->hasPermission('deals', 'delete')): ?>
                        <li><span class="dropdown-item small text-muted italic">No governance actions</span></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Sidebar Meta -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-4 small text-uppercase ls-wide text-muted">Opportunity Metrics</h6>

                <div class="mb-4">
                    <label class="uppercase-xs ls-wide text-muted fw-bold d-block mb-1">Engaged Client</label>
                    <a href="<?php echo APP_URL; ?>/clients/show/<?php echo $deal['client_id']; ?>"
                        class="d-flex align-items-center text-decoration-none">
                        <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-2"
                            style="width: 24px; height: 24px; font-size: 0.7rem;">
                            <?php echo strtoupper(substr($deal['client_name'], 0, 1)); ?>
                        </div>
                        <span class="client-name"><?php echo Security::escape($deal['client_name']); ?></span>
                    </a>
                </div>

                <div class="mb-4">
                    <label class="uppercase-xs ls-wide text-muted fw-bold d-block mb-1">Contract Valuation</label>
                    <div class="h3 mb-0 fw-bold text-dark">
                        <?php echo CURRENCY_SYMBOL . number_format($deal['amount'], 2); ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="uppercase-xs ls-wide text-muted fw-bold d-block mb-1">Pipeline Stage</label>
                    <div class="d-flex align-items-center">
                        <?php
                        $statusMeta = [
                            'new' => ['bg' => 'bg-indigo', 'text' => 'Discovery/New', 'icon' => 'bi-stars'],
                            'in_progress' => ['bg' => 'bg-warning', 'text' => 'In Negotiation', 'icon' => 'bi-hourglass-split'],
                            'completed' => ['bg' => 'bg-success', 'text' => 'Closed Won', 'icon' => 'bi-check-all']
                        ];
                        $s = $statusMeta[$deal['status']] ?? ['bg' => 'bg-secondary', 'text' => $deal['status'], 'icon' => 'bi-question-circle'];
                        ?>
                        <span
                            class="badge <?php echo $s['bg']; ?> bg-opacity-10 <?php echo str_replace('bg-', 'text-', $s['bg']); ?> uppercase-xs ls-wide py-2 px-3 d-flex align-items-center gap-2">
                            <i class="bi <?php echo $s['icon']; ?>"></i>
                            <?php echo $s['text']; ?>
                        </span>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="uppercase-xs ls-wide text-muted fw-bold d-block mb-1">Established On</label>
                    <div class="text-main fw-medium">
                        <?php echo date('F d, Y', strtotime($deal['created_at'])); ?>
                    </div>
                    <small class="text-muted small">ID Reference: #<?php echo $deal['id']; ?></small>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-4 small text-uppercase ls-wide text-muted">Operational History</h6>
                <div class="timeline-v2">
                    <div class="timeline-v2-item position-relative ps-4 pb-4">
                        <div class="timeline-v2-marker position-absolute rounded-circle bg-indigo"
                            style="left: -4px; top: 4px; width: 10px; height: 10px; z-index: 1;"></div>
                        <div class="timeline-v2-line position-absolute h-100 border-start border-light"
                            style="left: 0.5px; top: 14px;"></div>
                        <div class="small fw-bold text-dark mb-0">Opportunity Created</div>
                        <div class="uppercase-xs ls-wide text-muted">
                            <?php echo date('M d, Y \a\t H:i', strtotime($deal['created_at'])); ?>
                        </div>
                    </div>
                    <?php if ($deal['updated_at'] !== $deal['created_at']): ?>
                        <div class="timeline-v2-item position-relative ps-4 pb-2">
                            <div class="timeline-v2-marker position-absolute rounded-circle bg-warning"
                                style="left: -4px; top: 4px; width: 10px; height: 10px; z-index: 1;"></div>
                            <div class="small fw-bold text-dark mb-0">Registry Synchronized</div>
                            <div class="uppercase-xs ls-wide text-muted">
                                <?php echo date('M d, Y \a\t H:i', strtotime($deal['updated_at'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Related Invoices -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold d-flex align-items-center">
                    <i class="bi bi-receipt me-2 text-primary"></i>
                    Financial Instruments
                </h6>
                <?php if ($this->hasPermission('invoices', 'create')): ?>
                    <a href="<?php echo APP_URL; ?>/invoices/create?deal_id=<?php echo $deal['id']; ?>&client_id=<?php echo $deal['client_id']; ?>"
                        class="btn btn-primary btn-xs py-1 px-3 shadow-none">
                        <i class="bi bi-plus small"></i> New Invoice
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php
                $invoiceModel = new Invoice();
                $userId = $_SESSION['user_id'];
                $invoices = $invoiceModel->findAll(['deal_id' => $deal['id'], 'user_id' => $userId]);
                ?>
                <?php if (empty($invoices)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-journal-x display-6 text-muted opacity-25"></i>
                        <p class="text-muted small mb-0 mt-3">No financial records associated with this opportunity.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-4 border-0 uppercase-xs ls-wide text-muted py-2">Nominal ID</th>
                                    <th class="border-0 uppercase-xs ls-wide text-muted py-2">Valuation</th>
                                    <th class="border-0 uppercase-xs ls-wide text-muted py-2">Settlement</th>
                                    <th class="pe-4 border-0 uppercase-xs ls-wide text-muted py-2 text-end">Issuance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $invoice): ?>
                                    <tr onclick="location.href='<?php echo APP_URL; ?>/invoices/show/<?php echo $invoice['id']; ?>'"
                                        style="cursor: pointer;">
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold text-dark">
                                                #<?php echo Security::escape($invoice['invoice_number']); ?></div>
                                        </td>
                                        <td class="py-3">
                                            <div class="fw-medium text-main">
                                                <?php echo CURRENCY_SYMBOL . number_format($invoice['amount'], 2); ?>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <span
                                                class="badge bg-<?php echo $invoice['status'] === 'paid' ? 'success' : 'danger'; ?> bg-opacity-10 text-<?php echo $invoice['status'] === 'paid' ? 'success' : 'danger'; ?> uppercase-xs ls-wide py-1 px-2">
                                                <?php echo strtoupper($invoice['status']); ?>
                                            </span>
                                        </td>
                                        <td class="pe-4 py-3 text-end text-muted small">
                                            <?php echo date('M d, Y', strtotime($invoice['created_at'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Strategic Guidance (Placeholder for context) -->
        <?php if ($recommendations): ?>
            <div class="card border-0 shadow-sm bg-light mb-4">
                <div class="card-body p-4">
                    <div class="d-flex">
                        <div class="bg-white p-3 rounded-3 shadow-sm me-4 align-self-start">
                            <i class="bi bi-lightbulb text-warning h3 mb-0"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="fw-bold text-dark mb-2">Advancement Strategy</h6>
                            <p class="text-muted small mb-3 lh-base">
                                <?php echo $recommendations['message']; ?>
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($recommendations['actions'] as $action): ?>
                                    <a href="<?php echo $action['url']; ?>"
                                        class="btn btn-xs <?php echo $action['class']; ?> shadow-none small">
                                        <i class="bi <?php echo $action['icon']; ?> me-1"></i>
                                        <?php echo $action['text']; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .ls-wide {
        letter-spacing: 0.1em;
    }

    .uppercase-xs {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .btn-xs {
        padding: 0.25rem 0.6rem;
        font-size: 0.75rem;
    }

    .btn-white:hover {
        background-color: #f8fafc;
    }

    .bg-indigo {
        background-color: #4f46e5;
    }

    .text-indigo {
        color: #4f46e5;
    }

    .text-main {
        color: #1e293b;
    }

    .timeline-v2-marker {
        background-color: #cbd5e1;
    }

    .timeline-v2-item:last-child .timeline-v2-line {
        display: none;
    }
</style>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>