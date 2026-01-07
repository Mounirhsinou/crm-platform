<?php
// Simple test to check if enableTwoFactor is accessible
session_start();

// Simulate logged in user
$_SESSION['user_id'] = 1; // Change this to your actual user ID

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/helpers/TOTP.php';

echo "<h1>2FA Debug Test</h1>";

try {
    // Test 1: TOTP class
    echo "<h3>Test 1: TOTP Class</h3>";
    $secret = TOTP::generateSecret();
    echo "✅ Secret generated: <code>$secret</code><br>";

    // Test 2: QR Code URL
    echo "<h3>Test 2: QR Code Generation</h3>";
    $testUser = ['email' => 'test@example.com'];
    $qrUrl = TOTP::getQRCodeUrl($testUser, $secret);
    echo "✅ QR URL: <a href='$qrUrl' target='_blank'>View QR Code</a><br>";
    echo "<img src='$qrUrl' alt='QR Code'><br>";

    // Test 3: Database connection
    echo "<h3>Test 3: Database</h3>";
    $db = Database::getInstance()->getConnection();
    echo "✅ Database connected<br>";

    // Test 4: Check if user exists
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([1]);
    $user = $stmt->fetch();
    if ($user) {
        echo "✅ User found: " . $user['email'] . "<br>";
    } else {
        echo "❌ User not found<br>";
    }

    // Test 5: Session
    echo "<h3>Test 4: Session</h3>";
    echo "✅ Session user_id: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";

    echo "<hr>";
    echo "<h3>Conclusion</h3>";
    echo "<p>If all tests passed, the 2FA setup should work.</p>";
    echo "<p><a href='/CRM/public/settings/enableTwoFactor'>Try Enable 2FA</a></p>";

} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>