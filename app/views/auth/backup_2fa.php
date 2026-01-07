<?php
/**
 * 2FA Backup Code Verification View
 */
include_once APP_PATH . '/views/auth/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Codes -
        <?php echo APP_NAME; ?>
    </title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/login.css?v=1.1">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo APP_URL; ?>/assets/img/favicon.png">
</head>

<body class="bg-light">
    <div class="login-container d-flex align-items-center justify-content-center min-vh-100">
        <div class="login-right w-100" style="max-width: 400px;">
            <div class="login-form-container p-4 bg-white rounded shadow-sm">
                <div class="form-header text-center mb-4">
                    <h2 class="form-title">Security Backup Code</h2>
                    <p class="form-subtitle">Enter one of your 8-digit emergency backup codes.</p>
                </div>

                <form method="POST" action="<?php echo APP_URL; ?>/auth/backup2fa">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

                    <?php if ($flash = Session::getFlash('error')): ?>
                        <div class="alert alert-danger mb-3">
                            <?php echo Security::escape($flash); ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group mb-4">
                        <label for="code" class="form-label">Backup Code</label>
                        <input type="text" id="code" name="code" class="form-control text-center fs-4 letter-spacing-lg"
                            placeholder="XXXX-XXXX" maxlength="10" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">Verify and Login</button>
                </form>

                <div class="form-footer mt-4 text-center">
                    <p><a href="<?php echo APP_URL; ?>/auth/verify2fa" class="text-primary">Return to Authenticator
                            App</a></p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>