<?php include 'layouts/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">System Audit Logs</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="index.php?route=audit/export&action_type=<?= htmlspecialchars($_GET['action_type'] ?? '') ?>" class="btn btn-sm btn-outline-secondary">
                Export to CSV
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="route" value="audit">
                <div class="col-md-3">
                    <label class="form-label">Action Type</label>
                    <select name="action_type" class="form-select">
                        <option value="">All Actions</option>
                        <option value="login" <?= ($_GET['action_type'] ?? '') == 'login' ? 'selected' : '' ?>>Login</option>
                        <option value="upload" <?= ($_GET['action_type'] ?? '') == 'upload' ? 'selected' : '' ?>>Upload</option>
                        <option value="download" <?= ($_GET['action_type'] ?? '') == 'download' ? 'selected' : '' ?>>Download</option>
                        <option value="delete" <?= ($_GET['action_type'] ?? '') == 'delete' ? 'selected' : '' ?>>Delete</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="date_start" class="form-control" value="<?= htmlspecialchars($_GET['date_start'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="date_end" class="form-control" value="<?= htmlspecialchars($_GET['date_end'] ?? '') ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter Logs</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>IP Address</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="5" class="text-center">No logs found for this criteria.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= $log['created_at'] ?></td>
                        <td><strong><?= htmlspecialchars($log['username'] ?? 'System') ?></strong></td>
                        <td>
                            <span class="badge bg-secondary"><?= strtoupper($log['action_type']) ?></span>
                        </td>
                        <td><?= $log['ip_address'] ?></td>
                        <td><?= htmlspecialchars($log['description']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>