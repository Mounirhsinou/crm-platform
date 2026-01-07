<?php
/**
 * 2FA Test Page - FOR TESTING ONLY
 * This helps verify TOTP codes are working
 * DELETE THIS FILE after testing!
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/helpers/TOTP.php';

// Generate a test secret
$testSecret = TOTP::generateSecret();

// Get current TOTP code
$currentTime = floor(time() / 30);
$testCode = TOTP::generateCode($testSecret, $currentTime);

?>
<!DOCTYPE html>
<html>

<head>
    <title>2FA Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">⚠️ 2FA Testing Tool</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <strong>Warning:</strong> This is a test page. Delete it after testing!
                        </div>

                        <h6>Test Secret:</h6>
                        <div class="alert alert-secondary">
                            <code><?php echo $testSecret; ?></code>
                        </div>

                        <h6>Expected Code (right now):</h6>
                        <div class="alert alert-success">
                            <h3 class="mb-0"><code><?php echo $testCode; ?></code></h3>
                        </div>

                        <h6>Test Verification:</h6>
                        <form method="POST">
                            <input type="hidden" name="test_secret" value="<?php echo $testSecret; ?>">
                            <input type="text" name="test_code" class="form-control mb-2" placeholder="Enter code"
                                maxlength="6">
                            <button type="submit" class="btn btn-primary">Test Verify</button>
                        </form>

                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $inputCode = $_POST['test_code'] ?? '';
                            $secret = $_POST['test_secret'] ?? '';

                            if (TOTP::verify($secret, $inputCode)) {
                                echo '<div class="alert alert-success mt-3">✅ Code VERIFIED! TOTP is working!</div>';
                            } else {
                                echo '<div class="alert alert-danger mt-3">❌ Code FAILED! Check your input.</div>';
                                echo '<p class="small">Expected: ' . TOTP::generateCode($secret, floor(time() / 30)) . '</p>';
                            }
                        }
                        ?>

                        <hr>
                        <h6>Instructions:</h6>
                        <ol class="small">
                            <li>Copy the test secret above</li>
                            <li>Add it to Google Authenticator manually</li>
                            <li>Enter the code it shows</li>
                            <li>Click "Test Verify"</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>