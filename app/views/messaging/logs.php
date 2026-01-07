<?php $pageTitle = 'Messaging Logs'; ?>
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h4 mb-1">Messaging Logs</h1>
        <p class="text-muted small mb-0">Transmission history of all system-sent messages</p>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Channel</th>
                    <th>Recipient</th>
                    <th>Message Snippet</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted opacity-50">
                                <i class="bi bi-clock-history d-block fs-2 mb-3"></i>
                                <p class="mb-0">No messaging history found.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="ps-4">
                                <?php if ($log['type'] === 'sms'): ?>
                                    <span class="badge badge-soft-primary">
                                        <i class="bi bi-chat-left-text me-1"></i> SMS
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-soft-success">
                                        <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code class="small text-main bg-light px-2 py-1 rounded">
                                            <?php echo Security::escape($log['recipient']); ?>
                                        </code>
                            </td>
                            <td>
                                <div class="text-muted small text-truncate" style="max-width: 300px;"
                                    title="<?php echo Security::escape($log['message']); ?>">
                                    <?php echo Security::escape($log['message']); ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($log['status'] === 'sent'): ?>
                                    <div class="d-flex align-items-center text-success small fw-medium">
                                        <i class="bi bi-check2-all me-2"></i> Delivered
                                    </div>
                                <?php elseif ($log['status'] === 'failed'): ?>
                                    <div class="d-flex align-items-center text-danger small fw-medium"
                                        title="<?php echo Security::escape($log['error_message']); ?>">
                                        <i class="bi bi-exclamation-circle me-2"></i> Failed
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center text-warning small fw-medium">
                                        <i class="bi bi-clock me-2"></i> Pending
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4 small text-muted">
                                <?php echo date('M d, Y', strtotime($log['created_at'])); ?>
                                <span
                                    class="d-block opacity-50"><?php echo date('H:i', strtotime($log['created_at'])); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>