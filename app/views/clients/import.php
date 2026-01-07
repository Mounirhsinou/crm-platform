<?php $pageTitle = 'Data Onboarding'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Bulk Migration</h1>
        <p class="text-muted small mb-0">Import your client ledger from external sources via CSV</p>
    </div>
    <div class="col-auto">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small uppercase-xs ls-wide">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/clients"
                        class="text-decoration-none">Clients</a></li>
                <li class="breadcrumb-item active">Import</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-5">
                <form action="<?php echo APP_URL; ?>/clients/import" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

                    <div class="mb-5 text-center">
                        <div class="mb-3">
                            <i class="bi bi-cloud-arrow-up display-4 text-primary opacity-25"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Stage Your Data</h5>
                        <p class="text-muted small">Select a valid .csv file to begin the mapping process</p>

                        <div class="mt-4">
                            <input type="file" class="form-control form-control-lg bg-light border-dashed" id="csv_file"
                                name="csv_file" accept=".csv" required>
                            <div class="mt-3">
                                <a href="<?php echo APP_URL; ?>/assets/samples/clients_sample.csv"
                                    class="btn btn-white btn-xs border text-muted">
                                    <i class="bi bi-download me-1"></i> Retrieve Blueprint (Sample CSV)
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold small text-uppercase ls-wide text-muted mb-3 d-block">Collision
                            Strategy</label>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="duplicate_handling" id="dup_skip"
                                    value="skip" checked>
                                <label class="btn btn-outline-light text-start p-3 w-100 h-100 border rounded-3"
                                    for="dup_skip">
                                    <div class="fw-bold text-main small mb-1">Bypass</div>
                                    <div class="x-small text-muted lh-sm">Ignore records with existing overlap.</div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="duplicate_handling" id="dup_update"
                                    value="update">
                                <label class="btn btn-outline-light text-start p-3 w-100 h-100 border rounded-3"
                                    for="dup_update">
                                    <div class="fw-bold text-main small mb-1">Merge</div>
                                    <div class="x-small text-muted lh-sm">Sync data into existing profiles.</div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="duplicate_handling" id="dup_new"
                                    value="new">
                                <label class="btn btn-outline-light text-start p-3 w-100 h-100 border rounded-3"
                                    for="dup_new">
                                    <div class="fw-bold text-main small mb-1">Replicate</div>
                                    <div class="x-small text-muted lh-sm">Create unique entries for all rows.</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-primary btn-sm py-2">
                            Initialize Mapping Registry <i class="bi bi-chevron-right ms-1 small"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-4 text-center">
            <p class="text-muted x-small">Maximum upload size restricted to server limits. File must be UTF-8 encoded.
            </p>
        </div>
    </div>
</div>

<style>
    .x-small {
        font-size: 0.75rem;
    }

    .btn-xs {
        padding: 0.25rem 0.6rem;
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

    .border-dashed {
        border-style: dashed !important;
        border-width: 2px !important;
    }

    .btn-outline-light {
        border-color: #e2e8f0 !important;
        color: inherit;
    }

    .btn-outline-light:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1 !important;
    }

    .btn-check:checked+.btn-outline-light {
        background-color: #eff6ff;
        border-color: #3b82f6 !important;
        position: relative;
    }

    .btn-check:checked+.btn-outline-light::after {
        content: '\F272';
        font-family: 'bootstrap-icons';
        position: absolute;
        top: 8px;
        right: 8px;
        color: #3b82f6;
        font-size: 0.8rem;
    }
</style>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>