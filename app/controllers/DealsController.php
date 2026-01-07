<?php
/**
 * Deals Controller
 * Handles deal CRUD operations
 */

class DealsController extends Controller
{
    private $dealModel;
    private $clientModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->dealModel = $this->model('Deal');
        $this->clientModel = $this->model('Client');
    }

    /**
     * List all deals
     */
    public function index()
    {
        $this->requirePermission('deals', 'view');
        $companyId = Session::get('company_id');
        $status = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? '';

        if ($search) {
            $deals = $this->dealModel->searchByCompany($companyId, $search);
        } else {
            $deals = $this->dealModel->getWithClientsByCompany($companyId, $status);
        }

        $this->view('deals/index', [
            'deals' => $deals,
            'current_status' => $status,
            'search' => $search
        ]);
    }

    /**
     * View single deal
     */
    public function show($id)
    {
        $this->requirePermission('deals', 'view');
        $companyId = Session::get('company_id');
        $deal = $this->dealModel->getWithClientByCompany($id, $companyId);

        if (!$deal) {
            $this->setFlash('error', 'Deal not found');
            $this->redirect('deals');
        }

        // Generate dynamic recommendations based on deal status
        $recommendations = $this->getRecommendations($deal);

        $this->view('deals/view', [
            'deal' => $deal,
            'recommendations' => $recommendations
        ]);
    }

    /**
     * Get context-aware recommendations based on deal status
     */
    private function getRecommendations($deal)
    {
        $status = $deal['status'];
        $clientId = $deal['client_id'];
        $dealId = $deal['id'];

        switch ($status) {
            case 'new':
                return [
                    'message' => 'This opportunity is in the <strong>initial phase</strong>. Focus on gathering requirements and establishing clear communication channels with the client.',
                    'actions' => [
                        [
                            'text' => 'Add Requirements',
                            'url' => APP_URL . '/deals/edit/' . $dealId,
                            'icon' => 'bi-pencil',
                            'class' => 'btn-primary'
                        ],
                        [
                            'text' => 'Schedule First Call',
                            'url' => APP_URL . '/followups/create?client_id=' . $clientId . '&deal_id=' . $dealId,
                            'icon' => 'bi-telephone',
                            'class' => 'btn-white border'
                        ]
                    ]
                ];

            case 'in_progress':
                return [
                    'message' => 'This deal is <strong>actively progressing</strong>. Ensure all specifications are aligned with client expectations and maintain regular communication.',
                    'actions' => [
                        [
                            'text' => 'Review Specs',
                            'url' => APP_URL . '/deals/edit/' . $dealId,
                            'icon' => 'bi-file-text',
                            'class' => 'btn-white border'
                        ],
                        [
                            'text' => 'Update Progress',
                            'url' => APP_URL . '/deals/edit/' . $dealId,
                            'icon' => 'bi-arrow-repeat',
                            'class' => 'btn-white border'
                        ],
                        [
                            'text' => 'Create Follow-up',
                            'url' => APP_URL . '/followups/create?client_id=' . $clientId . '&deal_id=' . $dealId,
                            'icon' => 'bi-calendar-plus',
                            'class' => 'btn-primary'
                        ]
                    ]
                ];

            case 'completed':
                return [
                    'message' => 'This deal has been <strong>successfully completed</strong>. Time to finalize billing and gather client feedback for future improvements.',
                    'actions' => [
                        [
                            'text' => 'Generate Invoice',
                            'url' => APP_URL . '/invoices/create?client_id=' . $clientId,
                            'icon' => 'bi-file-earmark-diff',
                            'class' => 'btn-primary'
                        ],
                        [
                            'text' => 'Request Feedback',
                            'url' => APP_URL . '/followups/create?client_id=' . $clientId . '&deal_id=' . $dealId,
                            'icon' => 'bi-chat-left-quote',
                            'class' => 'btn-white border'
                        ]
                    ]
                ];

            default:
                return null;
        }
    }

    /**
     * Create new deal
     */
    public function create()
    {
        $this->requirePermission('deals', 'create');
        $companyId = Session::get('company_id');
        $clients = $this->clientModel->getByCompany($companyId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        } else {
            $this->view('deals/form', [
                'deal' => null,
                'clients' => $clients,
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Handle create form submission
     */
    private function handleCreate()
    {
        $companyId = Session::get('company_id');
        $clients = $this->clientModel->getByCompany($companyId);

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('deals/create');
        }

        $data = [
            'title' => Security::sanitize($_POST['title'] ?? ''),
            'client_id' => (int) ($_POST['client_id'] ?? 0),
            'amount' => (float) ($_POST['amount'] ?? 0),
            'status' => Security::sanitize($_POST['status'] ?? 'new')
        ];

        $validator = new Validator();
        $validator->required('title', $data['title'], 'Title')
            ->required('client_id', $data['client_id'], 'Client')
            ->numeric('amount', $data['amount'], 'Amount');

        if ($validator->fails()) {
            $this->view('deals/form', [
                'deal' => $data,
                'clients' => $clients,
                'errors' => $validator->getErrors(),
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        $userId = $this->getUserId();
        $data['company_id'] = $companyId;
        $data['user_id'] = $userId;
        $dealId = $this->dealModel->insert($data);

        if ($dealId) {
            $this->setFlash('success', 'Deal created successfully');
            $this->redirect('deals/show/' . $dealId);
        } else {
            $this->setFlash('error', 'Failed to create deal');
            $this->redirect('deals/create');
        }
    }

    /**
     * Edit deal
     */
    public function edit($id)
    {
        $this->requirePermission('deals', 'edit');
        $companyId = Session::get('company_id');
        $deal = $this->dealModel->findOne(['id' => $id, 'company_id' => $companyId]);
        $clients = $this->clientModel->getByCompany($companyId);

        if (!$deal) {
            $this->setFlash('error', 'Deal not found');
            $this->redirect('deals');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($id);
        } else {
            $this->view('deals/form', [
                'deal' => $deal,
                'clients' => $clients,
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
        $clients = $this->clientModel->getByCompany($companyId);

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('deals/edit/' . $id);
        }

        $data = [
            'title' => Security::sanitize($_POST['title'] ?? ''),
            'client_id' => (int) ($_POST['client_id'] ?? 0),
            'amount' => (float) ($_POST['amount'] ?? 0),
            'status' => Security::sanitize($_POST['status'] ?? 'new')
        ];

        $validator = new Validator();
        $validator->required('title', $data['title'], 'Title')
            ->required('client_id', $data['client_id'], 'Client')
            ->numeric('amount', $data['amount'], 'Amount');

        if ($validator->fails()) {
            $data['id'] = $id;
            $this->view('deals/form', [
                'deal' => $data,
                'clients' => $clients,
                'errors' => $validator->getErrors(),
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        if ($this->dealModel->update($id, $data)) {
            $this->setFlash('success', 'Deal updated successfully');
            $this->redirect('deals/show/' . $id);
        } else {
            $this->setFlash('error', 'Failed to update deal');
            $this->redirect('deals/edit/' . $id);
        }
    }

    /**
     * Delete deal
     */
    public function delete($id)
    {
        $this->requirePermission('deals', 'delete');
        $companyId = Session::get('company_id');
        $deal = $this->dealModel->findOne(['id' => $id, 'company_id' => $companyId]);

        if (!$deal) {
            $this->setFlash('error', 'Deal not found');
            $this->redirect('deals');
        }

        if ($this->dealModel->delete($id)) {
            $this->setFlash('success', 'Deal deleted successfully');
        } else {
            $this->setFlash('error', 'Failed to delete deal');
        }

        $this->redirect('deals');
    }

    /**
     * Get deals by client (AJAX)
     */
    public function getByClient($clientId)
    {
        header('Content-Type: application/json');
        $companyId = Session::get('company_id');

        $deals = $this->dealModel->findAll(['client_id' => $clientId, 'company_id' => $companyId]);

        echo json_encode([
            'success' => true,
            'deals' => $deals
        ]);
        exit;
    }

    /**
     * Export deals to CSV
     */
    public function export()
    {
        $this->requirePermission('deals', 'export');
        $companyId = Session::get('company_id');
        $deals = $this->dealModel->getWithClientsByCompany($companyId);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="deals_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Title', 'Client', 'Amount', 'Status', 'Created At']);

        foreach ($deals as $deal) {
            fputcsv($output, [
                $deal['title'],
                $deal['client_name'] ?? 'N/A',
                $deal['amount'],
                ucfirst(str_replace('_', ' ', $deal['status'])),
                $deal['created_at']
            ]);
        }

        fclose($output);
        exit;
    }
}
