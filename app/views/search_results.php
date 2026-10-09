<?php include 'layouts/header.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Search Results</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="index.php?route=dashboard" class="btn btn-sm btn-outline-secondary">
                &larr; Back to Dashboard
            </a>
        </div>
    </div>

    <div class="alert alert-light border shadow-sm">
        <strong>Query:</strong> "<?= htmlspecialchars($_GET['q'] ?? '') ?>" 
        <?php if(!empty($_GET['year'])): ?>
            | <strong>Year:</strong> <?= htmlspecialchars($_GET['year']) ?>
        <?php endif; ?>
        <span class="float-end badge bg-primary"><?= count($files) ?> matches found</span>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($files)): ?>
                <div class="text-center py-5">
                    <h4 class="text-muted">No documents found.</h4>
                    <p>Try refining your keywords or checking a different year.</p>
                </div>
            <?php else: ?>
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" width="10%">Year</th>
                            <th width="35%">Document Details</th>
                            <th width="25%">Relevance Match</th>
                            <th class="text-center" width="30%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                        
                        <?php 
                            // DYNAMIC ICON LOGIC
                            $ext = strtolower(pathinfo($file['file_path'], PATHINFO_EXTENSION));
                            $icon_class = ($ext === 'docx') ? 'bi-file-earmark-word-fill text-primary' : 'bi-file-earmark-pdf-fill text-danger';
                        ?>

                        <tr>
                            <td class="ps-4"><span class="badge bg-secondary"><?= htmlspecialchars($file['year']) ?></span></td>
                            
                            <td>
                                <div class="d-flex align-items-center py-1">
                                    <i class="<?= $icon_class ?> me-3" style="font-size: 2.2rem; line-height: 1;"></i>
                                    <div>
                                        <strong class="d-block text-dark fs-6" style="letter-spacing: 0.5px;"><?= htmlspecialchars($file['title']) ?></strong>
                                        <span class="text-muted" style="font-size: 0.9rem;">Associated Name: <?= htmlspecialchars($file['name']) ?></span>
                                    </div>
                                </div>
                            </td>
                            
                            <td>
                                <?php 
                                    $search_term = htmlspecialchars($_GET['q'] ?? '');
                                    if (stripos($file['title'], $search_term) !== false || stripos($file['name'], $search_term) !== false) {
                                        echo '<span class="text-success"><i class="bi bi-tag-fill me-1"></i> Match in Metadata</span>';
                                    } else {
                                        echo '<span class="text-info"><i class="bi bi-file-text-fill me-1"></i> Match in Content</span>';
                                    }
                                ?>
                                <br>
                                <small class="text-muted"><?= round($file['file_size']/1024) ?> KB</small>
                            </td>

                            <td class="text-center">
                                <div class="btn-group shadow-sm">
                                    <a href="index.php?route=files/download&id=<?= $file['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="View Document">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="index.php?route=files/download&id=<?= $file['id'] ?>&action=download" class="btn btn-sm btn-outline-success" title="Download">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $file['id'] ?>)" title="Delete">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if(confirm('Are you sure you want to delete this file? This action is logged.')) {
        window.location.href = 'index.php?route=files/delete&id=' + id;
    }
}
</script>

<?php include 'layouts/footer.php'; ?>