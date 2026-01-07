<?php
/**
 * FollowUps Controller
 * Handles follow-up CRUD operations
 */

class FollowupsController extends Controller
{
    private $followUpModel;
    private $clientModel;
    private $dealModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->followUpModel = $this->model('FollowUp');
        $this->clientModel = $this->model('Client');
        $this->dealModel = $this->model('Deal');
    }

    /**
     * List all follow-ups
     */
    public function index()
    {
        $this->requirePermission('followups', 'view');
        $companyId = Session::get('company_id');
        $status = $_GET['status'] ?? null;

        $followups = $this->followUpModel->getWithRelationsByCompany($companyId, $status);

        $this->view('followups/index', [
            'followups' => $followups,
            'current_status' => $status
        ]);
    }

    /**
     * Create new follow-up
     */
    public function create()
    {
        $this->requirePermission('followups', 'create');
        $companyId = Session::get('company_id');
        $clients = $this->clientModel->getByCompany($companyId);
        $deals = $this->dealModel->getByCompany($companyId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        } else {
            $this->view('followups/form', [
                'followup' => null,
                'clients' => $clients,
                'deals' => $deals,
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
        $deals = $this->dealModel->getByCompany($companyId);

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('followups/create');
        }

        $data = [
            'notes' => Security::sanitize($_POST['notes'] ?? ''),
            'client_id' => !empty($_POST['client_id']) ? (int) $_POST['client_id'] : null,
            'deal_id' => !empty($_POST['deal_id']) ? (int) $_POST['deal_id'] : null,
            'followup_date' => Security::sanitize($_POST['followup_date'] ?? ''),
            'status' => Security::sanitize($_POST['status'] ?? 'pending')
        ];

        $validator = new Validator();
        $validator->required('notes', $data['notes'], 'Notes')
            ->required('followup_date', $data['followup_date'], 'Follow-up Date')
            ->date('followup_date', $data['followup_date']);

        if ($validator->fails()) {
            $this->view('followups/form', [
                'followup' => $data,
                'clients' => $clients,
                'deals' => $deals,
                'errors' => $validator->getErrors(),
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        $data['company_id'] = $companyId;
        $data['user_id'] = $this->getUserId();
        $followupId = $this->followUpModel->insert($data);

        if ($followupId) {
            $this->setFlash('success', 'Follow-up created successfully');
            $this->redirect('followups');
        } else {
            $this->setFlash('error', 'Failed to create follow-up');
            $this->redirect('followups/create');
        }
    }

    /**
     * Edit follow-up
     */
    public function edit($id)
    {
        $this->requirePermission('followups', 'edit');
        $companyId = Session::get('company_id');
        $followup = $this->followUpModel->findOne(['id' => $id, 'company_id' => $companyId]);
        $clients = $this->clientModel->getByCompany($companyId);
        $deals = $this->dealModel->getByCompany($companyId);

        if (!$followup) {
            $this->setFlash('error', 'Follow-up not found');
            $this->redirect('followups');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($id);
        } else {
            $this->view('followups/form', [
                'followup' => $followup,
                'clients' => $clients,
                'deals' => $deals,
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
        $deals = $this->dealModel->getByCompany($companyId);

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('followups/edit/' . $id);
        }

        $data = [
            'notes' => Security::sanitize($_POST['notes'] ?? ''),
            'client_id' => !empty($_POST['client_id']) ? (int) $_POST['client_id'] : null,
            'deal_id' => !empty($_POST['deal_id']) ? (int) $_POST['deal_id'] : null,
            'followup_date' => Security::sanitize($_POST['followup_date'] ?? ''),
            'status' => Security::sanitize($_POST['status'] ?? 'pending')
        ];

        $validator = new Validator();
        $validator->required('notes', $data['notes'], 'Notes')
            ->required('followup_date', $data['followup_date'], 'Follow-up Date')
            ->date('followup_date', $data['followup_date']);

        if ($validator->fails()) {
            $data['id'] = $id;
            $this->view('followups/form', [
                'followup' => $data,
                'clients' => $clients,
                'deals' => $deals,
                'errors' => $validator->getErrors(),
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        if ($this->followUpModel->update($id, $data)) {
            $this->setFlash('success', 'Follow-up updated successfully');
            $this->redirect('followups');
        } else {
            $this->setFlash('error', 'Failed to update follow-up');
            $this->redirect('followups/edit/' . $id);
        }
    }

    /**
     * Delete follow-up
     */
    public function delete($id)
    {
        $this->requirePermission('followups', 'delete');
        $companyId = Session::get('company_id');
        $followup = $this->followUpModel->findOne(['id' => $id, 'company_id' => $companyId]);

        if (!$followup) {
            $this->setFlash('error', 'Follow-up not found');
            $this->redirect('followups');
        }

        if ($this->followUpModel->delete($id)) {
            $this->setFlash('success', 'Follow-up deleted successfully');
        } else {
            $this->setFlash('error', 'Failed to delete follow-up');
        }

        $this->redirect('followups');
    }

    /**
     * Mark follow-up as done
     */
    public function markDone($id)
    {
        $this->requirePermission('followups', 'edit');
        $companyId = Session::get('company_id');
        $followup = $this->followUpModel->findOne(['id' => $id, 'company_id' => $companyId]);

        if (!$followup) {
            $this->setFlash('error', 'Follow-up not found');
            $this->redirect('followups');
        }

        if ($this->followUpModel->update($id, ['status' => 'done'])) {
            $this->setFlash('success', 'Follow-up marked as done');
        } else {
            $this->setFlash('error', 'Failed to update follow-up');
        }

        $this->redirect('followups');
    }
}
