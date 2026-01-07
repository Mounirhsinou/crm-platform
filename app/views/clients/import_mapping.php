<?php $pageTitle = 'Registry Alignment'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Field Harmonization</h1>
        <p class="text-muted small mb-0">Map your source data columns to the internal CRM structure</p>
    </div>
    <div class="col-auto">
        <span class="badge bg-light text-muted border py-2 px-3 rounded-pill x-small uppercase-xs ls-wide">Step 2 of
            3</span>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-0">
                <form action="<?php echo APP_URL; ?>/clients/processImport" method="POST">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="temp_file" value="<?php echo $temp_file; ?>">
                    <input type="hidden" name="duplicate_handling" value="<?php echo $duplicate_handling; ?>">

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 small text-uppercase ls-wide text-muted" width="40%">
                                        Internal Core Field</th>
                                    <th class="pe-4 py-3 border-0 small text-uppercase ls-wide text-muted">Detected CSV
                                        Column Source</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <?php
                                $fields = [
                                    'name' => ['label' => 'Primary Identity (Full Name)', 'required' => true, 'icon' => 'bi-person'],
                                    'company' => ['label' => 'Corporate Legal Entity', 'required' => false, 'icon' => 'bi-building'],
                                    'email' => ['label' => 'Digital Contact (Email)', 'required' => false, 'icon' => 'bi-envelope'],
                                    'phone' => ['label' => 'Direct Communication (Phone)', 'required' => false, 'icon' => 'bi-telephone'],
                                    'address' => ['label' => 'Physical Location', 'required' => false, 'icon' => 'bi-geo-alt'],
                                    'default_price' => ['label' => 'Standard Rate (Pricing)', 'required' => false, 'icon' => 'bi-currency-dollar'],
                                    'notes' => ['label' => 'Contextual Observations', 'required' => false, 'icon' => 'bi-journal-text']
                                ];

                                foreach ($fields as $id => $config):
                                    ?>
                                    <tr>
                                        <td class="ps-4 py-4 border-light">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light p-2 rounded-3 me-3">
                                                    <i class="bi <?php echo $config['icon']; ?> text-primary"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-main small">
                                                        <?php echo $config['label']; ?>
                                                    </div>
                                                    <?php if ($config['required']): ?>
                                                        <div class="x-small text-danger fw-medium mt-1">
                                                            <i class="bi bi-asterisk"></i> Mandatory Field
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="x-small text-muted mt-1">Optional Entry</div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="pe-4 py-4 border-light">
                                            <select name="mapping[<?php echo $id; ?>]"
                                                class="form-select form-select-sm border-light bg-light" <?php echo $config['required'] ? 'required' : ''; ?>>
                                                <option value="">— Skip / Omit Field —</option>
                                                <?php foreach ($headers as $index => $header):
                                                    // Smart auto-mapping
                                                    $selected = (strcasecmp($header, $config['label']) === 0 || strcasecmp($header, $id) === 0 || str_contains(strtolower($header), strtolower($id))) ? 'selected' : '';
                                                    ?>
                                                    <option value="<?php echo $index; ?>" <?php echo $selected; ?>>
                                                        Source Col [<?php echo $index + 1; ?>]:
                                                        <?php echo htmlspecialchars($header); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="p-4 bg-light bg-opacity-50 border-top d-flex justify-content-between align-items-center">
                        <a href="<?php echo APP_URL; ?>/clients/import"
                            class="btn btn-white btn-sm border text-muted px-3">
                            <i class="bi bi-arrow-left me-1"></i> Rethink Strategy
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            Proceed to Integration <i class="bi bi-lightning-charge-fill ms-1 small"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-4 p-3 bg-indigo bg-opacity-10 rounded-3 border-dashed border-primary border">
            <div class="d-flex small text-indigo align-items-center">
                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                <div>
                    <strong>Intelligence active:</strong> Our system has attempted to auto-align columns based on naming
                    conventions. Please review for accuracy before final integration.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .x-small {
        font-size: 0.75rem;
    }

    .ls-wide {
        letter-spacing: 0.1em;
    }

    .uppercase-xs {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .btn-white:hover {
        background-color: #f8fafc;
    }

    .border-dashed {
        border-style: dashed !important;
        border-width: 2px !important;
    }

    .bg-light-indigo {
        background-color: #eef2ff;
    }

    .text-indigo {
        color: #4f46e5;
    }

    .border-indigo {
        border-color: #4f46e5 !important;
    }
</style>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>