<?php
/**
 * Stripe Payment Debug Script
 * 
 * This script helps debug Stripe payment issues by:
 * 1. Checking PHP error log location
 * 2. Verifying Stripe configuration
 * 3. Testing Stripe API connectivity
 * 
 * Usage: Access via browser at /public/debug-stripe.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

// Start session
session_start();

// Check if user is authenticated (optional - comment out for testing)
// if (!isset($_SESSION['user_id'])) {
//     die('Please login first');
// }

?>
<!DOCTYPE html>
<html>

<head>
    <title>Stripe Payment Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            border-bottom: 2px solid #635BFF;
            padding-bottom: 10px;
        }

        h2 {
            color: #635BFF;
            margin-top: 30px;
        }

        .success {
            color: #28a745;
            font-weight: bold;
        }

        .error {
            color: #dc3545;
            font-weight: bold;
        }

        .warning {
            color: #ffc107;
            font-weight: bold;
        }

        .info {
            background: #e7f3ff;
            padding: 10px;
            border-left: 4px solid #2196F3;
            margin: 10px 0;
        }

        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f8f9fa;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-test {
            background: #ffc107;
            color: #000;
        }

        .badge-live {
            background: #28a745;
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔍 Stripe Payment Debug Tool</h1>

        <h2>1. PHP Error Logging</h2>
        <?php
        $errorLog = ini_get('error_log');
        $logErrors = ini_get('log_errors');
        $displayErrors = ini_get('display_errors');

        echo "<table>";
        echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";
        echo "<tr><td>error_log</td><td>" . ($errorLog ?: 'default') . "</td><td>" . ($errorLog ? '<span class="success">✓</span>' : '<span class="warning">⚠</span>') . "</td></tr>";
        echo "<tr><td>log_errors</td><td>" . ($logErrors ? 'On' : 'Off') . "</td><td>" . ($logErrors ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . "</td></tr>";
        echo "<tr><td>display_errors</td><td>" . ($displayErrors ? 'On' : 'Off') . "</td><td>" . (!$displayErrors ? '<span class="success">✓ (Off is good)</span>' : '<span class="warning">⚠ Should be Off</span>') . "</td></tr>";
        echo "</table>";

        if ($errorLog) {
            echo "<div class='info'><strong>Error Log Location:</strong> $errorLog</div>";
        } else {
            echo "<div class='info'><strong>Default Error Log:</strong> Check your web server error log (Apache: error.log, Nginx: error.log)</div>";
        }
        ?>

        <h2>2. Stripe Configuration</h2>
        <?php
        try {
            $db = new Database();
            $stmt = $db->query("SELECT id, company_name, stripe_mode, stripe_publishable_key, stripe_secret_key FROM companies LIMIT 5");
            $companies = $stmt->fetchAll();

            if (empty($companies)) {
                echo "<p class='error'>No companies found in database</p>";
            } else {
                echo "<table>";
                echo "<tr><th>Company</th><th>Mode</th><th>Publishable Key</th><th>Secret Key</th><th>Status</th></tr>";

                foreach ($companies as $company) {
                    $mode = $company['stripe_mode'] ?? 'test';
                    $pubKey = $company['stripe_publishable_key'] ?? '';
                    $secKey = $company['stripe_secret_key'] ?? '';

                    $pubKeyPrefix = substr($pubKey, 0, 7);
                    $secKeyPrefix = substr($secKey, 0, 7);

                    $pubKeyMasked = $pubKey ? $pubKeyPrefix . '...' . substr($pubKey, -4) : 'Not set';
                    $secKeyMasked = $secKey ? $secKeyPrefix . '...' . substr($secKey, -4) : 'Not set';

                    // Validate keys match mode
                    $status = '';
                    if ($mode === 'test') {
                        if (strpos($pubKey, 'pk_test_') === 0 && strpos($secKey, 'sk_test_') === 0) {
                            $status = '<span class="success">✓ Valid test keys</span>';
                        } else {
                            $status = '<span class="error">✗ Invalid test keys</span>';
                        }
                    } else {
                        if (strpos($pubKey, 'pk_live_') === 0 && strpos($secKey, 'sk_live_') === 0) {
                            $status = '<span class="success">✓ Valid live keys</span>';
                        } else {
                            $status = '<span class="error">✗ Invalid live keys</span>';
                        }
                    }

                    $modeBadge = $mode === 'test' ? '<span class="badge badge-test">TEST</span>' : '<span class="badge badge-live">LIVE</span>';

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($company['company_name']) . "</td>";
                    echo "<td>$modeBadge</td>";
                    echo "<td>$pubKeyMasked</td>";
                    echo "<td>$secKeyMasked</td>";
                    echo "<td>$status</td>";
                    echo "</tr>";
                }

                echo "</table>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>

        <h2>3. Test Stripe API Connection</h2>
        <?php
        if (!empty($companies)) {
            $testCompany = $companies[0];
            $testSecretKey = $testCompany['stripe_secret_key'] ?? '';

            if ($testSecretKey) {
                echo "<p>Testing connection with company: <strong>" . htmlspecialchars($testCompany['company_name']) . "</strong></p>";

                // Test API call to retrieve account info
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/balance');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_USERPWD, $testSecretKey . ':');
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Stripe-Version: 2023-10-16']);

                $result = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                echo "<table>";
                echo "<tr><th>Check</th><th>Result</th></tr>";
                echo "<tr><td>HTTP Status</td><td>" . $httpCode . "</td></tr>";

                if ($curlError) {
                    echo "<tr><td>cURL Error</td><td class='error'>" . htmlspecialchars($curlError) . "</td></tr>";
                } else {
                    echo "<tr><td>cURL Error</td><td class='success'>None</td></tr>";
                }

                $response = json_decode($result, true);

                if ($httpCode === 200 && isset($response['object']) && $response['object'] === 'balance') {
                    echo "<tr><td>API Connection</td><td class='success'>✓ Success</td></tr>";
                    echo "<tr><td>Available Balance</td><td>" . number_format($response['available'][0]['amount'] / 100, 2) . " " . strtoupper($response['available'][0]['currency']) . "</td></tr>";
                } else {
                    echo "<tr><td>API Connection</td><td class='error'>✗ Failed</td></tr>";
                    if (isset($response['error'])) {
                        echo "<tr><td>Error Type</td><td>" . htmlspecialchars($response['error']['type']) . "</td></tr>";
                        echo "<tr><td>Error Message</td><td>" . htmlspecialchars($response['error']['message']) . "</td></tr>";
                    }
                }

                echo "</table>";

                echo "<h3>Full Response:</h3>";
                echo "<pre>" . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT)) . "</pre>";
            } else {
                echo "<p class='warning'>No Stripe secret key configured for testing</p>";
            }
        }
        ?>

        <h2>4. Recent Error Log Entries</h2>
        <div class='info'>
            <p><strong>To view Stripe payment errors:</strong></p>
            <ol>
                <li>Try making a test payment</li>
                <li>Check the error log file shown above</li>
                <li>Look for entries starting with "Stripe Payment Intent"</li>
            </ol>
            <p>Common test cards:</p>
            <ul>
                <li><strong>Success:</strong> 4242 4242 4242 4242</li>
                <li><strong>Decline:</strong> 4000 0000 0000 0002</li>
                <li><strong>Insufficient funds:</strong> 4000 0000 0000 9995</li>
            </ul>
        </div>

        <h2>5. Recommendations</h2>
        <div class='info'>
            <ul>
                <li>✓ Error logging is now enhanced with detailed Stripe API responses</li>
                <li>✓ All payment responses return JSON only (no HTML/PHP errors mixed in)</li>
                <li>✓ Test key validation added to prevent using live keys in test mode</li>
                <li>✓ cURL errors are now properly caught and logged</li>
            </ul>
            <p><strong>Next steps:</strong></p>
            <ol>
                <li>Verify your Stripe keys are correct in Settings</li>
                <li>Try a test payment with card 4242 4242 4242 4242</li>
                <li>Check the error log for detailed error messages</li>
                <li>If errors persist, share the log entries for further debugging</li>
            </ol>
        </div>
    </div>
</body>

</html>