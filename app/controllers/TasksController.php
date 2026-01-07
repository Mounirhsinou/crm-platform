<?php
/**
 * Task Controller
 * Handles task management
 */

class TasksController extends Controller
{
    private $taskModel;
    private $clientModel;
    private $dealModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->taskModel = $this->model('Task');
        $this->clientModel = $this->model('Client');
        $this->dealModel = $this->model('Deal');
    }

    /**
     * List all tasks
     */
    public function index()
    {
        $this->requirePermission('tasks', 'view');
        $status = $_GET['status'] ?? null;
        $companyId = Session::get('company_id');
        $tasks = $this->taskModel->getWithRelationsByCompany($companyId, $status);

        $this->view('tasks/index', [
            'tasks' => $tasks,
            'currentStatus' => $status
        ]);
    }

    /**
     * Show create task form
     */
    public function create()
    {
        $this->requirePermission('tasks', 'create');
        $companyId = Session::get('company_id');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        } else {
            $this->view('tasks/create', [
                'clients' => $this->clientModel->getByCompany($companyId),
                'deals' => $this->dealModel->getByCompany($companyId),
                'csrf_token' => Security::generateCsrfToken(),
                'clientId' => $_GET['client_id'] ?? null,
                'dealId' => $_GET['deal_id'] ?? null
            ]);
        }
    }

    /**
     * Handle task creation
     */
    private function handleCreate()
    {
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('tasks/create');
        }

        $companyId = Session::get('company_id');
        $userId = $this->getUserId();
        $data = [
            'company_id' => $companyId,
            'user_id' => $userId,
            'client_id' => $_POST['client_id'] ?: null,
            'deal_id' => $_POST['deal_id'] ?: null,
            'title' => Security::sanitize($_POST['title']),
            'description' => Security::sanitize($_POST['description']),
            'due_date' => $_POST['due_date'],
            'priority' => $_POST['priority'] ?? 'medium',
            'status' => 'pending'
        ];

        // Validation
        $validator = new Validator();
        $validator->required('title', $data['title'], 'Title')
            ->required('due_date', $data['due_date'], 'Due Date');

        if ($validator->fails()) {
            $this->view('tasks/create', [
                'errors' => $validator->getErrors(),
                'data' => $data,
                'clients' => $this->clientModel->getByCompany($companyId),
                'deals' => $this->dealModel->getByCompany($companyId),
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        $taskId = $this->taskModel->insert($data);

        if ($taskId) {
            // Log activity
            Activity::logTaskCreated($companyId, $userId, $data['client_id'], $data['title'], $data['due_date']);
            $this->setFlash('success', 'Task created successfully');
            $this->redirect('tasks');
        } else {
            $this->setFlash('error', 'Failed to create task');
            $this->redirect('tasks/create');
        }
    }

    /**
     * Show edit task form
     */
    public function edit($id)
    {
        $this->requirePermission('tasks', 'edit');
        $companyId = Session::get('company_id');
        $task = $this->taskModel->getWithRelationByCompany($id, $companyId);

        if (!$task) {
            $this->setFlash('error', 'Task not found');
            $this->redirect('tasks');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdate($id, $task);
        } else {
            $this->view('tasks/edit', [
                'task' => $task,
                'clients' => $this->clientModel->getByCompany($companyId),
                'deals' => $this->dealModel->getByCompany($companyId),
                'csrf_token' => Security::generateCsrfToken()
            ]);
        }
    }

    /**
     * Handle task update
     */
    private function handleUpdate($id, $task)
    {
        $companyId = Session::get('company_id');
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect("tasks/edit/{$id}");
        }

        $userId = $this->getUserId();
        $data = [
            'client_id' => $_POST['client_id'] ?: null,
            'deal_id' => $_POST['deal_id'] ?: null,
            'title' => Security::sanitize($_POST['title']),
            'description' => Security::sanitize($_POST['description']),
            'due_date' => $_POST['due_date'],
            'priority' => $_POST['priority'],
            'status' => $_POST['status']
        ];

        // Validation
        $validator = new Validator();
        $validator->required('title', $data['title'], 'Title')
            ->required('due_date', $data['due_date'], 'Due Date');

        if ($validator->fails()) {
            $this->view('tasks/edit', [
                'errors' => $validator->getErrors(),
                'task' => array_merge($task, $data),
                'clients' => $this->clientModel->getByCompany($companyId),
                'deals' => $this->dealModel->getByCompany($companyId),
                'csrf_token' => Security::generateCsrfToken()
            ]);
            return;
        }

        if ($this->taskModel->update($id, $data)) {
            // Log activity if status changed to completed
            if ($task['status'] !== 'completed' && $data['status'] === 'completed') {
                Activity::logTaskCompleted($companyId, $userId, $data['client_id'], $data['title']);
            }
            $this->setFlash('success', 'Task updated successfully');
            $this->redirect('tasks');
        } else {
            $this->setFlash('error', 'Failed to update task');
            $this->redirect("tasks/edit/{$id}");
        }
    }

    /**
     * Mark task as completed
     */
    public function finish($id)
    {
        $this->requirePermission('tasks', 'edit');
        $companyId = Session::get('company_id');
        $userId = $this->getUserId();
        $task = $this->taskModel->findOne(['id' => $id, 'company_id' => $companyId]);

        if ($task) {
            if ($this->taskModel->update($id, ['status' => 'completed'])) {
                Activity::logTaskCompleted($companyId, $userId, $task['client_id'], $task['title']);
                $this->setFlash('success', 'Task marked as completed');
            } else {
                $this->setFlash('error', 'Failed to update task');
            }
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?: APP_URL . '/tasks'));
        exit;
    }

    /**
     * Delete task
     */
    public function delete($id)
    {
        $this->requirePermission('tasks', 'delete');
        $companyId = Session::get('company_id');
        $task = $this->taskModel->findOne(['id' => $id, 'company_id' => $companyId]);

        if ($task) {
            if ($this->taskModel->delete($id)) {
                $this->setFlash('success', 'Task deleted successfully');
            } else {
                $this->setFlash('error', 'Failed to delete task');
            }
        }

        $this->redirect('tasks');
    }

    /**
     * Export tasks to CSV
     */
    public function export()
    {
        $this->requirePermission('tasks', 'export');
        $companyId = Session::get('company_id');
        $tasks = $this->taskModel->getWithRelationsByCompany($companyId);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="tasks_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Title', 'Description', 'Client', 'Deal', 'Due Date', 'Priority', 'Status', 'Created At']);

        foreach ($tasks as $task) {
            fputcsv($output, [
                $task['title'],
                $task['description'],
                $task['client_name'] ?? 'N/A',
                $task['deal_title'] ?? 'N/A',
                $task['due_date'],
                ucfirst($task['priority']),
                ucfirst($task['status']),
                $task['created_at']
            ]);
        }

        fclose($output);
        exit;
    }
}
