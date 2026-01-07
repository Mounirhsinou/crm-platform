<?php
/**
 * Checkout Controller
 * Dedicated management for integrated checkout links and lead conversion stats.
 */

class CheckoutController extends Controller
{
    private $templateModel;
    private $leadModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->templateModel = $this->model('PaymentTemplate');
        $this->leadModel = $this->model('Lead');
    }

    /**
     * Checkout Dashboard - Statistics and Links
     */
    public function index()
    {
        $this->requirePermission('reports', 'view');
        $companyId = Session::get('company_id');

        // Defensive: Ensure company_id is valid
        if (!$companyId || !is_numeric($companyId)) {
            error_log('CheckoutController: Invalid company_id in session');
            $this->redirect('auth/logout');
            return;
        }

        // Integrate with global DateFilter
        require_once APP_PATH . '/helpers/DateFilter.php';
        $range = DateFilter::getRange();

        $startDate = $range['start'] ? date('Y-m-d', strtotime($range['start'])) : null;
        $endDate = $range['end'] ? date('Y-m-d', strtotime($range['end'])) : null;
        $filter = $range['filter'];

        // Handle view filter (All Links vs Recent)
        $view = $_GET['view'] ?? 'all';

        $links = $this->templateModel->getUnifiedLinks($companyId);
        $filteredStats = $this->templateModel->getFilteredStats($companyId);

        // Apply "Recent" filter if selected (last 30 days)
        if ($view === 'recent') {
            $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));
            $links = array_filter($links, function ($link) use ($thirtyDaysAgo) {
                return $link['created_at'] >= $thirtyDaysAgo;
            });
        }

        // Enhance links with filtered lead stats
        foreach ($links as &$link) {
            if ($link['link_type'] === 'Client-filled') {
                $link['leads_count'] = $this->leadModel->countFiltered([
                    'company_id' => $companyId,
                    'source' => 'template',
                    'source_id' => $link['original_id']
                ], $startDate, $endDate);
            } else {
                $link['leads_count'] = 1;
            }
        }

        $this->view('checkout/index', [
            'pageTitle' => 'Checkout',
            'links' => $links,
            'stats' => $filteredStats,
            'filter' => $filter,
            'viewFilter' => $view,  // Renamed from 'view' to avoid conflict with Controller::view() method
            'startDate' => $startDate,
            'endDate' => $endDate,
            'csrf_token' => Security::generateCsrfToken()
        ]);
    }

    /**
     * Create a new checkout link
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Invalid request');
                $this->redirect('checkout');
            }

            $companyId = Session::get('company_id');

            // Process fields
            $fieldSettings = $_POST['fields'] ?? [];
            $settings = [];
            $availableFields = ['name', 'email', 'phone', 'address', 'city', 'country', 'company', 'notes'];

            foreach ($availableFields as $field) {
                $settings[$field] = [
                    'visible' => isset($fieldSettings[$field]['visible']) ? true : false,
                    'required' => isset($fieldSettings[$field]['required']) ? true : false
                ];

                if ($field === 'name' || $field === 'email') {
                    $settings[$field]['visible'] = true;
                }
            }

            $data = [
                'company_id' => $companyId,
                'token' => bin2hex(random_bytes(32)),
                'title' => Security::sanitize($_POST['title'] ?? ''),
                'description' => Security::sanitize($_POST['description'] ?? ''),
                'amount' => (float) ($_POST['amount'] ?? 0),
                'allow_paypal' => isset($_POST['allow_paypal']) ? 1 : 0,
                'allow_stripe' => isset($_POST['allow_stripe']) ? 1 : 0,
                'checkout_settings' => json_encode($settings),
                'payment_closed' => 0
            ];

            if ($this->templateModel->insert($data)) {
                $this->setFlash('success', 'Checkout link created successfully!');
            } else {
                $this->setFlash('error', 'Failed to create checkout link');
            }
            $this->redirect('checkout');
        }
    }

    /**
     * Edit an existing template
     */
    public function edit($id)
    {
        $companyId = Session::get('company_id');
        $template = $this->templateModel->findOne(['id' => $id, 'company_id' => $companyId]);

        if (!$template) {
            $this->setFlash('error', 'Link not found');
            $this->redirect('checkout');
        }

        $this->view('checkout/edit', [
            'pageTitle' => 'Edit Checkout Link',
            'template' => $template,
            'csrf_token' => Security::generateCsrfToken()
        ]);
    }

    /**
     * Update checkout link
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('checkout');
        }

        $companyId = Session::get('company_id');
        $template = $this->templateModel->findOne(['id' => $id, 'company_id' => $companyId]);

        if (!$template) {
            $this->setFlash('error', 'Link not found');
            $this->redirect('checkout');
        }

        $fieldSettings = $_POST['fields'] ?? [];
        $settings = [];
        $availableFields = ['name', 'email', 'phone', 'address', 'city', 'country', 'company', 'notes'];

        foreach ($availableFields as $field) {
            $settings[$field] = [
                'visible' => isset($fieldSettings[$field]['visible']) ? true : false,
                'required' => isset($fieldSettings[$field]['required']) ? true : false
            ];
            if ($field === 'name' || $field === 'email') {
                $settings[$field]['visible'] = true;
            }
        }

        $data = [
            'title' => Security::sanitize($_POST['title'] ?? ''),
            'description' => Security::sanitize($_POST['description'] ?? ''),
            'amount' => (float) ($_POST['amount'] ?? 0),
            'allow_paypal' => isset($_POST['allow_paypal']) ? 1 : 0,
            'allow_stripe' => isset($_POST['allow_stripe']) ? 1 : 0,
            'checkout_settings' => json_encode($settings)
        ];

        if ($this->templateModel->update($id, $data)) {
            $this->setFlash('success', 'Checkout link updated successfully!');
        } else {
            $this->setFlash('error', 'Failed to update link');
        }

        $this->redirect('checkout');
    }

    /**
     * Toggle status
     */
    public function toggle($id)
    {
        $companyId = Session::get('company_id');
        $template = $this->templateModel->findOne(['id' => $id, 'company_id' => $companyId]);

        if ($template) {
            $newStatus = $template['payment_closed'] ? 0 : 1;
            $this->templateModel->updateStatus($id, $companyId, $newStatus);
            $this->setFlash('success', 'Status updated successfully');
        }

        $this->redirect('checkout');
    }

    /**
     * Delete link
     */
    public function delete($id)
    {
        $companyId = Session::get('company_id');
        $template = $this->templateModel->findOne(['id' => $id, 'company_id' => $companyId]);

        if ($template) {
            $this->templateModel->delete($id);
            $this->setFlash('success', 'Checkout link deleted');
        }

        $this->redirect('checkout');
    }
}
