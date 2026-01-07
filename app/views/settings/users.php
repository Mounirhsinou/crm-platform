<?php $pageTitle = 'User Management'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">User Management</h1>
        <p class="text-muted small mb-0">Manage system users and their roles</p>
    </div>
    <?php if ($this->hasPermission('users', 'create')): ?>
        <div class="col-auto">
            <a href="<?php echo APP_URL; ?>/settings/createUser" class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus me-2"></i>Create User
            </a>
        </div>
    <?php endif; ?>
</div>

<?php echo $this->flash(); ?>

<div class="row g-4">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm overflow-hidden mb-4">
            <div class="list-group list-group-flush small">
                <a href="<?php echo APP_URL; ?>/settings"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center text-secondary">
                    <i class="bi bi-building me-3 opacity-50"></i> Company Profile
                </a>
                <a href="<?php echo APP_URL; ?>/settings/users"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center active shadow-sm">
                    <i class="bi bi-people me-3"></i> Roles & Users
                </a>
                <a href="<?php echo APP_URL; ?>/settings/payments"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center text-secondary">
                    <i class="bi bi-credit-card me-3 opacity-50"></i> Payment Setup
                </a>
                <a href="<?php echo APP_URL; ?>/settings/dataCollection"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center text-secondary">
                    <i class="bi bi-database-down me-3 opacity-50"></i> Data Storage
                </a>
                <a href="<?php echo APP_URL; ?>/settings/security"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center text-secondary">
                    <i class="bi bi-shield-lock me-3 opacity-50"></i> Security Portal
                </a>
                <a href="<?php echo APP_URL; ?>/settings/integrations"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center text-secondary">
                    <i class="bi bi-plug me-3 opacity-50"></i> Integrations
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-main fw-semibold small">User Info</th>
                                <th class="px-4 py-3 text-main fw-semibold small text-center">Role</th>
                                <th class="px-4 py-3 text-main fw-semibold small text-center">Status</th>
                                <th class="px-4 py-3 text-main fw-semibold small text-end px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
                                        No users found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circles bg-primary bg-opacity-10 text-primary me-3"
                                                    style="width: 38px; height: 38px; font-size: 14px; flex-shrink: 0;">
                                                    <?php echo strtoupper(substr($user['full_name'] ?? $user['email'], 0, 2)); ?>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-main mb-0">
                                                        <?php echo Security::escape($user['full_name'] ?? 'N/A'); ?>
                                                    </div>
                                                    <div class="data-email small opacity-75">
                                                        <?php echo Security::escape($user['email']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <?php
                                            $roleColors = [
                                                'super_admin' => 'danger',
                                                'admin' => 'primary',
                                                'moderator' => 'info',
                                                'developer' => 'warning',
                                                'viewer' => 'secondary'
                                            ];
                                            $roleColor = $roleColors[$user['role_slug'] ?? ''] ?? 'secondary';
                                            ?>
                                            <span
                                                class="badge bg-<?php echo $roleColor; ?> bg-opacity-10 text-<?php echo $roleColor; ?> fw-medium px-2 py-1">
                                                <?php echo Security::escape($user['role_name'] ?? 'No Role'); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <?php if ($user['is_active']): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success fw-medium small">
                                                    Active
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger fw-medium small">
                                                    Disabled
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-end px-4">
                                            <div class="dropdown">
                                                <button class="btn btn-light btn-sm px-2 border-0 bg-transparent"
                                                    data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots-vertical fs-6 text-secondary"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                    <li>
                                                        <button class="dropdown-item py-2 small"
                                                            onclick="viewUserActivity(<?php echo $user['id']; ?>, '<?php echo Security::escape($user['full_name'] ?? $user['email']); ?>')">
                                                            <i class="bi bi-clock-history me-2 opacity-50"></i> View Activity
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider opacity-50">
                                                    </li>

                                                    <?php if ($this->hasPermission('users', 'edit')): ?>
                                                        <li>
                                                            <button class="dropdown-item py-2 small"
                                                                onclick="openChangeRoleModal(<?php echo $user['id']; ?>, '<?php echo Security::escape($user['full_name'] ?? $user['email']); ?>', <?php echo $user['role_id']; ?>)">
                                                                <i class="bi bi-shield-check me-2 opacity-50"></i> Change Role
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item py-2 small"
                                                                onclick="openChangePasswordModal(<?php echo $user['id']; ?>, '<?php echo Security::escape($user['full_name'] ?? $user['email']); ?>')">
                                                                <i class="bi bi-key me-2 opacity-50"></i> Change Password
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button
                                                                class="dropdown-item py-2 small status-toggle-btn <?php echo $user['is_active'] ? 'text-warning' : 'text-success'; ?>"
                                                                onclick="toggleUserStatus(<?php echo $user['id']; ?>, this)">
                                                                <i
                                                                    class="bi bi-<?php echo $user['is_active'] ? 'slash-circle' : 'check-circle'; ?> me-2 opacity-50"></i>
                                                                <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                            </button>
                                                        </li>
                                                    <?php endif; ?>

                                                    <?php if ($this->hasPermission('users', 'delete') && $user['id'] != $this->getUserId()): ?>
                                                        <li>
                                                            <hr class="dropdown-divider opacity-50">
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item py-2 small text-danger"
                                                                onclick="confirmDeleteUser(<?php echo $user['id']; ?>, '<?php echo Security::escape($user['full_name'] ?? $user['email']); ?>')">
                                                                <i class="bi bi-trash me-2 opacity-50"></i> Delete User
                                                            </button>
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
        </div>
    </div>
</div>

<!-- Single Change Role Modal (Top-Level) -->
<div class="modal fade" id="changeRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Change Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="changeRoleForm" method="POST" action="<?php echo APP_URL; ?>/settings/updateUserRole">
                <div class="modal-body p-4 pt-3">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="user_id" id="modal_user_id" value="">
                    <p class="text-muted small mb-3">Change role for <strong id="modal_user_name"></strong></p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select New Role</label>
                        <select name="role_id" id="modal_role_id" class="form-select" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>">
                                    <?php echo Security::escape($role['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="updateRoleBtn">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo APP_URL; ?>/settings/adminSetPassword" method="POST">
                <div class="modal-body p-4 pt-3">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="user_id" id="pwd_user_id">
                    <p class="text-muted small mb-3">Set a new password for <strong id="pwd_user_name"></strong></p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">New Password</label>
                        <input type="password" name="password" class="form-control" required minlength="8"
                            placeholder="Min 8 characters">
                    </div>
                    <div class="form-check form-switch small">
                        <input class="form-check-input" type="checkbox" name="must_change_password" id="mustChangeCheck"
                            checked>
                        <label class="form-check-label" for="mustChangeCheck">Force password change on next
                            login</label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-4 text-center">
                <div class="mb-3 text-danger">
                    <i class="bi bi-exclamation-octagon fs-1"></i>
                </div>
                <h5 class="fw-bold mb-2">Delete User?</h5>
                <p class="text-muted small mb-4">Are you sure you want to permanently delete <strong
                        id="delete_user_name"></strong>? This action cannot be undone.</p>
                <form action="<?php echo APP_URL; ?>/settings/deleteUser" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="user_id" id="delete_user_id">
                    <button type="button" class="btn btn-light btn-sm w-100 mb-2"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm w-100">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Activity Logs Side Drawer -->
<div class="offcanvas offcanvas-end border-0 shadow-lg" tabindex="-1" id="activityDrawer" style="width: 400px;">
    <div class="offcanvas-header bg-light py-3">
        <h5 class="offcanvas-title fw-bold small text-uppercase">User Activity Log</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="p-4 border-bottom bg-white sticky-top">
            <h6 class="mb-1 fw-bold" id="activity_user_name">User Name</h6>
            <p class="text-muted small mb-0">Showing last 30 activities</p>
        </div>
        <div id="activity_list" class="p-4">
            <!-- Dynamic Logs -->
            <div class="text-center py-5 text-muted opacity-50">
                <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
                <div class="small">Loading activity...</div>
            </div>
        </div>
    </div>
</div>

<script>
    let changeRoleModal;

    let changePasswordModal;
    let deleteUserModal;
    let activityDrawer;

    document.addEventListener('DOMContentLoaded', function () {
        changeRoleModal = new bootstrap.Modal(document.getElementById('changeRoleModal'));
        changePasswordModal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
        deleteUserModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
        activityDrawer = new bootstrap.Offcanvas(document.getElementById('activityDrawer'));

        // Change Role AJAX
        document.getElementById('changeRoleForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const form = this;
            const updateBtn = document.getElementById('updateRoleBtn');
            const originalHtml = updateBtn.innerHTML;

            updateBtn.disabled = true;
            updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: new URLSearchParams(formData),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Refresh to show new role badge
                    } else {
                        alert('Error: ' + data.message);
                        updateBtn.disabled = false;
                        updateBtn.innerHTML = originalHtml;
                    }
                })
                .catch(err => {
                    alert('Network error. Please try again.');
                    updateBtn.disabled = false;
                    updateBtn.innerHTML = originalHtml;
                });
        });
    });

    function openChangeRoleModal(userId, userName, currentRoleId) {
        document.getElementById('modal_user_id').value = userId;
        document.getElementById('modal_user_name').textContent = userName;
        document.getElementById('modal_role_id').value = currentRoleId;
        changeRoleModal.show();
    }

    function openChangePasswordModal(userId, userName) {
        document.getElementById('pwd_user_id').value = userId;
        document.getElementById('pwd_user_name').textContent = userName;
        changePasswordModal.show();
    }

    function confirmDeleteUser(userId, userName) {
        document.getElementById('delete_user_id').value = userId;
        document.getElementById('delete_user_name').textContent = userName;
        deleteUserModal.show();
    }

    function toggleUserStatus(userId, btn) {
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Processing...';

        const formData = new URLSearchParams();
        formData.append('user_id', userId);
        formData.append('csrf_token', '<?php echo $csrf_token; ?>');

        fetch('<?php echo APP_URL; ?>/settings/toggleUser', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            })
            .catch(() => {
                alert('Network error');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
    }

    function viewUserActivity(userId, userName) {
        const list = document.getElementById('activity_list');
        document.getElementById('activity_user_name').textContent = userName;

        list.innerHTML = `
            <div class="text-center py-5 text-muted opacity-50">
                <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
                <div class="small">Loading activity...</div>
            </div>`;

        activityDrawer.show();

        fetch(`<?php echo APP_URL; ?>/settings/getUserActivity?user_id=${userId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(data => {
                if (!data || data.length === 0) {
                    list.innerHTML = '<div class="text-center py-5 text-muted small">No activity found for this user.</div>';
                    return;
                }

                let html = '';
                data.forEach(log => {
                    const iconMap = {
                        'login': 'bi-box-arrow-in-right text-success',
                        'logout': 'bi-box-arrow-right text-secondary',
                        'user_create': 'bi-person-plus text-primary',
                        'user_delete': 'bi-person-x text-danger',
                        'user_role_change': 'bi-shield-check text-info',
                        'user_password_change': 'bi-key text-warning',
                        'user_password_reset': 'bi-envelope text-warning',
                        'user_disabled': 'bi-slash-circle text-danger',
                        'user_enabled': 'bi-check-circle text-success'
                    };
                    const iconClass = iconMap[log.action_type] || 'bi-dot text-secondary';
                    const actionLabel = log.action_type.replace(/_/g, ' ').toUpperCase();

                    html += `
                    <div class="d-flex mb-4">
                        <div class="me-3">
                            <i class="bi ${iconClass} fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small fw-bold text-dark text-uppercase" style="font-size: 0.7rem;">${actionLabel}</div>
                            <div class="small text-secondary mb-1">${log.description || 'No details available'}</div>
                            <div class="text-muted d-flex align-items-center" style="font-size: 0.65rem;">
                                <i class="bi bi-calendar3 me-1"></i> <span class="data-numeric">${log.formatted_date}</span>
                                <i class="bi bi-geo-alt ms-2 me-1"></i> <span class="data-numeric">${log.ip_address}</span>
                            </div>
                        </div>
                    </div>`;
                });
                list.innerHTML = html;
            })
            .catch(() => {
                list.innerHTML = '<div class="text-center py-5 text-danger small">Failed to load activity log.</div>';
            });
    }
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>