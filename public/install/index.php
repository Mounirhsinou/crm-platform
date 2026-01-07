<?php
/**
 * CRM Web Installer
 */

// Define paths
define('ROOT_DIR', dirname(dirname(__DIR__)));
define('LOCK_FILE', __DIR__ . '/lock.txt');

// Check if already installed
if (file_exists(LOCK_FILE)) {
    die("Installation is locked. To re-install, delete public/install/lock.txt manually.");
}

$error = null;
$success = null;
$step = isset($_POST['step']) ? (int) $_POST['step'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        // Collect inputs
        $db_host = trim($_POST['db_host']);
        $db_name = trim($_POST['db_name']);
        $db_user = trim($_POST['db_user']);
        $db_pass = $_POST['db_pass'];
        $app_url = rtrim(trim($_POST['app_url']), '/');

        try {
            // 1. Validate Database Connection
            $dsn = "mysql:host=$db_host;charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Attempt to create database if it doesn't exist (if user has permissions)
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$db_name`;"); // Use the target database

            // 2. Import Schema
            $schema_file = ROOT_DIR . '/database/schema.sql';
            $migration_file = ROOT_DIR . '/database/security_migration.sql';

            if (!file_exists($schema_file)) {
                throw new Exception("Schema file not found: $schema_file");
            }

            // Run Schema
            $sql = file_get_contents($schema_file);
            // Split by semicolon but be careful with triggers/procedures if any (our schema is simple)
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $stmt) {
                if (!empty($stmt)) {
                    $pdo->exec($stmt);
                }
            }

            // Run Migrations if exists
            if (file_exists($migration_file)) {
                $sql = file_get_contents($migration_file);
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($statements as $stmt) {
                    if (!empty($stmt)) {
                        $pdo->exec($stmt);
                    }
                }
            }

            // 3. Create .env file
            $env_content = "DB_HOST=$db_host\n";
            $env_content .= "DB_PORT=3306\n";
            $env_content .= "DB_NAME=$db_name\n";
            $env_content .= "DB_USER=$db_user\n";
            $env_content .= "DB_PASS=$db_pass\n";
            $env_content .= "DB_CHARSET=utf8mb4\n\n";

            $env_content .= "APP_NAME=CRM\n";
            $env_content .= "APP_URL=$app_url\n";
            $env_content .= "APP_DEBUG=false\n\n";

            $env_content .= "SESSION_IDLE_TIMEOUT=1800\n";
            $env_content .= "ENFORCE_HTTPS=false\n";

            if (file_put_contents(ROOT_DIR . '/.env', $env_content) === false) {
                throw new Exception("Unable to write .env file. Please check folder permissions.");
            }

            // 4. Create Lock File
            file_put_contents(LOCK_FILE, "Installed on " . date('Y-m-d H:i:s'));

            $success = "Installation successful! You can now log in.";
            $step = 2;

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
} else {
    // Auto-detect App URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = str_replace('/install/index.php', '', $_SERVER['REQUEST_URI']);
    $uri = str_replace('/install', '', $uri);
    $auto_app_url = $protocol . '://' . $host . rtrim($uri, '/');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Web Installer</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="install-container">
        <div class="card">
            <div class="logo-area">
                <h1>CRM INSTALLER</h1>
                <p>Fast & Secure Deployment</p>
            </div>

            <?php if ($step === 1): ?>
                <div class="step-indicator">
                    <div class="step active">1</div>
                    <div class="step">2</div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="step" value="1">

                    <div class="form-group">
                        <label class="form-label">Database Host</label>
                        <input type="text" name="db_host" class="form-control" value="localhost" required
                            placeholder="e.g. localhost or 127.0.0.1">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Database Name</label>
                        <input type="text" name="db_name" class="form-control" value="crm_db" required
                            placeholder="Name of your database">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Database User</label>
                        <input type="text" name="db_user" class="form-control" value="root" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Database Password</label>
                        <input type="password" name="db_pass" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Application URL</label>
                        <input type="text" name="app_url" class="form-control" value="<?php echo $auto_app_url; ?>"
                            required>
                    </div>

                    <button type="submit" class="btn-primary">Connect & Install</button>
                </form>

            <?php else: ?>
                <div class="step-indicator">
                    <div class="step completed">✓</div>
                    <div class="step completed">✓</div>
                </div>

                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
                <p style="text-align: center; color: var(--text-muted); font-size: 0.875rem; margin-bottom: 2rem;">
                    Default Admin Credentials:<br>
                    <strong>Email:</strong> demo@crm.com<br>
                    <strong>Password:</strong> demo123
                </p>
                <a href="../auth/login" class="btn-primary"
                    style="display: block; text-decoration: none; text-align: center;">Go to Login Page</a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>