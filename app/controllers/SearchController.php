<?php
/**
 * Search Controller
 * Handles global search across multiple modules
 */

class SearchController extends Controller
{
    private $clientModel;
    private $dealModel;
    private $invoiceModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->clientModel = $this->model('Client');
        $this->dealModel = $this->model('Deal');
        $this->invoiceModel = $this->model('Invoice');
    }

    /**
     * Handle global search
     */
    public function index()
    {
        $query = $_GET['q'] ?? '';
        $companyId = Session::get('company_id');

        $results = [
            'query' => $query,
            'clients' => [],
            'deals' => [],
            'invoices' => []
        ];

        if ($query) {
            $results['clients'] = $this->clientModel->searchByCompany($companyId, $query);
            $results['deals'] = $this->dealModel->searchByCompany($companyId, $query);
            $results['invoices'] = $this->invoiceModel->searchByCompany($companyId, $query);
        }

        $this->view('search/results', $results);
    }
}
