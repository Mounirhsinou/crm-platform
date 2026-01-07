<?php $pageTitle = 'Clients'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Clients</h1>
        <p class="text-muted small mb-0">Manage and organize your client database</p>
    </div>
    <div class="col-auto">
        <div class="d-flex gap-2">
            <?php if ($this->hasPermission('clients', 'import')): ?>
                <a href="<?php echo APP_URL; ?>/clients/import" class="btn btn-light btn-sm px-3">
                    <i class="bi bi-box-arrow-in-up me-1"></i> Import
                </a>
            <?php endif; ?>

            <?php if ($this->hasPermission('clients', 'export')): ?>
                <a href="<?php echo APP_URL; ?>/clients/export" class="btn btn-light btn-sm px-3">
                    <i class="bi bi-box-arrow-up me-1"></i> Export
                </a>
            <?php endif; ?>

            <?php if ($this->hasPermission('clients', 'create')): ?>
                <a href="<?php echo APP_URL; ?>/clients/create" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-plus-lg me-1"></i> Add Client
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-10">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0 bg-transparent"
                        placeholder="Search by name, email or company..."
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
                        <a href="<?php echo APP_URL; ?>/clients" class="text-decoration-none ms-2"><i
                                class="bi bi-x"></i></a>
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
                    <th class="ps-4">Client Name</th>
                    <th>Email Address</th>
                    <th>Phone</th>
                    <th>Company</th>
                    <th>Joined</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clients)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-people fs-2 d-block mb-3"></i>
                                <p class="mb-0">No clients found.</p>
                                <p class="small">Try adjusting your search or add a new client.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-3 fw-semibold"
                                        style="width: 34px; height: 34px; font-size: 0.8rem; background-color: #f1f5f9 !important;">
                                        <?php echo strtoupper(substr($client['name'], 0, 1)); ?>
                                    </div>
                                    <div class="client-name">
                                        <a href="<?php echo APP_URL; ?>/clients/show/<?php echo $client['id']; ?>"
                                            class="text-decoration-none text-main hover-primary">
                                            <?php echo Security::escape($client['name']); ?>
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="data-email"><?php echo Security::escape($client['email']); ?></div>
                            </td>
                            <td>
                                <div class="data-numeric text-muted"><?php echo Security::escape($client['phone'] ?: '-'); ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-muted"><?php echo Security::escape($client['company'] ?? '-'); ?></div>
                            </td>
                            <td>
                                <div class="text-muted"><?php echo date('M d, Y', strtotime($client['created_at'])); ?></div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light px-2" data-bs-toggle="dropdown"
                                        data-bs-popper-config='{"strategy":"fixed"}'>
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                        <li><a class="dropdown-item py-2 small"
                                                href="<?php echo APP_URL; ?>/clients/show/<?php echo $client['id']; ?>"><i
                                                    class="bi bi-eye me-2"></i> View Profile</a></li>

                                        <?php if ($this->hasPermission('clients', 'edit')): ?>
                                            <li><a class="dropdown-item py-2 small"
                                                    href="<?php echo APP_URL; ?>/clients/edit/<?php echo $client['id']; ?>"><i
                                                        class="bi bi-pencil me-2"></i> Edit Details</a></li>
                                        <?php endif; ?>

                                        <?php if ($this->hasPermission('clients', 'delete')): ?>
                                            <li>
                                                <hr class="dropdown-divider opacity-50">
                                            </li>
                                            <li><a class="dropdown-item py-2 small text-danger"
                                                    href="<?php echo APP_URL; ?>/clients/delete/<?php echo $client['id']; ?>"
                                                    onclick="return confirm('Are you sure you want to delete this client?')"><i
                                                        class="bi bi-trash me-2"></i> Delete Client</a></li>
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