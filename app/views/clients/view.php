<?php $pageTitle = $client['name']; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<!-- Profile Header -->
<div class="row mb-4 align-items-center">
    <div class="col">
        <div class="d-flex align-items-center">
            <div class="bg-primary text-white rounded-pill d-flex align-items-center justify-content-center fw-bold shadow-sm me-3"
                style="width: 48px; height: 48px; font-size: 1.2rem;">
                <?php
                $initials = '';
                $nameParts = explode(' ', $client['name']);
                foreach ($nameParts as $part)
                    $initials .= strtoupper(substr($part, 0, 1));
                echo substr($initials, 0, 2);
                ?>
            </div>
            <div>
                <h1 class="h4 mb-1"><?php echo Security::escape($client['name']); ?></h1>
                <p class="text-muted small mb-0">Client Relationship Profile & Commercial History</p>
            </div>
        </div>
    </div>
    <div class="col-auto">
        <div class="d-flex gap-2">
            <a href="<?php echo APP_URL; ?>/clients/edit/<?php echo $client['id']; ?>"
                class="btn btn-white btn-sm border">
                <i class="bi bi-pencil me-1"></i> Edit Profile
            </a>
            <div class="dropdown">
                <button class="btn btn-white btn-sm border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    More Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li><a class="dropdown-item small"
                            href="<?php echo APP_URL; ?>/invoices/create?client_id=<?php echo $client['id']; ?>"><i
                                class="bi bi-file-earmark-plus me-2 opacity-50"></i>Create Invoice</a></li>
                    <li><a class="dropdown-item small"
                            href="<?php echo APP_URL; ?>/deals/create?client_id=<?php echo $client['id']; ?>"><i
                                class="bi bi-briefcase me-2 opacity-50"></i>Add Deal</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item small text-danger"
                            href="<?php echo APP_URL; ?>/clients/delete/<?php echo $client['id']; ?>"
                            onclick="return confirm('Are you sure you want to delete this client?')"><i
                                class="bi bi-trash me-2 opacity-50"></i>Terminate Account</a></li>
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
                <h6 class="fw-bold mb-4 small text-uppercase ls-wide text-muted">Core Intelligence</h6>

                <div class="mb-4">
                    <label class="uppercase-xs ls-wide text-muted fw-bold d-block mb-1">Electronic Mail</label>
                    <div class="text-main fw-medium">
                        <?php echo Security::escape($client['email'] ?: 'No record'); ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="uppercase-xs ls-wide text-muted fw-bold d-block mb-1">Direct Communication</label>
                    <div class="d-flex align-items-center">
                        <span
                            class="text-main fw-medium me-2"><?php echo Security::escape($client['phone'] ?: 'No record'); ?></span>
                        <?php if (!empty($client['phone'])): ?>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $client['phone']); ?>"
                                target="_blank"
                                class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 text-decoration-none py-1">
                                <i class="bi bi-whatsapp me-1"></i> WhatsApp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="uppercase-xs ls-wide text-muted fw-bold d-block mb-1">Registry Since</label>
                    <div class="text-main fw-medium">
                        <?php echo date('F d, Y', strtotime($client['created_at'])); ?>
                    </div>
                </div>

                <?php if ($client['notes']): ?>
                    <div class="mb-0">
                        <label class="uppercase-xs ls-wide text-muted fw-bold d-block mb-1">Contextual Background</label>
                        <div class="bg-light p-3 rounded-2 small text-muted lh-base">
                            <?php echo nl2br(Security::escape($client['notes'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card border-0 shadow-sm bg-primary bg-opacity-10 border border-primary border-opacity-10 mb-4">
            <div class="card-body p-4">
                <div class="row text-center">
                    <div class="col-6 border-end border-primary border-opacity-10">
                        <div class="h4 mb-0 fw-bold text-primary"><?php echo count($client['deals']); ?></div>
                        <div class="uppercase-xs ls-wide text-primary opacity-75">Active Deals</div>
                    </div>
                    <div class="col-6">
                        <?php
                        $totalInvoiced = 0;
                        foreach ($client['invoices'] as $inv)
                            $totalInvoiced += $inv['amount'];
                        ?>
                        <div class="h4 mb-0 fw-bold text-primary">
                            <?php echo CURRENCY_SYMBOL . number_format($totalInvoiced, 0); ?>
                        </div>
                        <div class="uppercase-xs ls-wide text-primary opacity-75">Invoiced</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-4 small text-uppercase ls-wide text-muted">Operations Log</h6>

                <?php
                $activityModel = new Activity();
                $companyId = Session::get('company_id');
                $activities = $activityModel->getByClientByCompany($client['id'], $companyId, 15);
                ?>

                <?php if (empty($activities)): ?>
                    <div class="text-center py-4 border border-dashed rounded-3">
                        <i class="bi bi-clock-history display-6 text-muted opacity-25"></i>
                        <p class="text-muted small mb-0 mt-2">No historical events recorded.</p>
                    </div>
                <?php else: ?>
                    <div class="timeline-v2">
                        <?php foreach ($activities as $activity): ?>
                            <div class="timeline-v2-item position-relative ps-4 pb-4">
                                <div class="timeline-v2-marker position-absolute rounded-circle"
                                    style="left: -4px; top: 4px; width: 10px; height: 10px; z-index: 1;"></div>
                                <div class="timeline-v2-line position-absolute h-100 border-start border-light"
                                    style="left: 0.5px; top: 14px;"></div>

                                <div class="small fw-medium text-main mb-1">
                                    <?php echo $activity['description']; ?>
                                </div>
                                <div class="uppercase-xs ls-wide text-muted">
                                    <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Deals Grid -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold d-flex align-items-center">
                    <i class="bi bi-briefcase me-2 text-primary"></i>
                    Commercial Pipeline
                </h6>
                <a href="<?php echo APP_URL; ?>/deals/create?client_id=<?php echo $client['id']; ?>"
                    class="btn btn-primary btn-xs py-1 px-3 shadow-none">
                    <i class="bi bi-plus small"></i> Initiate Deal
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($client['deals'])): ?>
                    <div class="text-center py-5">
                        <p class="text-muted small mb-0">No active commercial ventures detected.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-4 border-0 uppercase-xs ls-wide text-muted py-2">Proposition Name</th>
                                    <th class="border-0 uppercase-xs ls-wide text-muted py-2">Valuation</th>
                                    <th class="border-0 uppercase-xs ls-wide text-muted py-2">Condition</th>
                                    <th class="pe-4 border-0 uppercase-xs ls-wide text-muted py-2 text-end">Registry Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($client['deals'] as $deal): ?>
                                    <tr onclick="location.href='<?php echo APP_URL; ?>/deals/show/<?php echo $deal['id']; ?>'"
                                        style="cursor: pointer;">
                                        <td class="ps-4 py-3">
                                            <div class="fw-medium text-main"><?php echo Security::escape($deal['title']); ?>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <div class="fw-bold text-main">
                                                <?php echo CURRENCY_SYMBOL . number_format($deal['amount'], 2); ?>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <?php
                                            $statusBadge = [
                                                'new' => ['bg' => 'bg-indigo', 'text' => 'Initiated'],
                                                'in_progress' => ['bg' => 'bg-warning', 'text' => 'In Motion'],
                                                'completed' => ['bg' => 'bg-success', 'text' => 'Finalized']
                                            ];
                                            $s = $statusBadge[$deal['status']] ?? ['bg' => 'bg-secondary', 'text' => $deal['status']];
                                            ?>
                                            <span
                                                class="badge <?php echo $s['bg']; ?> bg-opacity-10 <?php echo str_replace('bg-', 'text-', $s['bg']); ?> uppercase-xs ls-wide py-1 px-2">
                                                <?php echo $s['text']; ?>
                                            </span>
                                        </td>
                                        <td class="pe-4 py-3 text-end text-muted small">
                                            <?php echo date('M d, Y', strtotime($deal['created_at'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Invoices -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold d-flex align-items-center">
                    <i class="bi bi-receipt me-2 text-primary"></i>
                    Fiscal Registry
                </h6>
                <a href="<?php echo APP_URL; ?>/invoices/create?client_id=<?php echo $client['id']; ?>"
                    class="btn btn-primary btn-xs py-1 px-3 shadow-none">
                    <i class="bi bi-plus small"></i> Generate Ledger
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($client['invoices'])): ?>
                    <div class="text-center py-5">
                        <p class="text-muted small mb-0">No fiscal documents issued yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-4 border-0 uppercase-xs ls-wide text-muted py-2">Identifier</th>
                                    <th class="border-0 uppercase-xs ls-wide text-muted py-2">Sum</th>
                                    <th class="border-0 uppercase-xs ls-wide text-muted py-2">Fiscal State</th>
                                    <th class="pe-4 border-0 uppercase-xs ls-wide text-muted py-2 text-end">Issuance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($client['invoices'] as $invoice): ?>
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

        <!-- Follow-ups / Workflow -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold d-flex align-items-center">
                    <i class="bi bi-calendar-check me-2 text-primary"></i>
                    Workflow Pipeline
                </h6>
                <a href="<?php echo APP_URL; ?>/followups/create" class="btn btn-primary btn-xs py-1 px-3 shadow-none">
                    <i class="bi bi-plus small"></i> Log Directive
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($client['followups'])): ?>
                    <div class="text-center py-5">
                        <p class="text-muted small mb-0">No pending workflow items.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($client['followups'] as $followup): ?>
                            <div class="list-group-item border-light py-4 ps-4 pe-4">
                                <div class="d-flex justify-content-between">
                                    <div class="d-flex">
                                        <div class="bg-light p-2 rounded-3 me-3 align-self-start">
                                            <i class="bi bi-chat-dots text-primary"></i>
                                        </div>
                                        <div>
                                            <p class="mb-1 text-main fw-medium small lh-base">
                                                <?php echo Security::escape($followup['notes']); ?>
                                            </p>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="uppercase-xs ls-wide text-muted fw-bold">
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    Scheduled:
                                                    <?php echo date('M d, Y', strtotime($followup['followup_date'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span
                                            class="badge bg-<?php echo $followup['status'] === 'done' ? 'success' : 'warning'; ?> bg-opacity-10 text-<?php echo $followup['status'] === 'done' ? 'success' : 'warning'; ?> uppercase-xs ls-wide py-1 px-2">
                                            <?php echo strtoupper($followup['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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

    .text-main {
        color: #1e293b;
    }

    .timeline-v2-marker {
        background-color: #cbd5e1;
        transition: all 0.2s;
    }

    .timeline-v2-item:hover .timeline-v2-marker {
        background-color: #4f46e5;
        transform: scale(1.3);
    }

    .timeline-v2-item:last-child .timeline-v2-line {
        display: none;
    }
</style>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>