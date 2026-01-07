<?php $pageTitle = 'Change Password'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $pageTitle; ?> - CRM
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/login.css?v=1.1">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo APP_URL; ?>/assets/img/favicon.png">
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="avatar-circles bg-warning bg-opacity-10 text-warning mx-auto mb-3"
                                style="width: 60px; height: 60px; font-size: 24px;">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <h3 class="h4 fw-bold text-main mb-2">Password Change Required</h3>
                            <p class="text-muted small mb-0">For security reasons, you must change your password before
                                continuing</p>
                        </div>

                        <?php echo $this->flash(); ?>

                        <?php if (isset($errors) && !empty($errors)): ?>
                            <div class="alert alert-danger border-0 mb-4">
                                <?php foreach ($errors as $error): ?>
                                    <div><i class="bi bi-exclamation-triangle me-2"></i>
                                        <?php echo $error; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo APP_URL; ?>/auth/changePassword">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-main">Current Password</label>
                                <input type="password" name="current_password" class="form-control border-0 bg-light"
                                    placeholder="Enter your current password" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-main">New Password</label>
                                <input type="password" name="new_password" id="newPassword"
                                    class="form-control border-0 bg-light" placeholder="Enter new password" required>
                                <small class="text-muted">Minimum 8 characters, must include uppercase, lowercase, and
                                    number</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-main">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control border-0 bg-light"
                                    placeholder="Confirm new password" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary py-2">
                                    <i class="bi bi-check-circle me-2"></i>Change Password
                                </button>
                            </div>
                        </form>

                        <div class="alert alert-warning border-0 mt-4 mb-0">
                            <small><i class="bi bi-info-circle me-2"></i>You cannot skip this step. Please create a
                                strong password to continue.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>