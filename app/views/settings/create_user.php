<?php $pageTitle = 'Create User'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4">
    <div class="col">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/settings/users">User Management</a></li>
                <li class="breadcrumb-item active">Create User</li>
            </ol>
        </nav>
        <h1 class="h4 mb-1">Create New User</h1>
        <p class="text-muted small mb-0">Add a new user to the system</p>
    </div>
</div>

<?php echo $this->flash(); ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="alert alert-info border-0 mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Note:</strong> A secure password will be automatically generated and sent to the user's
                    email address. They will be required to change it upon first login.
                </div>

                <form method="POST" action="<?php echo APP_URL; ?>/settings/createUser">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-main">Full Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="full_name"
                            class="form-control border-0 bg-light <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>"
                            value="<?php echo Security::escape($_POST['full_name'] ?? ''); ?>" placeholder="John Doe"
                            required>
                        <?php if (isset($errors['full_name'])): ?>
                            <div class="invalid-feedback">
                                <?php echo $errors['full_name']; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-main">Email Address <span
                                class="text-danger">*</span></label>
                        <input type="email" name="email"
                            class="form-control border-0 bg-light <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                            value="<?php echo Security::escape($_POST['email'] ?? ''); ?>"
                            placeholder="john@example.com" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback">
                                <?php echo $errors['email']; ?>
                            </div>
                        <?php endif; ?>
                        <small class="text-muted">Login credentials will be sent to this email</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-main">Role <span
                                class="text-danger">*</span></label>
                        <select name="role_id"
                            class="form-select border-0 bg-light <?php echo isset($errors['role_id']) ? 'is-invalid' : ''; ?>"
                            required>
                            <option value="">Select a role...</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>" <?php echo (isset($_POST['role_id']) && $_POST['role_id'] == $role['id']) ? 'selected' : ''; ?>>
                                    <?php echo Security::escape($role['name']); ?>
                                    <?php if (!empty($role['description'])): ?>
                                        -
                                        <?php echo Security::escape($role['description']); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['role_id'])): ?>
                            <div class="invalid-feedback">
                                <?php echo $errors['role_id']; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-main">Password <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="password"
                                class="form-control border-0 bg-light <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                                placeholder="Enter password" required minlength="8">
                            <button type="button" class="btn btn-outline-secondary border-0 bg-light"
                                onclick="togglePasswordVisibility('password')">
                                <i class="bi bi-eye" id="password-icon"></i>
                            </button>
                            <button type="button" class="btn btn-primary border-0" onclick="generatePassword()">
                                <i class="bi bi-shuffle me-1"></i> Generate
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback d-block"><?php echo $errors['password']; ?></div>
                        <?php endif; ?>
                        <small class="text-muted">Minimum 8 characters, include uppercase, lowercase, and number</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-main">Confirm Password <span
                                class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" id="confirm_password"
                            class="form-control border-0 bg-light <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>"
                            placeholder="Confirm password" required minlength="8">
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="must_change_password"
                                id="must_change_password" value="1" checked>
                            <label class="form-check-label small" for="must_change_password">
                                Force user to change password on first login
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">Recommended for security. User will be prompted to set a
                            new password after first login.</small>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?php echo APP_URL; ?>/settings/users" class="btn btn-secondary btn-sm px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i class="bi bi-person-plus me-2"></i>Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function generatePassword() {
    const length = 12;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
    let password = "";
    
    // Ensure at least one of each type
    password += "ABCDEFGHIJKLMNOPQRSTUVWXYZ"[Math.floor(Math.random() * 26)];
    password += "abcdefghijklmnopqrstuvwxyz"[Math.floor(Math.random() * 26)];
    password += "0123456789"[Math.floor(Math.random() * 10)];
    password += "!@#$%^&*"[Math.floor(Math.random() * 8)];
    
    // Fill the rest
    for (let i = password.length; i < length; i++) {
        password += charset[Math.floor(Math.random() * charset.length)];
    }
    
    // Shuffle the password
    password = password.split('').sort(() => Math.random() - 0.5).join('');
    
    document.getElementById('password').value = password;
    document.getElementById('confirm_password').value = password;
    document.getElementById('password').type = 'text';
    document.getElementById('password-icon').classList.replace('bi-eye', 'bi-eye-slash');
}

function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>