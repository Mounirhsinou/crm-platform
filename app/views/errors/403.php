<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="display-1 text-muted mb-4"><i class="bi bi-shield-lock"></i> 403</div>
            <h1 class="h2 mb-3">Access Denied</h1>
            <p class="text-muted mb-5">
                <?php echo Security::escape($message ?? 'You do not have permission to access this page.'); ?></p>
            <a href="<?php echo APP_URL; ?>" class="btn btn-primary px-4">Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>