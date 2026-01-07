<?php $pageTitle = 'Follow-ups'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Upcoming Follow-ups</h1>
        <p class="text-muted small mb-0">Scheduled engagements and client follow-ups</p>
    </div>
    <?php if ($this->hasPermission('followups', 'create')): ?>
        <div class="col-auto">
            <a href="<?php echo APP_URL; ?>/followups/create" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-plus-lg me-1"></i> Add Follow-up
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small fw-medium">Filter by Status:</span>
            <div class="nav nav-pills bg-light p-1 rounded-pill small">
                <a href="<?php echo APP_URL; ?>/followups"
                    class="nav-link py-1 px-3 rounded-pill <?php echo !$current_status ? 'active shadow-sm' : 'text-secondary'; ?>">All
                    Events</a>
                <a href="<?php echo APP_URL; ?>/followups?status=pending"
                    class="nav-link py-1 px-3 rounded-pill <?php echo $current_status === 'pending' ? 'active shadow-sm' : 'text-secondary'; ?>">Pending</a>
                <a href="<?php echo APP_URL; ?>/followups?status=done"
                    class="nav-link py-1 px-3 rounded-pill <?php echo $current_status === 'done' ? 'active shadow-sm' : 'text-secondary'; ?>">Completed</a>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Client / Account</th>
                    <th>Notes & Context</th>
                    <th>Scheduled Date</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($followups)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-calendar-event fs-2 d-block mb-3 opacity-25"></i>
                                <p class="mb-0">No follow-ups found.</p>
                                <p class="small text-muted">Stay on top of your deals by scheduling a follow-up.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($followups as $followup): ?>
                        <?php
                        $isOverdue = ($followup['status'] === 'pending' && strtotime($followup['followup_date']) < strtotime('today'));
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circles me-3 text-primary">
                                        <?php echo strtoupper(substr($followup['client_name'] ?? 'U', 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="client-name">
                                            <?php echo Security::escape($followup['client_name'] ?? 'Unlinked Record'); ?>
                                        </div>
                                        <div class="text-muted small">
                                            <?php echo Security::escape($followup['deal_title'] ?? 'General Follow-up'); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-muted small text-truncate" style="max-width: 300px;"
                                    title="<?php echo Security::escape($followup['notes']); ?>">
                                    <?php echo Security::escape($followup['notes']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="small <?php echo $isOverdue ? 'text-danger fw-bold' : 'text-muted'; ?>">
                                    <i
                                        class="bi <?php echo $isOverdue ? 'bi-exclamation-circle' : 'bi-calendar3'; ?> me-2 opacity-50"></i>
                                    <?php echo date('M d, Y', strtotime($followup['followup_date'])); ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($followup['status'] === 'done'): ?>
                                    <span class="badge badge-soft-success">Completed</span>
                                <?php else: ?>
                                    <span class="badge badge-soft-<?php echo $isOverdue ? 'danger' : 'warning'; ?>">
                                        <?php echo $isOverdue ? 'Overdue' : 'Scheduled'; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light px-2" data-bs-toggle="dropdown"
                                        data-bs-popper-config='{"strategy":"fixed"}'>
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                        <?php if ($this->hasPermission('followups', 'edit') && $followup['status'] === 'pending'): ?>
                                            <li><a class="dropdown-item py-2 small"
                                                    href="<?php echo APP_URL; ?>/followups/markDone/<?php echo $followup['id']; ?>"><i
                                                        class="bi bi-check2-circle me-2"></i> Mark as Done</a></li>
                                        <?php endif; ?>

                                        <?php if ($this->hasPermission('followups', 'edit')): ?>
                                            <li><a class="dropdown-item py-2 small"
                                                    href="<?php echo APP_URL; ?>/followups/edit/<?php echo $followup['id']; ?>"><i
                                                        class="bi bi-pencil me-2"></i> Edit</a></li>
                                        <?php endif; ?>

                                        <?php if ($this->hasPermission('followups', 'delete')): ?>
                                            <li>
                                                <hr class="dropdown-divider opacity-50">
                                            </li>
                                            <li><a class="dropdown-item py-2 small text-danger"
                                                    href="<?php echo APP_URL; ?>/followups/delete/<?php echo $followup['id']; ?>"
                                                    onclick="return confirm('Are you sure you want to delete this follow-up?')"><i
                                                        class="bi bi-trash me-2"></i> Delete</a></li>
                                        <?php endif; ?>

                                        <?php if (!$this->hasPermission('followups', 'edit') && !$this->hasPermission('followups', 'delete')): ?>
                                            <li><span class="dropdown-item py-2 small text-muted italic">No actions available</span>
                                            </li>
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