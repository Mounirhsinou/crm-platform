<?php $pageTitle = 'Tasks'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">My Tasks</h1>
        <p class="text-muted small mb-0">Track and manage your daily activities and follow-ups</p>
    </div>
    <?php if ($this->hasPermission('tasks', 'create')): ?>
        <div class="col-auto">
            <a href="<?php echo APP_URL; ?>/tasks/create" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-plus-lg me-1"></i> New Task
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mb-4" style="position: relative; z-index: 10;">
    <div class="card-body py-2">
        <form method="GET" action="<?php echo APP_URL; ?>/tasks" class="row g-2 align-items-center">
            <div class="col-auto">
                <span class="text-muted small fw-medium">Status Filter:</span>
            </div>
            <div class="col-auto">
                <div class="dropdown">
                    <button class="btn btn-white btn-sm dropdown-toggle px-3 border shadow-sm" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-popper-config='{"strategy":"fixed"}'
                        style="border-radius: 20px; background-color: white !important;">
                        <?php
                        $statusLabels = [
                            'pending' => 'Pending',
                            'completed' => 'Completed'
                        ];
                        echo $statusLabels[$currentStatus] ?? 'All Statuses';
                        ?>
                    </button>
                    <ul class="dropdown-menu shadow-premium border-0">
                        <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/tasks">All Statuses</a></li>
                        <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/tasks?status=pending">Pending</a>
                        </li>
                        <li><a class="dropdown-item py-2"
                                href="<?php echo APP_URL; ?>/tasks?status=completed">Completed</a></li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 60px;" class="ps-4"></th>
                    <th>Task Details</th>
                    <th>Linked To</th>
                    <th>Due Date</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tasks)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-check2-all fs-2 d-block mb-3 opacity-25"></i>
                                <p class="mb-0">You're all caught up!</p>
                                <p class="small text-muted">No tasks found in this view.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <tr class="<?php echo ($task['status'] === 'completed') ? 'opacity-50' : ''; ?>">
                            <td class="ps-4">
                                <?php if ($task['status'] === 'pending'): ?>
                                    <?php if ($this->hasPermission('tasks', 'edit')): ?>
                                        <a href="<?php echo APP_URL; ?>/tasks/finish/<?php echo $task['id']; ?>"
                                            class="btn btn-sm btn-light p-0 d-flex align-items-center justify-content-center border"
                                            style="width: 26px; height: 26px; border-radius: 8px; background-color: white !important;"
                                            title="Mark as completed">
                                            <i class="bi bi-circle text-muted" style="font-size: 0.7rem;"></i>
                                        </a>
                                    <?php else: ?>
                                        <div class="badge-soft-secondary d-flex align-items-center justify-content-center"
                                            style="width: 26px; height: 26px; border-radius: 8px;">
                                            <i class="bi bi-circle text-muted" style="font-size: 0.7rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="badge-soft-success d-flex align-items-center justify-content-center"
                                        style="width: 26px; height: 26px; border-radius: 8px;">
                                        <i class="bi bi-check2" style="font-size: 0.9rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div
                                    class="fw-medium text-main <?php echo ($task['status'] === 'completed') ? 'text-decoration-line-through text-muted' : ''; ?>">
                                    <?php echo Security::escape($task['title']); ?>
                                </div>
                                <?php if ($task['description']): ?>
                                    <div class="text-muted small text-truncate" style="max-width: 250px;">
                                        <?php echo Security::escape($task['description']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($task['client_name']): ?>
                                    <div class="d-flex align-items-center mb-1 text-primary">
                                        <i class="bi bi-person me-2"></i>
                                        <span class="client-name"><?php echo Security::escape($task['client_name']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($task['deal_title']): ?>
                                    <div class="small d-flex align-items-center text-muted">
                                        <i class="bi bi-briefcase me-2"></i>
                                        <span><?php echo Security::escape($task['deal_title']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!$task['client_name'] && !$task['deal_title']): ?>
                                    <span class="text-muted small italic">General Task</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $isOverdue = (strtotime($task['due_date']) < strtotime('today') && $task['status'] === 'pending');
                                ?>
                                <div class="small <?php echo $isOverdue ? 'text-danger fw-bold' : 'text-muted'; ?>">
                                    <i class="bi bi-calendar3 me-2 opacity-50"></i>
                                    <?php echo date('M d, Y', strtotime($task['due_date'])); ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $priorityColors = [
                                    'high' => 'danger',
                                    'medium' => 'warning',
                                    'low' => 'info'
                                ];
                                $pColor = $priorityColors[$task['priority']] ?? 'secondary';
                                ?>
                                <span class="badge badge-soft-<?php echo $pColor; ?>" style="font-size: 0.7rem;">
                                    <?php echo strtoupper($task['priority']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($task['status'] === 'completed'): ?>
                                    <span class="badge badge-soft-success">Done</span>
                                <?php else: ?>
                                    <span class="badge badge-soft-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light px-2" data-bs-toggle="dropdown"
                                        data-bs-popper-config='{"strategy":"fixed"}'>
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                        <?php if ($this->hasPermission('tasks', 'edit')): ?>
                                            <li><a class="dropdown-item py-2 small"
                                                    href="<?php echo APP_URL; ?>/tasks/edit/<?php echo $task['id']; ?>"><i
                                                        class="bi bi-pencil me-2"></i> Edit Task</a></li>
                                        <?php endif; ?>

                                        <?php if ($this->hasPermission('tasks', 'delete')): ?>
                                            <li>
                                                <hr class="dropdown-divider opacity-50">
                                            </li>
                                            <li><a class="dropdown-item py-2 small text-danger"
                                                    href="<?php echo APP_URL; ?>/tasks/delete/<?php echo $task['id']; ?>"
                                                    onclick="return confirm('Are you sure you want to delete this task?')"><i
                                                        class="bi bi-trash me-2"></i> Delete</a></li>
                                        <?php endif; ?>

                                        <?php if (!$this->hasPermission('tasks', 'edit') && !$this->hasPermission('tasks', 'delete')): ?>
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