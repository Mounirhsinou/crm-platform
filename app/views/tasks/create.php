<?php $pageTitle = 'Initiate Task'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">New Assignment</h1>
        <p class="text-muted small mb-0">Define a new actionable item for your workflow pipeline</p>
    </div>
    <div class="col-auto">
        <a href="<?php echo APP_URL; ?>/tasks" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Registry
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <form action="<?php echo APP_URL; ?>/tasks/create" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="row g-4">
                <!-- Task Core -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="bi bi-pencil-square me-2 text-primary"></i>
                                Task Definition
                            </h6>

                            <div class="mb-4">
                                <label for="title" class="form-label small fw-medium">Primary Directive <span
                                        class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>"
                                    id="title" name="title" value="<?php echo $data['title'] ?? ''; ?>"
                                    placeholder="What needs to be done?" required>
                                <?php if (isset($errors['title'])): ?>
                                    <div class="invalid-feedback small"><?php echo $errors['title']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-0">
                                <label for="description" class="form-label small fw-medium">Extended Context</label>
                                <textarea class="form-control bg-light border-light" id="description" name="description"
                                    rows="5"
                                    placeholder="Detailed instructions or background information..."><?php echo $data['description'] ?? ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="bi bi-link-45deg me-2 text-primary"></i>
                                Entity Linkage
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="client_id" class="form-label small fw-medium">Client Account</label>
                                    <select name="client_id" id="client_id" class="form-select bg-white border">
                                        <option value="">No Client Linked</option>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?php echo $client['id']; ?>" <?php echo (($data['client_id'] ?? $clientId) == $client['id']) ? 'selected' : ''; ?>>
                                                <?php echo Security::escape($client['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="deal_id" class="form-label small fw-medium">Commercial Deal</label>
                                    <select name="deal_id" id="deal_id" class="form-select bg-white border">
                                        <option value="">No Deal Linked</option>
                                        <?php foreach ($deals as $deal): ?>
                                            <option value="<?php echo $deal['id']; ?>" <?php echo (($data['deal_id'] ?? $dealId) == $deal['id']) ? 'selected' : ''; ?>>
                                                <?php echo Security::escape($deal['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scheduling & Action -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="bi bi-clock-history me-2 text-primary"></i>
                                Logistics
                            </h6>

                            <div class="mb-4">
                                <label for="due_date" class="form-label small fw-medium">Deadline <span
                                        class="text-danger">*</span></label>
                                <input type="date"
                                    class="form-control bg-light border-light <?php echo isset($errors['due_date']) ? 'is-invalid' : ''; ?>"
                                    id="due_date" name="due_date"
                                    value="<?php echo $data['due_date'] ?? date('Y-m-d'); ?>" required>
                                <?php if (isset($errors['due_date'])): ?>
                                    <div class="invalid-feedback small"><?php echo $errors['due_date']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-0">
                                <label class="form-label small fw-medium">Priority Level</label>
                                <div class="d-flex flex-column gap-2">
                                    <div class="form-check p-2 border rounded-3 bg-white d-flex align-items-center">
                                        <input class="form-check-input ms-0 me-2" type="radio" name="priority"
                                            id="p_low" value="low" <?php echo (($data['priority'] ?? '') === 'low') ? 'checked' : ''; ?>>
                                        <label class="form-check-label small fw-medium text-muted" for="p_low">Low
                                            Priority</label>
                                    </div>
                                    <div class="form-check p-2 border rounded-3 bg-white d-flex align-items-center">
                                        <input class="form-check-input ms-0 me-2" type="radio" name="priority"
                                            id="p_medium" value="medium" <?php echo (($data['priority'] ?? 'medium') === 'medium') ? 'checked' : ''; ?>>
                                        <label class="form-check-label small fw-medium text-primary"
                                            for="p_medium">Standard (Medium)</label>
                                    </div>
                                    <div class="form-check p-2 border rounded-3 bg-white d-flex align-items-center">
                                        <input class="form-check-input ms-0 me-2" type="radio" name="priority"
                                            id="p_high" value="high" <?php echo (($data['priority'] ?? '') === 'high') ? 'checked' : ''; ?>>
                                        <label class="form-check-label small fw-medium text-danger fw-bold"
                                            for="p_high">Critical (High)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                        <div class="card-body p-4 text-center">
                            <h6 class="fw-bold mb-3 small text-uppercase ls-wide text-muted">Finalize</h6>
                            <p class="small text-muted mb-4 px-2">Assigned tasks appear in the dashboard and global
                                calendar for tracking.</p>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-sm py-2">
                                    <i class="bi bi-plus-circle me-1"></i> Register Task
                                </button>
                                <a href="<?php echo APP_URL; ?>/tasks"
                                    class="btn btn-white btn-sm border py-2 text-muted">
                                    Abort Entry
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .ls-wide {
        letter-spacing: 0.1em;
    }

    .btn-white:hover {
        background-color: #f8fafc;
    }

    .form-check-input:checked+.form-check-label {
        font-weight: 600;
    }
</style>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>