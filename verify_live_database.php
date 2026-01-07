<?php
/**
 * Database Inspector Script
 * Helps identify phantom data on the live server.
 */

require_once 'config/config.php';
require_once 'app/core/Database.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    die("Please log in first.");
}

$db = Database::getInstance()->getConnection();
$companyId = $_SESSION['company_id'];

echo "<h1>Database Inspection</h1>";
echo "<p>Logged in Company ID: <strong>$companyId</strong></p>";

$tables = ['companies', 'users', 'clients', 'deals', 'invoices', 'payments', 'payment_templates'];

foreach ($tables as $table) {
    echo "<h2>Table: $table</h2>";

    // Count total
    $total = $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    echo "Total records in DB: <strong>$total</strong><br>";

    // Count for current company (if applicable)
    $hasCompanyId = false;
    $result = $db->query("SHOW COLUMNS FROM `$table` LIKE 'company_id'");
    if ($result->rowCount() > 0) {
        $hasCompanyId = true;
        $companyCount = $db->query("SELECT COUNT(*) FROM `$table` WHERE company_id = $companyId")->fetchColumn();
        echo "Records for your company ($companyId): <strong>$companyCount</strong><br>";

        $otherCount = $total - $companyCount;
        echo "Records for OTHER companies: <strong>$otherCount</strong><br>";
    }

    // Specific sums for revenue tables
    if ($table === 'invoices') {
        $sum = $db->query("SELECT SUM(amount) FROM `$table` WHERE company_id = $companyId AND status = 'paid'")->fetchColumn();
        echo "Sum of PAID invoices for your company: <strong>" . number_format($sum, 2) . "</strong><br>";

        $globalSum = $db->query("SELECT SUM(amount) FROM `$table` WHERE status = 'paid'")->fetchColumn();
        echo "Global sum of PAID invoices: <strong>" . number_format($globalSum, 2) . "</strong><br>";
    }

    if ($table === 'payments') {
        $sum = $db->query("SELECT SUM(amount) FROM `$table` WHERE company_id = $companyId")->fetchColumn();
        echo "Sum of payments for your company: <strong>" . number_format($sum, 2) . "</strong><br>";

        $globalSum = $db->query("SELECT SUM(amount) FROM `$table`")->fetchColumn();
        echo "Global sum of payments: <strong>" . number_format($globalSum, 2) . "</strong><br>";
    }

    if ($table === 'deals') {
        $sum = $db->query("SELECT SUM(amount) FROM `$table` WHERE company_id = $companyId AND status = 'completed'")->fetchColumn();
        echo "Sum of COMPLETED deals for your company: <strong>" . number_format($sum, 2) . "</strong><br>";
    }
}

echo "<h2>Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<hr><p>Please run <code>clear_demo_data.php</code> if you see unexpected records above.</p>";
