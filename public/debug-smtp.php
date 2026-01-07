<?php
// SMTP Debug Script
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/SmtpSetting.php';
require_once __DIR__ . '/../app/helpers/SimpleSMTP.php';

// Mock Session for Model
class Session
{
    public static function get($key)
    {
        return 1;
    } // Assume user ID 1
}

echo "<h1>SMTP Debugger</h1>";

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM smtp_settings WHERE user_id = ?");
    $stmt->execute([1]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        die("❌ No SMTP settings found for user ID 1.");
    }

    echo "<h3>Current Settings:</h3>";
    echo "Host: " . htmlspecialchars($settings['smtp_host']) . "<br>";
    echo "Port: " . htmlspecialchars($settings['smtp_port']) . "<br>";
    echo "User: " . htmlspecialchars($settings['smtp_username']) . "<br>";
    echo "Encryption: " . htmlspecialchars($settings['smtp_encryption']) . "<br>";

    // Decrypt password
    $key = ENCRYPTION_KEY;
    $iv = ENCRYPTION_IV;
    $password = openssl_decrypt($settings['smtp_password'], 'AES-256-CBC', $key, 0, $iv);

    echo "Password (decrypted length): " . strlen($password) . "<br>";

    echo "<hr><h3>Testing Connection...</h3>";

    $smtp = new SimpleSMTP(
        $settings['smtp_host'],
        $settings['smtp_port'],
        $settings['smtp_username'],
        $password,
        $settings['smtp_encryption']
    );

    // Try to send a test email to self
    $result = $smtp->send(
        $settings['from_email'],
        $settings['from_name'],
        $settings['from_email'],
        $settings['from_name'],
        'Debug Test ' . date('Y-m-d H:i:s'),
        'This is a debug test email.'
    );

    if ($result) {
        echo "<h2 style='color:green'>✅ Success! Email sent.</h2>";
    }

} catch (Exception $e) {
    echo "<h2 style='color:red'>❌ Error:</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<h3>Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
