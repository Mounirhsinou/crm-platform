<?php
/**
 * Clear Demo Data Script
 * This script removes test/demo data from the database.
 * 
 * WARNING: This is a destructive operation.
 */

require_once 'config/config.php';
require_once 'app/core/Database.php';

// Security check: only allow if logged in as owner or from CLI
if (php_sapi_name() !== 'cli') {
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['role_slug'] !== 'owner') {
        die("Unauthorized access.");
    }
}

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();

    echo "Cleaning up database...\n";

    // 1. Identify Demo Company (usually ID 1)
    $demoCompanyId = 1;

    // 2. Delete related records for the demo company
    // Tables to clear entirely (except settings if needed)
    $tablesToClearByCompany = [
        'payments',
        'invoice_items',
        'invoices',
        'tasks',
        'deals',
        'followups',
        'leads',
        'activities',
        'activity_logs',
        'email_logs',
        'message_logs',
        'payment_templates'
    ];

    foreach ($tablesToClearByCompany as $table) {
        $count = $db->exec("DELETE FROM `$table` WHERE company_id = $demoCompanyId");
        echo "Deleted $count records from `$table`.\n";
    }

    // 3. Delete clients for demo company
    $count = $db->exec("DELETE FROM `clients` WHERE company_id = $demoCompanyId");
    echo "Deleted $count clients.\n";

    // 4. Reset companies table if needed (be careful not to delete the active company record itself if it's being used)
    // For a truly fresh install, you might keep the company but clear its name if desired.
    $db->exec("UPDATE `companies` SET `company_name` = 'My Workspace', `logo_path` = NULL WHERE `id` = $demoCompanyId");
    echo "Reset company name to 'My Workspace'.\n";

    $db->commit();
    echo "\nSuccess: Database has been cleared of demo data.\n";

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "ERROR: " . $e->getMessage();
}
