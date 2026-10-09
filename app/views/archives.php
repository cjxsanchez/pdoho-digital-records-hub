<?php include 'layouts/header.php'; ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    body { font-family: 'Inter', sans-serif; background-color: #f4f7fa; }
    .content-card { border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.04); background: #ffffff; overflow: hidden; }
    .table > :not(caption) > * > * { padding: 1rem 1rem; vertical-align: middle; }
    .table-striped > tbody > tr:nth-of-type(odd) > * { background-color: rgba(0,0,0,0.01); }
    .table-hover > tbody > tr:hover > * { background-color: #f8fafc; transition: background-color 0.2s ease; }
    .folder-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
    .folder-card { transition: all 0.25s ease-in-out; border-radius: 12px; background-color: #ffffff; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 10px rgba(0,0,0,0.02); position: relative; }
    .folder-card:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.08); border-color: rgba(13, 110, 253, 0.3); }
    .folder-card-body { padding: 1.5rem; }
    .folder-icon { font-size: 3rem; color: #6c757d; filter: drop-shadow(0 2px 4px rgba(108, 117, 125, 0.3)); display: block; margin-bottom: 15px; }
    .file-icon-large { font-size: 2.2rem; line-height: 1; }
    .text-purple { color: #8b5cf6 !important; }
</style>

<div class="container-fluid pb-5">
    
    <nav aria-label="breadcrumb" class="mb-2 mt-2">
        <ol class="breadcrumb" style="font-size: 0.9rem;">
            <li class="breadcrumb-item"><a href="index.php?route=dashboard" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active fw-bold text-dark" aria-current="page">Archives</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h2 class="mb-0 fw-bold" style="color: #1e293b; letter-spacing: -0.5px;">
            <i class="bi bi-archive text-secondary me-2"></i> System Archives
        </h2>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'restored'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
                <i class="bi bi-arrow-counterclockwise me-2"></i> Item restored successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'restored_both'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
                <i class="bi bi-folder-check me-2"></i> File and its parent folder restored successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'restored_recovered'): ?>
            <div class="alert alert-info alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
                <i class="bi bi-info-circle-fill me-2"></i> File restored to the <strong>Recovered Files</strong> folder.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if(empty($files) && empty($folders)): ?>
        <div class="alert alert-light border shadow-sm d-flex align-items-center p-4 mt-3" style="border-radius: 12px;">
            <div class="bg-secondary bg-opacity-10 p-3 rounded-circle me-4 text-secondary">
                <i class="bi bi-archive fs-2"></i>
            </div>
            <div>
                <h5 class="mb-1 fw-bold text-dark">Archives are empty</h5>
                <p class="mb-0 text-muted">No folders or files have been archived yet.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if(!empty($folders)): ?>
        <h6 class="text-muted mt-4 mb-3 fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;"><i class="bi bi-folder2-open me-2"></i>Archived Folders</h6>
        <div class="folder-grid mb-4">
            <?php foreach ($folders as $folder): ?>
            <div class="folder-card h-100 text-center">
                <div class="folder-card-body">
                    <i class="bi bi-folder-fill folder-icon opacity-50"></i>
                    <h6 class="folder-title fw-bold text-dark text-truncate mb-3 text-decoration-line-through" title="<?= htmlspecialchars($folder['folder_name']) ?>" style="font-size: 1.1rem;">
                        <?= htmlspecialchars($folder['folder_name']) ?>
                    </h6>
                    <div class="d-flex flex-column text-muted" style="font-size: 0.85rem; gap: 6px;">
                        <span><i class="bi bi-file-earmark-text me-2 opacity-75"></i><?= $folder['file_count'] ?> Files</span>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top d-flex justify-content-center gap-2">
                        <a href="index.php?route=folder/restore&id=<?= $folder['id'] ?>" class="btn btn-sm btn-outline-success fw-bold" title="Restore Folder">
                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                        </a>
                        <a href="index.php?route=folder/delete&id=<?= $folder['id'] ?>" class="btn btn-sm btn-outline-danger fw-bold" onclick="return confirm('WARNING: Permanently delete this folder and all its files?');" title="Delete Permanently">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($files)): ?>
        <h6 class="text-muted mt-4 mb-3 fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;"><i class="bi bi-file-earmark-text me-2"></i>Archived Documents</h6>
        <div class="card content-card">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light" style="border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="ps-4 text-muted">Archived Document Details</th>
                            <th class="text-muted">Original Date</th>
                            <th class="text-center text-muted">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($files as $file): ?>
                        
                        <?php 
                            $ext = strtolower(pathinfo($file['file_path'], PATHINFO_EXTENSION));
                            $icon_class = 'bi-file-earmark-text-fill text-secondary'; 
                            if ($ext === 'pdf') { $icon_class = 'bi-file-earmark-pdf-fill text-danger opacity-50'; }
                            elseif (in_array($ext, ['doc', 'docx'])) { $icon_class = 'bi-file-earmark-word-fill text-primary opacity-50'; }
                            elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) { $icon_class = 'bi-file-earmark-excel-fill text-success opacity-50'; }
                            elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) { $icon_class = 'bi-file-earmark-image-fill text-purple opacity-50'; }
                        ?>

                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <i class="<?= $icon_class ?> me-3 file-icon-large"></i>
                                    <div>
                                        <strong class="d-block text-secondary text-decoration-line-through" style="font-size: 1rem; font-weight: 600; letter-spacing: 0.5px;">
                                            <?= htmlspecialchars($file['title']) ?>
                                        </strong>
                                        <span class="text-muted" style="font-size: 0.85rem;">From Folder: <?= htmlspecialchars($file['folder_name'] ?? 'Unknown') ?></span>
                                    </div>
                                </div>
                            </td>

                            <td class="text-muted fw-medium" style="font-size: 0.9rem;">
                                <?= date('M d, Y', strtotime($file['updated_at'])) ?>
                            </td>
                            
                            <td class="text-center">
                                <div class="btn-group shadow-sm">
                                    <?php if ($file['folder_archived'] == 1): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success fw-bold" onclick="openRestoreModal(<?= $file['id'] ?>)">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                        </button>
                                    <?php else: ?>
                                        <form action="index.php?route=files/restore" method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                            <input type="hidden" name="restore_type" value="direct">
                                            <button type="submit" class="btn btn-sm btn-outline-success fw-bold rounded-start-0" title="Restore Document">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <a href="index.php?route=files/delete&id=<?= $file['id'] ?>" class="btn btn-sm btn-outline-danger fw-bold" onclick="return confirm('Are you sure you want to permanently delete this file?');" title="Delete Permanently">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<div class="modal fade" id="restoreFileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <form action="index.php?route=files/restore" method="POST">
                <div class="modal-header text-dark" style="background-color: #0dcaf0; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-arrow-counterclockwise me-2"></i>Restore File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="file_id" id="restore_file_id" value="">
                    
                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4" style="border-radius: 10px;">
                        <i class="bi bi-exclamation-triangle-fill fs-2 me-3 text-warning"></i>
                        <div>
                            <strong class="text-dark">Folder is Archived</strong><br>
                            <span class="text-muted" style="font-size: 0.9rem;">This file belongs to a folder that is currently in the archives. Choose how you want to restore it:</span>
                        </div>
                    </div>

                    <div class="d-grid gap-3">
                        <button type="submit" name="restore_type" value="file_with_folder" class="btn btn-primary p-3 fw-bold shadow-sm d-flex align-items-center text-start" style="border-radius: 8px;">
                            <i class="bi bi-folder-check fs-3 me-3 text-light"></i>
                            <div>
                                <div>Restore File + Folder (Recommended)</div>
                                <div class="fw-normal" style="font-size: 0.8rem; opacity: 0.8;">Restores the file and makes its parent folder visible again.</div>
                            </div>
                        </button>
                        
                        <button type="submit" name="restore_type" value="file_only" class="btn btn-outline-secondary p-3 fw-bold d-flex align-items-center text-start" style="border-radius: 8px;">
                            <i class="bi bi-file-earmark-arrow-up fs-3 me-3"></i>
                            <div>
                                <div>Restore File Only</div>
                                <div class="fw-normal" style="font-size: 0.8rem; opacity: 0.8;">Restores the file and moves it to the "Recovered Files" folder.</div>
                            </div>
                        </button>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary fw-medium" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openRestoreModal(fileId) {
        document.getElementById('restore_file_id').value = fileId;
        var myModal = new bootstrap.Modal(document.getElementById('restoreFileModal'));
        myModal.show();
    }
</script>

<?php include 'layouts/footer.php'; ?>