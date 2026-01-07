<?php $pageTitle = 'Search Results'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Search Results</h1>
        <p class="text-muted small mb-0">Showing results for: <span class="fw-bold text-main">"
                <?php echo Security::escape($query); ?>"
            </span></p>
    </div>
</div>

<?php
$totalResults = count($clients) + count($deals) + count($invoices);
?>

<?php if ($totalResults === 0): ?>
    <div class="card border-0 shadow-sm py-5 text-center">
        <div class="card-body">
            <i class="bi bi-search fs-1 text-muted opacity-25 d-block mb-3"></i>
            <h5 class="text-main">No results found</h5>
            <p class="text-muted mb-0">We couldn't find anything matching your search term. Try different keywords.</p>
        </div>
    </div>
<?php else: ?>
    <!-- Clients Results -->
    <?php if (!empty($clients)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2 text-primary"></i> Clients <span
                        class="badge bg-light text-primary ms-2">
                        <?php echo count($clients); ?>
                    </span></h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td class="ps-4 fw-medium text-main">
                                    <?php echo Security::escape($client['name']); ?>
                                </td>
                                <td>
                                    <?php echo Security::escape($client['email']); ?>
                                </td>
                                <td class="text-muted">
                                    <?php echo Security::escape($client['company'] ?? '-'); ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="<?php echo APP_URL; ?>/clients/show/<?php echo $client['id']; ?>"
                                        class="btn btn-light btn-xs text-primary">View Profile</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Deals Results -->
    <?php if (!empty($deals)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h6 class="mb-0 fw-bold"><i class="bi bi-briefcase me-2 text-warning"></i> Deals <span
                        class="badge bg-light text-warning ms-2">
                        <?php echo count($deals); ?>
                    </span></h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Title</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deals as $deal): ?>
                            <tr>
                                <td class="ps-4 fw-medium text-main">
                                    <?php echo Security::escape($deal['title']); ?>
                                </td>
                                <td>
                                    <?php echo Security::escape($deal['client_name'] ?? '-'); ?>
                                </td>
                                <td class="fw-bold">
                                    <?php echo CURRENCY_SYMBOL . number_format($deal['amount'], 2); ?>
                                </td>
                                <td>
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
                                <td class="text-end pe-4">
                                    <a href="<?php echo APP_URL; ?>/deals/show/<?php echo $deal['id']; ?>"
                                        class="btn btn-light btn-xs text-primary">View Deal</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Invoices Results -->
    <?php if (!empty($invoices)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h6 class="mb-0 fw-bold"><i class="bi bi-file-earmark-diff me-2 text-info"></i> Invoices <span
                        class="badge bg-light text-info ms-2">
                        <?php echo count($invoices); ?>
                    </span></h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Invoice #</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td class="ps-4 fw-medium text-main">
                                    <?php echo Security::escape($invoice['invoice_number']); ?>
                                </td>
                                <td>
                                    <?php echo Security::escape($invoice['client_name'] ?? '-'); ?>
                                </td>
                                <td class="fw-bold">
                                    <?php echo CURRENCY_SYMBOL . number_format($invoice['amount'], 2); ?>
                                </td>
                                <td>
                                    <span
                                        class="badge badge-soft-<?php echo $invoice['status'] === 'paid' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($invoice['status']); ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="<?php echo APP_URL; ?>/invoices/show/<?php echo $invoice['id']; ?>"
                                        class="btn btn-light btn-xs text-primary">View Invoice</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>