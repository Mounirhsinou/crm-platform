<?php
/**
 * Clients Controller
 * Handles client CRUD operations
 */

class ClientsController extends Controller
{
    private $clientModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->clientModel = $this->model('Client');
    }

    /**
     * List all clients
     */
    public function index()
    {
        $this->requirePermission('clients', 'view');
        $companyId = Session::get('company_id');
        $search = $_GET['search'] ?? '';

        if ($search) {
            $clients = $this->clientModel->searchByCompany($companyId, $search);
            // Optional: apply date filter to search results if you want, 
            // but usually search implies seeking any record. 
            // The user said "all data, totals, lists... should update", so let's be strict or lenient.
            // For now, let's keep search broad as it's often used to find a specific person regardless of date.
        } else {
            $clients = $this->clientModel->findAllFiltered(['company_id' => $companyId], 'created_at DESC');
        }

        $this->view('clients/index', [
            'clients' => $clients,
            'search' => $search
        ]);
    }

    /**
     * View single client
     */
    public function show($id)
    {
        $this->requirePermission('clients', 'view');
        $companyId = Session::get('company_id');
        $client = $this->clientModel->getWithRelationsByCompany($id, $companyId);

        if (!$client) {
            $this->setFlash('error', 'Client not found');
            $this->redirect('clients');
        }

        $this->view('clients/view', ['client' => $client]);
    }

    /**
     * Create new client
     */
    public function create()
    {
        $this->requirePermission('clients', 'create');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        } else {
            $this->view('clients/form', [
                'client' => null,
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Handle create form submission
     */
    private function handleCreate()
    {
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('clients/create');
        }

        $data = [
            'name' => Security::sanitize($_POST['name'] ?? ''),
            'company' => Security::sanitize($_POST['company'] ?? ''),
            'phone' => Security::sanitize($_POST['phone'] ?? ''),
            'email' => Security::sanitize($_POST['email'] ?? ''),
            'default_price' => (float) ($_POST['default_price'] ?? 0),
            'notes' => Security::sanitize($_POST['notes'] ?? '')
        ];

        $validator = new Validator();
        $validator->required('name', $data['name'], 'Name')
            ->email('email', $data['email'], 'Email')
            ->phone('phone', $data['phone'], 'Phone');

        if ($validator->fails()) {
            $this->view('clients/form', [
                'client' => $data,
                'errors' => $validator->getErrors(),
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        $companyId = Session::get('company_id');
        $userId = $this->getUserId();
        $data['company_id'] = $companyId;
        $data['user_id'] = $userId;
        $clientId = $this->clientModel->insert($data);
        // Model handle company_id if defined in insert? No, I need to pass it.
        // Wait, Client model extends Model. insert() uses fillable? 
        // Actually, Client model uses $this->clientModel->create($data) which is a custom method? 
        // Let's check Client.php again. It doesn't have create(). It has insert() from Model.php?
        // Wait, line 106 says $clientId = $this->clientModel->create($data);
        // But Client.php doesn't have create(). Neither does Model.php usually (it has insert).
        // Let me check Model.php.

        if ($clientId) {
            // Log activity
            Activity::logClientCreated($companyId, $userId, $clientId, $data['name']);

            // --- START AUTOMATION ---
            $initialPrice = isset($_POST['initial_price']) ? (float) $_POST['initial_price'] : 0;
            if ($initialPrice > 0) {
                $dealModel = $this->model('Deal');
                $invoiceModel = $this->model('Invoice');

                // 1. Create Deal
                $dealData = [
                    'user_id' => $this->getUserId(),
                    'client_id' => $clientId,
                    'title' => _t('deal_for') . ' ' . $data['name'],
                    'amount' => $initialPrice,
                    'status' => 'new'
                ];
                $dealId = $dealModel->create($dealData);

                if ($dealId) {
                    Activity::logDealCreated($companyId, $userId, $clientId, $dealData['title'], $initialPrice);

                    // 2. Create Invoice
                    $invoiceNumber = $invoiceModel->generateInvoiceNumber($this->getUserId());
                    $invoiceData = [
                        'user_id' => $this->getUserId(),
                        'client_id' => $clientId,
                        'deal_id' => $dealId,
                        'invoice_number' => $invoiceNumber,
                        'amount' => $initialPrice,
                        'status' => 'unpaid'
                    ];
                    $invoiceId = $invoiceModel->create($invoiceData);

                    if ($invoiceId) {
                        Activity::logInvoiceCreated($companyId, $userId, $clientId, $invoiceNumber, $initialPrice);
                    }
                }
            }
            // --- END AUTOMATION ---

            $this->setFlash('success', 'Client created successfully');
            $this->redirect('clients/show/' . $clientId);
        } else {
            $this->setFlash('error', 'Failed to create client');
            $this->redirect('clients/create');
        }
    }

    /**
     * Edit client
     */
    public function edit($id)
    {
        $this->requirePermission('clients', 'edit');
        $companyId = Session::get('company_id');
        $client = $this->clientModel->findOne(['id' => $id, 'company_id' => $companyId]);

        if (!$client) {
            $this->setFlash('error', 'Client not found');
            $this->redirect('clients');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($id);
        } else {
            $this->view('clients/form', [
                'client' => $client,
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Handle edit form submission
     */
    private function handleEdit($id)
    {
        $companyId = Session::get('company_id');
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('clients/edit/' . $id);
        }

        $data = [
            'name' => Security::sanitize($_POST['name'] ?? ''),
            'company' => Security::sanitize($_POST['company'] ?? ''),
            'phone' => Security::sanitize($_POST['phone'] ?? ''),
            'email' => Security::sanitize($_POST['email'] ?? ''),
            'default_price' => (float) ($_POST['default_price'] ?? 0),
            'notes' => Security::sanitize($_POST['notes'] ?? '')
        ];

        $validator = new Validator();
        $validator->required('name', $data['name'], 'Name')
            ->email('email', $data['email'], 'Email')
            ->phone('phone', $data['phone'], 'Phone');

        if ($validator->fails()) {
            $data['id'] = $id;
            $this->view('clients/form', [
                'client' => $data,
                'errors' => $validator->getErrors(),
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        if ($this->clientModel->update($id, $data)) {
            // Log activity
            Activity::logClientUpdated($companyId, $this->getUserId(), $id, $data['name']);

            $this->setFlash('success', 'Client updated successfully');
            $this->redirect('clients/show/' . $id);
        } else {
            $this->setFlash('error', 'Failed to update client');
            $this->redirect('clients/edit/' . $id);
        }
    }

    /**
     * Delete client
     */
    public function delete($id)
    {
        $this->requirePermission('clients', 'delete');
        $companyId = Session::get('company_id');
        $client = $this->clientModel->findOne(['id' => $id, 'company_id' => $companyId]);

        if (!$client) {
            $this->setFlash('error', 'Client not found');
            $this->redirect('clients');
        }

        if ($this->clientModel->delete($id)) {
            $this->setFlash('success', 'Client deleted successfully');
        } else {
            $this->setFlash('error', 'Failed to delete client');
        }

        $this->redirect('clients');
    }

    /**
     * Get client amount info (AJAX)
     * Returns either the latest deal amount or the default price
     */
    public function getAmountInfo($id)
    {
        $this->requirePermission('clients', 'view');
        header('Content-Type: application/json');
        $companyId = Session::get('company_id');

        $client = $this->clientModel->findOne(['id' => $id, 'company_id' => $companyId]);
        if (!$client) {
            echo json_encode(['success' => false, 'message' => 'Client not found']);
            exit;
        }

        $dealModel = $this->model('Deal');
        $latestDeal = $dealModel->getLatestByClient($id, $companyId);

        $amount = 0.00;
        if ($latestDeal) {
            $amount = $latestDeal['amount'];
        } elseif ($client['default_price'] > 0) {
            $amount = $client['default_price'];
        }

        echo json_encode([
            'success' => true,
            'amount' => number_format($amount, 2, '.', '')
        ]);
        exit;
    }

    /**
     * Export clients to CSV
     */
    public function export()
    {
        $this->requirePermission('clients', 'export');
        $companyId = Session::get('company_id');
        $clients = $this->clientModel->getByCompany($companyId);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="clients_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Name', 'Company', 'Email', 'Phone', 'Address', 'Default Price', 'Notes', 'Created At']);

        foreach ($clients as $client) {
            fputcsv($output, [
                $client['name'],
                $client['company'],
                $client['email'],
                $client['phone'],
                $client['address'],
                $client['default_price'],
                $client['notes'],
                $client['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Import clients (Step 1: Upload, Step 2: Mapping)
     */
    public function import()
    {
        $this->requirePermission('clients', 'import');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Invalid request');
                $this->redirect('clients/import');
            }

            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                $this->setFlash('error', 'Please upload a valid CSV file');
                $this->redirect('clients/import');
            }

            $extension = pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION);
            if (strtolower($extension) !== 'csv') {
                $this->setFlash('error', 'Invalid file type. Please upload a CSV file.');
                $this->redirect('clients/import');
            }

            // Read headers for mapping
            $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
            $headers = fgetcsv($file);
            fclose($file);

            if (!$headers) {
                $this->setFlash('error', 'The file is empty or invalid.');
                $this->redirect('clients/import');
            }

            // Save file temporarily for processing
            $tempName = 'import_' . $this->getUserId() . '_' . time() . '.csv';
            $tempPath = ROOT_PATH . '/tmp/' . $tempName;
            if (!is_dir(ROOT_PATH . '/tmp'))
                mkdir(ROOT_PATH . '/tmp');
            move_uploaded_file($_FILES['csv_file']['tmp_name'], $tempPath);

            $this->view('clients/import_mapping', [
                'headers' => $headers,
                'temp_file' => $tempName,
                'duplicate_handling' => $_POST['duplicate_handling'] ?? 'skip',
                'csrf_token' => Security::generateCsrfToken()
            ]);
        } else {
            $this->view('clients/import', [
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Process the import with column mapping
     */
    public function processImport()
    {
        $this->requirePermission('clients', 'import');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('clients/import');
        }

        $companyId = Session::get('company_id');
        $userId = $this->getUserId();
        $mapping = $_POST['mapping'] ?? [];
        $tempFile = Security::sanitize($_POST['temp_file']);
        $duplicateHandling = $_POST['duplicate_handling'] ?? 'skip';
        $tempPath = ROOT_PATH . '/tmp/' . $tempFile;

        if (!file_exists($tempPath)) {
            $this->setFlash('error', 'Session expired or file not found.');
            $this->redirect('clients/import');
        }

        $file = fopen($tempPath, 'r');
        fgetcsv($file); // Skip headers

        $stats = ['success' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];
        $errorList = [];

        while (($row = fgetcsv($file)) !== false) {
            $data = [];
            foreach ($mapping as $field => $index) {
                if ($index !== '' && isset($row[$index])) {
                    $data[$field] = trim($row[$index]);
                }
            }

            if (empty($data['name'])) {
                $stats['errors']++;
                $errorList[] = "Row omitted: Missing Name";
                continue;
            }

            // Check for duplicates
            $existing = null;
            if (!empty($data['email'])) {
                $existing = $this->clientModel->findOne(['company_id' => $companyId, 'email' => $data['email']]);
            }
            if (!$existing && !empty($data['phone'])) {
                $existing = $this->clientModel->findOne(['company_id' => $companyId, 'phone' => $data['phone']]);
            }

            if ($existing) {
                if ($duplicateHandling === 'skip') {
                    $stats['skipped']++;
                    continue;
                } elseif ($duplicateHandling === 'update') {
                    $this->clientModel->update($existing['id'], $data);
                    $stats['updated']++;
                } else {
                    // Create new anyway
                    $data['company_id'] = $companyId;
                    $this->clientModel->create($data);
                    $stats['success']++;
                }
            } else {
                $data['company_id'] = $companyId;
                $this->clientModel->create($data);
                $stats['success']++;
            }
        }

        fclose($file);
        unlink($tempPath);

        $this->view('clients/import_results', [
            'stats' => $stats,
            'errors' => $errorList
        ]);
    }
}
