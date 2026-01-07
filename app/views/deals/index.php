<?php $pageTitle = 'Deals'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Sales Pipeline</h1>
        <p class="text-muted small mb-0">Track and manage your business deals and opportunities</p>
    </div>
    <?php if ($this->hasPermission('deals', 'create')): ?>
        <div class="col-auto">
            <a href="<?php echo APP_URL; ?>/deals/create" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-plus-lg me-1"></i> New Deal
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small fw-medium">Filter by Status:</span>
            <div class="nav nav-pills bg-light p-1 rounded-pill small">
                <a href="<?php echo APP_URL; ?>/deals"
                    class="nav-link py-1 px-3 rounded-pill <?php echo !$current_status ? 'active shadow-sm' : 'text-secondary'; ?>">All</a>
                <a href="<?php echo APP_URL; ?>/deals?status=new"
                    class="nav-link py-1 px-3 rounded-pill <?php echo $current_status === 'new' ? 'active shadow-sm' : 'text-secondary'; ?>">New</a>
                <a href="<?php echo APP_URL; ?>/deals?status=in_progress"
                    class="nav-link py-1 px-3 rounded-pill <?php echo $current_status === 'in_progress' ? 'active shadow-sm' : 'text-secondary'; ?>">In
                    Progress</a>
                <a href="<?php echo APP_URL; ?>/deals?status=completed"
                    class="nav-link py-1 px-3 rounded-pill <?php echo $current_status === 'completed' ? 'active shadow-sm' : 'text-secondary'; ?>">Completed</a>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <?php if ($current_status): ?>
                <input type="hidden" name="status" value="<?php echo Security::escape($current_status); ?>">
            <?php endif; ?>
            <div class="col-md-10">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0 bg-transparent"
                        placeholder="Search by title, client name or email..."
                        value="<?php echo Security::escape($search); ?>">
                </div>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-light btn-sm">Search</button>
            </div>
            <?php if ($search): ?>
                <div class="col-12 mt-1">
                    <span class="badge badge-soft-info fw-normal">
                        Showing results for: "<?php echo Security::escape($search); ?>"
                        <a href="<?php echo APP_URL; ?>/deals<?php echo $current_status ? '?status=' . $current_status : ''; ?>"
                            class="text-decoration-none ms-2"><i class="bi bi-x"></i></a>
                    </span>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Deal Title</th>
                    <th>Client</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($deals)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-briefcase fs-2 d-block mb-3 opacity-25"></i>
                                <p class="mb-0">No deals found.</p>
                                <p class="small text-muted">Start by creating a new deal to track progress.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($deals as $deal): ?>
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
                            <td>
                                <div class="data-amount text-main">
                                    <?php echo CURRENCY_SYMBOL . number_format($deal['amount'], 2); ?>
                                </div>
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
                            <td>
                                <div class="text-muted small"><?php echo date('M d, Y', strtotime($deal['created_at'])); ?>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light px-2" data-bs-toggle="dropdown"
                                        data-bs-popper-config='{"strategy":"fixed"}'>
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                        <li><a class="dropdown-item py-2 small"
                                                href="<?php echo APP_URL; ?>/deals/show/<?php echo $deal['id']; ?>"><i
                                                    class="bi bi-diagram-3 me-2"></i> View Details</a></li>

                                        <?php if ($this->hasPermission('deals', 'edit')): ?>
                                            <li><a class="dropdown-item py-2 small"
                                                    href="<?php echo APP_URL; ?>/deals/edit/<?php echo $deal['id']; ?>"><i
                                                        class="bi bi-pencil me-2"></i> Edit Deal</a></li>
                                        <?php endif; ?>

                                        <?php if ($this->hasPermission('deals', 'delete')): ?>
                                            <li>
                                                <hr class="dropdown-divider opacity-50">
                                            </li>
                                            <li><a class="dropdown-item py-2 small text-danger"
                                                    href="<?php echo APP_URL; ?>/deals/delete/<?php echo $deal['id']; ?>"
                                                    onclick="return confirm('Are you sure you want to delete this deal?')"><i
                                                        class="bi bi-trash me-2"></i> Delete</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>