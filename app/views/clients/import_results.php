<?php $pageTitle = 'Migration Conclusion'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Integration Results</h1>
        <p class="text-muted small mb-0">Analysis and summary of the data synchronization process</p>
    </div>
    <div class="col-auto">
        <span
            class="badge bg-success bg-opacity-10 text-success border py-2 px-3 rounded-pill x-small uppercase-xs ls-wide">Finalized</span>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-5">
                <div class="row g-4 text-center">
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-10">
                            <div class="h3 fw-bold mb-1 text-primary"><?php echo $stats['success']; ?></div>
                            <div class="x-small text-uppercase ls-wide fw-bold opacity-75">New Profiles</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-10">
                            <div class="h3 fw-bold mb-1 text-success"><?php echo $stats['updated']; ?></div>
                            <div class="x-small text-uppercase ls-wide fw-bold opacity-75">Merged</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 bg-warning bg-opacity-10 border border-warning border-opacity-10">
                            <div class="h3 fw-bold mb-1 text-warning"><?php echo $stats['skipped']; ?></div>
                            <div class="x-small text-uppercase ls-wide fw-bold opacity-75">Bypassed</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-10">
                            <div class="h3 fw-bold mb-1 text-danger"><?php echo $stats['errors']; ?></div>
                            <div class="x-small text-uppercase ls-wide fw-bold opacity-75">Clashes</div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 text-center">
                    <div class="mb-4">
                        <i class="bi bi-file-earmark-check display-5 text-success opacity-25"></i>
                    </div>
                    <p class="text-muted small mb-4">Migration registry has been successfully synchronized with your
                        core database.</p>
                    <a href="<?php echo APP_URL; ?>/clients" class="btn btn-primary btn-sm px-5 py-2">
                        Navigate to Fleet Registry <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="card border-0 shadow-sm border-start border-4 border-danger">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h6 class="mb-0 fw-bold text-danger d-flex align-items-center">
                        <i class="bi bi-exclamation-octagon me-2"></i>
                        Capture Log: Encountered Anomalies (<?php echo count($errors); ?>)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($errors as $error): ?>
                            <div class="list-group-item bg-light-danger border-light py-3 px-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 opacity-50"><i class="bi bi-bug"></i></div>
                                    <div class="x-small fw-medium text-danger"><?php echo htmlspecialchars($error); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-2 px-4">
                    <div class="x-small text-muted">Review the anomalies above for manual correction.</div>
                </div>
            </div>
        <?php endif; ?>
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

    .bg-light-danger {
        background-color: #fef2f2;
    }
</style>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>